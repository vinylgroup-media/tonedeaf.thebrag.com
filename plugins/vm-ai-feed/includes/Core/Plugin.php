<?php

/**
 * Main Plugin Controller
 *
 * Handles WordPress integration, admin menus, AJAX handlers, and script enqueuing
 * for the AI Feed plugin.
 *
 * @package VM\AIFeed
 * @since 1.0.0
 */

namespace VM\AIFeed\Core;

use VM\AIFeed\Admin\Tables\AIFeedArticlesTable;
use VM\AIFeed\Admin\Tables\AIFeedQueueTable;
use VM\AIFeed\Admin\Views\AIFeedArticleView;
use VM\AIFeed\Admin\Views\AIFeedQueueView;
use VM\AIFeed\Admin\Pages\AIFeedAIConfigs;
use VM\AIFeed\Content\AIMarkdown;
use VM\AIFeed\Helpers\AIFeedUIHelpers;

/**
 * Main plugin controller class
 *
 * Singleton class that manages the entire plugin lifecycle including:
 * - Admin menu registration
 * - AJAX endpoint handling for articles, research, and publishing
 * - Script and style enqueuing
 * - Page routing and display
 *
 * @since 1.0.0
 */
class AIFeed
{

	/**
	 * Default category IDs for AI-generated posts
	 *
	 * @var array
	 */
	private const DEFAULT_CATEGORIES = [11, 5];

	/**
	 * Maximum revisions to fetch per request
	 *
	 * @var int
	 */
	private const REVISIONS_PER_PAGE = 100;

	/**
	 * Maximum pages to fetch when retrieving revisions
	 *
	 * @var int
	 */
	private const MAX_REVISION_PAGES = 100;

	private static AIFeed $_instance;
	private bool $isViewingItem       = false;
	private string $viewType          = '';
	private string $itemId            = '';
	private string $researchRequestId = '';

	public static function getInstance(): self
	{
		if (! isset(self::$_instance) || ! (self::$_instance instanceof self)) {
			$c               = __CLASS__;
			self::$_instance = new $c();
		}

		return self::$_instance;
	}

	public function init(): void
	{
		add_action('admin_menu', [$this, 'addAdminMenu']);
		add_action('admin_enqueue_scripts', [$this, 'enqueueAdminScripts']);
		add_action('admin_init', [$this, 'handleCustomPages']);
		add_action('admin_init', [$this, 'handleSettingsSave']);
		add_action('admin_init', [$this, 'handleAIConfigFormSubmissions']);
		add_action('wp_ajax_publish_article', [$this, 'handle_publish_article']);
		add_action('wp_ajax_generate_article', [$this, 'handle_generate_article']);
		add_action('wp_ajax_delete_ai_draft', [$this, 'handle_delete_ai_draft']);
		add_action('wp_ajax_refresh_article_content', [$this, 'handle_refresh_article_content']);
		add_action('wp_ajax_delete_research', [$this, 'handle_delete_research']);
		add_action('wp_ajax_delete_article', [$this, 'handle_delete_article']);
		add_action('wp_ajax_restart_research', [$this, 'handle_restart_research']);
		add_action('wp_ajax_get_article_revisions', [$this, 'handle_get_article_revisions']);
		add_action('wp_ajax_process_revision_content', [$this, 'handle_process_revision_content']);
		add_action('wp_ajax_check_article_by_research', [$this, 'handle_check_article_by_research']);
		add_action('wp_ajax_get_ai_configs', [$this, 'handle_get_ai_configs']);
	}

	/**
	 * Handle publishing an AI article to WordPress
	 */
	function handle_publish_article()
	{
		// Verify nonce
		if (! check_ajax_referer('publish_article_nonce', 'nonce', false)) {
			wp_send_json_error('Invalid security token', 403);
		}

		// Check user permissions
		if (! current_user_can('publish_posts')) {
			wp_send_json_error('Insufficient permissions to publish posts', 403);
		}

		// Get article ID and check for existing published post
		$article_id        = sanitize_text_field($_POST['article_id'] ?? '');
		$published_post_id = sanitize_text_field($_POST['published_post_id'] ?? '');

		if (empty($article_id)) {
			wp_send_json_error('Article ID is required', 400);
		}

		// Fetch article data from API
		$article = $this->fetchArticleById($article_id);
		if (empty($article)) {
			wp_send_json_error('Article not found', 404);
		}

		// Check if this is a republish operation
		$is_republish     = ! empty($published_post_id);
		$existing_post_id = null;

		if ($is_republish) {
			// Verify the published post exists and belongs to this article
			$existing_post = get_post($published_post_id);
			if (! $existing_post) {
				wp_send_json_error('Published post not found', 404);
			}

			$existing_article_id = get_post_meta($published_post_id, '_ai_article_id', true);
			if ($existing_article_id !== $article_id) {
				wp_send_json_error('Post does not belong to this article', 400);
			}

			$existing_post_id = $published_post_id;
		} else {
			// Check if article is already published (prevent duplicate publishing)
			$existing_posts = get_posts(
				[
					'meta_query'  => [
						[
							'key'     => '_ai_article_id',
							'value'   => $article_id,
							'compare' => '=',
						],
					],
					'post_type'   => 'post',
					'post_status' => 'any',
					'numberposts' => 1,
				]
			);

			if (! empty($existing_posts)) {
				wp_send_json_error('Draft already exists. Use the unlock option to update.', 409);
			}
		}

		// Prepare post data using new API schema (store Markdown + heading)
		$headline = isset($article['post_content_headline']) ? trim((string) $article['post_content_headline']) : '';
		$bodyMd   = isset($article['post_content']) ? (string) $article['post_content'] : '';
		if ($headline !== '') {
			// Escape the headline to prevent Markdown injection attacks
			$escapedHeadline         = AIMarkdown::escapeMarkdown($headline);
			$article['post_content'] = '### ' . $escapedHeadline . "\n\n" . ltrim($bodyMd);
		} else {
			$article['post_content'] = $bodyMd;
		}

		// Get editor type setting
		$config      = AIFeedConfig::getInstance();
		$editor_type = $config->getEditorType();

		// Convert content based on editor type
		// Both conversion methods handle sanitization internally:
		// - Classic editor: Markdown converted to HTML via league/commonmark (strips raw HTML), then sanitized via wp_kses()
		// - Block editor: Markdown parsed and converted to block markup with esc_html() for all content
		if ($editor_type === 'blocks') {
			// Convert markdown to WordPress blocks for Gutenberg editor
			$post_content = \VM\AIFeed\Content\AIBlockConverter::convertToBlocks($article['post_content']);
		} else {
			// Classic editor: Convert markdown to HTML with embeds for visual/HTML view
			$post_content = AIMarkdown::renderWithEmbeds($article['post_content']);
		}

		$post_data = [
			'post_title'    => wp_strip_all_tags($article['post_title']),
			'post_content'  => $post_content,
			'post_excerpt'  => wp_strip_all_tags($article['post_excerpt']),
			'post_status'   => $article['status'] ?? 'draft',
			'post_author'   => $article['post_author'] ?? get_current_user_id(),
			'post_type'     => 'post',
			'post_category' => $this->getDefaultCategory(),
		];

		// Set post name if provided
		if (! empty($article['post_name'])) {
			$post_data['post_name'] = sanitize_title($article['post_name']);
		}

		// Insert or update the post
		if ($is_republish && $existing_post_id) {
			// Update existing post
			$post_data['ID'] = $existing_post_id;
			$post_id         = wp_update_post($post_data, true);
			$action          = 'updated';
		} else {
			// Create new post
			$post_id = wp_insert_post($post_data, true);
			$action  = 'created';
		}

		if (is_wp_error($post_id)) {
			wp_send_json_error('Failed to ' . $action . ' post: ' . $post_id->get_error_message(), 500);
		}

		// Update article status in API (optional)
		$this->updateArticleStatus($article_id, 'published');

		// Store additional metadata from the API response
		if (! empty($article['id'])) {
			update_post_meta($post_id, '_ai_article_id', sanitize_text_field($article['id']));
		}
		if (! empty($article['runId'])) {
			update_post_meta($post_id, '_ai_run_id', sanitize_text_field($article['runId']));
		}
		if (! empty($article['source'])) {
			update_post_meta($post_id, '_ai_source', esc_url_raw($article['source']));
		}
		if (! empty($article['completed_time'])) {
			update_post_meta($post_id, '_ai_completed_time', sanitize_text_field($article['completed_time']));
		}

		// Populate Yoast SEO fields
		// Basic SEO fields
		if (! empty($article['seo_title'])) {
			update_post_meta($post_id, '_yoast_wpseo_title', sanitize_text_field($article['seo_title']));
		}
		if (! empty($article['seo_metadesc'])) {
			update_post_meta($post_id, '_yoast_wpseo_metadesc', sanitize_textarea_field($article['seo_metadesc']));
		}
		if (! empty($article['seo_focus_keyword'])) {
			update_post_meta($post_id, '_yoast_wpseo_focuskw', sanitize_text_field($article['seo_focus_keyword']));
		}

		// Open Graph (Facebook) fields
		if (! empty($article['seo_og_title'])) {
			update_post_meta($post_id, '_yoast_wpseo_opengraph-title', sanitize_text_field($article['seo_og_title']));
		}
		if (! empty($article['seo_og_description'])) {
			update_post_meta($post_id, '_yoast_wpseo_opengraph-description', sanitize_textarea_field($article['seo_og_description']));
		}

		// Twitter fields
		if (! empty($article['seo_twitter_title'])) {
			update_post_meta($post_id, '_yoast_wpseo_twitter-title', sanitize_text_field($article['seo_twitter_title']));
		}
		if (! empty($article['seo_twitter_description'])) {
			update_post_meta($post_id, '_yoast_wpseo_twitter-description', sanitize_textarea_field($article['seo_twitter_description']));
		}

		// Return success with post URL and metadata
		$post_url = get_permalink($post_id);
		$message  = $is_republish ? 'Draft updated successfully!' : 'Draft created successfully!';

		wp_send_json_success(
			[
				'post_id'      => $post_id,
				'post_url'     => $post_url,
				'message'      => $message,
				'action'       => $action,
				'is_republish' => $is_republish,
				'metadata'     => [
					'run_id'         => $article['runId'] ?? null,
					'source'         => $article['source'] ?? null,
					'completed_time' => $article['completed_time'] ?? null,
					'status'         => $article['status'] ?? null,
				],
			]
		);
	}

	/**
	 * Fetch article by ID from API
	 */
	private function fetchArticleById(string $article_id): array
	{
		// Get configurable API URL
		$config  = AIFeedConfig::getInstance();
		$api_url = $config->getApiUrl('api/articles/' . $article_id);
		$api_key = $config->getApiKey();

		$response = wp_remote_get(
			$api_url,
			[
				'headers' => [
					'Authorization' => 'Bearer ' . $api_key,
					'Content-Type'  => 'application/json',
				],
				'timeout' => 30,
			]
		);

		if (is_wp_error($response)) {
			return [];
		}

		$body = wp_remote_retrieve_body($response);
		$data = json_decode($body, true);

		return $data['data'] ?? [];
	}

	/**
	 * Fetch article ID associated with a research request
	 */
	private function getArticleIdByResearchRequestId(string $research_request_id): string
	{
		if (empty($research_request_id)) {
			return '';
		}

		$config  = AIFeedConfig::getInstance();
		$api_url = $config->getApiUrl('api/articles?research_request_id=' . urlencode($research_request_id));
		$api_key = $config->getApiKey();

		$response = wp_remote_get(
			$api_url,
			[
				'headers' => [
					'Authorization' => 'Bearer ' . $api_key,
					'Content-Type'  => 'application/json',
				],
				'timeout' => 30,
			]
		);

		if (is_wp_error($response)) {
			return '';
		}

		$response_code = wp_remote_retrieve_response_code($response);
		if ($response_code !== 200) {
			return '';
		}

		$body = wp_remote_retrieve_body($response);
		$data = json_decode($body, true);

		if (json_last_error() !== JSON_ERROR_NONE) {
			return '';
		}

		if (isset($data['success']) && $data['success'] === true && isset($data['data'])) {
			if (isset($data['data']['articles']) && is_array($data['data']['articles']) && ! empty($data['data']['articles'])) {
				return (string) ($data['data']['articles'][0]['id'] ?? '');
			}

			if (is_array($data['data']) && ! empty($data['data'])) {
				return (string) ($data['data'][0]['id'] ?? '');
			}
		}

		return '';
	}

	/**
	 * Get default category for AI articles
	 *
	 * @since 1.0.0
	 * @return array Array of category IDs
	 */
	private function getDefaultCategory(): array
	{
		return self::DEFAULT_CATEGORIES;
	}

	/**
	 * Update article status in API
	 */
	private function updateArticleStatus(string $article_id, string $status): void
	{
		// Get configurable API URL
		$config  = AIFeedConfig::getInstance();
		$api_url = $config->getApiUrl('api/articles/' . $article_id);
		$api_key = $config->getApiKey();

		wp_remote_post(
			$api_url,
			[
				'method'  => 'PUT',
				'headers' => [
					'Authorization' => 'Bearer ' . $api_key,
					'Content-Type'  => 'application/json',
				],
				'body'    => json_encode(['status' => $status]),
				'timeout' => 30,
			]
		);
	}

	/**
	 * Add admin menu page
	 */
	public function addAdminMenu(): void
	{
		add_menu_page(
			'AI Tools Dashboard',
			'AI Tools',
			'manage_options',
			'vm-ai',
			[$this, 'index'],
			'dashicons-rss',
			30
		);

		add_submenu_page(
			'vm-ai',
			'Articles',
			'Articles',
			'manage_options',
			'vm-ai-feed-articles',
			[$this, 'renderArticlesPage'],
			2
		);

		add_submenu_page(
			'vm-ai',
			'Writers',
			'Writers',
			'manage_options',
			'vm-ai-feed-ai-configs',
			[$this, 'renderAIConfigsPage'],
			5
		);

		add_submenu_page(
			'vm-ai',
			'Settings',
			'Settings',
			'manage_options',
			'vm-ai-feed-settings',
			[$this, 'renderSettingsPage'],
			6
		);

		remove_submenu_page('vm-ai', 'vm-ai');
	}

	/**
	 * Handle custom page routing for individual post/article views
	 */
	public function handleCustomPages(): void
	{
		if (! isset($_GET['page'])) {
			return;
		}

		$page = sanitize_text_field($_GET['page']);

		// Handle individual item views
		if (isset($_GET['action'])) {
			$action = sanitize_text_field($_GET['action']);

			if ($page === 'vm-ai-feed-articles' && $action === 'view-article') {
				$this->isViewingItem = true;
				$this->viewType      = 'article';
				// Only use research_request_id for viewing articles
				$research_request_id = sanitize_text_field($_GET['research_request_id'] ?? '');

				if (! empty($research_request_id)) {
					$this->itemId            = $research_request_id;
					$this->researchRequestId = $research_request_id;
				}
			}

			// Handle research request views via article page (for unified workflow)
			if ($page === 'vm-ai-feed-articles' && $action === 'view-research') {
				$this->isViewingItem = true;
				$this->viewType      = 'research';
				$this->itemId        = sanitize_text_field($_GET['research_request_id'] ?? '');
			}
		}
	}

	/**
	 * Enqueue admin scripts and styles
	 */
	public function enqueueAdminScripts($hook): void
	{
		// DEBUG: Print hook to console
		echo "<script>console.log('Current Admin Hook: " . esc_js($hook) . "');</script>";

		if (
			$hook !== 'toplevel_page_vm-ai-feed' &&
			$hook !== 'ai-tools_page_vm-ai-feed-articles' &&
			$hook !== 'ai-tools_page_vm-ai-feed-ai-configs' &&
			$hook !== 'ai-tools_page_vm-ai-feed-settings' &&
			$hook !== 'ai-tools_page_vm-ai-generate-article'
		) {
			return;
		}

		wp_enqueue_style('wp-list-table');
		wp_enqueue_script('wp-list-table');

		// Enqueue custom admin styles
		// Fix path: go up from includes/Core to root
		wp_enqueue_style(
			'vm-ai-feed-admin',
			plugins_url('../../css/admin-styles.css', __FILE__) . '?cache=' . time(),
			[],
			'1.0.0'
		);

		// Enqueue DOMPurify for client-side XSS protection
		wp_enqueue_script(
			'dompurify',
			'https://cdn.jsdelivr.net/npm/dompurify@3.0.6/dist/purify.min.js',
			[],
			'3.0.6',
			true
		);

		wp_enqueue_script(
			'ai-chat',
			plugins_url('../../js/ai-chat.js', __FILE__) . '?cache=' . time(),
			['jquery', 'dompurify'],
			'1.0.0',
			true
		);

		// Pass data to JavaScript
		wp_localize_script(
			'ai-chat',
			'aiChat',
			[
				'ajax_url'      => admin_url('admin-ajax.php'),
				'nonce'         => wp_create_nonce('ai_chat_nonce'),
				'publish_nonce' => wp_create_nonce('publish_article_nonce'),
				'delete_nonce'  => wp_create_nonce('delete_ai_draft_nonce'),
				'api_key'       => get_option('your_plugin_ai_api_key'), // Store this securely
			]
		);

		// Add global ajaxurl for inline scripts
		echo '<script>var ajaxurl = "' . admin_url('admin-ajax.php') . '";</script>';
	}

	public function index(): void
	{
		echo 'Hello World';
	}

	/**
	 * Render articles page content
	 */
	public function renderArticlesPage(): void
	{
		if ($this->isViewingItem && ($this->viewType === 'article' || $this->viewType === 'research')) {
			$article_view = new AIFeedArticleView();
			$article_id   = sanitize_text_field($_GET['article_id'] ?? '');

			// Use the research context workflow view for both articles and research requests
			$research_request_id = $this->viewType === 'article' ? $this->researchRequestId : $this->itemId;

			// For view-research action, ALWAYS show workflow view
			if ($this->viewType === 'research') {
				if (! empty($research_request_id)) {
					$article_view->displayWorkflow($research_request_id);
					return;
				}
				wp_die('Research Request ID is required to view workflow.');
			}

			// For view-article action, try to find article, fallback to workflow
			if (empty($article_id) && ! empty($research_request_id)) {
				$article_id = $this->getArticleIdByResearchRequestId($research_request_id);
			}

			if (! empty($article_id)) {
				$article_view->display($article_id);
				return;
			}

			if (! empty($research_request_id)) {
				$article_view->displayWorkflow($research_request_id);
				return;
			}

			wp_die('Research Request ID is required to view workflow.');
		} else {
			$articles_table = new AIFeedArticlesTable();

			echo '<div class="wrap">';
			echo '<h1>Articles</h1>';

			// Display the generate form (outside of the table form)
			$articles_table->displayGenerateForm();

			echo '<form method="post">';
			$articles_table->displayTable();
			echo '</form>';
			echo '</div>';
		}
	}

	/**
	 * Handle AI config form submissions early
	 */
	public function handleAIConfigFormSubmissions(): void
	{
		// Only handle on AI configs page
		if (! isset($_GET['page']) || $_GET['page'] !== 'vm-ai-feed-ai-configs') {
			return;
		}

		$ai_configs = new AIFeedAIConfigs();
		$ai_configs->handleFormSubmission();
	}

	/**
	 * Render AI configs page content
	 */
	public function renderAIConfigsPage(): void
	{
		$ai_configs = new AIFeedAIConfigs();
		$ai_configs->display();
	}

	/**
	 * Render settings page content
	 */
	public function renderSettingsPage(): void
	{
		$config = AIFeedConfig::getInstance();

		echo '<div class="wrap">';
		echo '<h1>AI Feed Settings</h1>';

		// Show current environment status
		echo '<div class="notice notice-info">';
		echo '<p><strong>Current Environment:</strong> ' . esc_html($config->getEnvironmentDisplayName()) . '</p>';
		echo '<p><strong>API Base URL:</strong> ' . esc_html($config->getApiBaseUrl()) . '</p>';
		echo '</div>';

		echo '<form method="post" action="">';
		wp_nonce_field('vm_ai_feed_settings', 'vm_ai_feed_settings_nonce');

		echo '<table class="form-table">';

		// Environment selection
		echo '<tr>';
		echo '<th scope="row"><label for="current_environment">Environment</label></th>';
		echo '<td>';
		echo '<select name="current_environment" id="current_environment">';
		echo '<option value="local"' . selected($config->getCurrentEnvironment(), 'local', false) . '>Local Development</option>';
		echo '<option value="production"' . selected($config->getCurrentEnvironment(), 'production', false) . '>Production</option>';
		echo '</select>';
		echo '<p class="description">Choose between local development or production API endpoints.</p>';
		echo '</td>';
		echo '</tr>';

		// Editor Type selection
		echo '<tr>';
		echo '<th scope="row"><label for="editor_type">WordPress Editor Type</label></th>';
		echo '<td>';
		echo '<select name="editor_type" id="editor_type">';
		echo '<option value="classic"' . selected($config->getEditorType(), 'classic', false) . '>Classic Editor</option>';
		echo '<option value="blocks"' . selected($config->getEditorType(), 'blocks', false) . '>Block Editor (Gutenberg)</option>';
		echo '</select>';
		echo '<p class="description">Choose how content should be formatted when publishing to WordPress. Classic Editor outputs raw HTML for the visual/HTML editor, while Block Editor converts markdown to WordPress blocks.</p>';
		echo '</td>';
		echo '</tr>';

		// Local API URL
		echo '<tr>';
		echo '<th scope="row"><label for="local_api_url">Local API URL</label></th>';
		echo '<td>';
		echo '<input type="url" name="local_api_url" id="local_api_url" value="' . esc_attr($config->getAllConfig()['api_urls']['local']) . '" class="regular-text" />';
		echo '<p class="description">Base URL for local development API (e.g., http://localhost:8787)</p>';
		echo '</td>';
		echo '</tr>';

		// Production API URL
		echo '<tr>';
		echo '<th scope="row"><label for="production_api_url">Production API URL</label></th>';
		echo '<td>';
		echo '<input type="url" name="production_api_url" id="production_api_url" value="' . esc_attr($config->getAllConfig()['api_urls']['production']) . '" class="regular-text" />';
		echo '<p class="description">Base URL for production API</p>';
		echo '</td>';
		echo '</tr>';

		// API Key
		echo '<tr>';
		echo '<th scope="row"><label for="api_key">API Key</label></th>';
		echo '<td>';
		echo '<input type="password" name="api_key" id="api_key" value="' . esc_attr($config->getApiKey()) . '" class="regular-text" />';
		echo '<p class="description">API key for authentication</p>';
		echo '</td>';
		echo '</tr>';

		echo '</table>';

		submit_button('Save Settings');
		echo '</form>';

		echo '</div>';
	}

	/**
	 * Handle settings form submission
	 */
	public function handleSettingsSave(): void
	{
		if (! isset($_POST['vm_ai_feed_settings_nonce']) || ! wp_verify_nonce($_POST['vm_ai_feed_settings_nonce'], 'vm_ai_feed_settings')) {
			return;
		}

		if (! current_user_can('manage_options')) {
			return;
		}

		$config = AIFeedConfig::getInstance();

		// Update configuration
		$new_config = [
			'current_environment' => sanitize_text_field($_POST['current_environment'] ?? 'local'),
			'api_urls'            => [
				'local'      => esc_url_raw($_POST['local_api_url'] ?? 'http://localhost:8787'),
				'production' => esc_url_raw($_POST['production_api_url'] ?? 'https://vinyl-media.vinyl-media.workers.dev'),
			],
			'api_key'             => sanitize_text_field($_POST['api_key'] ?? 'YOUR_API_KEY'),
			'editor_type'         => sanitize_text_field($_POST['editor_type'] ?? 'classic'),
		];

		$config->updateConfig($new_config);

		// Add success notice
		add_action(
			'admin_notices',
			function () {
				echo '<div class="notice notice-success is-dismissible"><p>Settings saved successfully!</p></div>';
			}
		);
	}

	/**
	 * Handle article writing request
	 */
	public function handle_generate_article(): void
	{
		// Verify nonce
		if (! check_ajax_referer('generate_article_nonce', 'nonce', false)) {
			wp_send_json_error('Invalid security token', 403);
		}

		// Check user permissions
		if (! current_user_can('manage_options')) {
			wp_send_json_error('Insufficient permissions', 403);
		}

		// Get and validate URL
		$url = sanitize_url($_POST['url'] ?? '');
		if (empty($url) || ! filter_var($url, FILTER_VALIDATE_URL)) {
			wp_send_json_error('Invalid URL provided', 400);
		}

		// Get AI config name
		$ai_config_name = sanitize_text_field($_POST['ai_config_name'] ?? 'default');

		// Get optional context and sanitize it
		$context = isset($_POST['context']) ? sanitize_textarea_field($_POST['context']) : '';
		$context = trim($context);

		// Get the wordpress_user_id from the AI config
		$wordpress_user_id = $this->getWordPressUserIdFromAIConfig($ai_config_name);

		// Get configurable API URL
		$config  = AIFeedConfig::getInstance();
		$api_url = $config->getApiUrl('workflows/requests-scrape');
		$api_key = $config->getApiKey();

		// Build request body
		$request_body = [
			'url'            => $url,
			'post_author'    => $wordpress_user_id,
			'ai_config_name' => $ai_config_name,
		];

		// Include context only if provided
		if (! empty($context)) {
			$request_body['context'] = $context;
		}

		$response = wp_remote_post(
			$api_url,
			[
				'headers'     => [
					'Authorization' => 'Bearer ' . $api_key,
					'Content-Type'  => 'application/json',
				],
				'body'        => json_encode($request_body),
				'timeout'     => 60,
				'data_format' => 'body',
			]
		);

		// Handle errors
		if (is_wp_error($response)) {
			wp_send_json_error('Backend request failed: ' . $response->get_error_message(), 500);
		}

		$response_code = wp_remote_retrieve_response_code($response);
		$body          = wp_remote_retrieve_body($response);
		$data          = json_decode($body, true);

		if ($response_code !== 200) {
			$error_message = 'Backend error';
			if (isset($data['error'])) {
				$error_message = $data['error'];
			}

			// Return debug info
			wp_send_json_error(
				[
					'message'       => $error_message,
					'response_code' => $response_code,
					'api_url'       => $api_url,
					'request_body'  => $request_body,
					'response_body' => $data,
				],
				$response_code
			);
		}

		// Extract workflow instance ID from response
		$workflow_instance_id = null;
		if (isset($data['data']['instanceId'])) {
			$workflow_instance_id = $data['data']['instanceId'];
		}

		// Use workflow_instance_id as research_request_id for unified workflow
		$research_request_id = $workflow_instance_id;

		// Success response - return research_request_id for unified workflow
		wp_send_json_success(
			[
				'message'              => 'Research workflow started successfully!',
				'research_request_id'  => $research_request_id,
				'workflow_instance_id' => $workflow_instance_id, // Also return instanceId for reference
				'queue_id'             => $research_request_id, // Keep for backward compatibility
				'data'                 => $data,
			]
		);
	}

	/**
	 * Handle deleting an AI draft post
	 */
	public function handle_delete_ai_draft(): void
	{
		// Verify nonce
		if (! check_ajax_referer('delete_ai_draft_nonce', 'nonce', false)) {
			wp_send_json_error('Invalid security token', 403);
		}

		// Check user permissions
		if (! current_user_can('delete_posts')) {
			wp_send_json_error('Insufficient permissions to delete posts', 403);
		}

		// Get post ID
		$post_id = \intval($_POST['post_id'] ?? 0);
		if (! $post_id) {
			wp_send_json_error('Post ID is required', 400);
		}

		// Get the post
		$post = get_post($post_id);
		if (! $post) {
			wp_send_json_error('Post not found', 404);
		}

		// Check if it's an AI-generated post
		$ai_article_id = get_post_meta($post_id, '_ai_article_id', true);
		if (empty($ai_article_id)) {
			wp_send_json_error('This is not an AI-generated post', 400);
		}

		// Check if post is published (don't allow deletion of published posts)
		if ($post->post_status === 'publish') {
			wp_send_json_error('Cannot delete published posts', 400);
		}

		// Delete the post
		$result = wp_delete_post($post_id, true);

		if (! $result) {
			wp_send_json_error('Failed to delete post', 500);
		}

		// Update article status back to draft in API
		$this->updateArticleStatus($ai_article_id, 'draft');

		wp_send_json_success(
			[
				'message'    => 'Draft deleted successfully',
				'post_id'    => $post_id,
				'article_id' => $ai_article_id,
			]
		);
	}

	/**
	 * Handle deleting a research request and associated articles (cascade deletion)
	 */
	public function handle_delete_research(): void
	{
		// Verify nonce
		if (! check_ajax_referer('generate_article_nonce', 'nonce', false)) {
			wp_send_json_error('Invalid security token', 403);
		}

		// Check user permissions
		if (! current_user_can('manage_options')) {
			wp_send_json_error('Insufficient permissions', 403);
		}

		// Get research ID (can be numeric database ID or research_request_id text field)
		$research_id = sanitize_text_field($_POST['workflow_id'] ?? '');
		if (empty($research_id)) {
			wp_send_json_error('Research ID is required', 400);
		}

		$config  = AIFeedConfig::getInstance();
		$api_key = $config->getApiKey();

		// DELETE research request with cascade deletion of associated articles, revisions, and memories
		$delete_url = $config->getApiUrl('api/research-request-combined/' . urlencode($research_id));
		$response   = wp_remote_request(
			$delete_url,
			[
				'method'  => 'DELETE',
				'headers' => [
					'Authorization' => 'Bearer ' . $api_key,
					'Content-Type'  => 'application/json',
				],
				'timeout' => 30,
			]
		);

		if (is_wp_error($response)) {
			wp_send_json_error('Failed to delete research request: ' . $response->get_error_message(), 500);
		}

		$response_code = wp_remote_retrieve_response_code($response);
		$body          = wp_remote_retrieve_body($response);
		$data          = json_decode($body, true);

		if ($response_code !== 200) {
			$error_message = 'Failed to delete research request';
			if (isset($data['error']['message'])) {
				$error_message = $data['error']['message'];
			} elseif (isset($data['message'])) {
				$error_message = $data['message'];
			}
			wp_send_json_error($error_message, $response_code);
		}

		// Extract deletion details from response
		$deleted_article_count = isset($data['data']['deleted_article_count']) ? $data['data']['deleted_article_count'] : 0;
		$message               = 'Research request and associated data deleted successfully';
		if ($deleted_article_count > 0) {
			$message .= ' (' . $deleted_article_count . ' article' . ($deleted_article_count > 1 ? 's' : '') . ' also deleted)';
		}

		wp_send_json_success(
			[
				'message'               => $message,
				'research_id'           => $research_id,
				'deleted_article_count' => $deleted_article_count,
			]
		);
	}

	/**
	 * Handle deleting an article
	 */
	public function handle_delete_article(): void
	{
		// Verify nonce - accept either create_article or generate_article nonce
		$nonce_valid = check_ajax_referer('create_article_nonce', 'nonce', false) ||
			check_ajax_referer('generate_article_nonce', 'nonce', false);

		if (! $nonce_valid) {
			wp_send_json_error('Invalid security token', 403);
		}

		// Check user permissions
		if (! current_user_can('manage_options')) {
			wp_send_json_error('Insufficient permissions', 403);
		}

		// Get both IDs
		$research_request_id = sanitize_text_field($_POST['research_request_id'] ?? '');
		$article_id = sanitize_text_field($_POST['article_id'] ?? '');

		// Validate both IDs are present
		if (empty($article_id)) {
			wp_send_json_error('Article ID is required', 400);
		}
		if (empty($research_request_id)) {
			wp_send_json_error('Research request ID is required', 400);
		}

		$config = AIFeedConfig::getInstance();
		$api_key = $config->getApiKey();

		error_log('VM AI Feed: Deleting article ID: ' . $article_id . ' and research request ID: ' . $research_request_id);

		// STEP 1: Delete the article first
		$article_url = $config->getApiUrl('v1/api/articles/' . $article_id);
		$article_response = wp_remote_request(
			$article_url,
			[
				'method'  => 'DELETE',
				'headers' => [
					'Authorization' => 'Bearer ' . $api_key,
					'Content-Type'  => 'application/json',
				],
				'timeout' => 30,
			]
		);

		if (is_wp_error($article_response)) {
			$error_msg = 'Failed to delete article: ' . $article_response->get_error_message();
			error_log('VM AI Feed: ' . $error_msg);
			wp_send_json_error($error_msg, 500);
		}

		$article_code = wp_remote_retrieve_response_code($article_response);
		error_log('VM AI Feed: Article delete response code: ' . $article_code);

		// STEP 2: Delete the research request
		$research_url = $config->getApiUrl('api/research-request-combined/' . urlencode($research_request_id));
		$research_response = wp_remote_request(
			$research_url,
			[
				'method'  => 'DELETE',
				'headers' => [
					'Authorization' => 'Bearer ' . $api_key,
					'Content-Type'  => 'application/json',
				],
				'timeout' => 30,
			]
		);

		if (is_wp_error($research_response)) {
			$error_msg = 'Failed to delete research request: ' . $research_response->get_error_message();
			error_log('VM AI Feed: ' . $error_msg);
			wp_send_json_error($error_msg, 500);
		}

		$research_code = wp_remote_retrieve_response_code($research_response);
		error_log('VM AI Feed: Research request delete response code: ' . $research_code);

		// Check if both deletions succeeded
		if ($article_code === 200 && $research_code === 200) {
			error_log('VM AI Feed: Article and research request deleted successfully');
			wp_send_json_success(
				[
					'message' => 'Article and research request deleted successfully',
					'article_id' => $article_id,
					'research_request_id' => $research_request_id,
				]
			);
		} else {
			$error_msg = 'Deletion partially failed. Article: ' . $article_code . ', Research: ' . $research_code;
			error_log('VM AI Feed: ' . $error_msg);
			wp_send_json_error($error_msg, 500);
		}
	}

	/**
	 * Handle restarting a research
	 */
	public function handle_restart_research(): void
	{
		// Verify nonce
		if (! check_ajax_referer('generate_article_nonce', 'nonce', false)) {
			error_log('VM AI Feed: Restart workflow - Invalid nonce');
			wp_send_json_error('Invalid security token', 403);
		}

		// Check user permissions
		if (! current_user_can('manage_options')) {
			error_log('VM AI Feed: Restart workflow - Insufficient permissions');
			wp_send_json_error('Insufficient permissions', 403);
		}

		// Get research instance ID
		$research_instance_id = sanitize_text_field($_POST['workflow_instance_id'] ?? '');
		error_log('VM AI Feed: Restart workflow - Instance ID: ' . $research_instance_id);

		if (empty($research_instance_id)) {
			error_log('VM AI Feed: Restart workflow - Empty instance ID');
			wp_send_json_error('Research instance ID is required', 400);
		}

		// Get configurable API URL
		$config = AIFeedConfig::getInstance();

		// Research page only uses requests-scrape workflow type
		$workflow_type = 'requests-research';
		$api_url       = $config->getApiUrl('v1/workflows/' . $workflow_type . '/' . $research_instance_id . '/restart');
		$api_key       = $config->getApiKey();

		error_log('VM AI Feed: Restarting workflow type: ' . $workflow_type);
		error_log('VM AI Feed: API URL: ' . $api_url);

		$response = wp_remote_post(
			$api_url,
			[
				'method'  => 'POST',
				'headers' => [
					'Authorization' => 'Bearer ' . $api_key,
					'Content-Type'  => 'application/json',
				],
				'timeout' => 30,
			]
		);

		if (is_wp_error($response)) {
			$error_msg = 'Failed to restart research: ' . $response->get_error_message();
			error_log('VM AI Feed: WP Error: ' . $error_msg);
			wp_send_json_error($error_msg, 500);
		}

		$response_code = wp_remote_retrieve_response_code($response);
		$body          = wp_remote_retrieve_body($response);
		$data          = json_decode($body, true);

		error_log('VM AI Feed: Response code: ' . $response_code);
		error_log('VM AI Feed: Response body: ' . substr($body, 0, 500));

		if ($response_code === 200 && isset($data['success']) && $data['success'] === true) {
			error_log('VM AI Feed: Success! Research restarted');

			// Defensive logging: Check if API returned a different instance ID
			$new_instance_id = $data['data']['instanceId'] ?? null;
			if ($new_instance_id && $new_instance_id !== $research_instance_id) {
				error_log('VM AI Feed: WARNING - API returned different instance ID on restart');
				error_log('VM AI Feed: Original research_request_id: ' . $research_instance_id);
				error_log('VM AI Feed: New workflow instance ID: ' . $new_instance_id);
				error_log('VM AI Feed: This may indicate an API bug where research_request_id is being overwritten');
				error_log('VM AI Feed: If you see this, the original research request reference may be lost');
			}

			wp_send_json_success(
				[
					'message'              => 'Research restarted successfully',
					'research_instance_id' => $research_instance_id,
					'original_request_id'  => $research_instance_id, // Preserve original ID
					'new_instance_id'      => $new_instance_id,
					'data'                 => $data,
				]
			);
		} else {
			// Extract error message
			$error_msg = 'Failed to restart research.';
			if (isset($data['error']['message'])) {
				$error_msg = $data['error']['message'];
			} elseif (isset($data['message'])) {
				$error_msg = $data['message'];
			} else {
				$error_msg .= ' HTTP ' . $response_code . ': ' . substr($body, 0, 200);
			}
			error_log('VM AI Feed: Error: ' . $error_msg);
			wp_send_json_error($error_msg, $response_code);
		}
	}

	/**
	 * Handle getting article revisions
	 */
	public function handle_get_article_revisions(): void
	{
		// Verify nonce
		if (! check_ajax_referer('ai_chat_nonce', 'nonce', false)) {
			wp_send_json_error('Invalid security token', 403);
		}

		// Get article ID
		$article_id = sanitize_text_field($_POST['article_id'] ?? '');
		if (empty($article_id)) {
			wp_send_json_error('Article ID is required', 400);
		}

		// Get configurable API URL
		$config  = AIFeedConfig::getInstance();
		$api_key = $config->getApiKey();

		// Fetch all revisions with pagination support
		$all_revisions = [];
		$page          = 1;
		$per_page      = self::REVISIONS_PER_PAGE;
		$max_pages     = self::MAX_REVISION_PAGES; // Safety limit to prevent infinite loops

		do {
			$api_url = $config->getApiUrl('api/articles/' . $article_id . '/revisions?page=' . $page . '&limit=' . $per_page);

			$response = wp_remote_get(
				$api_url,
				[
					'headers' => [
						'Authorization' => 'Bearer ' . $api_key,
						'Content-Type'  => 'application/json',
					],
					'timeout' => 30,
				]
			);

			if (is_wp_error($response)) {
				wp_send_json_error('Failed to fetch revisions: ' . $response->get_error_message(), 500);
			}

			$response_code = wp_remote_retrieve_response_code($response);
			$body          = wp_remote_retrieve_body($response);
			$data          = json_decode($body, true);

			if ($response_code !== 200) {
				$error_message = 'API request failed';
				if (isset($data['error'])) {
					$error_message = $data['error'];
				} elseif (isset($data['message'])) {
					$error_message = $data['message'];
				}
				wp_send_json_error($error_message, $response_code);
			}

			if (! isset($data['success']) || $data['success'] !== true || ! isset($data['data'])) {
				wp_send_json_error('Invalid API response structure', 500);
			}

			// Append revisions from this page
			if (isset($data['data']['revisions']) && is_array($data['data']['revisions'])) {
				$all_revisions = array_merge($all_revisions, $data['data']['revisions']);

				// Check if there are more pages
				$has_more           = isset($data['data']['has_more']) ? $data['data']['has_more'] : false;
				$current_page_count = count($data['data']['revisions']);

				// Stop if no more pages or current page has fewer items than requested
				if (! $has_more || $current_page_count < $per_page) {
					break;
				}
			} else {
				break;
			}

			++$page;
		} while ($page <= $max_pages);

		// Return all collected revisions
		wp_send_json_success(
			[
				'revisions' => $all_revisions,
				'total'     => count($all_revisions),
			]
		);
	}

	/**
	 * Handle refreshing article content
	 */
	public function handle_refresh_article_content(): void
	{
		// Verify nonce
		if (! check_ajax_referer('ai_chat_nonce', 'nonce', false)) {
			wp_send_json_error('Invalid security token', 403);
		}

		// Check user permissions
		if (! current_user_can('read')) {
			wp_send_json_error('Insufficient permissions', 403);
		}

		// Get article ID
		$article_id = sanitize_text_field($_POST['article_id'] ?? '');
		if (empty($article_id)) {
			wp_send_json_error('Article ID is required', 400);
		}

		// Fetch fresh article data from API
		$article = $this->fetchArticleById($article_id);
		if (empty($article)) {
			wp_send_json_error('Article not found', 404);
		}

		// Format tags for display
		$article['tags_input'] = is_array($article['tags_input']) ? implode(', ', $article['tags_input']) : $article['tags_input'];

		// Process category names for display
		$article['post_category_names'] = AIFeedUIHelpers::getCategoryNames($article['post_category'] ?? '[]');

		// Render Markdown + embeds to HTML for admin view refresh
		$raw          = (string) ($article['post_content'] ?? '');
		$article_html = AIMarkdown::renderWithEmbeds($raw);

		// Return the updated article data
		wp_send_json_success(
			[
				'article'      => $article,
				'article_html' => $article_html,
				'message'      => 'Content refreshed successfully',
			]
		);
	}

	/**
	 * Handle processing revision content through markdown library
	 */
	public function handle_process_revision_content(): void
	{
		// Verify nonce
		if (! check_ajax_referer('ai_chat_nonce', 'nonce', false)) {
			wp_send_json_error('Invalid security token', 403);
		}

		// Check user permissions
		if (! current_user_can('read')) {
			wp_send_json_error('Insufficient permissions', 403);
		}

		// Get content from POST
		$content = isset($_POST['content']) ? wp_unslash($_POST['content']) : '';
		if (empty($content)) {
			wp_send_json_error('Content is required', 400);
		}

		// Process through markdown and embeds
		$html = AIMarkdown::renderWithEmbeds((string) $content);

		wp_send_json_success(
			[
				'html'    => $html,
				'message' => 'Content processed successfully',
			]
		);
	}

	/**
	 * Get WordPress user ID from AI config
	 */
	private function getWordPressUserIdFromAIConfig(string $ai_config_name): int
	{
		return AIFeedConfig::getInstance()->getWordPressUserIdFromAIConfig($ai_config_name);
	}

	/**
	 * Get research request by workflow instance ID
	 * This helps us get the actual research_request_id from the research request record
	 */
	private function getResearchRequestByInstanceId(string $instance_id): array
	{
		$config  = AIFeedConfig::getInstance();
		$api_key = $config->getApiKey();

		// Try to get research request by instanceId (which might be the research_request_id)
		// First try the by-request-id endpoint
		$api_url = $config->getApiUrl('api/research-requests/by-request-id/' . urlencode($instance_id));

		$response = wp_remote_get(
			$api_url,
			[
				'headers' => [
					'Authorization' => 'Bearer ' . $api_key,
					'Content-Type'  => 'application/json',
				],
				'timeout' => 10, // Shorter timeout for this lookup
			]
		);

		if (is_wp_error($response)) {
			return [];
		}

		$response_code = wp_remote_retrieve_response_code($response);
		if ($response_code !== 200) {
			return [];
		}

		$body = wp_remote_retrieve_body($response);
		$data = json_decode($body, true);

		if (json_last_error() !== JSON_ERROR_NONE) {
			return [];
		}

		// Handle API response structure
		if (isset($data['success']) && $data['success'] === true && isset($data['data'])) {
			return $data['data'];
		}

		return [];
	}

	/**
	 * Handle checking if article exists by research request ID
	 */
	public function handle_check_article_by_research(): void
	{
		// Verify nonce
		if (! check_ajax_referer('ai_chat_nonce', 'nonce', false)) {
			wp_send_json_error('Invalid security token', 403);
		}

		// Check user permissions
		if (! current_user_can('read')) {
			wp_send_json_error('Insufficient permissions', 403);
		}

		// Get research request ID
		$research_request_id = sanitize_text_field($_POST['research_request_id'] ?? '');
		if (empty($research_request_id)) {
			wp_send_json_error('Research request ID is required', 400);
		}

		$article_id = $this->getArticleIdByResearchRequestId($research_request_id);

		if (! empty($article_id)) {
			wp_send_json_success(
				[
					'article_id' => $article_id,
					'message'    => 'Article found',
				]
			);
		} else {
			wp_send_json_success(
				[
					'article_id' => null,
					'message'    => 'Article not yet created',
				]
			);
		}
	}

	/**
	 * Handle getting AI configs for dropdown
	 */
	public function handle_get_ai_configs(): void
	{
		// Verify nonce
		if (! check_ajax_referer('generate_article_nonce', 'nonce', false)) {
			wp_send_json_error('Invalid security token', 403);
		}

		// Check user permissions
		if (! current_user_can('manage_options')) {
			wp_send_json_error('Insufficient permissions', 403);
		}

		// Fetch AI configs using existing method
		$configs = AIFeedAIConfigs::fetchAIConfigsForDropdown();

		wp_send_json_success($configs);
	}
}
