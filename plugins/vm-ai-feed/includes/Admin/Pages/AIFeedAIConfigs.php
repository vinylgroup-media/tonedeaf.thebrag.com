<?php

namespace VM\AIFeed\Admin\Pages;

use VM\AIFeed\Core\AIFeedConfig;

/**
 * Handles Writer Profile management page
 */
class AIFeedAIConfigs
{

	private string $current_action = '';
	private string $config_id      = '';

	/**
	 * Safely parse metadata from an API config row
	 *
	 * @param array $config The configuration array
	 * @return array<string,string|int|float> Parsed metadata with default values
	 */
	private function parseConfigMetadata(array $config): array
	{
		$defaults = [
			'temperature'      => '',
			'maxTokens'        => '',
			'topP'             => '',
			'seed'             => '',
			'frequencyPenalty' => '',
			'presencePenalty'  => '',
		];
		if (! isset($config['metadata']) || $config['metadata'] === null || $config['metadata'] === '') {
			return $defaults;
		}
		$raw = $config['metadata'];
		if (\is_string($raw)) {
			$decoded = json_decode($raw, true);
		} elseif (\is_array($raw)) {
			$decoded = $raw;
		} else {
			$decoded = [];
		}
		if (! \is_array($decoded)) {
			$decoded = [];
		}
		return array_merge($defaults, array_intersect_key($decoded, $defaults));
	}

	/**
	 * Render inputs for metadata parameters
	 *
	 * @param array $values The metadata values to display
	 */
	private function renderMetadataFields(array $values): void
	{
		$val = fn($key) => isset($values[$key]) && $values[$key] !== null ? $values[$key] : '';
		echo '<fieldset style="display:grid; gap:14px; grid-template-columns: repeat(2, minmax(260px, 1fr)); max-width: 100%;">';
		// temperature
		echo '<label><span style="display:block; font-size:12px; font-weight:600; margin-bottom:4px;">Temperature (creativity)</span>';
		echo '<input type="number" step="0.01" min="0" max="2" name="metadata_temperature" value="' . esc_attr($val('temperature')) . '" class="small-text" style="width:120px;">';
		echo '<span class="description" style="display:block; color:#666; font-size:12px;">Higher = more creative, lower = more focused. Range 0–2.</span>';
		echo '</label>';
		// maxTokens
		echo '<label><span style="display:block; font-size:12px; font-weight:600; margin-bottom:4px;">Max tokens (length limit)</span>';
		echo '<input type="number" min="1" name="metadata_maxTokens" value="' . esc_attr($val('maxTokens')) . '" class="small-text" style="width:120px;">';
		echo '<span class="description" style="display:block; color:#666; font-size:12px;">Maximum number of tokens in the AI\'s response.</span>';
		echo '</label>';
		// topP
		echo '<label><span style="display:block; font-size:12px; font-weight:600; margin-bottom:4px;">Top P (diversity)</span>';
		echo '<input type="number" step="0.01" min="0" max="1" name="metadata_topP" value="' . esc_attr($val('topP')) . '" class="small-text" style="width:120px;">';
		echo '<span class="description" style="display:block; color:#666; font-size:12px;">Nucleus sampling. Lower focuses on most likely words; 1 disables.</span>';
		echo '</label>';
		// seed
		echo '<label><span style="display:block; font-size:12px; font-weight:600; margin-bottom:4px;">Seed (reproducibility)</span>';
		echo '<input type="number" step="1" name="metadata_seed" value="' . esc_attr($val('seed')) . '" class="small-text" style="width:120px;">';
		echo '<span class="description" style="display:block; color:#666; font-size:12px;">Use a number to get repeatable results from the same prompt.</span>';
		echo '</label>';
		// frequencyPenalty
		echo '<label><span style="display:block; font-size:12px; font-weight:600; margin-bottom:4px;">Frequency penalty (reduce repetition, OpenAI)</span>';
		echo '<input type="number" step="0.1" min="-2" max="2" name="metadata_frequencyPenalty" value="' . esc_attr($val('frequencyPenalty')) . '" class="small-text" style="width:120px;">';
		echo '<span class="description" style="display:block; color:#666; font-size:12px;">Penalizes repeated phrases. Range -2 to 2. OpenAI only.</span>';
		echo '</label>';
		// presencePenalty
		echo '<label><span style="display:block; font-size:12px; font-weight:600; margin-bottom:4px;">Presence penalty (encourage new topics, OpenAI)</span>';
		echo '<input type="number" step="0.1" min="-2" max="2" name="metadata_presencePenalty" value="' . esc_attr($val('presencePenalty')) . '" class="small-text" style="width:120px;">';
		echo '<span class="description" style="display:block; color:#666; font-size:12px;">Encourages introducing new ideas. Range -2 to 2. OpenAI only.</span>';
		echo '</label>';
		echo '</fieldset>';
		echo '<p class="description">Universal: temperature, maxTokens, topP, seed. OpenAI-only: frequencyPenalty, presencePenalty.</p>';
	}

	/**
	 * Collect sanitized metadata from POST
	 */
	private function getMetadataFromPost(): array
	{
		$getn = fn($name) => isset($_POST[$name]) && $_POST[$name] !== '' ? $_POST[$name] : null;
		$meta = [];
		$t    = $getn('metadata_temperature');
		if ($t !== null) {
			$meta['temperature'] = max(0, min(2, floatval($t)));
		}
		$mt = $getn('metadata_maxTokens');
		if ($mt !== null) {
			$meta['maxTokens'] = max(1, \intval($mt));
		}
		$tp = $getn('metadata_topP');
		if ($tp !== null) {
			$meta['topP'] = max(0, min(1, floatval($tp)));
		}
		$seed = $getn('metadata_seed');
		if ($seed !== null) {
			$meta['seed'] = \intval($seed);
		}
		$fp = $getn('metadata_frequencyPenalty');
		if ($fp !== null) {
			$meta['frequencyPenalty'] = max(-2, min(2, floatval($fp)));
		}
		$pp = $getn('metadata_presencePenalty');
		if ($pp !== null) {
			$meta['presencePenalty'] = max(-2, min(2, floatval($pp)));
		}
		return $meta;
	}

	public function __construct()
	{
		// Determine current action
		if (isset($_GET['action'])) {
			$this->current_action = sanitize_text_field($_GET['action']);
		}

		if (isset($_GET['config_id'])) {
			$this->config_id = sanitize_text_field($_GET['config_id']);
		}
	}

	/**
	 * Main display method
	 */
	public function display(): void
	{
		echo '<div class="wrap">';
		echo '<h1>Writer Profiles</h1>';

		// Display any stored admin notices
		$this->displayStoredNotice();

		// Show different views based on action
		switch ($this->current_action) {
			case 'create':
				$this->displayCreateForm();
				break;
			case 'edit':
				$this->displayEditForm($this->config_id);
				break;
			case 'duplicate':
				$this->displayDuplicateForm($this->config_id);
				break;
			case 'shared-content':
				$this->displaySharedContent();
				break;
			default:
				$this->displayListView();
				break;
		}

		echo '</div>';
	}

	/**
	 * Display stored admin notice from transient
	 */
	private function displayStoredNotice(): void
	{
		$notice = get_transient('vm_ai_feed_admin_notice');

		if ($notice) {
			delete_transient('vm_ai_feed_admin_notice');

			$notice_class = $notice['type'] === 'success' ? 'notice-success' : 'notice-warning';

			echo '<div class="notice ' . esc_attr($notice_class) . ' is-dismissible">';
			echo '<p>' . esc_html($notice['message']) . '</p>';
			echo '</div>';
		}
	}

	/**
	 * Sanitize AI prompt content
	 * Removes dangerous scripts but preserves all other HTML and custom tags
	 *
	 * @param string $content Content to sanitize
	 * @return string Sanitized content
	 */
	private function sanitizePromptContent(string $content): string
	{
		// Remove script tags and event handlers but keep everything else
		$content = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $content);
		$content = preg_replace('/<iframe\b[^>]*>(.*?)<\/iframe>/is', '', $content);
		$content = preg_replace('/\bon\w+\s*=\s*["\'].*?["\']/is', '', $content); // Remove onclick, onload, etc.

		return $content;
	}

	/**
	 * Parse system prompt into sections
	 *
	 * @param string $system_prompt The full system prompt
	 * @return array Associative array with keys: role, voice, instructions, tone_examples, research
	 */
	private function parseSystemPrompt(string $system_prompt): array
	{
		$sections = [
			'role'          => '',
			'voice'         => '',
			'instructions'  => '',
			'tone_examples' => '',
			'research'      => '',
		];

		// Try to extract sections marked with markdown headers
		$patterns = [
			'role'          => '/##\s*Role\s*\n(.*?)(?=\n##|\z)/is',
			'voice'         => '/##\s*Voice\s*\n(.*?)(?=\n##|\z)/is',
			'instructions'  => '/##\s*Instructions\s*\n(.*?)(?=\n##|\z)/is',
			'tone_examples' => '/##\s*Tone\s*Examples?\s*\n(.*?)(?=\n##|\z)/is',
			'research'      => '/##\s*Research\s*\n(.*?)(?=\n##|\z)/is',
		];

		foreach ($patterns as $key => $pattern) {
			if (preg_match($pattern, $system_prompt, $matches)) {
				$sections[$key] = trim($matches[1]);
			}
		}

		// If no sections found, put everything in role (legacy support)
		if (empty($sections['role']) && empty($sections['voice']) && empty($sections['instructions'])) {
			$sections['role'] = trim($system_prompt);
		}

		return $sections;
	}

	/**
	 * Merge prompt sections into a single system prompt with markdown headers
	 *
	 * @param string $role Role section content
	 * @param string $voice Voice section content
	 * @param string $instructions Instructions section content
	 * @param string $tone_examples Tone examples section content
	 * @param string $research Research section content
	 * @return string The merged system prompt
	 */
	private function mergeSystemPrompt(string $role, string $voice, string $instructions, string $tone_examples = '', string $research = ''): string
	{
		$prompt_parts = [];

		if (! empty(trim($role))) {
			$prompt_parts[] = "## Role\n\n" . trim($role);
		}

		if (! empty(trim($voice))) {
			$prompt_parts[] = "## Voice\n\n" . trim($voice);
		}

		if (! empty(trim($instructions))) {
			$prompt_parts[] = "## Instructions\n\n" . trim($instructions);
		}

		if (! empty(trim($tone_examples))) {
			$prompt_parts[] = "## Tone Examples\n\n" . trim($tone_examples);
		}

		if (! empty(trim($research))) {
			$prompt_parts[] = "## Research\n\n" . trim($research);
		}

		return implode("\n\n", $prompt_parts);
	}

	/**
	 * Clean system prompt by removing problematic Unicode characters and normalizing line breaks
	 *
	 * @param string $system_prompt The system prompt to clean
	 * @return string The cleaned system prompt
	 */
	private function cleanSystemPrompt(string $system_prompt): string
	{
		// Replace \u2028 (line separator) with \n
		$system_prompt = str_replace("\u{2028}", "\n", $system_prompt);
		// Normalize line breaks (replace \r\n and \r with \n)
		$system_prompt = str_replace(["\r\n", "\r"], "\n", $system_prompt);
		// Replace smart quotes with standard quotes
		$system_prompt = str_replace(["\u{2018}", "\u{2019}"], "'", $system_prompt); // Left/Right single quotation marks
		$system_prompt = str_replace(["\u{201C}", "\u{201D}"], '"', $system_prompt); // Left/Right double quotation marks
		// Replace special dashes with standard hyphens
		$system_prompt = str_replace(["\u{2013}", "\u{2014}"], '-', $system_prompt); // En dash and Em dash
		// Trim whitespace
		$system_prompt = trim($system_prompt);

		return $system_prompt;
	}

	/**
	 * Parse editorial guidelines into sections
	 *
	 * @param string $content The full editorial guidelines content
	 * @return array Associative array with keys: editorial_guidelines, formatting, checklist
	 */
	private function parseEditorialGuidelines(string $content): array
	{
		$sections = [
			'editorial_guidelines' => '',
			'formatting'           => '',
			'checklist'            => '',
		];

		// Try to extract sections marked with markdown headers
		$patterns = [
			'editorial_guidelines' => '/##\s*Editorial\s+Guidelines\s*\n(.*?)(?=\n##|\z)/is',
			'formatting'           => '/##\s*Formatting\s*\n(.*?)(?=\n##|\z)/is',
			'checklist'            => '/##\s*Checklist\s*\n(.*?)(?=\n##|\z)/is',
		];

		foreach ($patterns as $key => $pattern) {
			if (preg_match($pattern, $content, $matches)) {
				$sections[$key] = trim($matches[1]);
			}
		}

		// If no sections found, put everything in editorial_guidelines (legacy support)
		if (empty($sections['editorial_guidelines']) && empty($sections['formatting']) && empty($sections['checklist'])) {
			$sections['editorial_guidelines'] = trim($content);
		}

		return $sections;
	}

	/**
	 * Merge editorial guidelines sections into a single content with markdown headers
	 *
	 * @param string $editorial_guidelines Editorial guidelines section content
	 * @param string $formatting Formatting section content
	 * @param string $checklist Checklist section content
	 * @return string The merged editorial guidelines content
	 */
	private function mergeEditorialGuidelines(string $editorial_guidelines, string $formatting, string $checklist): string
	{
		$content_parts = [];

		if (! empty(trim($editorial_guidelines))) {
			$content_parts[] = "## Editorial Guidelines\n\n" . trim($editorial_guidelines);
		}

		if (! empty(trim($formatting))) {
			$content_parts[] = "## Formatting\n\n" . trim($formatting);
		}

		if (! empty(trim($checklist))) {
			$content_parts[] = "## Checklist\n\n" . trim($checklist);
		}

		return implode("\n\n", $content_parts);
	}

	/**
	 * Display list of AI configurations
	 */
	private function displayListView(): void
	{
		$configs = $this->fetchAIConfigs();

		// Action buttons
		echo '<div style="margin-bottom: 20px;">';
		echo '<a href="' . esc_url(admin_url('admin.php?page=vm-ai-feed-ai-configs&action=create')) . '" class="button button-primary">Add New Writer Profile</a>';
		echo ' ';
		echo '<a href="' . esc_url(admin_url('admin.php?page=vm-ai-feed-ai-configs&action=shared-content')) . '" class="button">Manage Shared Content</a>';
		echo '</div>';

		// Display table
		echo '<table class="wp-list-table widefat striped">';
		echo '<thead>';
		echo '<tr>';
		echo '<th style="width: 5%;">ID</th>';
		echo '<th style="width: 15%;">Name</th>';
		echo '<th style="width: 15%;">Display Name</th>';
		echo '<th style="width: 15%;">WordPress User</th>';
		echo '<th style="width: 10%;">Provider</th>';
		echo '<th style="width: 12%;">Model</th>';
		echo '<th style="width: 8%;">Status</th>';
		echo '<th style="width: 8%;">Default</th>';
		echo '<th style="width: 12%;">Created</th>';
		echo '<th style="width: 8%;">Actions</th>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';

		if (! empty($configs)) {
			foreach ($configs as $config) {
				$edit_url      = admin_url('admin.php?page=vm-ai-feed-ai-configs&action=edit&config_id=' . urlencode($config['id']));
				$delete_url    = wp_nonce_url(
					admin_url('admin.php?page=vm-ai-feed-ai-configs&action=delete&config_id=' . urlencode($config['id'])),
					'delete_ai_config_' . $config['id']
				);
				$duplicate_url = admin_url('admin.php?page=vm-ai-feed-ai-configs&action=duplicate&config_id=' . urlencode($config['id']));

				$user         = get_user_by('id', $config['wordpress_user_id']);
				$user_display = $user ? "{$user->display_name} ({$user->user_login})" : 'User #' . $config['wordpress_user_id'];

				$status_class = $config['is_active'] ? 'status-published' : 'status-pending';
				$status_text  = $config['is_active'] ? 'Active' : 'Inactive';

				$is_default = ! empty($config['is_default']) && $config['is_default'] === true;

				$provider = ! empty($config['provider']) ? $config['provider'] : 'openai';
				$model    = ! empty($config['model']) ? $config['model'] : 'gpt-4o';

				echo '<tr>';
				echo '<td>' . esc_html($config['id']) . '</td>';
				echo '<td><strong>' . esc_html($config['name']) . '</strong></td>';
				echo '<td>' . esc_html($config['display_name']) . '</td>';
				echo '<td>' . esc_html($user_display) . '</td>';
				echo '<td>' . esc_html($provider) . '</td>';
				echo '<td>' . esc_html($model) . '</td>';
				echo '<td><span class="status-badge ' . esc_attr($status_class) . '">' . esc_html($status_text) . '</span></td>';
				if ($is_default) {
					echo '<td><span style="display: inline-block; padding: 3px 8px; background: #2271b1; color: white; border-radius: 3px; font-size: 11px; font-weight: bold;">★ Default</span></td>';
				} else {
					echo '<td>—</td>';
				}
				echo '<td>' . esc_html(date('Y-m-d H:i', strtotime($config['created_at']))) . '</td>';
				echo '<td>';
				echo '<a href="' . esc_url($edit_url) . '" class="button button-small">Edit</a> ';
				echo '<a href="' . esc_url($duplicate_url) . '" class="button button-small">Duplicate</a> ';
				echo '<a href="' . esc_url($delete_url) . '" class="button button-small button-link-delete" onclick="return confirm(\'Are you sure you want to delete this writer profile?\');">Delete</a>';
				echo '</td>';
				echo '</tr>';
			}
		} else {
			echo '<tr><td colspan="10">No writer profiles found. <a href="' . esc_url(admin_url('admin.php?page=vm-ai-feed-ai-configs&action=create')) . '">Create one now</a>.</td></tr>';
		}

		echo '</tbody>';
		echo '</table>';
	}

	/**
	 * Display create form
	 */
	private function displayCreateForm(): void
	{
		$back_url = admin_url('admin.php?page=vm-ai-feed-ai-configs');

		echo '<a href="' . esc_url($back_url) . '" class="button" style="margin-bottom: 20px;">← Back to Writers</a>';
		echo '<div class="card" style="max-width: 800px;">';
		echo '<h2>Create New Writer Profile</h2>';

		echo '<form method="post">';
		wp_nonce_field('create_ai_config', 'ai_config_nonce');
		echo '<input type="hidden" name="ai_config_action" value="create">';

		echo '<table class="form-table">';

		// Name
		echo '<tr>';
		echo '<th scope="row"><label for="config_name">Name *</label></th>';
		echo '<td>';
		echo '<input type="text" id="config_name" name="name" class="regular-text" required pattern="[a-z0-9-]+" placeholder="e.g., news-writer">';
		echo '<p class="description">Lowercase alphanumeric with dashes only (e.g., news-writer, feature-editor)</p>';
		echo '</td>';
		echo '</tr>';

		// Display Name
		echo '<tr>';
		echo '<th scope="row"><label for="display_name">Display Name *</label></th>';
		echo '<td>';
		echo '<input type="text" id="display_name" name="display_name" class="regular-text" required placeholder="e.g., News Writer">';
		echo '<p class="description">Human-readable name for this writer</p>';
		echo '</td>';
		echo '</tr>';

		// WordPress User
		echo '<tr>';
		echo '<th scope="row"><label for="wordpress_user_id">WordPress User *</label></th>';
		echo '<td>';
		$this->renderUserDropdown('wordpress_user_id', 0);
		echo '<p class="description">WordPress user account for attribution</p>';
		echo '</td>';
		echo '</tr>';

		// System Prompt - Role
		echo '<tr>';
		echo '<th scope="row"><label for="prompt_role">Role *</label></th>';
		echo '<td>';
		echo '<textarea id="prompt_role" name="prompt_role" rows="6" class="large-text" required placeholder="e.g., You are a professional music journalist..."></textarea>';
		echo '<p class="description">Define the AI\'s role and identity</p>';
		echo '</td>';
		echo '</tr>';

		// System Prompt - Voice
		echo '<tr>';
		echo '<th scope="row"><label for="prompt_voice">Voice *</label></th>';
		echo '<td>';
		echo '<textarea id="prompt_voice" name="prompt_voice" rows="6" class="large-text" required placeholder="e.g., Write in a casual, conversational tone..."></textarea>';
		echo '<p class="description">Describe the writing voice and style</p>';
		echo '</td>';
		echo '</tr>';

		// System Prompt - Instructions
		echo '<tr>';
		echo '<th scope="row"><label for="prompt_instructions">Instructions *</label></th>';
		echo '<td>';
		echo '<textarea id="prompt_instructions" name="prompt_instructions" rows="10" class="large-text" required placeholder="e.g., Always fact-check information..."></textarea>';
		echo '<p class="description">Specific instructions and guidelines</p>';
		echo '</td>';
		echo '</tr>';

		// System Prompt - Tone Examples
		echo '<tr>';
		echo '<th scope="row"><label for="prompt_tone_examples">Tone Examples</label></th>';
		echo '<td>';
		echo '<textarea id="prompt_tone_examples" name="prompt_tone_examples" rows="6" class="large-text" placeholder="e.g., Good: \'The band rocked the stage...\' Bad: \'The band performed...\'"></textarea>';
		echo '<p class="description">Examples of good vs bad tone (optional)</p>';
		echo '</td>';
		echo '</tr>';

		// System Prompt - Research
		echo '<tr>';
		echo '<th scope="row"><label for="prompt_research">Research</label></th>';
		echo '<td>';
		echo '<textarea id="prompt_research" name="prompt_research" rows="8" class="large-text" placeholder="e.g., Step-by-step process for content creation..."></textarea>';
		echo '<p class="description">Content creation research and process steps (optional)</p>';
		echo '</td>';
		echo '</tr>';

		// Provider
		echo '<tr>';
		echo '<th scope="row"><label for="provider">AI Provider</label></th>';
		echo '<td>';
		echo '<select id="provider" name="provider" class="regular-text">';
		echo '<option value="openai" selected>OpenAI</option>';
		echo '<option value="google-ai-studio">Google AI Studio</option>';
		echo '</select>';
		echo '<p class="description">Select the AI provider to use</p>';
		echo '</td>';
		echo '</tr>';

		// Model
		echo '<tr>';
		echo '<th scope="row"><label for="model">Model</label></th>';
		echo '<td>';
		echo '<input type="text" id="model" name="model" class="regular-text" value="gpt-4o" placeholder="e.g., gpt-4o, gpt-4-turbo, gemini-1.5-pro">';
		echo '<p class="description">Specify the model name for the selected provider</p>';
		echo '</td>';
		echo '</tr>';

		// Metadata - Generation Parameters
		echo '<tr>';
		echo '<th scope="row">Generation Parameters</th>';
		echo '<td>';
		$this->renderMetadataFields([]);
		echo '</td>';
		echo '</tr>';

		// Active Status
		echo '<tr>';
		echo '<th scope="row"><label for="is_active">Active</label></th>';
		echo '<td>';
		echo '<input type="checkbox" id="is_active" name="is_active" value="1" checked>';
		echo '<label for="is_active">Enable this writer profile</label>';
		echo '</td>';
		echo '</tr>';

		// Default Writer
		echo '<tr>';
		echo '<th scope="row"><label for="is_default">Set as Default Writer</label></th>';
		echo '<td>';
		echo '<input type="checkbox" id="is_default" name="is_default" value="1">';
		echo '<label for="is_default">Use this writer profile as the default fallback</label>';
		echo '<p class="description">When set as default, this writer will be used when no WordPress user ID is provided or when a user has no matching configuration. Only one writer can be default at a time.</p>';
		echo '</td>';
		echo '</tr>';

		// Allowed Tools
		echo '<tr>';
		echo '<th scope="row"><label>Allowed Tools</label></th>';
		echo '<td>';
		$this->renderToolsCheckboxes([]);
		echo '</td>';
		echo '</tr>';

		echo '</table>';

		submit_button('Create Writer Profile');

		echo '</form>';
		echo '</div>';
	}

	/**
	 * Display edit form
	 */
	private function displayEditForm(string $config_id): void
	{
		if (empty($config_id)) {
			echo '<div class="notice notice-error"><p>Invalid configuration ID.</p></div>';
			return;
		}

		$config = $this->getAIConfigById($config_id);

		if (empty($config)) {
			echo '<div class="notice notice-error"><p>Configuration not found.</p></div>';
			return;
		}

		// Parse system prompt into sections
		$prompt_sections = $this->parseSystemPrompt($config['system_prompt']);

		$back_url = admin_url('admin.php?page=vm-ai-feed-ai-configs');

		echo '<a href="' . esc_url($back_url) . '" class="button" style="margin-bottom: 20px;">← Back to Writers</a>';
		echo '<div class="card" style="max-width: 800px;">';
		echo '<h2>Edit Writer Profile</h2>';
		echo '<div style="margin: 10px 0 20px 0;">';
		$duplicate_url = admin_url('admin.php?page=vm-ai-feed-ai-configs&action=duplicate&config_id=' . urlencode($config_id));
		echo '<a href="' . esc_url($duplicate_url) . '" class="button">Duplicate</a>';
		echo '</div>';

		echo '<form method="post">';
		wp_nonce_field("update_ai_config_{$config_id}", 'ai_config_nonce');
		echo '<input type="hidden" name="ai_config_action" value="update">';
		echo '<input type="hidden" name="config_id" value="' . esc_attr($config_id) . '">';

		echo '<table class="form-table">';

		// Name (read-only after creation)
		echo '<tr>';
		echo '<th scope="row"><label for="config_name">Name</label></th>';
		echo '<td>';
		echo '<input type="text" id="config_name" name="name" class="regular-text" value="' . esc_attr($config['name']) . '" readonly>';
		echo '<p class="description">Name cannot be changed after creation</p>';
		echo '</td>';
		echo '</tr>';

		// Display Name
		echo '<tr>';
		echo '<th scope="row"><label for="display_name">Display Name *</label></th>';
		echo '<td>';
		echo '<input type="text" id="display_name" name="display_name" class="regular-text" required value="' . esc_attr($config['display_name']) . '">';
		echo '</td>';
		echo '</tr>';

		// WordPress User
		echo '<tr>';
		echo '<th scope="row"><label for="wordpress_user_id">WordPress User *</label></th>';
		echo '<td>';
		$this->renderUserDropdown('wordpress_user_id', $config['wordpress_user_id']);
		echo '</td>';
		echo '</tr>';

		// System Prompt - Role
		echo '<tr>';
		echo '<th scope="row"><label for="prompt_role">Role *</label></th>';
		echo '<td>';
		echo '<textarea id="prompt_role" name="prompt_role" rows="6" class="large-text" required>' . esc_textarea($prompt_sections['role']) . '</textarea>';
		echo '<p class="description">Define the AI\'s role and identity</p>';
		echo '</td>';
		echo '</tr>';

		// System Prompt - Voice
		echo '<tr>';
		echo '<th scope="row"><label for="prompt_voice">Voice *</label></th>';
		echo '<td>';
		echo '<textarea id="prompt_voice" name="prompt_voice" rows="6" class="large-text" required>' . esc_textarea($prompt_sections['voice']) . '</textarea>';
		echo '<p class="description">Describe the writing voice and style</p>';
		echo '</td>';
		echo '</tr>';

		// System Prompt - Instructions
		echo '<tr>';
		echo '<th scope="row"><label for="prompt_instructions">Instructions *</label></th>';
		echo '<td>';
		echo '<textarea id="prompt_instructions" name="prompt_instructions" rows="10" class="large-text" required>' . esc_textarea($prompt_sections['instructions']) . '</textarea>';
		echo '<p class="description">Specific instructions and guidelines</p>';
		echo '</td>';
		echo '</tr>';

		// System Prompt - Tone Examples
		echo '<tr>';
		echo '<th scope="row"><label for="prompt_tone_examples">Tone Examples</label></th>';
		echo '<td>';
		echo '<textarea id="prompt_tone_examples" name="prompt_tone_examples" rows="6" class="large-text">' . esc_textarea($prompt_sections['tone_examples']) . '</textarea>';
		echo '<p class="description">Examples of good vs bad tone (optional)</p>';
		echo '</td>';
		echo '</tr>';

		// System Prompt - Research
		echo '<tr>';
		echo '<th scope="row"><label for="prompt_research">Research</label></th>';
		echo '<td>';
		echo '<textarea id="prompt_research" name="prompt_research" rows="8" class="large-text">' . esc_textarea($prompt_sections['research']) . '</textarea>';
		echo '<p class="description">Content creation research and process steps (optional)</p>';
		echo '</td>';
		echo '</tr>';

		// Provider
		echo '<tr>';
		echo '<th scope="row"><label for="provider">AI Provider</label></th>';
		echo '<td>';
		$provider = ! empty($config['provider']) ? $config['provider'] : 'openai';
		echo '<select id="provider" name="provider" class="regular-text">';
		echo '<option value="openai"' . selected($provider, 'openai', false) . '>OpenAI</option>';
		echo '<option value="google-ai-studio"' . selected($provider, 'google-ai-studio', false) . '>Google AI Studio</option>';
		echo '</select>';
		echo '<p class="description">Select the AI provider to use</p>';
		echo '</td>';
		echo '</tr>';

		// Model
		echo '<tr>';
		echo '<th scope="row"><label for="model">Model</label></th>';
		echo '<td>';
		$model = ! empty($config['model']) ? $config['model'] : 'gpt-4o';
		echo '<input type="text" id="model" name="model" class="regular-text" value="' . esc_attr($model) . '" placeholder="e.g., gpt-4o, gpt-4-turbo, gemini-1.5-pro">';
		echo '<p class="description">Specify the model name for the selected provider</p>';
		echo '</td>';
		echo '</tr>';

		// Metadata - Generation Parameters
		echo '<tr>';
		echo '<th scope="row">Generation Parameters</th>';
		echo '<td>';
		$metadata_prefill = $this->parseConfigMetadata($config);
		$this->renderMetadataFields($metadata_prefill);
		echo '</td>';
		echo '</tr>';

		// Active Status
		echo '<tr>';
		echo '<th scope="row"><label for="is_active">Active</label></th>';
		echo '<td>';
		echo '<input type="checkbox" id="is_active" name="is_active" value="1"' . checked($config['is_active'], true, false) . '>';
		echo '<label for="is_active">Enable this writer profile</label>';
		echo '</td>';
		echo '</tr>';

		// Default Writer
		$is_default = ! empty($config['is_default']) && $config['is_default'] === true;
		echo '<tr>';
		echo '<th scope="row"><label for="is_default">Set as Default Writer</label></th>';
		echo '<td>';
		echo '<input type="checkbox" id="is_default" name="is_default" value="1"' . checked($is_default, true, false) . '>';
		echo '<label for="is_default">Use this writer profile as the default fallback</label>';
		echo '<p class="description">When set as default, this writer will be used when no WordPress user ID is provided or when a user has no matching configuration. Only one writer can be default at a time.</p>';
		echo '</td>';
		echo '</tr>';

		// Allowed Tools
		echo '<tr>';
		echo '<th scope="row"><label>Allowed Tools</label></th>';
		echo '<td>';
		$allowed_tools = $this->fetchAllowedTools($config_id);
		$this->renderToolsCheckboxes($allowed_tools);
		echo '</td>';
		echo '</tr>';

		echo '</table>';

		submit_button('Update Writer Profile');

		echo '</form>';
		echo '</div>';

		// Memories Management Section
		$this->displayMemoriesSection($config['wordpress_user_id'], $config_id);
	}

	/**
	 * Display duplicate form (prefilled name/display_name)
	 */
	private function displayDuplicateForm(string $config_id): void
	{
		if (empty($config_id)) {
			echo '<div class="notice notice-error"><p>Invalid configuration ID.</p></div>';
			return;
		}
		$config = $this->getAIConfigById($config_id);
		if (empty($config)) {
			echo '<div class="notice notice-error"><p>Configuration not found.</p></div>';
			return;
		}
		$back_url = admin_url('admin.php?page=vm-ai-feed-ai-configs');
		echo '<a href="' . esc_url($back_url) . '" class="button" style="margin-bottom: 20px;">← Back to Writers</a>';
		echo '<div class="card" style="max-width: 800px;">';
		echo '<h2>Duplicate Writer Profile</h2>';
		echo '<p>Duplicating: <strong>' . esc_html($config['display_name']) . '</strong> (' . esc_html($config['name']) . ')</p>';
		echo '<form method="post">';
		wp_nonce_field("duplicate_ai_config_{$config_id}", 'ai_config_nonce');
		echo '<input type="hidden" name="ai_config_action" value="create_duplicate">';
		echo '<input type="hidden" name="source_config_id" value="' . esc_attr($config_id) . '">';
		echo '<table class="form-table">';
		// name
		echo '<tr>';
		echo '<th scope="row"><label for="dup_name">New Name *</label></th>';
		echo '<td>';
		echo '<input type="text" id="dup_name" name="name" class="regular-text" required pattern="[a-z0-9-]+" value="' . esc_attr($config['name'] . '-copy') . '">';
		echo '<p class="description">Lowercase alphanumeric with dashes only. Must be unique.</p>';
		echo '</td>';
		echo '</tr>';
		// display_name
		echo '<tr>';
		echo '<th scope="row"><label for="dup_display_name">New Display Name *</label></th>';
		echo '<td>';
		echo '<input type="text" id="dup_display_name" name="display_name" class="regular-text" required value="' . esc_attr($config['display_name'] . ' (Copy)') . '">';
		echo '</td>';
		echo '</tr>';
		echo '</table>';
		submit_button('Create Duplicate');
		echo '</form>';
		echo '</div>';
	}

	/**
	 * Display shared content management
	 */
	private function displaySharedContent(): void
	{
		$shared_content = $this->fetchSharedContent();
		$back_url       = admin_url('admin.php?page=vm-ai-feed-ai-configs');

		echo '<a href="' . esc_url($back_url) . '" class="button" style="margin-bottom: 20px;">← Back to Writers</a>';

		// Shared content prompt
		echo '<div class="card" style="max-width: 800px;">';
		echo '<h2>Shared Content Prompt</h2>';
		echo '<p>Shared content is automatically appended to all writer profiles. Use this for editorial guidelines, writing style rules, and common instructions.</p>';

		if (! empty($shared_content)) {
			foreach ($shared_content as $content) {
				echo '<div style="margin-bottom: 30px; padding: 20px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;">';
				echo '<h3 style="margin-top: 0;">' . esc_html(ucwords(str_replace('_', ' ', $content['key']))) . '</h3>';

				echo '<form method="post">';
				wp_nonce_field("update_shared_content_{$content['key']}", 'shared_content_nonce');
				echo '<input type="hidden" name="ai_config_action" value="update_shared_content">';
				echo '<input type="hidden" name="content_key" value="' . esc_attr($content['key']) . '">';

				echo '<p class="description" style="margin-bottom: 10px;">' . esc_html($content['description']) . '</p>';

				// Check if this is editorial_guidelines - use three separate fields
				if ($content['key'] === 'editorial_guidelines') {
					$sections = $this->parseEditorialGuidelines($content['content']);

					echo '<table class="form-table">';

					// Editorial Guidelines
					echo '<tr>';
					echo '<th scope="row"><label for="editorial_guidelines">Editorial Guidelines *</label></th>';
					echo '<td>';
					echo '<textarea id="editorial_guidelines" name="editorial_guidelines" rows="10" class="large-text" style="font-family: monospace;">' . esc_textarea($sections['editorial_guidelines']) . '</textarea>';
					echo '<p class="description">Core editorial guidelines and principles</p>';
					echo '</td>';
					echo '</tr>';

					// Formatting
					echo '<tr>';
					echo '<th scope="row"><label for="formatting">Formatting *</label></th>';
					echo '<td>';
					echo '<textarea id="formatting" name="formatting" rows="8" class="large-text" style="font-family: monospace;">' . esc_textarea($sections['formatting']) . '</textarea>';
					echo '<p class="description">Formatting rules and conventions</p>';
					echo '</td>';
					echo '</tr>';

					// Checklist
					echo '<tr>';
					echo '<th scope="row"><label for="checklist">Checklist</label></th>';
					echo '<td>';
					echo '<textarea id="checklist" name="checklist" rows="6" class="large-text" style="font-family: monospace;">' . esc_textarea($sections['checklist']) . '</textarea>';
					echo '<p class="description">Pre-publication checklist items (optional)</p>';
					echo '</td>';
					echo '</tr>';

					echo '</table>';
				} else {
					// For other shared content, use single textarea
					echo '<textarea name="content" rows="10" class="large-text" style="font-family: monospace;">' . esc_textarea($content['content']) . '</textarea>';
				}

				submit_button('Update ' . ucwords(str_replace('_', ' ', $content['key'])), 'primary', 'submit', false);
				echo '</form>';
				echo '</div>';
			}
		} else {
			echo '<p>No shared content found.</p>';
		}

		echo '</div>';
	}

	/**
	 * Handle form submissions
	 */
	public function handleFormSubmission(): void
	{
		// Only handle on our page
		if (! isset($_GET['page']) || $_GET['page'] !== 'vm-ai-feed-ai-configs') {
			return;
		}

		// Handle AI config deletion from URL (triggered by delete link)
		if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['config_id']) && isset($_GET['_wpnonce'])) {
			$this->handleDelete();
			return;
		}

		// Handle shared content deletion from URL
		if (isset($_GET['delete_content']) && isset($_GET['_wpnonce'])) {
			$this->handleDeleteSharedContent();
			return;
		}

		// Handle memory deletion from URL
		if (isset($_GET['delete_memory']) && isset($_GET['_wpnonce'])) {
			$this->handleDeleteMemory();
			return;
		}

		if (! isset($_POST['ai_config_action'])) {
			return;
		}

		$action = sanitize_text_field($_POST['ai_config_action']);

		switch ($action) {
			case 'create':
				$this->handleCreate();
				break;
			case 'update':
				$this->handleUpdate();
				break;
			case 'create_duplicate':
				$this->handleCreateDuplicate();
				break;
			case 'create_shared_content':
				$this->handleCreateSharedContent();
				break;
			case 'update_shared_content':
				$this->handleUpdateSharedContent();
				break;
			case 'create_memory':
				$this->handleCreateMemory();
				break;
			case 'update_memory':
				$this->handleUpdateMemory();
				break;
		}
	}

	/**
	 * Handle create AI config
	 */
	private function handleCreate(): void
	{
		if (! isset($_POST['ai_config_nonce']) || ! wp_verify_nonce($_POST['ai_config_nonce'], 'create_ai_config')) {
			wp_die('Security check failed');
		}

		if (! current_user_can('manage_options')) {
			wp_die('Insufficient permissions');
		}

		// Validate and sanitize input
		$name              = sanitize_text_field($_POST['name']);
		$display_name      = sanitize_text_field(wp_unslash($_POST['display_name']));
		$wordpress_user_id = \intval($_POST['wordpress_user_id']);

		// Get prompt sections and merge them
		$prompt_role          = $this->sanitizePromptContent(wp_unslash($_POST['prompt_role']));
		$prompt_voice         = $this->sanitizePromptContent(wp_unslash($_POST['prompt_voice']));
		$prompt_instructions  = $this->sanitizePromptContent(wp_unslash($_POST['prompt_instructions']));
		$prompt_tone_examples = ! empty($_POST['prompt_tone_examples']) ? $this->sanitizePromptContent(wp_unslash($_POST['prompt_tone_examples'])) : '';
		$prompt_research      = ! empty($_POST['prompt_research']) ? $this->sanitizePromptContent(wp_unslash($_POST['prompt_research'])) : '';

		$system_prompt = $this->mergeSystemPrompt($prompt_role, $prompt_voice, $prompt_instructions, $prompt_tone_examples, $prompt_research);
		$system_prompt = $this->cleanSystemPrompt($system_prompt);

		$is_active  = isset($_POST['is_active']) && $_POST['is_active'] === '1' ? true : false;
		$is_default = isset($_POST['is_default']) && $_POST['is_default'] === '1' ? true : false;
		$provider   = ! empty($_POST['provider']) ? sanitize_text_field($_POST['provider']) : 'openai';
		$model      = ! empty($_POST['model']) ? sanitize_text_field(wp_unslash($_POST['model'])) : 'gpt-4o';

		// Validate name format
		if (! preg_match('/^[a-z0-9-]+$/', $name)) {
			add_action(
				'admin_notices',
				function () {
					echo '<div class="notice notice-error"><p>Invalid name format. Use lowercase alphanumeric with dashes only.</p></div>';
				}
			);
			return;
		}

		// Call API to create config
		$config  = AIFeedConfig::getInstance();
		$api_url = $config->getApiUrl('api/ai-configs');
		$api_key = $config->getApiKey();

		$response = wp_remote_post(
			$api_url,
			[
				'headers' => [
					'Content-Type'  => 'application/json',
					'Authorization' => "Bearer {$api_key}",
				],
				'body'    => json_encode(
					[
						'name'              => $name,
						'display_name'      => $display_name,
						'wordpress_user_id' => $wordpress_user_id,
						'system_prompt'     => $system_prompt,
						'is_active'         => $is_active,
						'is_default'        => $is_default,
						'provider'          => $provider,
						'model'             => $model,
						'metadata'          => $this->getMetadataFromPost(),
					]
				),
				'timeout' => 30,
			]
		);

		if (is_wp_error($response)) {
			add_action(
				'admin_notices',
				function () use ($response) {
					echo '<div class="notice notice-error"><p>Failed to create AI config: ' . esc_html($response->get_error_message()) . '</p></div>';
				}
			);
			return;
		}

		$response_code = wp_remote_retrieve_response_code($response);
		$response_body = wp_remote_retrieve_body($response);

		if ($response_code !== 200 && $response_code !== 201) {
			$data          = json_decode($response_body, true);
			$error_message = $data['error']['message'] ?? 'Unknown error';

			add_action(
				'admin_notices',
				function () use ($error_message, $response_body, $response_code) {
					echo '<div class="notice notice-error">';
					echo '<p><strong>Failed to create AI config:</strong> ' . esc_html($error_message) . '</p>';

					if (current_user_can('manage_options')) {
						echo '<details><summary>API Response Details (Response Code: ' . esc_html($response_code) . ')</summary>';
						echo '<pre style="background: #f5f5f5; padding: 10px; overflow: auto;">' . esc_html($response_body) . '</pre>';
						echo '</details>';
					}
					echo '</div>';
				}
			);

			return;
		}

		// Success - get the created config ID and save tools
		$response_data = json_decode($response_body, true);
		$new_config_id = $response_data['data']['id'] ?? null;

		$tools_error = null;

		// Save allowed tools if config was created successfully
		if ($new_config_id && isset($_POST['allowed_tools']) && \is_array($_POST['allowed_tools'])) {
			$allowed_tools = array_map('sanitize_text_field', $_POST['allowed_tools']);
			$tools_result  = $this->updateAllowedTools((string) $new_config_id, $allowed_tools);

			if (! $tools_result['success']) {
				$tools_error = $tools_result;
			}
		}

		// Store message in transient to survive the redirect
		if ($tools_error) {
			set_transient(
				'vm_ai_feed_admin_notice',
				[
					'type'    => 'warning',
					'message' => 'Writer profile created successfully, but tools failed to save: ' . $tools_error['error'],
				],
				30
			);
		} else {
			set_transient(
				'vm_ai_feed_admin_notice',
				[
					'type'    => 'success',
					'message' => 'Writer profile created successfully!',
				],
				30
			);
		}

		wp_redirect(admin_url('admin.php?page=vm-ai-feed-ai-configs'));
		exit;
	}

	/**
	 * Handle update AI config
	 */
	private function handleUpdate(): void
	{
		$config_id = sanitize_text_field($_POST['config_id']);

		if (! isset($_POST['ai_config_nonce']) || ! wp_verify_nonce($_POST['ai_config_nonce'], "update_ai_config_{$config_id}")) {
			wp_die('Security check failed');
		}

		if (! current_user_can('manage_options')) {
			wp_die('Insufficient permissions');
		}

		// Validate and sanitize input
		$display_name      = sanitize_text_field(wp_unslash($_POST['display_name']));
		$wordpress_user_id = \intval($_POST['wordpress_user_id']);

		// Get prompt sections and merge them
		$prompt_role          = $this->sanitizePromptContent(wp_unslash($_POST['prompt_role']));
		$prompt_voice         = $this->sanitizePromptContent(wp_unslash($_POST['prompt_voice']));
		$prompt_instructions  = $this->sanitizePromptContent(wp_unslash($_POST['prompt_instructions']));
		$prompt_tone_examples = ! empty($_POST['prompt_tone_examples']) ? $this->sanitizePromptContent(wp_unslash($_POST['prompt_tone_examples'])) : '';
		$prompt_research      = ! empty($_POST['prompt_research']) ? $this->sanitizePromptContent(wp_unslash($_POST['prompt_research'])) : '';

		$system_prompt = $this->mergeSystemPrompt($prompt_role, $prompt_voice, $prompt_instructions, $prompt_tone_examples, $prompt_research);
		$system_prompt = $this->cleanSystemPrompt($system_prompt);

		// Ensure booleans are proper booleans (not strings)
		$is_active  = isset($_POST['is_active']) && $_POST['is_active'] === '1' ? true : false;
		$is_default = isset($_POST['is_default']) && $_POST['is_default'] === '1' ? true : false;
		$provider   = ! empty($_POST['provider']) ? sanitize_text_field($_POST['provider']) : 'openai';
		$model      = ! empty($_POST['model']) ? sanitize_text_field(wp_unslash($_POST['model'])) : 'gpt-4o';

		// Get existing config to preserve metadata (but NOT tools - tools are managed separately)
		$existing_config   = $this->getAIConfigById($config_id);
		$existing_metadata = [];
		if (! empty($existing_config['metadata'])) {
			if (is_string($existing_config['metadata'])) {
				$existing_metadata = json_decode($existing_config['metadata'], true) ?: [];
			} elseif (is_array($existing_config['metadata'])) {
				$existing_metadata = $existing_config['metadata'];
			}
		}

		// Remove tools from metadata if present (tools are managed via separate endpoint)
		unset($existing_metadata['allowed_tools']);
		unset($existing_metadata['tools']);

		// Merge new metadata with existing metadata (new values override existing ones)
		$new_metadata = $this->getMetadataFromPost();
		$metadata     = array_merge($existing_metadata, $new_metadata);

		// Call API to update config
		$config  = AIFeedConfig::getInstance();
		$api_url = $config->getApiUrl("api/ai-configs/{$config_id}");
		$api_key = $config->getApiKey();

		// Build request body - match the create format exactly
		$request_body = [
			'display_name'      => $display_name,
			'wordpress_user_id' => $wordpress_user_id,
			'system_prompt'     => $system_prompt,
			'is_active'         => $is_active,
			'is_default'        => $is_default,
			'provider'          => $provider,
			'model'             => $model,
		];

		// Only include metadata if it has values (metadata should only contain generation parameters, not tools)
		if (! empty($metadata) && is_array($metadata)) {
			$request_body['metadata'] = $metadata;
		}

		// Encode request body
		$json_body = json_encode($request_body, JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);

		// Debug: Log the request (only for admins)
		if (current_user_can('manage_options') && defined('WP_DEBUG') && WP_DEBUG) {
			error_log("AI Config Update Request: {$json_body}");
		}

		$response = wp_remote_request(
			$api_url,
			[
				'method'  => 'PUT',
				'headers' => [
					'Content-Type'  => 'application/json',
					'Authorization' => "Bearer {$api_key}",
				],
				'body'    => $json_body,
				'timeout' => 30,
			]
		);

		if (is_wp_error($response)) {
			add_action(
				'admin_notices',
				function () use ($response) {
					echo '<div class="notice notice-error"><p>Failed to update AI config: ' . esc_html($response->get_error_message()) . '</p></div>';
				}
			);
			return;
		}

		$response_code = wp_remote_retrieve_response_code($response);
		if ($response_code !== 200) {
			$body          = wp_remote_retrieve_body($response);
			$data          = json_decode($body, true);
			$error_message = $data['error']['message'] ?? 'Unknown error';
			$error_details = $data['error']['details'] ?? null;

			add_action(
				'admin_notices',
				function () use ($error_message, $error_details, $response_code, $body, $json_body) {
					echo '<div class="notice notice-error">';
					echo '<p><strong>Failed to update AI config:</strong> ' . esc_html($error_message) . '</p>';
					if (current_user_can('manage_options')) {
						echo '<details style="margin-top: 10px;"><summary style="cursor: pointer; font-weight: bold;">Error Details (Response Code: ' . esc_html($response_code) . ')</summary>';
						echo '<p style="font-weight: bold; margin-top: 10px;">Request Body Sent:</p>';
						echo '<pre style="background: #f5f5f5; padding: 10px; overflow: auto; margin-top: 5px; white-space: pre-wrap;">' . esc_html($json_body) . '</pre>';
						if (! empty($error_details)) {
							echo '<p style="font-weight: bold; margin-top: 10px;">Error Details:</p>';
							echo '<pre style="background: #f5f5f5; padding: 10px; overflow: auto; margin-top: 5px;">' . esc_html(print_r($error_details, true)) . '</pre>';
						}
						echo '<p style="font-weight: bold; margin-top: 10px;">API Response:</p>';
						echo '<pre style="background: #f5f5f5; padding: 10px; overflow: auto; margin-top: 5px; white-space: pre-wrap;">' . esc_html($body) . '</pre>';
						echo '</details>';
					}
					echo '</div>';
				}
			);
			return;
		}

		// Success - save allowed tools
		$tools_error = null;

		if (isset($_POST['allowed_tools']) && is_array($_POST['allowed_tools'])) {
			$allowed_tools = array_map('sanitize_text_field', $_POST['allowed_tools']);
			$tools_result  = $this->updateAllowedTools($config_id, $allowed_tools);

			if (! $tools_result['success']) {
				$tools_error = $tools_result;
			}
		} else {
			// No tools selected - clear all tools
			$tools_result = $this->updateAllowedTools($config_id, array());

			if (! $tools_result['success']) {
				$tools_error = $tools_result;
			}
		}

		// Store message in transient to survive the redirect
		if ($tools_error) {
			set_transient(
				'vm_ai_feed_admin_notice',
				array(
					'type'    => 'warning',
					'message' => 'Writer profile updated successfully, but tools failed to save: ' . $tools_error['error'],
				),
				30
			);
		} else {
			set_transient(
				'vm_ai_feed_admin_notice',
				array(
					'type'    => 'success',
					'message' => 'Writer profile updated successfully!',
				),
				30
			);
		}

		wp_redirect(admin_url('admin.php?page=vm-ai-feed-ai-configs&action=edit&config_id=' . urlencode($config_id)));
		exit;
	}

	/**
	 * Handle create duplicate AI config
	 */
	private function handleCreateDuplicate(): void
	{
		$source_id = sanitize_text_field($_POST['source_config_id'] ?? '');
		if (! $source_id) {
			wp_die('Missing source configuration ID');
		}
		if (! isset($_POST['ai_config_nonce']) || ! wp_verify_nonce($_POST['ai_config_nonce'], 'duplicate_ai_config_' . $source_id)) {
			wp_die('Security check failed');
		}
		if (! current_user_can('manage_options')) {
			wp_die('Insufficient permissions');
		}
		// Validate inputs
		$name         = sanitize_text_field($_POST['name'] ?? '');
		$display_name = sanitize_text_field(wp_unslash($_POST['display_name'] ?? ''));
		if (! preg_match('/^[a-z0-9-]+$/', $name)) {
			add_action(
				'admin_notices',
				function () {
					echo '<div class="notice notice-error"><p>Invalid name format. Use lowercase alphanumeric with dashes only.</p></div>';
				}
			);
			return;
		}
		// Fetch source config
		$source = $this->getAIConfigById($source_id);
		if (empty($source)) {
			add_action(
				'admin_notices',
				function () {
					echo '<div class="notice notice-error"><p>Source configuration not found.</p></div>';
				}
			);
			return;
		}
		// Prepare payload
		$payload = array(
			'name'              => $name,
			'display_name'      => $display_name,
			'wordpress_user_id' => \intval($source['wordpress_user_id'] ?? 0),
			'system_prompt'     => $source['system_prompt'] ?? '',
			'is_active'         => (bool) ($source['is_active'] ?? true),
			'provider'          => $source['provider'] ?? 'openai',
			'model'             => $source['model'] ?? 'gpt-4o',
		);
		// Metadata can be string or object in API response
		$metadata = array();
		if (isset($source['metadata'])) {
			if (is_string($source['metadata'])) {
				$decoded = json_decode($source['metadata'], true);
				if (is_array($decoded)) {
					$metadata = $decoded;
				}
			} elseif (is_array($source['metadata'])) {
				$metadata = $source['metadata'];
			}
		}
		if (! empty($metadata)) {
			$payload['metadata'] = $metadata;
		}
		// Create config via API
		$config   = AIFeedConfig::getInstance();
		$api_url  = $config->getApiUrl('api/ai-configs');
		$api_key  = $config->getApiKey();
		$response = wp_remote_post(
			$api_url,
			array(
				'headers' => array(
					'Content-Type'  => 'application/json',
					'Authorization' => 'Bearer ' . $api_key,
				),
				'body'    => json_encode($payload),
				'timeout' => 30,
			)
		);
		if (is_wp_error($response)) {
			add_action(
				'admin_notices',
				function () use ($response) {
					echo '<div class="notice notice-error"><p>Failed to create duplicate: ' . esc_html($response->get_error_message()) . '</p></div>';
				}
			);
			return;
		}
		$code = wp_remote_retrieve_response_code($response);
		$body = wp_remote_retrieve_body($response);
		if ($code !== 200 && $code !== 201) {
			$data = json_decode($body, true);
			$msg  = $data['error']['message'] ?? 'Unknown error';
			add_action(
				'admin_notices',
				function () use ($msg, $code, $body) {
					echo '<div class="notice notice-error"><p>Failed to create duplicate: ' . esc_html($msg) . '</p></div>';
				}
			);
			return;
		}
		$new         = json_decode($body, true);
		$new_id      = $new['data']['id'] ?? null;
		$tools_error = null;
		if ($new_id) {
			// Copy tools from source
			$source_tools = $this->fetchAllowedTools($source_id);
			$result       = $this->updateAllowedTools((string) $new_id, $source_tools);
			if (! $result['success']) {
				$tools_error = $result['error'] ?? 'Unknown error copying tools';
			}
		}
		// Notices and redirect
		if ($tools_error) {
			set_transient(
				'vm_ai_feed_admin_notice',
				array(
					'type'    => 'warning',
					'message' => 'Writer profile duplicated, but tools failed to copy: ' . $tools_error,
				),
				30
			);
		} else {
			set_transient(
				'vm_ai_feed_admin_notice',
				array(
					'type'    => 'success',
					'message' => 'Writer profile duplicated successfully!',
				),
				30
			);
		}
		wp_redirect(admin_url('admin.php?page=vm-ai-feed-ai-configs'));
		exit;
	}

	/**
	 * Handle delete AI config
	 */
	private function handleDelete(): void
	{
		if (! isset($_GET['config_id'])) {
			wp_die('Missing configuration ID');
		}

		$config_id = sanitize_text_field($_GET['config_id']);

		if (! isset($_GET['_wpnonce']) || ! wp_verify_nonce($_GET['_wpnonce'], 'delete_ai_config_' . $config_id)) {
			wp_die('Security check failed');
		}

		if (! current_user_can('manage_options')) {
			wp_die('Insufficient permissions');
		}

		// Call API to delete config
		$config  = AIFeedConfig::getInstance();
		$api_url = $config->getApiUrl('api/ai-configs/' . $config_id);
		$api_key = $config->getApiKey();

		$response = wp_remote_request(
			$api_url,
			array(
				'method'  => 'DELETE',
				'headers' => array(
					'Authorization' => 'Bearer ' . $api_key,
				),
				'timeout' => 30,
			)
		);

		if (is_wp_error($response)) {
			add_action(
				'admin_notices',
				function () use ($response) {
					echo '<div class="notice notice-error"><p>Failed to delete AI config: ' . esc_html($response->get_error_message()) . '</p></div>';
				}
			);
			wp_redirect(admin_url('admin.php?page=vm-ai-feed-ai-configs'));
			exit;
		}

		$response_code = wp_remote_retrieve_response_code($response);
		if ($response_code !== 200) {
			$body          = wp_remote_retrieve_body($response);
			$data          = json_decode($body, true);
			$error_message = $data['error']['message'] ?? 'Unknown error';

			add_action(
				'admin_notices',
				function () use ($error_message) {
					echo '<div class="notice notice-error"><p>Failed to delete AI config: ' . esc_html($error_message) . '</p></div>';
				}
			);
			wp_redirect(admin_url('admin.php?page=vm-ai-feed-ai-configs'));
			exit;
		}

		// Success
		add_action(
			'admin_notices',
			function () {
				echo '<div class="notice notice-success is-dismissible"><p>Writer profile deleted successfully!</p></div>';
			}
		);

		wp_redirect(admin_url('admin.php?page=vm-ai-feed-ai-configs'));
		exit;
	}

	/**
	 * Handle create shared content
	 */
	private function handleCreateSharedContent(): void
	{
		if (! isset($_POST['shared_content_nonce']) || ! wp_verify_nonce($_POST['shared_content_nonce'], 'create_shared_content')) {
			wp_die('Security check failed');
		}

		if (! current_user_can('manage_options')) {
			wp_die('Insufficient permissions');
		}

		$content_key = sanitize_text_field($_POST['content_key']);
		$description = sanitize_text_field(wp_unslash($_POST['description']));

		// Check if this is editorial_guidelines - merge the three fields
		if ($content_key === 'editorial_guidelines') {
			$editorial_guidelines = isset($_POST['editorial_guidelines']) ? $this->sanitizePromptContent(wp_unslash($_POST['editorial_guidelines'])) : '';
			$formatting           = isset($_POST['formatting']) ? $this->sanitizePromptContent(wp_unslash($_POST['formatting'])) : '';
			$checklist            = isset($_POST['checklist']) ? $this->sanitizePromptContent(wp_unslash($_POST['checklist'])) : '';
			$content              = $this->mergeEditorialGuidelines($editorial_guidelines, $formatting, $checklist);
		} else {
			$content = $this->sanitizePromptContent(wp_unslash($_POST['content']));
		}

		// Validate key format
		if (! preg_match('/^[a-z0-9_]+$/', $content_key)) {
			add_action(
				'admin_notices',
				function () {
					echo '<div class="notice notice-error"><p>Invalid key format. Use lowercase alphanumeric with underscores only.</p></div>';
				}
			);
			return;
		}

		// Call API to create shared content
		$config  = AIFeedConfig::getInstance();
		$api_url = $config->getApiUrl('api/ai-configs/shared-content');
		$api_key = $config->getApiKey();

		$response = wp_remote_post(
			$api_url,
			array(
				'headers' => array(
					'Content-Type'  => 'application/json',
					'Authorization' => 'Bearer ' . $api_key,
				),
				'body'    => json_encode(
					array(
						'key'         => $content_key,
						'content'     => $content,
						'description' => $description,
					)
				),
				'timeout' => 30,
			)
		);

		if (is_wp_error($response)) {
			add_action(
				'admin_notices',
				function () use ($response) {
					echo '<div class="notice notice-error"><p>Failed to create shared content: ' . esc_html($response->get_error_message()) . '</p></div>';
				}
			);
			return;
		}

		$response_code = wp_remote_retrieve_response_code($response);
		if ($response_code !== 200 && $response_code !== 201) {
			$body          = wp_remote_retrieve_body($response);
			$data          = json_decode($body, true);
			$error_message = $data['error']['message'] ?? 'Unknown error';

			add_action(
				'admin_notices',
				function () use ($error_message) {
					echo '<div class="notice notice-error"><p>Failed to create shared content: ' . esc_html($error_message) . '</p></div>';
				}
			);
			return;
		}

		// Success
		add_action(
			'admin_notices',
			function () {
				echo '<div class="notice notice-success is-dismissible"><p>Shared content created successfully!</p></div>';
			}
		);

		wp_redirect(admin_url('admin.php?page=vm-ai-feed-ai-configs&action=shared-content'));
		exit;
	}

	/**
	 * Handle update shared content
	 */
	private function handleUpdateSharedContent(): void
	{
		$content_key = sanitize_text_field($_POST['content_key']);

		if (! isset($_POST['shared_content_nonce']) || ! wp_verify_nonce($_POST['shared_content_nonce'], 'update_shared_content_' . $content_key)) {
			wp_die('Security check failed');
		}

		if (! current_user_can('manage_options')) {
			wp_die('Insufficient permissions');
		}

		// Check if this is editorial_guidelines - merge the three fields
		if ($content_key === 'editorial_guidelines') {
			$editorial_guidelines = isset($_POST['editorial_guidelines']) ? $this->sanitizePromptContent(wp_unslash($_POST['editorial_guidelines'])) : '';
			$formatting           = isset($_POST['formatting']) ? $this->sanitizePromptContent(wp_unslash($_POST['formatting'])) : '';
			$checklist            = isset($_POST['checklist']) ? $this->sanitizePromptContent(wp_unslash($_POST['checklist'])) : '';
			$content              = $this->mergeEditorialGuidelines($editorial_guidelines, $formatting, $checklist);
		} else {
			$content = $this->sanitizePromptContent(wp_unslash($_POST['content']));
		}

		// Call API to update shared content
		$config  = AIFeedConfig::getInstance();
		$api_url = $config->getApiUrl('api/ai-configs/shared-content/' . $content_key);
		$api_key = $config->getApiKey();

		$response = wp_remote_request(
			$api_url,
			array(
				'method'  => 'PUT',
				'headers' => array(
					'Content-Type'  => 'application/json',
					'Authorization' => 'Bearer ' . $api_key,
				),
				'body'    => json_encode(
					array(
						'content' => $content,
					)
				),
				'timeout' => 30,
			)
		);

		if (is_wp_error($response)) {
			add_action(
				'admin_notices',
				function () use ($response) {
					echo '<div class="notice notice-error"><p>Failed to update shared content: ' . esc_html($response->get_error_message()) . '</p></div>';
				}
			);
			return;
		}

		$response_code = wp_remote_retrieve_response_code($response);
		if ($response_code !== 200) {
			$body          = wp_remote_retrieve_body($response);
			$data          = json_decode($body, true);
			$error_message = $data['error']['message'] ?? 'Unknown error';

			add_action(
				'admin_notices',
				function () use ($error_message) {
					echo '<div class="notice notice-error"><p>Failed to update shared content: ' . esc_html($error_message) . '</p></div>';
				}
			);
			return;
		}

		// Success
		add_action(
			'admin_notices',
			function () {
				echo '<div class="notice notice-success is-dismissible"><p>Shared content updated successfully!</p></div>';
			}
		);

		wp_redirect(admin_url('admin.php?page=vm-ai-feed-ai-configs&action=shared-content'));
		exit;
	}

	/**
	 * Handle delete shared content
	 */
	private function handleDeleteSharedContent(): void
	{
		$content_key = sanitize_text_field($_GET['delete_content']);

		if (! isset($_GET['_wpnonce']) || ! wp_verify_nonce($_GET['_wpnonce'], 'delete_shared_content_' . $content_key)) {
			wp_die('Security check failed');
		}

		if (! current_user_can('manage_options')) {
			wp_die('Insufficient permissions');
		}

		// Call API to delete shared content
		$config  = AIFeedConfig::getInstance();
		$api_url = $config->getApiUrl('api/ai-configs/shared-content/' . $content_key);
		$api_key = $config->getApiKey();

		$response = wp_remote_request(
			$api_url,
			array(
				'method'  => 'DELETE',
				'headers' => array(
					'Authorization' => 'Bearer ' . $api_key,
				),
				'timeout' => 30,
			)
		);

		if (is_wp_error($response)) {
			add_action(
				'admin_notices',
				function () use ($response) {
					echo '<div class="notice notice-error"><p>Failed to delete shared content: ' . esc_html($response->get_error_message()) . '</p></div>';
				}
			);
			wp_redirect(admin_url('admin.php?page=vm-ai-feed-ai-configs&action=shared-content'));
			exit;
		}

		$response_code = wp_remote_retrieve_response_code($response);
		if ($response_code !== 200) {
			$body          = wp_remote_retrieve_body($response);
			$data          = json_decode($body, true);
			$error_message = $data['error']['message'] ?? 'Unknown error';

			add_action(
				'admin_notices',
				function () use ($error_message) {
					echo '<div class="notice notice-error"><p>Failed to delete shared content: ' . esc_html($error_message) . '</p></div>';
				}
			);
			wp_redirect(admin_url('admin.php?page=vm-ai-feed-ai-configs&action=shared-content'));
			exit;
		}

		// Success
		add_action(
			'admin_notices',
			function () {
				echo '<div class="notice notice-success is-dismissible"><p>Shared content deleted successfully!</p></div>';
			}
		);

		wp_redirect(admin_url('admin.php?page=vm-ai-feed-ai-configs&action=shared-content'));
		exit;
	}

	/**
	 * Fetch AI configs from API
	 */
	private function fetchAIConfigs(): array
	{
		$config  = AIFeedConfig::getInstance();
		$api_url = $config->getApiUrl('api/ai-configs?limit=100');

		$response = wp_remote_get(
			$api_url,
			array(
				'timeout' => 30,
			)
		);

		if (is_wp_error($response)) {
			return array();
		}

		$body = wp_remote_retrieve_body($response);
		$data = json_decode($body, true);

		return $data['data']['ai_configs'] ?? array();
	}

	/**
	 * Get AI config by ID
	 */
	private function getAIConfigById(string $config_id): array
	{
		$config  = AIFeedConfig::getInstance();
		$api_url = $config->getApiUrl('api/ai-configs/' . $config_id);

		$response = wp_remote_get(
			$api_url,
			array(
				'timeout' => 30,
			)
		);

		if (is_wp_error($response)) {
			return array();
		}

		$body = wp_remote_retrieve_body($response);
		$data = json_decode($body, true);

		return $data['data'] ?? array();
	}

	/**
	 * Fetch shared content from API
	 */
	private function fetchSharedContent(): array
	{
		$config  = AIFeedConfig::getInstance();
		$api_url = $config->getApiUrl('api/ai-configs/shared-content');

		$response = wp_remote_get(
			$api_url,
			array(
				'timeout' => 30,
			)
		);

		if (is_wp_error($response)) {
			return array();
		}

		$response_code = wp_remote_retrieve_response_code($response);
		$body          = wp_remote_retrieve_body($response);

		if ($response_code !== 200) {
			return array();
		}

		$data = json_decode($body, true);

		if (json_last_error() !== JSON_ERROR_NONE) {
			return array();
		}

		return $data['data']['shared_content'] ?? array();
	}

	/**
	 * Fetch available tools from API
	 */
	private function fetchAvailableTools(): array
	{
		$config  = AIFeedConfig::getInstance();
		$api_url = $config->getApiUrl('api/ai-configs/available-tools');

		$response = wp_remote_get(
			$api_url,
			array(
				'timeout' => 30,
			)
		);

		if (is_wp_error($response)) {
			return array();
		}

		$response_code = wp_remote_retrieve_response_code($response);
		$body          = wp_remote_retrieve_body($response);

		if ($response_code !== 200) {
			return array();
		}

		$data = json_decode($body, true);

		if (json_last_error() !== JSON_ERROR_NONE) {
			return array();
		}

		return $data['data']['available_tools'] ?? array();
	}

	/**
	 * Fetch allowed tools for a specific config
	 */
	private function fetchAllowedTools(string $config_id): array
	{
		$config  = AIFeedConfig::getInstance();
		$api_url = $config->getApiUrl('api/ai-configs/' . $config_id . '/tools');
		$api_key = $config->getApiKey();

		$response = wp_remote_get(
			$api_url,
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $api_key,
				),
				'timeout' => 30,
			)
		);

		if (is_wp_error($response)) {
			return array();
		}

		$response_code = wp_remote_retrieve_response_code($response);
		$body          = wp_remote_retrieve_body($response);

		if ($response_code !== 200) {
			return array();
		}

		$data = json_decode($body, true);

		if (json_last_error() !== JSON_ERROR_NONE) {
			return array();
		}

		return $data['data']['allowed_tools'] ?? array();
	}

	/**
	 * Update allowed tools for a config
	 */
	private function updateAllowedTools(string $config_id, array $tools): array
	{
		$config  = AIFeedConfig::getInstance();
		$api_url = $config->getApiUrl('api/ai-configs/' . $config_id . '/tools');
		$api_key = $config->getApiKey();

		$response = wp_remote_request(
			$api_url,
			array(
				'method'  => 'PUT',
				'headers' => array(
					'Content-Type'  => 'application/json',
					'Authorization' => 'Bearer ' . $api_key,
				),
				'body'    => json_encode(
					array(
						'tools' => $tools,
					)
				),
				'timeout' => 30,
			)
		);

		if (is_wp_error($response)) {
			$error_msg = $response->get_error_message();
			return array(
				'success' => false,
				'error'   => $error_msg,
			);
		}

		$response_code = wp_remote_retrieve_response_code($response);
		$response_body = wp_remote_retrieve_body($response);

		if ($response_code !== 200) {
			$data      = json_decode($response_body, true);
			$error_msg = $data['error']['message'] ?? 'Unknown error';
			return array(
				'success'       => false,
				'error'         => $error_msg,
				'response_code' => $response_code,
				'response_body' => $response_body,
				'api_url'       => $api_url,
			);
		}

		return array(
			'success' => true,
			'data'    => json_decode($response_body, true),
		);
	}

	/**
	 * Render WordPress user dropdown
	 */
	private function renderUserDropdown(string $name, int $selected_user_id): void
	{
		$users = get_users(
			array(
				'orderby' => 'display_name',
				'order'   => 'ASC',
			)
		);

		echo '<select id="' . esc_attr($name) . '" name="' . esc_attr($name) . '" class="regular-text" required>';
		echo '<option value="">Select a user...</option>';

		foreach ($users as $user) {
			$selected = selected($selected_user_id, $user->ID, false);
			echo '<option value="' . esc_attr($user->ID) . '"' . $selected . '>';
			echo esc_html($user->display_name . ' (' . $user->user_login . ')');
			echo '</option>';
		}

		echo '</select>';
	}

	/**
	 * Render tools checkboxes
	 */
	private function renderToolsCheckboxes(array $selected_tools = array()): void
	{
		$available_tools = $this->fetchAvailableTools();

		if (empty($available_tools)) {
			echo '<p class="description">No tools available or unable to fetch tools list.</p>';
			return;
		}

		echo '<div style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background: #f9f9f9;">';

		foreach ($available_tools as $tool) {
			$tool_name             = $tool['name'];
			$tool_description      = $tool['description'];
			$requires_confirmation = $tool['requiresConfirmation'] ?? false;
			$is_checked            = in_array($tool_name, $selected_tools);

			echo '<label style="display: block; margin-bottom: 10px; padding: 5px;">';
			echo '<input type="checkbox" name="allowed_tools[]" value="' . esc_attr($tool_name) . '"' . checked($is_checked, true, false) . '> ';
			echo '<strong>' . esc_html($tool_name) . '</strong>';
			if ($requires_confirmation) {
				echo ' <span style="color: #d63638; font-size: 11px;">(requires confirmation)</span>';
			}
			echo '<br><span style="margin-left: 20px; color: #666; font-size: 12px;">' . esc_html($tool_description) . '</span>';
			echo '</label>';
		}

		echo '</div>';
		echo '<p class="description">Select which tools this writer can access. Tools marked "(requires confirmation)" will ask for user approval before executing.</p>';
	}

	/**
	 * Static method to fetch AI configs for dropdown
	 */
	public static function fetchAIConfigsForDropdown(): array
	{
		static $cached_configs = null;

		if ($cached_configs !== null) {
			return $cached_configs;
		}

		$config  = AIFeedConfig::getInstance();
		$api_url = $config->getApiUrl('api/ai-configs?limit=100');

		$response = wp_remote_get(
			$api_url,
			array(
				'timeout' => 30,
			)
		);

		if (is_wp_error($response)) {
			$cached_configs = array();
			return array();
		}

		$body = wp_remote_retrieve_body($response);
		$data = json_decode($body, true);

		$configs = $data['data']['ai_configs'] ?? array();

		// Filter to only active configs and reindex array
		$cached_configs = array_values(
			array_filter(
				$configs,
				function ($config) {
					return $config['is_active'] === true;
				}
			)
		);

		// Sort by created_at descending (newest first)
		usort(
			$cached_configs,
			function ($a, $b) {
				$time_a = isset($a['created_at']) ? strtotime($a['created_at']) : 0;
				$time_b = isset($b['created_at']) ? strtotime($b['created_at']) : 0;
				return $time_b - $time_a;
			}
		);

		return $cached_configs;
	}

	/**
	 * Display memories section for writer
	 */
	private function displayMemoriesSection(int $wordpress_user_id, string $config_id): void
	{
		$memories = $this->fetchMemoriesForUser($wordpress_user_id);

		echo '<div class="card" style="max-width: 800px; margin-top: 20px;">';
		echo '<h2>Writer Memories</h2>';
		echo '<p>Memories are automatically loaded when this writer starts a conversation. Use them to save preferences, writing style guidelines, and other important notes.</p>';

		// Create new memory form
		echo '<div style="margin-bottom: 30px; padding: 20px; background: #f0f0f1; border: 1px solid #c3c4c7; border-radius: 4px;">';
		echo '<h3 style="margin-top: 0;">Add New Memory</h3>';
		echo '<form method="post">';
		wp_nonce_field('create_memory_' . $config_id, 'memory_nonce');
		echo '<input type="hidden" name="ai_config_action" value="create_memory">';
		echo '<input type="hidden" name="wordpress_user_id" value="' . esc_attr($wordpress_user_id) . '">';
		echo '<input type="hidden" name="config_id" value="' . esc_attr($config_id) . '">';

		echo '<table class="form-table" style="margin-top: 0;">';
		echo '<tr>';
		echo '<th scope="row"><label for="new_memory_text">Memory Text *</label></th>';
		echo '<td>';
		echo '<textarea id="new_memory_text" name="memory_text" rows="3" class="large-text" required placeholder="e.g., Always write in an energetic and enthusiastic tone when covering live music events."></textarea>';
		echo '<p class="description">The memory content that will be included in the AI\'s system prompt</p>';
		echo '</td>';
		echo '</tr>';

		echo '<tr>';
		echo '<th scope="row"><label for="new_memory_category">Category</label></th>';
		echo '<td>';
		echo '<select id="new_memory_category" name="category" class="regular-text">';
		echo '<option value="">No category</option>';
		echo '<option value="style">Style</option>';
		echo '<option value="content">Content</option>';
		echo '<option value="formatting">Formatting</option>';
		echo '<option value="preferences">Preferences</option>';
		echo '</select>';
		echo '<p class="description">Optional category to organize memories</p>';
		echo '</td>';
		echo '</tr>';
		echo '</table>';

		submit_button('Add Memory', 'secondary', 'submit', false);
		echo '</form>';
		echo '</div>';

		// Existing memories
		if (! empty($memories)) {
			echo '<h3>Existing Memories (' . count($memories) . ')</h3>';

			foreach ($memories as $memory) {
				$memory_id      = $memory['id'];
				$category_badge = '';

				if (! empty($memory['category'])) {
					$category_colors = array(
						'style'       => '#2271b1',
						'content'     => '#d63638',
						'formatting'  => '#00a32a',
						'preferences' => '#996800',
					);
					$color           = $category_colors[$memory['category']] ?? '#666';
					$category_badge  = '<span style="display: inline-block; padding: 2px 8px; background: ' . esc_attr($color) . '; color: white; border-radius: 3px; font-size: 11px; font-weight: bold; text-transform: uppercase; margin-left: 10px;">' . esc_html($memory['category']) . '</span>';
				}

				echo '<div class="memory-item" id="memory-' . esc_attr($memory_id) . '" style="margin-bottom: 20px; padding: 15px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;">';

				// Display view
				echo '<div class="memory-display">';
				echo '<div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">';
				echo '<div style="flex: 1;">';
				echo '<p style="margin: 0; font-size: 14px; line-height: 1.6;">' . esc_html($memory['memory_text']) . '</p>';
				echo $category_badge;
				echo '</div>';
				echo '<div style="margin-left: 15px; white-space: nowrap;">';
				echo '<button type="button" class="button button-small" onclick="toggleMemoryEdit(' . esc_js($memory_id) . ')">Edit</button> ';

				$delete_url = wp_nonce_url(
					admin_url('admin.php?page=vm-ai-feed-ai-configs&action=edit&config_id=' . urlencode($config_id) . '&delete_memory=' . urlencode($memory_id)),
					'delete_memory_' . $memory_id
				);
				echo '<a href="' . esc_url($delete_url) . '" class="button button-small button-link-delete" onclick="return confirm(\'Are you sure you want to delete this memory?\');">Delete</a>';
				echo '</div>';
				echo '</div>';

				if (! empty($memory['created_at'])) {
					echo '<p class="description" style="margin: 5px 0 0 0; font-size: 12px;">Created: ' . esc_html(date('Y-m-d H:i', strtotime($memory['created_at']))) . '</p>';
				}
				echo '</div>';

				// Edit form (hidden by default)
				echo '<div class="memory-edit" id="memory-edit-' . esc_attr($memory_id) . '" style="display: none;">';
				echo '<form method="post">';
				wp_nonce_field('update_memory_' . $memory_id, 'memory_nonce');
				echo '<input type="hidden" name="ai_config_action" value="update_memory">';
				echo '<input type="hidden" name="memory_id" value="' . esc_attr($memory_id) . '">';
				echo '<input type="hidden" name="config_id" value="' . esc_attr($config_id) . '">';

				echo '<table class="form-table" style="margin-top: 0;">';
				echo '<tr>';
				echo '<th scope="row"><label for="memory_text_' . esc_attr($memory_id) . '">Memory Text *</label></th>';
				echo '<td>';
				echo '<textarea id="memory_text_' . esc_attr($memory_id) . '" name="memory_text" rows="3" class="large-text" required>' . esc_textarea($memory['memory_text']) . '</textarea>';
				echo '</td>';
				echo '</tr>';

				echo '<tr>';
				echo '<th scope="row"><label for="memory_category_' . esc_attr($memory_id) . '">Category</label></th>';
				echo '<td>';
				echo '<select id="memory_category_' . esc_attr($memory_id) . '" name="category" class="regular-text">';
				echo '<option value=""' . selected($memory['category'], '', false) . '>No category</option>';
				echo '<option value="style"' . selected($memory['category'], 'style', false) . '>Style</option>';
				echo '<option value="content"' . selected($memory['category'], 'content', false) . '>Content</option>';
				echo '<option value="formatting"' . selected($memory['category'], 'formatting', false) . '>Formatting</option>';
				echo '<option value="preferences"' . selected($memory['category'], 'preferences', false) . '>Preferences</option>';
				echo '</select>';
				echo '</td>';
				echo '</tr>';
				echo '</table>';

				echo '<div style="display: flex; gap: 10px;">';
				submit_button('Update Memory', 'primary', 'submit', false);
				echo '<button type="button" class="button" onclick="toggleMemoryEdit(' . esc_js($memory_id) . ')">Cancel</button>';
				echo '</div>';

				echo '</form>';
				echo '</div>';

				echo '</div>';
			}
		} else {
			echo '<p>No memories saved yet. Add your first memory above to help guide this writer.</p>';
		}

		echo '</div>';

		// Add JavaScript for toggling edit forms
?>
		<script>
			function toggleMemoryEdit(memoryId) {
				const displayEl = document.querySelector('#memory-' + memoryId + ' .memory-display');
				const editEl = document.querySelector('#memory-edit-' + memoryId);

				if (displayEl && editEl) {
					// Check computed style or current visibility
					const isEditHidden = editEl.style.display === 'none' || window.getComputedStyle(editEl).display === 'none';

					if (isEditHidden) {
						displayEl.style.display = 'none';
						editEl.style.display = 'block';
					} else {
						displayEl.style.display = 'block';
						editEl.style.display = 'none';
					}
				}
			}
		</script>
<?php
	}

	/**
	 * Fetch memories for a WordPress user
	 */
	private function fetchMemoriesForUser(int $wordpress_user_id): array
	{
		$config  = AIFeedConfig::getInstance();
		$api_url = $config->getApiUrl('api/ai-writer-memories?wordpress_user_id=' . $wordpress_user_id);
		$api_key = $config->getApiKey();

		$response = wp_remote_get(
			$api_url,
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $api_key,
				),
				'timeout' => 30,
			)
		);

		if (is_wp_error($response)) {
			return array();
		}

		$response_code = wp_remote_retrieve_response_code($response);
		$body          = wp_remote_retrieve_body($response);

		if ($response_code !== 200) {
			return array();
		}

		$data = json_decode($body, true);

		if (json_last_error() !== JSON_ERROR_NONE) {
			return array();
		}

		return $data['data']['memories'] ?? array();
	}

	/**
	 * Handle create memory
	 */
	private function handleCreateMemory(): void
	{
		$config_id = sanitize_text_field($_POST['config_id']);

		if (! isset($_POST['memory_nonce']) || ! wp_verify_nonce($_POST['memory_nonce'], 'create_memory_' . $config_id)) {
			wp_die('Security check failed');
		}

		if (! current_user_can('manage_options')) {
			wp_die('Insufficient permissions');
		}

		// Validate and sanitize input
		$wordpress_user_id = \intval($_POST['wordpress_user_id']);
		$memory_text       = $this->sanitizePromptContent(wp_unslash($_POST['memory_text']));
		$category          = ! empty($_POST['category']) ? sanitize_text_field($_POST['category']) : '';

		if (empty(trim($memory_text))) {
			add_action(
				'admin_notices',
				function () {
					echo '<div class="notice notice-error"><p>Memory text is required.</p></div>';
				}
			);
			return;
		}

		// Call API to create memory
		$config  = AIFeedConfig::getInstance();
		$api_url = $config->getApiUrl('api/ai-writer-memories');
		$api_key = $config->getApiKey();

		$request_body = array(
			'wordpress_user_id' => $wordpress_user_id,
			'memory_text'       => $memory_text,
		);

		if (! empty($category)) {
			$request_body['category'] = $category;
		}

		$response = wp_remote_post(
			$api_url,
			array(
				'headers' => array(
					'Content-Type'  => 'application/json',
					'Authorization' => 'Bearer ' . $api_key,
				),
				'body'    => json_encode($request_body),
				'timeout' => 30,
			)
		);

		if (is_wp_error($response)) {
			add_action(
				'admin_notices',
				function () use ($response) {
					echo '<div class="notice notice-error"><p>Failed to create memory: ' . esc_html($response->get_error_message()) . '</p></div>';
				}
			);
			return;
		}

		$response_code = wp_remote_retrieve_response_code($response);
		$response_body = wp_remote_retrieve_body($response);

		if ($response_code !== 200 && $response_code !== 201) {
			$data          = json_decode($response_body, true);
			$error_message = $data['error']['message'] ?? 'Unknown error';

			add_action(
				'admin_notices',
				function () use ($error_message) {
					echo '<div class="notice notice-error"><p>Failed to create memory: ' . esc_html($error_message) . '</p></div>';
				}
			);
			return;
		}

		// Success
		add_action(
			'admin_notices',
			function () {
				echo '<div class="notice notice-success is-dismissible"><p>Memory created successfully!</p></div>';
			}
		);

		wp_redirect(admin_url('admin.php?page=vm-ai-feed-ai-configs&action=edit&config_id=' . urlencode($config_id)));
		exit;
	}

	/**
	 * Handle update memory
	 */
	private function handleUpdateMemory(): void
	{
		$memory_id = sanitize_text_field($_POST['memory_id']);
		$config_id = sanitize_text_field($_POST['config_id']);

		if (! isset($_POST['memory_nonce']) || ! wp_verify_nonce($_POST['memory_nonce'], 'update_memory_' . $memory_id)) {
			wp_die('Security check failed');
		}

		if (! current_user_can('manage_options')) {
			wp_die('Insufficient permissions');
		}

		// Validate and sanitize input
		$memory_text = $this->sanitizePromptContent(wp_unslash($_POST['memory_text']));
		$category    = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';

		if (empty(trim($memory_text))) {
			add_action(
				'admin_notices',
				function () {
					echo '<div class="notice notice-error"><p>Memory text is required.</p></div>';
				}
			);
			return;
		}

		// Call API to update memory
		$config  = AIFeedConfig::getInstance();
		$api_url = $config->getApiUrl('api/ai-writer-memories/' . $memory_id);
		$api_key = $config->getApiKey();

		$request_body = array(
			'memory_text' => $memory_text,
		);

		// Include category (even if empty string, to allow clearing it)
		if ($category !== '') {
			$request_body['category'] = $category;
		} else {
			$request_body['category'] = null;
		}

		$response = wp_remote_request(
			$api_url,
			array(
				'method'  => 'PUT',
				'headers' => array(
					'Content-Type'  => 'application/json',
					'Authorization' => 'Bearer ' . $api_key,
				),
				'body'    => json_encode($request_body),
				'timeout' => 30,
			)
		);

		if (is_wp_error($response)) {
			add_action(
				'admin_notices',
				function () use ($response) {
					echo '<div class="notice notice-error"><p>Failed to update memory: ' . esc_html($response->get_error_message()) . '</p></div>';
				}
			);
			return;
		}

		$response_code = wp_remote_retrieve_response_code($response);
		if ($response_code !== 200) {
			$body          = wp_remote_retrieve_body($response);
			$data          = json_decode($body, true);
			$error_message = $data['error']['message'] ?? 'Unknown error';

			add_action(
				'admin_notices',
				function () use ($error_message) {
					echo '<div class="notice notice-error"><p>Failed to update memory: ' . esc_html($error_message) . '</p></div>';
				}
			);
			return;
		}

		// Success
		add_action(
			'admin_notices',
			function () {
				echo '<div class="notice notice-success is-dismissible"><p>Memory updated successfully!</p></div>';
			}
		);

		wp_redirect(admin_url('admin.php?page=vm-ai-feed-ai-configs&action=edit&config_id=' . urlencode($config_id)));
		exit;
	}

	/**
	 * Handle delete memory
	 */
	private function handleDeleteMemory(): void
	{
		if (! isset($_GET['delete_memory']) || ! isset($_GET['config_id'])) {
			wp_die('Missing required parameters');
		}

		$memory_id = sanitize_text_field($_GET['delete_memory']);
		$config_id = sanitize_text_field($_GET['config_id']);

		if (! isset($_GET['_wpnonce']) || ! wp_verify_nonce($_GET['_wpnonce'], 'delete_memory_' . $memory_id)) {
			wp_die('Security check failed');
		}

		if (! current_user_can('manage_options')) {
			wp_die('Insufficient permissions');
		}

		// Call API to delete memory
		$config  = AIFeedConfig::getInstance();
		$api_url = $config->getApiUrl('api/ai-writer-memories/' . $memory_id);
		$api_key = $config->getApiKey();

		$response = wp_remote_request(
			$api_url,
			array(
				'method'  => 'DELETE',
				'headers' => array(
					'Authorization' => 'Bearer ' . $api_key,
				),
				'timeout' => 30,
			)
		);

		if (is_wp_error($response)) {
			add_action(
				'admin_notices',
				function () use ($response) {
					echo '<div class="notice notice-error"><p>Failed to delete memory: ' . esc_html($response->get_error_message()) . '</p></div>';
				}
			);
			wp_redirect(admin_url('admin.php?page=vm-ai-feed-ai-configs&action=edit&config_id=' . urlencode($config_id)));
			exit;
		}

		$response_code = wp_remote_retrieve_response_code($response);
		if ($response_code !== 200) {
			$body          = wp_remote_retrieve_body($response);
			$data          = json_decode($body, true);
			$error_message = $data['error']['message'] ?? 'Unknown error';

			add_action(
				'admin_notices',
				function () use ($error_message) {
					echo '<div class="notice notice-error"><p>Failed to delete memory: ' . esc_html($error_message) . '</p></div>';
				}
			);
			wp_redirect(admin_url('admin.php?page=vm-ai-feed-ai-configs&action=edit&config_id=' . urlencode($config_id)));
			exit;
		}

		// Success
		add_action(
			'admin_notices',
			function () {
				echo '<div class="notice notice-success is-dismissible"><p>Memory deleted successfully!</p></div>';
			}
		);

		wp_redirect(admin_url('admin.php?page=vm-ai-feed-ai-configs&action=edit&config_id=' . urlencode($config_id)));
		exit;
	}
}
