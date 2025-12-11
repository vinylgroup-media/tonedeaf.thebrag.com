<?php

namespace VM\AIFeed\Admin\Views;

use VM\AIFeed\Core\AIFeedConfig;
use VM\AIFeed\Helpers\AIDateHelpers;
use VM\AIFeed\Helpers\AIStatusHelpers;
use VM\AIFeed\Helpers\AIFeedUIHelpers;
use VM\AIFeed\Helpers\ResearchDataFormatter;
use VM\AIFeed\Content\AIMarkdown;
use VM\AIFeed\Content\AIEmbeds;

/**
 * Handles displaying individual AI Feed article pages
 */
class AIFeedArticleView
{

	/**
	 * Display the complete research workflow using the research context endpoint
	 *
	 * @param string $research_request_id The research request ID
	 */
	public function displayWorkflow(string $research_request_id = ''): void
	{
		// Check user capability
		if (! current_user_can('manage_options')) {
			wp_die('You do not have permission to access this page.');
		}

		if (empty($research_request_id)) {
			wp_die('Research Request ID is required');
		}

		// Fetch research context from API
		$context = $this->getResearchContext($research_request_id);

		if (empty($context)) {
			wp_die('Research context not found for ID: ' . esc_html($research_request_id));
		}

		$back_url = admin_url('admin.php?page=vm-ai-feed-articles');

		// Render the workflow view
		$this->renderWorkflowView($context, $back_url);
	}

	/**
	 * Render the admin article view page including article metadata, content, draft/publish controls, and an embedded AI editor iframe.
	 *
	 * This function performs access checks and will terminate the request with wp_die if the current user lacks
	 * the 'manage_options' capability or if no article ID is provided. It fetches article data from the remote API,
	 * determines WordPress post/draft state, validates the configured API base URL (showing an admin error and a
	 * link to settings if the URL is invalid), constructs a secure iframe URL for the AI editor, and outputs the
	 * HTML and JavaScript required for the article UI. The injected JavaScript handles iframe postMessage events,
	 * refreshes article content via AJAX, and provides an unlock flow to delete an existing draft when applicable.
	 *
	 * @param string $article_id The article ID to display (may be 'new' for a newly created article, or a research_request_id if $try_research_id is true).
	 * @param bool   $try_research_id If true, treat $article_id as research_request_id and try to find article by that ID first.
	 */
	public function display(string $article_id = '', bool $try_research_id = false): void
	{
		// Check user capability
		if (! current_user_can('manage_options')) {
			wp_die('You do not have permission to access this page.');
		}

		if (empty($article_id)) {
			wp_die('Article ID or Research Request ID is required');
		}

		// If try_research_id is true, first try to find article by research_request_id
		$article = array();
		if ($try_research_id) {
			$article = $this->getArticleByResearchRequestId($article_id);
			// If found by research_request_id, use that article's ID
			if (! empty($article) && ! empty($article['id'])) {
				$article_id = $article['id'];
			} else {
				// Article doesn't exist yet, show research workflow view
				$this->displayByResearchRequestId($article_id);
				return;
			}
		}

		// If we don't have an article yet, try fetching by article_id
		if (empty($article)) {
			$article = $this->getArticleById($article_id);
		}

		if (empty($article)) {
			error_log('VM AI Feed: Article not found for ID: ' . $article_id);
			wp_die('Article not found. Article ID: ' . esc_html($article_id));
		}

		// Check if article is already published
		$published_post       = $this->getPublishedPost($article_id);
		$is_published         = ! empty($published_post);
		$is_wp_post_published = false;

		if ($published_post) {
			$is_wp_post_published = $published_post->post_status === 'publish';
		}

		$back_url = admin_url('admin.php?page=vm-ai-feed-articles');

		// Build iframe URL with article ID, research request ID, and WordPress user ID
		$research_request_id = $article['research_request_id'] ?? '';
		$wordpress_user_id   = $article['post_author'] ?? '';
		$writer_name         = $this->getAuthorName($article['post_author'] ?? 0);

		// Get article type to determine chat mode
		$article_type = $article['type'] ?? 'queue'; // Default to queue for backward compatibility

		// Get configurable API URL from settings
		$config       = AIFeedConfig::getInstance();
		$api_base_url = $config->getApiBaseUrl();

		// Validate API URL
		$parsed_url = parse_url($api_base_url);
		if (! $parsed_url || ! isset($parsed_url['scheme']) || ! isset($parsed_url['host'])) {
			// Show error if API URL is invalid
			echo '<div class="wrap">';
			echo '<div class="notice notice-error"><p><strong>Configuration Error:</strong> Invalid API URL configured. Please check your AI Feed Settings.</p></div>';
			echo '<p><a href="' . admin_url('admin.php?page=vm-ai-feed-settings') . '" class="button button-primary">Go to Settings</a></p>';
			echo '</div>';
			return;
		}

		// Build origin (scheme://host[:port])
		$origin = $parsed_url['scheme'] . '://' . $parsed_url['host'];
		if (isset($parsed_url['port'])) {
			$origin .= ':' . $parsed_url['port'];
		}

		$iframe_url = $api_base_url . '/chat?articleId=' . urlencode($article_id);

		// Add WordPress user ID for AI personality
		if (! empty($wordpress_user_id)) {
			$iframe_url .= '&wordpressUserId=' . urlencode($wordpress_user_id);
		}

		// Add research request ID only for queue-type articles (Research Chat Mode)
		// type="chat" articles never include researchRequestId (New Article Chat Mode)
		if ($article_type === 'queue' && ! empty($research_request_id)) {
			$iframe_url .= '&researchRequestId=' . urlencode($research_request_id);
		}

		// Add writer name
		if (! empty($writer_name)) {
			$iframe_url .= '&writerName=' . urlencode($writer_name);
		}

		echo '<div class="wrap">';
		echo '<div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">';
		echo '<a href="' . esc_url($back_url) . '" class="button" style="text-decoration: none;">← Back to Articles</a>';
		echo '<div class="nav-tab-wrapper" style="border-bottom: none; margin: 0;">';
		echo '<a href="#" class="nav-tab nav-tab-active article-view-tab" data-tab="content">Content</a>';
		echo '<a href="#" class="nav-tab article-view-tab" data-tab="seo">SEO Settings</a>';
		echo '<a href="#" class="nav-tab article-view-tab" data-tab="social">Social Media</a>';
		if (! empty($research_request_id)) {
			echo '<a href="#" class="nav-tab article-view-tab" data-tab="research">Research</a>';
		}
		echo '</div>';
		echo '</div>';

		// Main content and sidebar container
		echo '<div style="display: flex; gap: 20px; margin-bottom: 20px;">';
		// Left column - Main content
		echo '<div style="width: 50%;">';

		// Content Tab
		echo '<div id="tab-content" class="article-tab-content">';
		echo '<div class="article-content" style="background: #f9f9f9; padding: 20px; padding-top: 10px; border: 1px solid #c3c4c7; margin-bottom: 20px;">';
		echo '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">';
		echo '<p style="margin: 0;"><span class="status-badge ' . esc_attr($this->getStatusClass($article['status'] ?? 'draft')) . '">' . esc_html(ucfirst($article['status'] ?? 'draft')) . '</span></p>';
		echo '<div style="display: flex; align-items: center; gap: 10px;">';
		echo '<label for="revision-selector" style="margin: 0; font-weight: 500;">Revision:</label>';
		echo '<select id="revision-selector" class="button" style="padding: 4px 8px; height: auto;">';
		echo '<option value="">Loading...</option>';
		echo '</select>';
		echo '<button id="refresh-revisions" type="button" class="button" style="padding: 4px 8px; height: auto;" title="Refresh revisions">';
		echo '<span id="refresh-icon">🔄</span>';
		echo '</button>';
		echo '<span id="revision-status" style="font-size: 11px; color: #666; margin-left: 5px;"></span>';
		echo '</div>';
		echo '</div>';
		echo '<h1 id="article-headline" style="line-height: 2rem;">' . esc_html($article['post_title']) . '</h1>';
		echo '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">';
		echo '<div>';
		echo '<p><strong>Author:</strong> <span id="article-author">' . esc_html($this->getAuthorName($article['post_author'] ?? 0)) . '</span></p>';
		echo '<p><strong>Categories:</strong> <span id="article-categories">' . esc_html(AIFeedUIHelpers::getCategoryNames($article['post_category'] ?? '[]')) . '</span></p>';
		echo '<p><strong>Tags:</strong> <span id="article-tags">' . esc_html($article['tags_input']) . '</span></p>';
		echo '</div>';
		echo '<div>';
		echo '<p><strong>Time to Write:</strong> <span id="article-time">' . esc_html($this->calculateDuration($article['start_time'], $article['completed_time'])) . '</span></p>';
		echo '<p><strong>Current Revision:</strong> <span id="article-revision">' . esc_html($article['revision'] ?? '0') . '</span></p>';
		echo '</div>';
		echo '</div>';

		// Display progress percentage - only for queue-type articles
		if ($article_type === 'queue') {
			$progress = $article['progress_percentage'] ?? null;
			if ($progress !== null && $progress !== '') {
				$progress = (int) $progress;
				$color    = AIFeedUIHelpers::getProgressBarColor($progress);
				echo '<div style="margin-top: 15px;" id="article-progress-wrapper">';
				echo '<p><strong>Progress:</strong></p>';
				echo '<div id="article-progress-container" style="display: flex; align-items: center; gap: 10px;">';
				echo '<div style="flex: 1; background: #f0f0f0; border-radius: 5px; height: 20px; overflow: hidden;">';
				echo '<div id="article-progress-bar" style="background: ' . esc_attr($color) . '; height: 100%; width: ' . esc_attr($progress) . '%; transition: width 0.3s ease;"></div>';
				echo '</div>';
				echo '<span id="article-progress-text" style="font-weight: 600; min-width: 40px;">' . esc_html($progress) . '%</span>';
				echo '</div>';
				echo '</div>';
			} else {
				echo '<div style="margin-top: 15px; display: none;" id="article-progress-wrapper">';
				echo '<p><strong>Progress:</strong></p>';
				echo '<div id="article-progress-container" style="display: flex; align-items: center; gap: 10px;">';
				echo '<div style="flex: 1; background: #f0f0f0; border-radius: 5px; height: 20px; overflow: hidden;">';
				echo '<div id="article-progress-bar" style="background: #007cba; height: 100%; width: 0%; transition: width 0.3s ease;"></div>';
				echo '</div>';
				echo '<span id="article-progress-text" style="font-weight: 600; min-width: 40px;">0%</span>';
				echo '</div>';
				echo '</div>';
			}
		}
		echo '</div>';

		echo '<div class="article-content" style="background: #f9f9f9; padding: 20px; margin-bottom: 20px; padding-top: 0; border: 1px solid #c3c4c7;">';
		echo '<h3>Excerpt</h3>';
		echo '<p id="article-excerpt" style="font-style: italic; margin: 0;">' . esc_html($article['post_excerpt'] ?? 'No excerpt available') . '</p>';
		echo '</div>';

		echo '<div class="article-content" style="background: #f9f9f9; padding: 20px; margin-bottom: 20px; padding-top: 0; border: 1px solid #c3c4c7;">';
		echo '<h3>Content</h3>';
		echo '<p id="article-content-headline" style="font-style: italic; margin: 0;">' . esc_html($article['post_content_headline'] ?? 'No headline available') . '</p>';
		echo '<p><hr/></p>';
		echo '<div id="article-content" style="line-height: 1.6; color: #333;">';
		$__raw                 = $article['post_content'] ?? 'No content available';
		list($__md, $__embeds) = AIEmbeds::extract($__raw);
		$__html                = AIMarkdown::convert($__md);
		$__html                = AIEmbeds::inject($__html, $__embeds);
		echo wp_kses($__html, AIEmbeds::allowedHtml());
		echo '</div>';
		echo '</div>';

		// Feedback Section (only on Content tab)
		$feedback_data = null;
		if (isset($article['feedback']) && ! empty($article['feedback']) && $article['feedback'] !== 'null') {
			// Handle if feedback is already an array (unlikely but possible)
			if (is_array($article['feedback'])) {
				$feedback_data = $article['feedback'];
			} else {
				// Parse JSON string
				$feedback_data = json_decode($article['feedback'], true);
				if (json_last_error() !== JSON_ERROR_NONE || ! is_array($feedback_data)) {
					$feedback_data = null;
				}
			}
		}

		if ($feedback_data) {
			echo '<div class="article-content" id="article-feedback" style="background: #f9f9f9; padding: 20px; margin-bottom: 20px; padding-top: 0; border: 1px solid #c3c4c7;">';
			echo '<h3>Feedback</h3>';

			// Scores section
			if (
				isset($feedback_data['sourceAdherence']) || isset($feedback_data['factualAccuracy']) || isset($feedback_data['citationIntegrity']) ||
				isset($feedback_data['temporalAccuracy']) || isset($feedback_data['internalLinkAccuracy']) || isset($feedback_data['seoOptimized'])
			) {
				echo '<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 15px;">';

				if (isset($feedback_data['sourceAdherence'])) {
					echo '<div style="text-align: center; padding: 10px; background: white; border-radius: 4px; border: 1px solid #e0e0e0;">';
					echo '<div style="font-weight: bold; font-size: 20px; color: #0073aa;">' . esc_html($feedback_data['sourceAdherence']) . '</div>';
					echo '<div style="font-size: 11px; color: #666; margin-top: 4px;">Source Adherence</div>';
					echo '</div>';
				}

				if (isset($feedback_data['factualAccuracy'])) {
					echo '<div style="text-align: center; padding: 10px; background: white; border-radius: 4px; border: 1px solid #e0e0e0;">';
					echo '<div style="font-weight: bold; font-size: 20px; color: #0073aa;">' . esc_html($feedback_data['factualAccuracy']) . '</div>';
					echo '<div style="font-size: 11px; color: #666; margin-top: 4px;">Factual Accuracy</div>';
					echo '</div>';
				}

				if (isset($feedback_data['citationIntegrity'])) {
					echo '<div style="text-align: center; padding: 10px; background: white; border-radius: 4px; border: 1px solid #e0e0e0;">';
					echo '<div style="font-weight: bold; font-size: 20px; color: #0073aa;">' . esc_html($feedback_data['citationIntegrity']) . '</div>';
					echo '<div style="font-size: 11px; color: #666; margin-top: 4px;">Citation Integrity</div>';
					echo '</div>';
				}

				if (isset($feedback_data['temporalAccuracy'])) {
					echo '<div style="text-align: center; padding: 10px; background: white; border-radius: 4px; border: 1px solid #e0e0e0;">';
					echo '<div style="font-weight: bold; font-size: 20px; color: #0073aa;">' . esc_html($feedback_data['temporalAccuracy']) . '</div>';
					echo '<div style="font-size: 11px; color: #666; margin-top: 4px;">Temporal Accuracy</div>';
					echo '</div>';
				}

				if (isset($feedback_data['internalLinkAccuracy'])) {
					echo '<div style="text-align: center; padding: 10px; background: white; border-radius: 4px; border: 1px solid #e0e0e0;">';
					echo '<div style="font-weight: bold; font-size: 20px; color: #0073aa;">' . esc_html($feedback_data['internalLinkAccuracy']) . '</div>';
					echo '<div style="font-size: 11px; color: #666; margin-top: 4px;">Internal Link Accuracy</div>';
					echo '</div>';
				}

				if (isset($feedback_data['seoOptimized'])) {
					echo '<div style="text-align: center; padding: 10px; background: white; border-radius: 4px; border: 1px solid #e0e0e0;">';
					echo '<div style="font-weight: bold; font-size: 20px; color: #0073aa;">' . esc_html($feedback_data['seoOptimized']) . '</div>';
					echo '<div style="font-size: 11px; color: #666; margin-top: 4px;">SEO Optimized</div>';
					echo '</div>';
				}

				echo '</div>';
			}

			// Specific Issues section
			if (isset($feedback_data['specificIssues']) && is_array($feedback_data['specificIssues']) && count($feedback_data['specificIssues']) > 0) {
				echo '<div style="margin-bottom: 15px;">';
				echo '<h4 style="margin: 0 0 10px 0; font-size: 14px; color: #d63638; font-weight: 600;">Specific Issues</h4>';
				echo '<ul style="margin: 0; padding-left: 20px; line-height: 1.6;">';
				foreach ($feedback_data['specificIssues'] as $issue) {
					echo '<li style="margin-bottom: 8px; font-size: 13px; color: #333;">' . esc_html($issue) . '</li>';
				}
				echo '</ul>';
				echo '</div>';
			}

			// Improvement Suggestions section
			if (isset($feedback_data['improvementSuggestions']) && is_array($feedback_data['improvementSuggestions']) && count($feedback_data['improvementSuggestions']) > 0) {
				echo '<div>';
				echo '<h4 style="margin: 0 0 10px 0; font-size: 14px; color: #0073aa; font-weight: 600;">Improvement Suggestions</h4>';
				echo '<ul style="margin: 0; padding-left: 20px; line-height: 1.6;">';
				foreach ($feedback_data['improvementSuggestions'] as $suggestion) {
					echo '<li style="margin-bottom: 8px; font-size: 13px; color: #333;">' . esc_html($suggestion) . '</li>';
				}
				echo '</ul>';
				echo '</div>';
			}
			echo '</div>';
		} else {
			echo '<div class="article-content" id="article-feedback" style="background: #f9f9f9; padding: 20px; margin-bottom: 20px; padding-top: 0; border: 1px solid #c3c4c7;">';
			echo '<h3>Feedback</h3>';
			echo '<p style="margin: 0; color: #666; font-style: italic;">No feedback on current edit</p>';
			echo '</div>';
		}

		// Close Content Tab
		echo '</div>'; // end tab-content

		// SEO Settings Tab
		echo '<div id="tab-seo" class="article-tab-content" style="display: none;">';
		echo '<div class="article-content" style="background: #f9f9f9; padding: 20px; margin-bottom: 20px; padding-top: 0; border: 1px solid #c3c4c7;">';
		echo '<h3>SEO Settings</h3>';
		echo '<div style="display: grid; gap: 12px;">';
		echo '<div>';
		echo '<strong style="display: block; margin-bottom: 4px; font-size: 13px; color: #666;">SEO Title</strong>';
		echo '<p id="article-seo-title" style="margin: 0; font-size: 14px;">' . esc_html($article['seo_title'] ?? 'Not set') . '</p>';
		echo '</div>';
		echo '<div>';
		echo '<strong style="display: block; margin-bottom: 4px; font-size: 13px; color: #666;">Meta Description</strong>';
		echo '<p id="article-seo-metadesc" style="margin: 0; font-size: 14px;">' . esc_html($article['seo_metadesc'] ?? 'Not set') . '</p>';
		echo '</div>';
		echo '<div>';
		echo '<strong style="display: block; margin-bottom: 4px; font-size: 13px; color: #666;">Focus Keyword</strong>';
		echo '<p id="article-seo-focus-keyword" style="margin: 0; font-size: 14px;">' . esc_html($article['seo_focus_keyword'] ?? 'Not set') . '</p>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
		// Close SEO Tab
		echo '</div>'; // end tab-seo

		// Social Media Tab
		echo '<div id="tab-social" class="article-tab-content" style="display: none;">';
		echo '<div class="article-content" style="background: #f9f9f9; padding: 20px; margin-bottom: 20px; padding-top: 0; border: 1px solid #c3c4c7;">';
		echo '<h3>Social Media</h3>';
		echo '<div style="display: grid; gap: 15px;">';
		echo '<div style="padding: 12px; background: white; border-radius: 4px; border: 1px solid #e0e0e0;">';
		echo '<h4 style="margin: 0 0 10px 0; font-size: 14px; color: #0073aa;">Open Graph (Facebook, LinkedIn)</h4>';
		echo '<div style="display: grid; gap: 8px;">';
		echo '<div>';
		echo '<strong style="display: block; margin-bottom: 4px; font-size: 12px; color: #666;">Title</strong>';
		echo '<p id="article-seo-og-title" style="margin: 0; font-size: 13px;">' . esc_html($article['seo_og_title'] ?? 'Not set') . '</p>';
		echo '</div>';
		echo '<div>';
		echo '<strong style="display: block; margin-bottom: 4px; font-size: 12px; color: #666;">Description</strong>';
		echo '<p id="article-seo-og-description" style="margin: 0; font-size: 13px;">' . esc_html($article['seo_og_description'] ?? 'Not set') . '</p>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
		echo '<div style="padding: 12px; background: white; border-radius: 4px; border: 1px solid #e0e0e0;">';
		echo '<h4 style="margin: 0 0 10px 0; font-size: 14px; color: #1DA1F2;">Twitter Card</h4>';
		echo '<div style="display: grid; gap: 8px;">';
		echo '<div>';
		echo '<strong style="display: block; margin-bottom: 4px; font-size: 12px; color: #666;">Title</strong>';
		echo '<p id="article-seo-twitter-title" style="margin: 0; font-size: 13px;">' . esc_html($article['seo_twitter_title'] ?? 'Not set') . '</p>';
		echo '</div>';
		echo '<div>';
		echo '<strong style="display: block; margin-bottom: 4px; font-size: 12px; color: #666;">Description</strong>';
		echo '<p id="article-seo-twitter-description" style="margin: 0; font-size: 13px;">' . esc_html($article['seo_twitter_description'] ?? 'Not set') . '</p>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
		// Close Social Media Tab
		echo '</div>'; // end tab-social

		// Research Tab - only if article has research_request_id
		if (! empty($research_request_id)) {
			// Use combined endpoint to get research request with articles
			$combined_data = $this->getResearchRequestCombined($research_request_id);
			$research_data = $combined_data['research_request'] ?? array();

			echo '<div id="tab-research" class="article-tab-content" style="display: none;">';

			// Queries
			if (! empty($research_data['queries']) && is_array($research_data['queries'])) {
				$q = $research_data['queries'];
				echo '<div class="article-content" style="background: #f9f9f9; padding: 20px; margin-bottom: 20px; padding-top: 0; border: 1px solid #c3c4c7;">';
				echo '<h3>Queries</h3>';
				if (! empty($q['searchQuery'])) {
					echo '<p><strong>Search Query:</strong> ' . esc_html($q['searchQuery']) . '</p>';
				}
				if (! empty($q['researchQuery'])) {
					echo '<p><strong>Research Query:</strong> ' . esc_html($q['researchQuery']) . '</p>';
				}
				if (! empty($q['youTubeSearchQuery'])) {
					echo '<p><strong>YouTube Search:</strong> ' . esc_html($q['youTubeSearchQuery']) . '</p>';
				}
				if (! empty($q['context'])) {
					echo '<p><strong>Context:</strong> ' . esc_html($q['context']) . '</p>';
				}
				if (! empty($q['primaryEntities']) && is_array($q['primaryEntities'])) {
					echo '<p><strong>Primary Entities:</strong> ' . esc_html(implode(', ', $q['primaryEntities'])) . '</p>';
				}
				if (! empty($q['secondaryEntities']) && is_array($q['secondaryEntities'])) {
					echo '<p><strong>Secondary Entities:</strong> ' . esc_html(implode(', ', $q['secondaryEntities'])) . '</p>';
				}
				echo '</div>';
			}

			// Archives
			if (! empty($research_data['archives']) && is_array($research_data['archives'])) {
				echo '<div class="article-content" style="background: #f9f9f9; padding: 20px; margin-bottom: 20px; padding-top: 0; border: 1px solid #c3c4c7;">';
				echo '<h3>Archives</h3>';
				if (! empty($research_data['archives']['query'])) {
					echo '<p><strong>Query:</strong> ' . esc_html($research_data['archives']['query']) . '</p>';
				}
				if (! empty($research_data['archives']['response'])) {
					$archives_html = ResearchDataFormatter::formatResearchResponse($research_data['archives']['response']);
					echo '<div style="background: #f8f9fa; padding: 10px; border-left: 4px solid #007cba; line-height: 1.6;">' . wp_kses_post($archives_html) . '</div>';
				}
				echo '</div>';
			}

			// Recent News
			if (! empty($research_data['recent_news']) && is_array($research_data['recent_news'])) {
				echo '<div class="article-content" style="background: #f9f9f9; padding: 20px; margin-bottom: 20px; padding-top: 0; border: 1px solid #c3c4c7;">';
				echo '<h3>Recent News</h3>';
				foreach ($research_data['recent_news'] as $rn) {
					if (! empty($rn['query'])) {
						echo '<p><strong>Query:</strong> ' . esc_html($rn['query']) . '</p>';
					}
					if (! empty($rn['response'])) {
						$recent_news_html = ResearchDataFormatter::formatResearchResponse($rn['response']);
						echo '<div style="background: #f8f9fa; padding: 10px; border-left: 4px solid #007cba; line-height: 1.6; margin-bottom: 15px;">' . wp_kses_post($recent_news_html) . '</div>';
					}
				}
				echo '</div>';
			}

			// Web search results
			if (! empty($research_data['web_search']) && is_array($research_data['web_search'])) {
				echo '<div class="article-content" style="background: #f9f9f9; padding: 20px; margin-bottom: 20px; padding-top: 0; border: 1px solid #c3c4c7;">';
				echo '<h3>Web Search</h3>';
				if (! empty($research_data['web_search']['query'])) {
					echo '<p><strong>Query:</strong> ' . esc_html($research_data['web_search']['query']) . '</p>';
				}
				if (! empty($research_data['web_search']['results']) && is_array($research_data['web_search']['results'])) {
					foreach ($research_data['web_search']['results'] as $result) {
						echo '<div style="background: white; padding: 15px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 12px;">';
						echo '<h4 style="margin: 0 0 8px 0; font-size: 16px;">';
						if (! empty($result['link'])) {
							echo '<a href="' . esc_url($result['link']) . '" target="_blank" style="color: #007cba; text-decoration: none;">' . esc_html($result['title'] ?? 'Untitled') . '</a>';
						} else {
							echo esc_html($result['title'] ?? 'Untitled');
						}
						echo '</h4>';
						if (! empty($result['source']) || ! empty($result['date'])) {
							echo '<div style="color: #666; font-size: 13px; margin-bottom: 6px;">';
							if (! empty($result['source'])) {
								echo '<span><strong>Source:</strong> ' . esc_html($result['source']) . '</span>';
							}
							if (! empty($result['date'])) {
								echo '<span style="margin-left: 12px;"><strong>Date:</strong> ' . esc_html($result['date']) . '</span>';
							}
							echo '</div>';
						}
						if (! empty($result['snippet'])) {
							echo '<div style="line-height: 1.5; color: #555; font-size: 14px;">' . esc_html($result['snippet']) . '</div>';
						}
						echo '</div>';
					}
				} else {
					echo '<p style="color: #666; font-style: italic;">No search results.</p>';
				}
				echo '</div>';
			}

			// YouTube results
			if (! empty($research_data['youtube']) && is_array($research_data['youtube'])) {
				echo '<div class="article-content" style="background: #f9f9f9; padding: 20px; margin-bottom: 20px; padding-top: 0; border: 1px solid #c3c4c7;">';
				echo '<h3>YouTube</h3>';
				if (! empty($research_data['youtube']['query'])) {
					echo '<p><strong>Query:</strong> ' . esc_html($research_data['youtube']['query']) . '</p>';
				}
				if (! empty($research_data['youtube']['results']) && is_array($research_data['youtube']['results'])) {
					foreach ($research_data['youtube']['results'] as $yt) {
						echo '<div style="background: white; padding: 15px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 12px;">';
						echo '<h4 style="margin: 0 0 8px 0; font-size: 16px;">';
						if (! empty($yt['link'])) {
							echo '<a href="' . esc_url($yt['link']) . '" target="_blank" style="color: #007cba; text-decoration: none;">' . esc_html($yt['title'] ?? 'Video') . '</a>';
						} else {
							echo esc_html($yt['title'] ?? 'Video');
						}
						echo '</h4>';
						if (! empty($yt['description'])) {
							echo '<div style="line-height: 1.5; color: #555; font-size: 14px;">' . esc_html($yt['description']) . '</div>';
						}
						echo '</div>';
					}
				} else {
					echo '<p style="color: #666; font-style: italic;">No YouTube results.</p>';
				}
				echo '</div>';
			}

			echo '</div>'; // end tab-research
		}
		echo '</div>'; // end left column

		// Right column - Sidebar with iframe chat
		echo '<div style="width: 50%; position: sticky; top: 40px; z-index: 2000; align-self: flex-start;">';
		echo '<div class="article-content" style="background: #f9f9f9; padding: 20px; margin-bottom: 20px; border: 1px solid #c3c4c7;">';
		echo '<div style="display: flex; flex-direction: column; gap: 10px;">';

		if ($is_published) {
			// Show published status
			echo '<div style="padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 10px;">';
			echo '<p style="margin: 0; color: #155724;"><strong>✓ Draft Created</strong></p>';
			echo '<p style="margin: 5px 0 0 0; font-size: 12px; color: #155724;">';
			echo '<a href="' . esc_url(get_permalink($published_post->ID)) . '" target="_blank" style="color: #155724;">View Post</a> | ';
			echo '<a href="' . esc_url(get_edit_post_link($published_post->ID)) . '" target="_blank" style="color: #155724;">Edit Post</a>';
			echo '</p>';
			echo '</div>';

			// Only show unlock option if WP post is still a draft
			if (! $is_wp_post_published) {
				echo '<div id="unlock-section" style="display: none;">';
				echo '<button id="publish-article" class="button button-primary" style="width: 100%;" data-article-id="' . esc_attr($article_id) . '" data-published-id="' . esc_attr($published_post->ID) . '">Update Draft</button>';
				echo '</div>';

				echo '<button id="unlock-publish" class="button button-secondary" style="width: 100%;" data-warning="true">Unlock to Update Draft</button>';
			} else {
				// Show message that post is published and cannot be updated
				echo '<div style="padding: 10px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px; margin-bottom: 10px;">';
				echo '<p style="margin: 0; color: #856404; font-size: 13px;"><strong>Post is published</strong> - Cannot update published content</p>';
				echo '</div>';
			}
		} else {
			// Show normal publish button
			echo '<button id="publish-article" class="button button-primary" style="width: 100%;" data-article-id="' . esc_attr($article_id) . '">Create Draft</button>';
		}

		echo '<div id="publish-status" style="display: none; margin-top: 10px; padding: 10px; border-radius: 4px;"></div>';
		echo '</div>';
		echo '</div>';
		echo '<div class="article-content" style="background: #f9f9f9; padding: 20px; margin-bottom: 20px; border: 1px solid #c3c4c7;">';
		echo '<div id="ai-chat-iframe-container" style="width: 100%; height: 600px; border: 1px solid #ddd; border-radius: 4px; overflow: hidden; position: relative;">';
		echo '<iframe id="chat-agent-iframe" src="' . esc_url($iframe_url) . '&cache=' . time() . '" width="100%" height="600" scrolling="yes" style="border: none; display: block; position: absolute; top: 0; left: 0; width: 100%; height: 100%;" loading="eager" sandbox="allow-scripts allow-same-origin"></iframe>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
		echo '</div>';

		// Add research request ID, article ID, and type to JavaScript context
		echo '<script>var currentResearchRequestId = "' . esc_js($article['research_request_id'] ?? '') . '";</script>';
		echo '<script>var currentArticleId = "' . esc_js($article_id) . '";</script>';
		echo '<script>var currentArticleType = "' . esc_js($article_type) . '";</script>';
?>
		<style>
			.article-view-tab {
				cursor: pointer;
				transition: all 0.2s ease;
			}

			.article-view-tab:hover {
				background: #e9ecef;
			}

			.article-view-tab.nav-tab-active {
				background: #f9f9f9;
				border-bottom-color: #f9f9f9;
			}

			.article-tab-content {
				animation: fadeIn 0.3s ease-in;
			}

			@keyframes fadeIn {
				from {
					opacity: 0;
					transform: translateY(-10px);
				}

				to {
					opacity: 1;
					transform: translateY(0);
				}
			}
		</style>
		<script>
			var ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
			var aiChatNonce = '<?php echo wp_create_nonce('ai_chat_nonce'); ?>';

			// Tab switching functionality
			document.addEventListener('DOMContentLoaded', function() {
				const tabs = document.querySelectorAll('.article-view-tab');
				const tabContents = document.querySelectorAll('.article-tab-content');

				tabs.forEach(function(tab) {
					tab.addEventListener('click', function(e) {
						e.preventDefault();

						const targetTab = this.getAttribute('data-tab');

						// Remove active class from all tabs
						tabs.forEach(function(t) {
							t.classList.remove('nav-tab-active');
						});

						// Add active class to clicked tab
						this.classList.add('nav-tab-active');

						// Hide all tab contents
						tabContents.forEach(function(content) {
							content.style.display = 'none';
						});

						// Show the selected tab content
						const selectedContent = document.getElementById('tab-' + targetTab);
						if (selectedContent) {
							selectedContent.style.display = 'block';
						}

						console.log('[Article View] Switched to tab:', targetTab);
					});
				});

				// Ensure publish button is enabled on page load if no draft exists
				const publishBtn = document.getElementById('publish-article');
				const unlockBtn = document.getElementById('unlock-publish');

				// If there's no unlock button (meaning no draft exists), ensure publish button is enabled
				if (publishBtn && !unlockBtn) {
					publishBtn.disabled = false;
					console.log('Publish button enabled on page load');
				}
			});

			// Listen for messages from the iframe
			// Based on iframe API docs: only 'article_updated' events are sent
			console.log('[Article View] PostMessage listener initialized');
			console.log('[Article View] Expected origin:', '<?php echo esc_js($origin); ?>');
			console.log('[Article View] Current article ID:', currentArticleId);

			window.addEventListener('message', function(event) {
				console.log('[Article View] Received postMessage:', {
					origin: event.origin,
					type: event.data?.type,
					updateType: event.data?.updateType,
					articleId: event.data?.articleId,
					revisionNumber: event.data?.revisionNumber
				});

				// Security: Verify origin
				if (event.origin !== '<?php echo esc_js($origin); ?>') {
					console.warn('[Article View] ⚠️ Ignoring message from unknown origin:', event.origin);
					console.warn('[Article View] Expected:', '<?php echo esc_js($origin); ?>');
					return;
				}

				const message = event.data;

				// Handle article updates (revision_created, rolled_back, etc)
				if (message.type === 'article_updated') {
					console.log('[Article View] ✓ Article updated:', message.updateType || 'unknown type');
					console.log('[Article View] Revision:', message.revisionNumber, '| Timestamp:', message.timestamp);
					console.log('[Article View] Article ID:', message.articleId || 'not specified', '| Current:', currentArticleId);

					// Only refresh for actual tool completions (not typing/editing)
					// According to iframe docs, should only fire on tool completion (createArticleRevision, rollbackArticleRevision)
					if (message.updateType === 'revision_created' || message.updateType === 'rolled_back') {
						// Refresh if it's the current article or if no specific ID was sent
						if (!message.articleId || message.articleId == currentArticleId) {
							console.log('[Article View] → Refreshing article content...');
							refreshArticleContent();
						} else {
							console.log('[Article View] ⊗ Different article - skipping refresh');
						}
					} else {
						console.log('[Article View] ⊗ Ignoring update type:', message.updateType, '(only refresh on revision_created or rolled_back)');
					}
				}
			});

			// Function to update feedback section
			function updateFeedbackSection(feedbackJson) {
				const feedbackContainer = document.getElementById('article-feedback');
				if (!feedbackContainer) return;

				let feedbackData = null;

				// Skip if feedback is null, undefined, empty string, or the string 'null'
				if (!feedbackJson || feedbackJson === 'null' || feedbackJson === '') {
					feedbackContainer.innerHTML = '<h3>Feedback</h3><p style="margin: 0; color: #666; font-style: italic;">No feedback on current edit</p>';
					return;
				}

				try {
					// Handle string JSON
					if (typeof feedbackJson === 'string') {
						feedbackData = JSON.parse(feedbackJson);
					} else if (typeof feedbackJson === 'object') {
						feedbackData = feedbackJson;
					}
				} catch (e) {
					console.error('Failed to parse feedback JSON:', e);
					feedbackContainer.innerHTML = '<h3>Feedback</h3><p style="margin: 0; color: #666; font-style: italic;">No feedback on current edit</p>';
					return;
				}

				// Check if we have valid feedback data
				if (!feedbackData || (typeof feedbackData === 'object' && Object.keys(feedbackData).length === 0)) {
					feedbackContainer.innerHTML = '<h3>Feedback</h3><p style="margin: 0; color: #666; font-style: italic;">No feedback on current edit</p>';
					return;
				}

				let html = '<h3>Feedback</h3>';

				// Scores section
				const hasScores = feedbackData.sourceAdherence || feedbackData.factualAccuracy || feedbackData.citationIntegrity ||
					feedbackData.temporalAccuracy || feedbackData.internalLinkAccuracy || feedbackData.seoOptimized;

				if (hasScores) {
					html += '<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 15px;">';

					if (feedbackData.sourceAdherence) {
						html += '<div style="text-align: center; padding: 10px; background: white; border-radius: 4px; border: 1px solid #e0e0e0;">';
						html += '<div style="font-weight: bold; font-size: 20px; color: #0073aa;">' + feedbackData.sourceAdherence + '</div>';
						html += '<div style="font-size: 11px; color: #666; margin-top: 4px;">Source Adherence</div>';
						html += '</div>';
					}

					if (feedbackData.factualAccuracy) {
						html += '<div style="text-align: center; padding: 10px; background: white; border-radius: 4px; border: 1px solid #e0e0e0;">';
						html += '<div style="font-weight: bold; font-size: 20px; color: #0073aa;">' + feedbackData.factualAccuracy + '</div>';
						html += '<div style="font-size: 11px; color: #666; margin-top: 4px;">Factual Accuracy</div>';
						html += '</div>';
					}

					if (feedbackData.citationIntegrity) {
						html += '<div style="text-align: center; padding: 10px; background: white; border-radius: 4px; border: 1px solid #e0e0e0;">';
						html += '<div style="font-weight: bold; font-size: 20px; color: #0073aa;">' + feedbackData.citationIntegrity + '</div>';
						html += '<div style="font-size: 11px; color: #666; margin-top: 4px;">Citation Integrity</div>';
						html += '</div>';
					}

					if (feedbackData.temporalAccuracy) {
						html += '<div style="text-align: center; padding: 10px; background: white; border-radius: 4px; border: 1px solid #e0e0e0;">';
						html += '<div style="font-weight: bold; font-size: 20px; color: #0073aa;">' + feedbackData.temporalAccuracy + '</div>';
						html += '<div style="font-size: 11px; color: #666; margin-top: 4px;">Temporal Accuracy</div>';
						html += '</div>';
					}

					if (feedbackData.internalLinkAccuracy) {
						html += '<div style="text-align: center; padding: 10px; background: white; border-radius: 4px; border: 1px solid #e0e0e0;">';
						html += '<div style="font-weight: bold; font-size: 20px; color: #0073aa;">' + feedbackData.internalLinkAccuracy + '</div>';
						html += '<div style="font-size: 11px; color: #666; margin-top: 4px;">Internal Link Accuracy</div>';
						html += '</div>';
					}

					if (feedbackData.seoOptimized) {
						html += '<div style="text-align: center; padding: 10px; background: white; border-radius: 4px; border: 1px solid #e0e0e0;">';
						html += '<div style="font-weight: bold; font-size: 20px; color: #0073aa;">' + feedbackData.seoOptimized + '</div>';
						html += '<div style="font-size: 11px; color: #666; margin-top: 4px;">SEO Optimized</div>';
						html += '</div>';
					}

					html += '</div>';
				}

				// Specific Issues section
				if (feedbackData.specificIssues && Array.isArray(feedbackData.specificIssues) && feedbackData.specificIssues.length > 0) {
					html += '<div style="margin-bottom: 15px;">';
					html += '<h4 style="margin: 0 0 10px 0; font-size: 14px; color: #d63638; font-weight: 600;">Specific Issues</h4>';
					html += '<ul style="margin: 0; padding-left: 20px; line-height: 1.6;">';
					feedbackData.specificIssues.forEach(function(issue) {
						html += '<li style="margin-bottom: 8px; font-size: 13px; color: #333;">' + escapeHtml(issue) + '</li>';
					});
					html += '</ul>';
					html += '</div>';
				}

				// Improvement Suggestions section
				if (feedbackData.improvementSuggestions && Array.isArray(feedbackData.improvementSuggestions) && feedbackData.improvementSuggestions.length > 0) {
					html += '<div>';
					html += '<h4 style="margin: 0 0 10px 0; font-size: 14px; color: #0073aa; font-weight: 600;">Improvement Suggestions</h4>';
					html += '<ul style="margin: 0; padding-left: 20px; line-height: 1.6;">';
					feedbackData.improvementSuggestions.forEach(function(suggestion) {
						html += '<li style="margin-bottom: 8px; font-size: 13px; color: #333;">' + escapeHtml(suggestion) + '</li>';
					});
					html += '</ul>';
					html += '</div>';
				}

				if (typeof DOMPurify !== 'undefined') {
					feedbackContainer.innerHTML = DOMPurify.sanitize(html);
				} else {
					feedbackContainer.innerHTML = html;
				}
			}

			// Helper function to escape HTML
			function escapeHtml(text) {
				const div = document.createElement('div');
				div.textContent = text;
				return div.innerHTML;
			}

			// Expose updateFeedbackSection globally for use by ai-chat.js
			window.updateFeedbackSection = updateFeedbackSection;

			// Function to refresh article content from API
			function refreshArticleContent() {
				if (!currentArticleId || currentArticleId === 'new') {
					console.log('[Article View] No article ID available for content refresh');
					return;
				}

				console.log('[Article View] → Starting article content refresh for ID:', currentArticleId);

				fetch(ajaxUrl, {
						method: 'POST',
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded',
						},
						body: 'action=refresh_article_content&article_id=' + currentArticleId + '&nonce=' + aiChatNonce
					})
					.then(response => response.json())
					.then(data => {
						if (data.success && data.data.article) {
							const article = data.data.article;

							// Update article headline
							const headlineEl = document.getElementById('article-headline');
							if (headlineEl) headlineEl.textContent = article.post_title;

							// Update article content (prefer rendered HTML from server)
							const contentEl = document.getElementById('article-content');
							if (contentEl && typeof DOMPurify !== 'undefined') {
								const rawHtml = data.data.article_html || article.post_content || '';
								// Sanitize HTML with DOMPurify before inserting
								const cleanHtml = DOMPurify.sanitize(rawHtml, {
									ADD_TAGS: ['iframe'],
									ADD_ATTR: ['allow', 'allowfullscreen', 'frameborder', 'scrolling', 'loading', 'referrerpolicy']
								});
								contentEl.innerHTML = cleanHtml;
							} else if (contentEl) {
								// Fallback: DOMPurify is unavailable
								console.warn('[Article View] ⚠️ DOMPurify is not available. Article content will not be rendered for security reasons.');
								contentEl.innerHTML = '<div style="color: #b00; font-weight: bold;">Unable to display article content: required security library (DOMPurify) is missing.</div>';
							}
							// Update categories
							const categoriesEl = document.getElementById('article-categories');
							if (categoriesEl) categoriesEl.textContent = article.post_category_names || 'Uncategorized';

							// Update SEO fields
							const seoTitleEl = document.getElementById('article-seo-title');
							if (seoTitleEl) seoTitleEl.textContent = article.seo_title || 'Not set';

							const seoMetadescEl = document.getElementById('article-seo-metadesc');
							if (seoMetadescEl) seoMetadescEl.textContent = article.seo_metadesc || 'Not set';

							const seoFocusKeywordEl = document.getElementById('article-seo-focus-keyword');
							if (seoFocusKeywordEl) seoFocusKeywordEl.textContent = article.seo_focus_keyword || 'Not set';

							// Update Social Media fields
							const ogTitleEl = document.getElementById('article-seo-og-title');
							if (ogTitleEl) ogTitleEl.textContent = article.seo_og_title || 'Not set';

							const ogDescriptionEl = document.getElementById('article-seo-og-description');
							if (ogDescriptionEl) ogDescriptionEl.textContent = article.seo_og_description || 'Not set';

							const twitterTitleEl = document.getElementById('article-seo-twitter-title');
							if (twitterTitleEl) twitterTitleEl.textContent = article.seo_twitter_title || 'Not set';

							const twitterDescriptionEl = document.getElementById('article-seo-twitter-description');
							if (twitterDescriptionEl) twitterDescriptionEl.textContent = article.seo_twitter_description || 'Not set';

							// Update excerpt
							const excerptEl = document.getElementById('article-excerpt');
							if (excerptEl) excerptEl.textContent = article.post_excerpt || 'No excerpt available';

							// Update headline
							const contentHeadlineEl = document.getElementById('article-content-headline');
							if (contentHeadlineEl) contentHeadlineEl.textContent = article.post_content_headline || 'No headline available';

							// Update tags
							const tagsEl = document.getElementById('article-tags');
							if (tagsEl) tagsEl.textContent = article.tags_input || '';

							// Update revision
							const revisionEl = document.getElementById('article-revision');
							if (revisionEl) revisionEl.textContent = article.revision || '0';

							// Update progress - only for queue-type articles
							const articleType = article.type || currentArticleType || 'queue';
							const progressBar = document.getElementById('article-progress-bar');
							const progressText = document.getElementById('article-progress-text');
							const progressWrapper = document.getElementById('article-progress-wrapper');

							if (articleType === 'queue') {
								if (article.progress_percentage !== null && article.progress_percentage !== undefined && article.progress_percentage !== '') {
									const progress = parseInt(article.progress_percentage);
									const color = progress >= 100 ? '#28a745' : '#007cba';

									if (progressBar) {
										progressBar.style.width = progress + '%';
										progressBar.style.background = color;
									}
									if (progressText) progressText.textContent = progress + '%';
									if (progressWrapper) progressWrapper.style.display = 'block';
								} else {
									if (progressBar) progressBar.style.width = '0%';
									if (progressText) progressText.textContent = '0%';
									if (progressWrapper) progressWrapper.style.display = 'none';
								}
							} else {
								// Hide progress for chat-type articles
								if (progressWrapper) progressWrapper.style.display = 'none';
							}

							// Update status badge
							const statusBadge = document.querySelector('.status-badge');
							if (statusBadge && article.status) {
								// Remove old status classes
								statusBadge.className = 'status-badge';

								// Add new status class
								const statusClasses = {
									'generating': 'status-review',
									'published': 'status-published',
									'draft': 'status-draft',
									'review': 'status-review',
									'pending': 'status-pending'
								};
								const statusClass = statusClasses[article.status.toLowerCase()] || 'status-default';
								statusBadge.classList.add(statusClass);
								statusBadge.textContent = article.status.charAt(0).toUpperCase() + article.status.slice(1);
							}

							// Update feedback section
							updateFeedbackSection(article.feedback);

							console.log('[Article View] ✓ Article content refreshed successfully');

							// Refresh the revisions dropdown if the function exists (from ai-chat.js)
							if (typeof refreshArticleRevisions === 'function') {
								console.log('[Article View] → Calling refreshArticleRevisions() to update revisions dropdown');
								refreshArticleRevisions(false); // false = not manual, no status messages
							} else {
								console.warn('[Article View] ⚠️ refreshArticleRevisions() function not found - revisions dropdown will not update');
							}

							// Show a brief success indicator
							showRefreshIndicator();
						} else {
							console.error('[Article View] ✗ Failed to refresh article content:', data.data);
						}
					})
					.catch(error => {
						console.error('[Article View] ✗ Error refreshing article:', error);
					});
			}

			// Show a brief visual indicator that content was refreshed
			function showRefreshIndicator() {
				const headline = document.getElementById('article-headline');
				if (headline) {
					headline.style.transition = 'background-color 0.3s';
					headline.style.backgroundColor = '#d4edda';
					setTimeout(function() {
						headline.style.backgroundColor = '';
					}, 1000);
				}
			}

			// Expose refresh function globally for debugging
			window.refreshArticle = refreshArticleContent;
			console.log('Article view initialized. Article ID:', currentArticleId);
			console.log('Use window.refreshArticle() to manually refresh article content.');
		</script>
		<?php

		// Add JavaScript for unlock functionality
		if ($is_published && ! $is_wp_post_published) {
			echo '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    const unlockBtn = document.getElementById("unlock-publish");
                    const unlockSection = document.getElementById("unlock-section");

                    if (unlockBtn && unlockSection) {
                        unlockBtn.addEventListener("click", function() {
                            const hasWarning = unlockBtn.getAttribute("data-warning") === "true";

                            if (hasWarning) {
                                if (confirm("Warning: This will delete the existing draft post and allow you to create a new one. Are you sure you want to continue?")) {
                                    // Delete the existing draft post
                                    const publishedPostId = document.querySelector("#publish-article").getAttribute("data-published-id");

                                    fetch(ajaxurl, {
                                        method: "POST",
                                        headers: {
                                            "Content-Type": "application/x-www-form-urlencoded",
                                        },
                                        body: "action=delete_ai_draft&post_id=" + publishedPostId + "&nonce=" + "' . wp_create_nonce('delete_ai_draft_nonce') . '"
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            // Refresh the page to show the clean state
                                            // Add cache buster to force fresh data
                                            window.location.href = window.location.href.split("?")[0] + "?page=vm-ai-feed-articles&action=view-article&article_id=' . esc_js($article_id) . '&t=" + Date.now();
                                        } else {
                                            alert("Failed to delete draft: " + (data.data || "Unknown error"));
                                        }
                                    })
                                    .catch(error => {
                                        console.error("Error:", error);
                                        alert("Failed to delete draft: Network error");
                                    });
                                }
                            } else {
                                unlockSection.style.display = "block";
                                unlockBtn.style.display = "none";
                            }
                        });
                    }
                });
            </script>';
		}

		echo '</div>';
	}

	/**
	 * Display article view by research request ID
	 * Shows research status while research is active, then switches to article view when article is created
	 */
	public function displayByResearchRequestId(string $research_request_id = ''): void
	{
		// Check user capability
		if (! current_user_can('manage_options')) {
			wp_die('You do not have permission to access this page.');
		}

		if (empty($research_request_id)) {
			wp_die('Research request ID is required');
		}

		// Fetch research request with articles using combined endpoint
		$combined_data = $this->getResearchRequestCombined($research_request_id);

		if (empty($combined_data)) {
			error_log('VM AI Feed: Research request not found for ID: ' . $research_request_id);
			wp_die('Research request not found. Research Request ID: ' . esc_html($research_request_id));
		}

		$research_data = $combined_data['research_request'] ?? array();
		$articles = $combined_data['articles'] ?? array();

		// Check if an article already exists for this research request
		if (! empty($articles) && is_array($articles) && ! empty($articles[0]['id'])) {
			// Article exists, redirect to article view
			$article = $articles[0];
			$redirect_url = esc_url(admin_url('admin.php?page=vm-ai-feed-articles&action=view-article&article_id=' . urlencode($article['id'])));
			echo '<script>window.location.href = "' . esc_js($redirect_url) . '";</script>';
			echo '<p>Redirecting to article view... <a href="' . $redirect_url . '">Click here if not redirected</a></p>';
			return;
		}

		// No article yet, show research status

		$back_url = admin_url('admin.php?page=vm-ai-feed-articles');
		$status = $research_data['status'] ?? 'pending';
		$progress = $research_data['progress_percentage'] ?? null;
		$progress = ($progress === null || $progress === '') ? 0 : (int) $progress;

		// Get configurable API URL from settings
		$config       = AIFeedConfig::getInstance();
		$api_base_url = $config->getApiBaseUrl();

		// Validate API URL
		$parsed_url = parse_url($api_base_url);
		if (! $parsed_url || ! isset($parsed_url['scheme']) || ! isset($parsed_url['host'])) {
			echo '<div class="wrap">';
			echo '<div class="notice notice-error"><p><strong>Configuration Error:</strong> Invalid API URL configured. Please check your AI Feed Settings.</p></div>';
			echo '<p><a href="' . admin_url('admin.php?page=vm-ai-feed-settings') . '" class="button button-primary">Go to Settings</a></p>';
			echo '</div>';
			return;
		}

		// Build origin (scheme://host[:port])
		$origin = $parsed_url['scheme'] . '://' . $parsed_url['host'];
		if (isset($parsed_url['port'])) {
			$origin .= ':' . $parsed_url['port'];
		}

		echo '<div class="wrap">';
		echo '<div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">';
		echo '<a href="' . esc_url($back_url) . '" class="button" style="text-decoration: none;">← Back to Articles</a>';
		echo '</div>';

		// Main content container
		echo '<div style="display: flex; gap: 20px; margin-bottom: 20px;">';
		// Left column - Research status
		echo '<div style="width: 100%;">';
		echo '<div class="article-content" style="background: #f9f9f9; padding: 20px; padding-top: 10px; border: 1px solid #c3c4c7; margin-bottom: 20px;">';
		echo '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">';
		echo '<p style="margin: 0;"><span class="status-badge ' . esc_attr($this->getStatusClass($status)) . '" id="workflow-status-badge">' . esc_html(ucfirst($status)) . '</span></p>';
		echo '</div>';

		$title = $this->getResearchItemTitle($research_data);
		echo '<h1 id="research-headline" style="line-height: 2rem;">' . esc_html($title) . '</h1>';

		echo '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px;">';
		echo '<div>';
		echo '<p><strong>Type:</strong> ' . esc_html(ucfirst($research_data['type'] ?? 'unknown')) . '</p>';
		echo '<p><strong>Author:</strong> ' . esc_html($this->getAuthorName($research_data['post_author'] ?? 0)) . '</p>';
		echo '</div>';
		echo '<div>';
		echo '<p><strong>Created:</strong> ' . esc_html($this->formatDate($research_data['created_at'] ?? '')) . '</p>';
		echo '<p><strong>Duration:</strong> <span id="workflow-duration">' . esc_html($this->calculateDuration($research_data['start_time'] ?? null, $research_data['end_time'] ?? null)) . '</span></p>';
		echo '</div>';
		echo '</div>';

		// Display workflow stage and progress
		echo '<div style="margin-top: 20px; padding: 15px; background: #fff; border: 2px solid #007cba; border-radius: 8px;">';
		echo '<h3 id="workflow-stage-title" style="margin: 0 0 10px 0; color: #007cba; font-size: 16px;">Research in Progress</h3>';

		// Progress bar
		if ($progress !== null && $progress !== '') {
			$color = AIFeedUIHelpers::getProgressBarColor($progress);
			echo '<div id="workflow-progress-wrapper">';
			echo '<div id="workflow-progress-container" style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">';
			echo '<div style="flex: 1; background: #f0f0f0; border-radius: 5px; height: 24px; overflow: hidden; border: 1px solid #ddd;">';
			echo '<div id="workflow-progress-bar" style="background: ' . esc_attr($color) . '; height: 100%; width: ' . esc_attr($progress) . '%; transition: width 0.3s ease;"></div>';
			echo '</div>';
			echo '<span id="workflow-progress-text" style="font-weight: 600; min-width: 50px; font-size: 14px;">' . esc_html($progress) . '%</span>';
			echo '</div>';
			echo '</div>';
		} else {
			echo '<div id="workflow-progress-wrapper">';
			echo '<div id="workflow-progress-container" style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">';
			echo '<div style="flex: 1; background: #f0f0f0; border-radius: 5px; height: 24px; overflow: hidden; border: 1px solid #ddd;">';
			echo '<div id="workflow-progress-bar" style="background: #007cba; height: 100%; width: 0%; transition: width 0.3s ease;"></div>';
			echo '</div>';
			echo '<span id="workflow-progress-text" style="font-weight: 600; min-width: 50px; font-size: 14px;">0%</span>';
			echo '</div>';
			echo '</div>';
		}

		// Status message
		$initial_message = ! empty($research_data['message']) ? $research_data['message'] : 'Analyzing source material...';
		echo '<p id="workflow-status-message" style="margin: 0; font-size: 13px; color: #666;">' . esc_html($initial_message) . '</p>';
		echo '</div>';

		// Show note about automatic article creation
		echo '<div style="margin-top: 15px; padding: 10px; background: #e7f3ff; border-left: 4px solid #007cba; border-radius: 4px;">';
		echo '<p style="margin: 0; font-size: 13px;"><strong>Info:</strong> This page will automatically redirect when the article is ready. You can continue working while the AI processes the content.</p>';
		echo '</div>';
		echo '</div>';

		// Display research data sections (similar to QueueView)
		$this->displayResearchData($research_data);
		echo '</div>';
		echo '</div>';
		echo '</div>';

		// Add polling script to check for article creation
		$this->addResearchPollingScript($research_request_id, $api_base_url, $config->getApiKey());
	}

	/**
	 * Get article by research request ID
	 */
	private function getArticleByResearchRequestId(string $research_request_id): array
	{
		$config  = AIFeedConfig::getInstance();
		$api_url = $config->getApiUrl('api/articles?research_request_id=' . urlencode($research_request_id));
		$api_key = $config->getApiKey();

		$response = wp_remote_get(
			$api_url,
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $api_key,
					'Content-Type'  => 'application/json',
				),
				'timeout' => 30,
			)
		);

		if (is_wp_error($response)) {
			return array();
		}

		$response_code = wp_remote_retrieve_response_code($response);
		$body          = wp_remote_retrieve_body($response);
		$data          = json_decode($body, true);

		if ($response_code !== 200) {
			return array();
		}

		// Handle API response structure
		if (isset($data['success']) && $data['success'] === true && isset($data['data'])) {
			// Check if data contains articles array
			if (isset($data['data']['articles']) && is_array($data['data']['articles']) && ! empty($data['data']['articles'])) {
				return $data['data']['articles'][0]; // Return first article
			}
			// Fallback: data is directly an array of articles
			elseif (is_array($data['data']) && ! empty($data['data'])) {
				return $data['data'][0]; // Return first article
			}
		}

		return array();
	}

	/**
	 * Display research data sections
	 */
	private function displayResearchData(array $research_data): void
	{
		// Queries
		if (! empty($research_data['queries']) && is_array($research_data['queries'])) {
			$q = $research_data['queries'];
			echo '<div class="article-content" style="background: #f9f9f9; padding: 20px; margin-bottom: 20px; padding-top: 0; border: 1px solid #c3c4c7;">';
			echo '<h3>Queries</h3>';
			if (! empty($q['searchQuery'])) {
				echo '<p><strong>Search Query:</strong> ' . esc_html($q['searchQuery']) . '</p>';
			}
			if (! empty($q['researchQuery'])) {
				echo '<p><strong>Research Query:</strong> ' . esc_html($q['researchQuery']) . '</p>';
			}
			if (! empty($q['youTubeSearchQuery'])) {
				echo '<p><strong>YouTube Search:</strong> ' . esc_html($q['youTubeSearchQuery']) . '</p>';
			}
			if (! empty($q['context'])) {
				echo '<p><strong>Context:</strong> ' . esc_html($q['context']) . '</p>';
			}
			if (! empty($q['primaryEntities']) && is_array($q['primaryEntities'])) {
				echo '<p><strong>Primary Entities:</strong> ' . esc_html(implode(', ', $q['primaryEntities'])) . '</p>';
			}
			if (! empty($q['secondaryEntities']) && is_array($q['secondaryEntities'])) {
				echo '<p><strong>Secondary Entities:</strong> ' . esc_html(implode(', ', $q['secondaryEntities'])) . '</p>';
			}
			echo '</div>';
		}

		// Archives
		if (! empty($research_data['archives']) && is_array($research_data['archives'])) {
			echo '<div class="article-content" style="background: #f9f9f9; padding: 20px; margin-bottom: 20px; padding-top: 0; border: 1px solid #c3c4c7;">';
			echo '<h3>Archives</h3>';
			if (! empty($research_data['archives']['query'])) {
				echo '<p><strong>Query:</strong> ' . esc_html($research_data['archives']['query']) . '</p>';
			}
			if (! empty($research_data['archives']['response'])) {
				$archives_html = ResearchDataFormatter::formatResearchResponse($research_data['archives']['response']);
				echo '<div style="background: #f8f9fa; padding: 10px; border-left: 4px solid #007cba; line-height: 1.6;">' . wp_kses_post($archives_html) . '</div>';
			}
			echo '</div>';
		}

		// Recent News
		if (! empty($research_data['recent_news']) && is_array($research_data['recent_news'])) {
			echo '<div class="article-content" style="background: #f9f9f9; padding: 20px; margin-bottom: 20px; padding-top: 0; border: 1px solid #c3c4c7;">';
			echo '<h3>Recent News</h3>';
			foreach ($research_data['recent_news'] as $rn) {
				if (! empty($rn['query'])) {
					echo '<p><strong>Query:</strong> ' . esc_html($rn['query']) . '</p>';
				}
				if (! empty($rn['response'])) {
					$recent_news_html = ResearchDataFormatter::formatResearchResponse($rn['response']);
					echo '<div style="background: #f8f9fa; padding: 10px; border-left: 4px solid #007cba; line-height: 1.6; margin-bottom: 15px;">' . wp_kses_post($recent_news_html) . '</div>';
				}
			}
			echo '</div>';
		}

		// Web search results
		if (! empty($research_data['web_search']) && is_array($research_data['web_search'])) {
			echo '<div class="article-content" style="background: #f9f9f9; padding: 20px; margin-bottom: 20px; padding-top: 0; border: 1px solid #c3c4c7;">';
			echo '<h3>Web Search</h3>';
			if (! empty($research_data['web_search']['query'])) {
				echo '<p><strong>Query:</strong> ' . esc_html($research_data['web_search']['query']) . '</p>';
			}
			if (! empty($research_data['web_search']['results']) && is_array($research_data['web_search']['results'])) {
				foreach ($research_data['web_search']['results'] as $result) {
					echo '<div style="background: white; padding: 15px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 12px;">';
					echo '<h4 style="margin: 0 0 8px 0; font-size: 16px;">';
					if (! empty($result['link'])) {
						echo '<a href="' . esc_url($result['link']) . '" target="_blank" style="color: #007cba; text-decoration: none;">' . esc_html($result['title'] ?? 'Untitled') . '</a>';
					} else {
						echo esc_html($result['title'] ?? 'Untitled');
					}
					echo '</h4>';
					if (! empty($result['source']) || ! empty($result['date'])) {
						echo '<div style="color: #666; font-size: 13px; margin-bottom: 6px;">';
						if (! empty($result['source'])) {
							echo '<span><strong>Source:</strong> ' . esc_html($result['source']) . '</span>';
						}
						if (! empty($result['date'])) {
							echo '<span style="margin-left: 12px;"><strong>Date:</strong> ' . esc_html($result['date']) . '</span>';
						}
						echo '</div>';
					}
					if (! empty($result['snippet'])) {
						echo '<div style="line-height: 1.5; color: #555; font-size: 14px;">' . esc_html($result['snippet']) . '</div>';
					}
					echo '</div>';
				}
			} else {
				echo '<p style="color: #666; font-style: italic;">No search results.</p>';
			}
			echo '</div>';
		}

		// YouTube results
		if (! empty($research_data['youtube']) && is_array($research_data['youtube'])) {
			echo '<div class="article-content" style="background: #f9f9f9; padding: 20px; margin-bottom: 20px; padding-top: 0; border: 1px solid #c3c4c7;">';
			echo '<h3>YouTube</h3>';
			if (! empty($research_data['youtube']['query'])) {
				echo '<p><strong>Query:</strong> ' . esc_html($research_data['youtube']['query']) . '</p>';
			}
			if (! empty($research_data['youtube']['results']) && is_array($research_data['youtube']['results'])) {
				foreach ($research_data['youtube']['results'] as $yt) {
					echo '<div style="background: white; padding: 15px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 12px;">';
					echo '<h4 style="margin: 0 0 8px 0; font-size: 16px;">';
					if (! empty($yt['link'])) {
						echo '<a href="' . esc_url($yt['link']) . '" target="_blank" style="color: #007cba; text-decoration: none;">' . esc_html($yt['title'] ?? 'Video') . '</a>';
					} else {
						echo esc_html($yt['title'] ?? 'Video');
					}
					echo '</h4>';
					if (! empty($yt['description'])) {
						echo '<div style="line-height: 1.5; color: #555; font-size: 14px;">' . esc_html($yt['description']) . '</div>';
					}
					echo '</div>';
				}
			} else {
				echo '<p style="color: #666; font-style: italic;">No YouTube results.</p>';
			}
			echo '</div>';
		}
	}

	/**
	 * Extract title from research item data
	 */
	private function getResearchItemTitle(array $item): string
	{
		// Try to get title from input JSON
		if (! empty($item['input']) && is_array($item['input'])) {
			if (! empty($item['input']['title'])) {
				return $item['input']['title'];
			}
		}

		// Try to get title from output JSON
		if (! empty($item['output']) && is_array($item['output'])) {
			if (! empty($item['output']['title'])) {
				return $item['output']['title'];
			}
		}

		// Fall back to URL domain or path
		if (! empty($item['url'])) {
			$parsed = parse_url($item['url']);
			if ($parsed !== false && ! empty($parsed['host'])) {
				return $parsed['host'] . (! empty($parsed['path']) ? $parsed['path'] : '');
			}
			return $item['url'];
		}

		return 'Research Item #' . ($item['id'] ?? 'Unknown');
	}

	/**
	 * Add polling script to track dual workflow (research → article creation)
	 *
	 * This script automatically switches between tracking research workflow and article
	 * creation workflow. When article creation begins, it seamlessly transitions to
	 * showing article progress and redirects when the article is complete.
	 */
	private function addResearchPollingScript(string $research_request_id, string $api_base_url, string $api_key): void
	{
		?>
		<script>
			(function() {
				var researchRequestId = '<?php echo esc_js($research_request_id); ?>';
				var apiBaseUrl = '<?php echo esc_js($api_base_url); ?>';
				var apiKey = '<?php echo esc_js($api_key); ?>';
				var statusCheckInterval = null;
				var pollCount = 0;
				var maxPolls = 240; // Stop after 20 minutes (240 * 5 seconds)
				var currentStage = 'research'; // 'research' or 'article'
				var articleId = null;

				// Helper function to get progress bar color
				function getProgressColor(status, progress) {
					if (status === 'failed' || status === 'error') return '#dc3232';
					if (status === 'completed' || status === 'draft' || status === 'published') return '#46b450';
					if (progress >= 75) return '#00a32a';
					if (progress >= 50) return '#007cba';
					if (progress >= 25) return '#2271b1';
					return '#72aee6';
				}

				// Helper function to update progress bar UI
				function updateProgressBar(stageName, progress, status, message) {
					var stageTitle = document.getElementById('workflow-stage-title');
					var statusBadge = document.getElementById('workflow-status-badge');
					var statusMessage = document.getElementById('workflow-status-message');
					var progressBar = document.getElementById('workflow-progress-bar');
					var progressText = document.getElementById('workflow-progress-text');

					if (stageTitle) stageTitle.textContent = stageName;
					if (statusBadge) {
						statusBadge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
						statusBadge.className = 'status-badge status-' + status.toLowerCase();
					}
					if (statusMessage) statusMessage.textContent = message || status;
					if (progressBar) {
						var color = getProgressColor(status, progress);
						progressBar.style.width = progress + '%';
						progressBar.style.background = color;
					}
					if (progressText) progressText.textContent = progress + '%';
				}

				// Check if article exists for this research request
				function checkForArticle(callback) {
					fetch(ajaxurl, {
							method: 'POST',
							headers: {
								'Content-Type': 'application/x-www-form-urlencoded',
							},
							body: 'action=check_article_by_research&research_request_id=' + encodeURIComponent(researchRequestId) + '&nonce=' + encodeURIComponent('<?php echo wp_create_nonce('ai_chat_nonce'); ?>')
						})
						.then(response => response.json())
						.then(data => {
							if (data.success && data.data && data.data.article_id) {
								callback(data.data.article_id);
							} else {
								callback(null);
							}
						})
						.catch(error => {
							console.error('[Workflow Tracker] Error checking for article:', error);
							callback(null);
						});
				}

				// Fetch full article data by ID
				function fetchArticleData(articleId, callback) {
					fetch(apiBaseUrl + '/v1/api/articles/' + articleId, {
							method: 'GET',
							headers: {
								'Authorization': 'Bearer ' + apiKey,
								'Content-Type': 'application/json'
							}
						})
						.then(response => response.json())
						.then(data => {
							if (data.success && data.data) {
								callback(data.data);
							} else {
								callback(null);
							}
						})
						.catch(error => {
							console.error('[Workflow Tracker] Error fetching article data:', error);
							callback(null);
						});
				}

				// Update research progress using combined endpoint
				function updateResearchProgress() {
					fetch(apiBaseUrl + '/v1/api/research-request-combined/' + encodeURIComponent(researchRequestId), {
							method: 'GET',
							headers: {
								'Authorization': 'Bearer ' + apiKey,
								'Content-Type': 'application/json'
							}
						})
						.then(response => response.json())
						.then(data => {
							if (data.success && data.data) {
								var research = data.data.research_request || data.data;
								var articles = data.data.articles || [];
								var status = research.status || 'pending';
								var progress = parseInt(research.progress_percentage) || 0;
								var message = research.message || 'Analyzing source material...';

								console.log('[Workflow Tracker] Research status:', status, 'Progress:', progress + '%');
								updateProgressBar('Research in Progress', progress, status, message);

								// Check if articles were created
								if (articles.length > 0 && articles[0].id) {
									// Article exists, use article ID directly
									var foundArticleId = articles[0].id;
									if (foundArticleId) {
										if (currentStage === 'research') {
											console.log('[Workflow Tracker] Article detected! Switching to article creation tracking...');
											currentStage = 'article';
											articleId = foundArticleId;
										}
										fetchArticleData(foundArticleId, function(articleData) {
											if (articleData) {
												updateArticleCreationProgress(articleData);
											}
										});
									}
								}
							}
						})
						.catch(error => {
							console.error('[Workflow Tracker] Error updating research status:', error);
						});
				}

				// Update article creation progress
				function updateArticleCreationProgress(articleData) {
					var status = articleData.status || 'generating';
					var progress = parseInt(articleData.progress_percentage) || 0;
					var message = articleData.message || 'Writing article...';

					console.log('[Workflow Tracker] Article status:', status, 'Progress:', progress + '%');
					updateProgressBar('Writing Article', progress, status, message);

					// If article is complete (status = 'draft' or 'published'), reload page to show full article view
					if (status === 'draft' || status === 'published') {
						console.log('[Workflow Tracker] Article complete! Reloading page to show article view...');
						stopPolling();

						// Reload the page - it will now show the full article view with all tabs
						window.location.reload();
					}
				}

				// Main workflow status update function
				function updateWorkflowStatus() {
					pollCount++;

					if (pollCount > maxPolls) {
						console.log('[Workflow Tracker] Stopping polling after max attempts');
						stopPolling();
						return;
					}

					// First check if article exists
					checkForArticle(function(foundArticleId) {
						if (foundArticleId) {
							// Article exists - switch to article creation tracking
							if (currentStage === 'research') {
								console.log('[Workflow Tracker] Article detected! Switching to article creation tracking...');
								currentStage = 'article';
								articleId = foundArticleId;
							}

							// Fetch and update article creation progress
							fetchArticleData(foundArticleId, function(articleData) {
								if (articleData) {
									updateArticleCreationProgress(articleData);
								}
							});
						} else {
							// No article yet - continue tracking research
							if (currentStage !== 'research') {
								console.log('[Workflow Tracker] Back to research tracking');
								currentStage = 'research';
							}
							updateResearchProgress();
						}
					});
				}

				var pollingTimeout = null;

				function getPollingInterval() {
					// Progressive backoff: 5s for first 12 polls (1 min), 10s for next 18 polls (3 min), 30s thereafter
					if (pollCount < 12) {
						return 5000; // 5 seconds
					} else if (pollCount < 30) {
						return 10000; // 10 seconds
					} else {
						return 30000; // 30 seconds
					}
				}

				function startPolling() {
					function poll() {
						updateWorkflowStatus();
						// Only schedule next poll if polling hasn't been stopped
						if (pollingTimeout !== null) {
							pollingTimeout = setTimeout(poll, getPollingInterval());
						}
					}
					// Start polling
					if (pollingTimeout === null) {
						pollingTimeout = setTimeout(poll, 0); // Initial poll immediately
					}
				}

				function stopPolling() {
					if (pollingTimeout !== null) {
						clearTimeout(pollingTimeout);
						pollingTimeout = null;
					}
				}

				// Start polling when page loads
				document.addEventListener('DOMContentLoaded', function() {
					startPolling();
				});

				// Stop polling when page is hidden
				document.addEventListener('visibilitychange', function() {
					if (document.hidden) {
						stopPolling();
					} else {
						startPolling();
					}
				});
			})();
		</script>
	<?php
	}

	private function getArticleById(string $article_id): array
	{
		// Get configurable API URL
		$config  = AIFeedConfig::getInstance();
		$api_url = $config->getApiUrl('api/articles/' . $article_id);
		$api_key = $config->getApiKey();

		$response = wp_remote_get(
			$api_url,
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $api_key,
					'Content-Type'  => 'application/json',
				),
				'timeout' => 30,
			)
		);

		if (is_wp_error($response)) {
			error_log('VM AI Feed: Error fetching article ' . $article_id . ': ' . $response->get_error_message());
			return array();
		}

		$response_code = wp_remote_retrieve_response_code($response);
		$body          = wp_remote_retrieve_body($response);
		$data          = json_decode($body, true);

		if ($response_code !== 200) {
			error_log('VM AI Feed: Failed to fetch article ' . $article_id . ' - Status: ' . $response_code . ', Response: ' . substr($body, 0, 200));
			return array();
		}

		// Handle API response structure
		$article = array();
		if (isset($data['success']) && $data['success'] === true && isset($data['data'])) {
			$article = $data['data'];

			// Process tags if they exist
			if (isset($article['tags_input'])) {
				$article['tags_input'] = is_array($article['tags_input']) ? implode(', ', $article['tags_input']) : $article['tags_input'];
			}
		} else {
			error_log('VM AI Feed: Invalid response structure when fetching article ' . $article_id . ': ' . substr($body, 0, 200));
		}

		return $article;
	}

	/**
	 * Get complete research context (research request + articles + drafts)
	 */
	private function getResearchContext(string $research_request_id): array
	{
		$config  = AIFeedConfig::getInstance();
		$api_url = $config->getApiUrl('api/research-context/' . $research_request_id);
		$api_key = $config->getApiKey();

		error_log('VM AI Feed: Fetching research context for ID: ' . $research_request_id);
		error_log('VM AI Feed: API URL: ' . $api_url);

		$response = wp_remote_get(
			$api_url,
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $api_key,
					'Content-Type'  => 'application/json',
				),
				'timeout' => 30,
			)
		);

		if (is_wp_error($response)) {
			$error_message = 'Error fetching research context: ' . $response->get_error_message();
			error_log('VM AI Feed: ' . $error_message);
			wp_die(esc_html($error_message));
		}

		$response_code = wp_remote_retrieve_response_code($response);
		$body          = wp_remote_retrieve_body($response);
		$data          = json_decode($body, true);

		error_log('VM AI Feed: Response code: ' . $response_code);
		error_log('VM AI Feed: Response body: ' . substr($body, 0, 500));

		if ($response_code !== 200) {
			// Extract detailed error message from API response
			$error_message = 'Failed to fetch research context (HTTP ' . $response_code . ')';

			if (isset($data['error']['message'])) {
				$error_message .= ': ' . $data['error']['message'];
			} elseif (isset($data['error']) && is_string($data['error'])) {
				$error_message .= ': ' . $data['error'];
			} elseif (isset($data['message'])) {
				$error_message .= ': ' . $data['message'];
			}

			// Add the raw response for debugging
			$error_message .= '<br><br><strong>Debug Info:</strong><br>';
			$error_message .= 'Research Request ID: ' . esc_html($research_request_id) . '<br>';
			$error_message .= 'API URL: ' . esc_html($api_url) . '<br>';
			$error_message .= 'Response: <pre>' . esc_html(substr($body, 0, 1000)) . '</pre>';

			error_log('VM AI Feed: ' . strip_tags($error_message));
			wp_die($error_message);
		}

		// Handle API response structure
		if (isset($data['success']) && $data['success'] === true && isset($data['data'])) {
			error_log('VM AI Feed: Successfully fetched research context');
			return $data['data'];
		}

		error_log('VM AI Feed: Invalid API response structure');
		wp_die('Invalid API response structure for research context');
	}

	/**
	 * Get research request with articles using combined endpoint
	 */
	private function getResearchRequestCombined(string $research_request_id): array
	{
		$config  = AIFeedConfig::getInstance();
		$api_url = $config->getApiUrl('api/research-request-combined/' . urlencode($research_request_id));
		$api_key = $config->getApiKey();

		$response = wp_remote_get(
			$api_url,
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $api_key,
					'Content-Type'  => 'application/json',
				),
				'timeout' => 30,
			)
		);

		if (is_wp_error($response)) {
			error_log('VM AI Feed: Error fetching combined research request ' . $research_request_id . ': ' . $response->get_error_message());
			return array();
		}

		$response_code = wp_remote_retrieve_response_code($response);
		$body          = wp_remote_retrieve_body($response);
		$data          = json_decode($body, true);

		if ($response_code !== 200) {
			error_log('VM AI Feed: Failed to fetch combined research request ' . $research_request_id . ' - Status: ' . $response_code);
			return array();
		}

		if (json_last_error() !== JSON_ERROR_NONE) {
			error_log('VM AI Feed: Invalid JSON when fetching combined research request ' . $research_request_id);
			return array();
		}

		// Handle API response structure
		if (isset($data['success']) && $data['success'] === true && isset($data['data'])) {
			$combined_data = $data['data'];
			$research_request = $combined_data['research_request'] ?? array();
			$articles = $combined_data['articles'] ?? array();

			// Parse JSON fields in research request if they exist and are strings
			foreach (array('input', 'output', 'queries', 'archives', 'recent_news', 'web_search', 'youtube', 'recentnews') as $jsonField) {
				if (! empty($research_request[$jsonField]) && is_string($research_request[$jsonField])) {
					$decoded = json_decode($research_request[$jsonField], true);
					if (json_last_error() === JSON_ERROR_NONE) {
						$research_request[$jsonField] = $decoded;
					}
				}
			}

			return array(
				'research_request' => $research_request,
				'articles'         => $articles,
			);
		}

		return array();
	}

	/**
	 * Get research request data by ID
	 */
	private function getResearchRequestById(string $research_request_id): array
	{
		$config  = AIFeedConfig::getInstance();
		$api_url = $config->getApiUrl('v1/api/research-request-combined/' . $research_request_id);
		$api_key = $config->getApiKey();

		$response = wp_remote_get(
			$api_url,
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $api_key,
					'Content-Type'  => 'application/json',
				),
				'timeout' => 30,
			)
		);

		if (is_wp_error($response)) {
			error_log('VM AI Feed: Error fetching research request ' . $research_request_id . ': ' . $response->get_error_message());
			return array();
		}

		$response_code = wp_remote_retrieve_response_code($response);
		$body          = wp_remote_retrieve_body($response);
		$data          = json_decode($body, true);

		if ($response_code !== 200) {
			error_log('VM AI Feed: Failed to fetch research request ' . $research_request_id . ' - Status: ' . $response_code);
			return array();
		}

		if (json_last_error() !== JSON_ERROR_NONE) {
			error_log('VM AI Feed: Invalid JSON when fetching research request ' . $research_request_id);
			return array();
		}

		// Handle API response structure
		if (isset($data['success']) && $data['success'] === true && isset($data['data'])) {
			$item = $data['data'];

			// Parse JSON fields if they exist and are strings
			foreach (array('input', 'output', 'queries', 'archives', 'recent_news', 'web_search', 'youtube', 'recentnews') as $jsonField) {
				if (! empty($item[$jsonField]) && is_string($item[$jsonField])) {
					$decoded = json_decode($item[$jsonField], true);
					if (json_last_error() === JSON_ERROR_NONE) {
						$item[$jsonField] = $decoded;
					}
				}
			}

			return $item;
		}

		return array();
	}

	/**
	 * Get a template for a new article
	 */
	private function getNewArticleTemplate(): array
	{
		return array(
			'id'                      => 'new',
			'post_title'              => 'New Article',
			'post_content'            => 'Start writing your article content here...',
			'post_content_headline'   => 'Article Headline',
			'post_excerpt'            => 'Brief summary of the article',
			'post_category'           => '[]',
			'tags_input'              => '[]',
			'post_author'             => get_current_user_id(),
			'seo_title'               => null,
			'seo_metadesc'            => null,
			'seo_focus_keyword'       => null,
			'seo_og_title'            => null,
			'seo_og_description'      => null,
			'seo_twitter_title'       => null,
			'seo_twitter_description' => null,
			'status'                  => 'draft',
			'start_time'              => null,
			'completed_time'          => null,
			'revision'                => 0,
			'iterations'              => 0,
			'research_request_id'     => null,
		);
	}

	/**
	 * Check if article has already been published to WordPress
	 */
	private function getPublishedPost(string $article_id): ?\WP_Post
	{
		$posts = get_posts(
			array(
				'meta_query'  => array(
					array(
						'key'     => '_ai_article_id',
						'value'   => $article_id,
						'compare' => '=',
					),
				),
				'post_type'   => 'post',
				'post_status' => 'any',
				'numberposts' => 1,
			)
		);

		return ! empty($posts) ? $posts[0] : null;
	}

	private function getArticleDrafts(string $research_request_id): array
	{
		// Get configurable API URL
		$config  = AIFeedConfig::getInstance();
		$api_url = $config->getApiUrl('api/article-drafts/research-request/' . $research_request_id);
		$api_key = $config->getApiKey();

		$response = wp_remote_get(
			$api_url,
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $api_key,
					'Content-Type'  => 'application/json',
				),
				'timeout' => 30,
			)
		);

		if (is_wp_error($response)) {
			return array();
		}

		$body = wp_remote_retrieve_body($response);
		$data = json_decode($body, true);

		if (json_last_error() !== JSON_ERROR_NONE) {
			return array();
		}

		// Handle API response structure
		if (isset($data['success']) && $data['success'] === true && isset($data['data'])) {
			return is_array($data['data']['article_drafts']) ? $data['data']['article_drafts'] : array();
		}

		return array();
	}

	private function getStatusClass(string $status): string
	{
		return AIStatusHelpers::getStatusClass($status);
	}

	/**
	 * Get author name from WordPress database
	 */
	private function getAuthorName(int $author_id): string
	{
		return AIFeedUIHelpers::getAuthorName($author_id);
	}

	/**
	 * Format date in human-readable format
	 */
	private function formatDate(?string $date_string): string
	{
		return AIDateHelpers::formatDate($date_string);
	}

	/**
	 * Calculate duration between start and end time
	 */
	private function calculateDuration(?string $start_time, ?string $end_time): string
	{
		return AIDateHelpers::calculateDuration($start_time, $end_time);
	}

	/**
	 * Get WordPress user ID from AI config
	 */
	private function getWordPressUserIdFromAIConfig(string $ai_config_name): int
	{
		return AIFeedConfig::getInstance()->getWordPressUserIdFromAIConfig($ai_config_name);
	}

	/**
	 * Create a new research request via API
	 */
	private function createResearchRequest(string $ai_config_name = 'default'): array
	{
		$config  = AIFeedConfig::getInstance();
		$api_url = $config->getApiUrl('api/research-requests');
		$api_key = $config->getApiKey();

		// Get the wordpress_user_id from the AI config
		$wordpress_user_id = $this->getWordPressUserIdFromAIConfig($ai_config_name);

		$request_body = array(
			'type'           => 'manual-research',
			'status'         => 'pending',
			'post_author'    => $wordpress_user_id,
			'message'        => 'Article creation from WordPress',
			'ai_config_name' => $ai_config_name,
		);

		$response = wp_remote_post(
			$api_url,
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $api_key,
					'Content-Type'  => 'application/json',
				),
				'body'    => json_encode($request_body),
				'timeout' => 30,
			)
		);

		if (is_wp_error($response)) {
			error_log('VM AI Feed: Failed to create research request - ' . $response->get_error_message());
			return array();
		}

		$response_code = wp_remote_retrieve_response_code($response);
		$body          = wp_remote_retrieve_body($response);
		$data          = json_decode($body, true);

		if (isset($data['success']) && $data['success'] === true && isset($data['data'])) {
			return $data['data'];
		}

		error_log('VM AI Feed: Invalid API response when creating research request - Code: ' . $response_code);
		return array();
	}

	/**
	 * Helper method to write to log files with rotation and error handling
	 *
	 * @param string $log_file Path to the log file
	 * @param string $message Message to write
	 * @param int    $max_size Maximum file size in bytes before rotation (default 1MB)
	 * @return bool True on success, false on failure
	 */
	private function logToFile(string $log_file, string $message, int $max_size = 1048576): bool
	{
		// Ensure the directory exists
		$log_dir = dirname($log_file);
		if (! file_exists($log_dir)) {
			if (! @mkdir($log_dir, 0755, true)) {
				error_log('VM AI Feed: Failed to create log directory: ' . $log_dir);
				error_log('VM AI Feed (fallback): ' . $message);
				return false;
			}
		}

		// Check if log rotation is needed
		if (file_exists($log_file) && @filesize($log_file) >= $max_size) {
			$timestamp    = date('Y-m-d_H-i-s');
			$rotated_file = $log_file . '.' . $timestamp;
			if (! @rename($log_file, $rotated_file)) {
				error_log('VM AI Feed: Failed to rotate log file: ' . $log_file);
				// Continue anyway, attempt to write to the existing file
			}
		}

		// Attempt to write to the log file
		$result = @file_put_contents($log_file, $message, FILE_APPEND);

		if ($result === false) {
			// Fall back to error_log if file write fails
			error_log('VM AI Feed: Failed to write to log file: ' . $log_file);
			error_log('VM AI Feed (fallback): ' . $message);
			return false;
		}

		return true;
	}

	/**
	 * Render the workflow view showing research context
	 */
	private function renderWorkflowView(array $context, string $back_url): void
	{
		$research_request = $context['research_request'] ?? array();
		$articles = $context['articles'] ?? array();
		$draft_revisions = $context['draft_revisions'] ?? array();
		$summary = $context['summary'] ?? array();

		$research_id = $research_request['research_request_id'] ?? '';
		$status = $research_request['status'] ?? 'unknown';
		$url = $research_request['url'] ?? '';
	?>
		<div class="wrap">
			<h1 class="wp-heading-inline">
				Research Workflow
				<?php if (! empty($research_id)) : ?>
					<span style="font-size: 14px; color: #666; font-weight: normal;">(<?php echo esc_html($research_id); ?>)</span>
				<?php endif; ?>
			</h1>

			<a href="<?php echo esc_url($back_url); ?>" class="page-title-action">← Back to Articles</a>

			<hr class="wp-header-end">

			<!-- Workflow Timeline -->
			<div style="background: white; padding: 20px; margin: 20px 0; border: 1px solid #ccd0d4; border-radius: 4px;">
				<h2 style="margin-top: 0;">Workflow Timeline</h2>

				<!-- Step 1: Research Phase -->
				<div style="margin-bottom: 30px; padding-left: 30px; border-left: 3px solid #2271b1; position: relative;">
					<div style="position: absolute; left: -9px; top: 0; width: 15px; height: 15px; background: #2271b1; border-radius: 50%;"></div>
					<h3 style="margin: 0 0 10px 0;">1. Research Phase</h3>
					<p style="margin: 5px 0;">
						<strong>Overall Status:</strong>
						<span class="status-badge <?php echo esc_attr($this->getStatusClass($status)); ?>"><?php echo esc_html(ucfirst($status)); ?></span>
					</p>
					<?php if (! empty($url)) : ?>
						<p style="margin: 5px 0;">
							<strong>Source URL:</strong>
							<a href="<?php echo esc_url($url); ?>" target="_blank"><?php echo esc_html($url); ?></a>
						</p>
					<?php endif; ?>
					<?php if (! empty($research_request['start_time'])) : ?>
						<p style="margin: 5px 0;">
							<strong>Started:</strong>
							<?php echo esc_html(AIDateHelpers::formatDate($research_request['start_time'])); ?>
						</p>
					<?php endif; ?>
					<?php if (! empty($research_request['end_time'])) : ?>
						<p style="margin: 5px 0;">
							<strong>Completed:</strong>
							<?php echo esc_html(AIDateHelpers::formatDate($research_request['end_time'])); ?>
						</p>
						<p style="margin: 5px 0;">
							<strong>Duration:</strong>
							<?php echo esc_html(AIDateHelpers::calculateDuration($research_request['start_time'], $research_request['end_time'])); ?>
						</p>
					<?php endif; ?>

					<!-- Research Sub-Steps -->
					<?php
					$research_steps = $research_request['research_steps'] ?? $research_request['steps'] ?? array();
					$default_steps = array(
						array('name' => 'Scrape URL', 'status' => 'unknown'),
						array('name' => 'Input', 'status' => 'unknown'),
						array('name' => 'Queries', 'status' => 'unknown'),
						array('name' => 'Archives', 'status' => 'unknown'),
						array('name' => 'Recent News', 'status' => 'unknown'),
						array('name' => 'Web Search', 'status' => 'unknown'),
						array('name' => 'YouTube', 'status' => 'unknown'),
						array('name' => 'Vector Search', 'status' => 'unknown'),
					);

					// Use API steps if available, otherwise show default structure
					$steps_to_display = ! empty($research_steps) ? $research_steps : $default_steps;
					?>

					<div style="margin-top: 20px;">
						<h4 style="margin: 15px 0 10px 0; font-size: 14px; color: #666;">Research Steps:</h4>
						<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; margin-top: 10px;">
							<?php foreach ($steps_to_display as $index => $step) : ?>
								<?php
								$step_name = $step['name'] ?? $step['step_name'] ?? 'Unknown';
								$step_status = $step['status'] ?? 'pending';
								$step_duration = isset($step['duration']) ? $step['duration'] : null;
								$step_count = isset($step['count']) ? $step['count'] : null;

								// Determine step color based on status
								$step_color = '#ddd';
								$step_icon = '○';
								if (in_array($step_status, array('completed', 'complete', 'success'))) {
									$step_color = '#00a32a';
									$step_icon = '✓';
								} elseif (in_array($step_status, array('in_progress', 'running', 'processing'))) {
									$step_color = '#f0b849';
									$step_icon = '◐';
								} elseif (in_array($step_status, array('failed', 'error'))) {
									$step_color = '#d63638';
									$step_icon = '✗';
								}
								?>
								<div style="background: #f6f7f7; padding: 12px; border-radius: 4px; border-left: 3px solid <?php echo esc_attr($step_color); ?>;">
									<div style="display: flex; align-items: center; gap: 8px; margin-bottom: 5px;">
										<span style="color: <?php echo esc_attr($step_color); ?>; font-size: 16px; font-weight: bold;"><?php echo esc_html($step_icon); ?></span>
										<strong style="font-size: 13px;"><?php echo esc_html($step_name); ?></strong>
									</div>
									<div style="font-size: 11px; color: #666;">
										<span class="status-badge" style="font-size: 10px; padding: 2px 6px;"><?php echo esc_html(ucfirst($step_status)); ?></span>
										<?php if ($step_duration) : ?>
											<div style="margin-top: 5px;"><?php echo esc_html($step_duration); ?></div>
										<?php endif; ?>
										<?php if ($step_count !== null) : ?>
											<div style="margin-top: 5px;">Items: <?php echo esc_html($step_count); ?></div>
										<?php endif; ?>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>

				<!-- Step 2: Draft Revisions -->
				<?php if (! empty($draft_revisions)) : ?>
					<div style="margin-bottom: 30px; padding-left: 30px; border-left: 3px solid <?php echo count($draft_revisions) > 0 ? '#00a32a' : '#ddd'; ?>; position: relative;">
						<div style="position: absolute; left: -9px; top: 0; width: 15px; height: 15px; background: <?php echo count($draft_revisions) > 0 ? '#00a32a' : '#ddd'; ?>; border-radius: 50%;"></div>
						<h3 style="margin: 0 0 10px 0;">2. Draft Revisions (<?php echo count($draft_revisions); ?>)</h3>
						<?php foreach ($draft_revisions as $draft) : ?>
							<div style="background: #f6f7f7; padding: 15px; margin: 10px 0; border-radius: 4px;">
								<p style="margin: 0 0 5px 0;">
									<strong>Revision <?php echo esc_html($draft['revision_number'] ?? 'N/A'); ?>:</strong>
									<?php echo esc_html($draft['post_title'] ?? 'Untitled'); ?>
								</p>
								<?php if (! empty($draft['changes_summary'])) : ?>
									<p style="margin: 5px 0; color: #666; font-size: 13px;">
										<?php echo esc_html($draft['changes_summary']); ?>
									</p>
								<?php endif; ?>
								<?php if (! empty($draft['start_time']) && ! empty($draft['end_time'])) : ?>
									<p style="margin: 5px 0; font-size: 13px;">
										<strong>Duration:</strong>
										<?php echo esc_html(AIDateHelpers::calculateDuration($draft['start_time'], $draft['end_time'])); ?>
									</p>
								<?php endif; ?>
								<?php if (isset($draft['has_passed'])) : ?>
									<p style="margin: 5px 0; font-size: 13px;">
										<strong>Quality Check:</strong>
										<?php if ($draft['has_passed']) : ?>
											<span style="color: #00a32a;">✓ Passed</span>
										<?php else : ?>
											<span style="color: #d63638;">✗ Failed</span>
										<?php endif; ?>
									</p>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<!-- Step 3: Final Articles -->
				<?php if (! empty($articles)) : ?>
					<div style="margin-bottom: 30px; padding-left: 30px; border-left: 3px solid <?php echo count($articles) > 0 ? '#00a32a' : '#ddd'; ?>; position: relative;">
						<div style="position: absolute; left: -9px; top: 0; width: 15px; height: 15px; background: <?php echo count($articles) > 0 ? '#00a32a' : '#ddd'; ?>; border-radius: 50%;"></div>
						<h3 style="margin: 0 0 10px 0;">3. Final Articles (<?php echo count($articles); ?>)</h3>
						<?php foreach ($articles as $article) : ?>
							<div style="background: #f0f6fc; padding: 15px; margin: 10px 0; border-radius: 4px; border-left: 3px solid #2271b1;">
								<p style="margin: 0 0 5px 0;">
									<strong>Article #<?php echo esc_html($article['id'] ?? 'N/A'); ?>:</strong>
									<?php echo esc_html($article['post_title'] ?? 'Untitled'); ?>
								</p>
								<p style="margin: 5px 0;">
									<strong>Status:</strong>
									<span class="status-badge <?php echo esc_attr($this->getStatusClass($article['status'] ?? 'unknown')); ?>"><?php echo esc_html(ucfirst($article['status'] ?? 'unknown')); ?></span>
								</p>
								<?php if (! empty($article['message'])) : ?>
									<p style="margin: 5px 0; color: #666; font-size: 13px;">
										<?php echo esc_html($article['message']); ?>
									</p>
								<?php endif; ?>
								<?php if (isset($article['progress_percentage'])) : ?>
									<p style="margin: 5px 0; font-size: 13px;">
										<strong>Progress:</strong> <?php echo esc_html($article['progress_percentage']); ?>%
									</p>
								<?php endif; ?>
								<?php if (! empty($article['created_at'])) : ?>
									<p style="margin: 5px 0; font-size: 13px;">
										<strong>Created:</strong>
										<?php echo esc_html(AIDateHelpers::formatDate($article['created_at'])); ?>
									</p>
								<?php endif; ?>
								<?php if (! empty($article['id'])) : ?>
									<p style="margin: 10px 0 0 0;">
										<a href="<?php echo esc_url(admin_url('admin.php?page=vm-ai-feed-articles&action=view-article&research_request_id=' . urlencode($research_id))); ?>" class="button button-primary">
											View Article
										</a>
									</p>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				<?php else : ?>
					<div style="margin-bottom: 30px; padding-left: 30px; border-left: 3px solid #ddd; position: relative;">
						<div style="position: absolute; left: -9px; top: 0; width: 15px; height: 15px; background: #ddd; border-radius: 50%;"></div>
						<h3 style="margin: 0 0 10px 0; color: #999;">3. Final Articles</h3>
						<p style="margin: 5px 0; color: #666; font-style: italic;">No articles created yet</p>
					</div>
				<?php endif; ?>
			</div>

			<!-- Summary Statistics -->
			<?php if (! empty($summary)) : ?>
				<div style="background: white; padding: 20px; margin: 20px 0; border: 1px solid #ccd0d4; border-radius: 4px;">
					<h2 style="margin-top: 0;">Summary</h2>
					<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
						<?php if (isset($summary['total_articles'])) : ?>
							<div style="text-align: center; padding: 20px; background: #f0f6fc; border-radius: 4px;">
								<div style="font-size: 36px; font-weight: bold; color: #2271b1;">
									<?php echo esc_html($summary['total_articles']); ?>
								</div>
								<div style="color: #666; margin-top: 5px;">Total Articles</div>
							</div>
						<?php endif; ?>
						<?php if (isset($summary['total_draft_revisions'])) : ?>
							<div style="text-align: center; padding: 20px; background: #f6f7f7; border-radius: 4px;">
								<div style="font-size: 36px; font-weight: bold; color: #666;">
									<?php echo esc_html($summary['total_draft_revisions']); ?>
								</div>
								<div style="color: #666; margin-top: 5px;">Draft Revisions</div>
							</div>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
<?php
	}
}
