<?php

namespace VM\AIFeed\Admin\Views;

use VM\AIFeed\Core\AIFeedConfig;
use VM\AIFeed\Helpers\AIDateHelpers;
use VM\AIFeed\Helpers\AIStatusHelpers;
use VM\AIFeed\Helpers\AIFeedUIHelpers;
use VM\AIFeed\Content\AIMarkdown;

/**
 * Handles displaying individual AI Feed research item pages
 */
class AIFeedQueueView
{

	public function display(string $research_id = ''): void
	{
		if (empty($research_id)) {
			wp_die('Research ID is required');
		}

		$research_item = $this->getResearchItemById($research_id);

		if (empty($research_item)) {
			wp_die('Research item not found');
		}

		$back_url = admin_url('admin.php?page=vm-ai-feed-queue');
		// Calculate duration between start and end time
		$duration = $this->calculateDuration($research_item['start_time'], $research_item['end_time']);

		echo '<div class="wrap">';
		echo '<div class="nav-tab-wrapper" style="border-bottom: none;">';
		echo '<a href="' . esc_url($back_url) . '" class="nav-tab nav-tab-active" style="background: #f9f9f9;">← Back to Research</a>';
		echo '</div>';

		// Main content and sidebar container
		echo '<div style="display: flex; gap: 20px; margin-bottom: 20px;">';
		// Left column - Main content
		echo '<div style="flex: 1;">';
		echo '<div class="research-content" style="background: #f9f9f9; padding: 20px; padding-top: 10px; border: 1px solid #c3c4c7; margin-bottom: 20px;">';
		$status_display = $this->formatStatusDisplay($research_item['status'] ?? 'pending');
		echo '<p><span class="status-badge ' . esc_attr($this->getStatusClass($research_item['status'] ?? 'pending')) . '" id="main-status-badge">' . esc_html($status_display) . '</span></p>';
		echo '<h1 id="research-headline" style="line-height: 2rem;">' . esc_html($this->getResearchItemTitle($research_item)) . '</h1>';

		// Real-time status monitoring section
		echo '<div id="realtime-status-container" style="margin: 20px 0; padding: 20px; background: white; border: 1px solid #ddd; border-radius: 8px; display: none;">';
		echo '<div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">';
		echo '<h3 style="margin: 0; color: #333;">Real-time Status</h3>';
		echo '<div id="status-indicator" style="width: 12px; height: 12px; border-radius: 50%; background: #ccc;"></div>';
		echo '<span id="last-updated" style="color: #666; font-size: 14px;"></span>';
		echo '<button id="refresh-status" class="button button-secondary" style="margin-left: 10px;">Refresh Now</button>';
		echo '<button id="toggle-auto-refresh" class="button button-secondary" style="margin-left: 5px;">Auto-refresh: ON</button>';
		echo '<button id="toggle-page-refresh" class="button button-primary" style="margin-left: 5px;">Page Refresh: ON</button>';
		echo '</div>';

		echo '<div id="research-status" style="margin-bottom: 20px;">';
		echo '<div id="progress-container" style="margin-bottom: 15px;">';
		echo '<div style="display: flex; justify-content: space-between; margin-bottom: 5px;">';
		echo '<span id="progress-label">Loading...</span>';
		echo '<span id="progress-percentage">0%</span>';
		echo '</div>';
		echo '<div style="background: #f0f0f0; border-radius: 10px; height: 8px; overflow: hidden;">';
		echo '<div id="progress-bar" style="background: #007cba; height: 100%; width: 0%; transition: width 0.3s ease;"></div>';
		echo '</div>';
		echo '</div>';

		echo '<div id="steps-container">';
		echo '<div id="steps-list"></div>';
		echo '</div>';

		echo '<div id="error-container" style="display: none; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px; padding: 15px; margin-top: 15px;">';
		echo '<h4 style="margin: 0 0 10px 0; color: #856404;">Error Details</h4>';
		echo '<div id="error-message" style="color: #856404;"></div>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
		echo '<div style="display: grid; grid-template-columns: 1fr 400px; gap: 15px;">';
		echo '<div>';
		echo '<p><strong>Type:</strong> ' . esc_html(ucfirst($research_item['type'] ?? 'unknown')) . '</p>';
		echo '<p><strong>Author:</strong> ' . esc_html($this->getAuthorName($research_item['post_author'] ?? 0)) . '</p>';
		echo '</div>';
		echo '<div>';
		echo '<p><strong>Created:</strong> ' . esc_html($this->formatDate($research_item['created_at'])) . '</p>';
		echo '<p><strong>Time To Write:</strong> ' . esc_html($duration) . '</p>';
		echo '</div>';
		echo '</div>';

		// Display progress percentage
		$progress = $research_item['progress_percentage'] ?? null;
		if ($progress !== null && $progress !== '') {
			$progress = (int) $progress;
			$color    = AIFeedUIHelpers::getProgressBarColor($progress);
			echo '<div style="margin-top: 15px;">';
			echo '<p><strong>Progress:</strong></p>';
			echo '<div style="display: flex; align-items: center; gap: 10px;">';
			echo '<div style="flex: 1; background: #f0f0f0; border-radius: 5px; height: 20px; overflow: hidden;">';
			echo '<div style="background: ' . esc_attr($color) . '; height: 100%; width: ' . esc_attr($progress) . '%; transition: width 0.3s ease;"></div>';
			echo '</div>';
			echo '<span style="font-weight: 600; min-width: 40px;">' . esc_html($progress) . '%</span>';
			echo '</div>';
			echo '</div>';
		}
		echo '</div>';

		// Display input data if available
		if (! empty($research_item['input']) && is_array($research_item['input'])) {
			echo '<div class="research-content" style="background: #f9f9f9; padding: 20px; margin-bottom: 20px; padding-top: 0; border: 1px solid #c3c4c7;">';
			echo '<h3>Input</h3>';
			// Display title
			if (! empty($research_item['input']['title'])) {
				echo '<p><strong>Title:</strong> ' . esc_html($research_item['input']['title']) . '</p>';
			}

			// Display URL
			if (! empty($research_item['input']['url'])) {
				echo '<p><strong>URL:</strong> <a href="' . esc_url($research_item['input']['url']) . '" target="_blank">' . esc_html($research_item['input']['url']) . '</a></p>';
			}

			// Display content
			if (! empty($research_item['input']['content'])) {
				echo '<div style="margin: 15px 0;">';
				echo '<strong>Content:</strong>';
				echo '<div style="background: #f8f9fa; padding: 10px; padding-left: 20px; padding-right: 20px; border-left: 4px solid #007cba; margin-top: 5px; max-height: 300px; overflow-y: auto;">';
				echo '<div style="white-space: pre-wrap; line-height: 1.5;">' . esc_html($research_item['input']['content']) . '</div>';
				echo '</div>';
				echo '</div>';
			}

			// Display keywords
			if (! empty($research_item['input']['keywords']) && is_array($research_item['input']['keywords'])) {
				echo '<p><strong>Keywords:</strong> ' . esc_html(implode(', ', $research_item['input']['keywords'])) . '</p>';
			}

			// Display backlinks
			if (! empty($research_item['input']['backlinks']) && is_array($research_item['input']['backlinks'])) {
				echo '<div style="margin: 15px 0;">';
				echo '<strong>Backlinks:</strong>';
				echo '<ul style="margin: 5px 0 0 20px;">';
				foreach ($research_item['input']['backlinks'] as $backlink) {
					echo '<li><a href="' . esc_url($backlink) . '" target="_blank">' . esc_html($backlink) . '</a></li>';
				}
				echo '</ul>';
				echo '</div>';
			}

			// Display embeds (objects with type/url/platform)
			if (! empty($research_item['input']['embeds']) && is_array($research_item['input']['embeds'])) {
				echo '<div style="margin: 15px 0;">';
				echo '<strong>Embeds:</strong>';
				echo '<ul style="margin: 5px 0 0 20px;">';
				foreach ($research_item['input']['embeds'] as $embed) {
					if (is_array($embed)) {
						$embed_url   = $embed['url'] ?? '';
						$embed_title = ($embed['platform'] ?? ($embed['type'] ?? 'Embed'));
						if (! empty($embed_url)) {
							echo '<li><a href="' . esc_url($embed_url) . '" target="_blank">' . esc_html($embed_title) . '</a></li>';
						}
					} else {
						echo '<li><a href="' . esc_url((string) $embed) . '" target="_blank">' . esc_html((string) $embed) . '</a></li>';
					}
				}
				echo '</ul>';
				echo '</div>';
			}
			echo '</div>';
		}

		// Queries
		if (! empty($research_item['queries']) && is_array($research_item['queries'])) {
			$q = $research_item['queries'];
			echo '<div class="research-content" style="background: #f9f9f9; padding: 20px; margin-bottom: 20px; padding-top: 0; border: 1px solid #c3c4c7;">';
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
		if (! empty($research_item['archives']) && is_array($research_item['archives'])) {
			echo '<div class="research-content" style="background: #f9f9f9; padding: 20px; margin-bottom: 20px; padding-top: 0; border: 1px solid #c3c4c7;">';
			echo '<h3>Archives</h3>';
			if (! empty($research_item['archives']['query'])) {
				echo '<p><strong>Query:</strong> ' . esc_html($research_item['archives']['query']) . '</p>';
			}
			if (! empty($research_item['archives']['response'])) {
				echo '<div style="background: #f8f9fa; padding: 10px; border-left: 4px solid #007cba;">' . nl2br(esc_html($research_item['archives']['response'])) . '</div>';
			}
			echo '</div>';
		}

		// Recent News
		if (! empty($research_item['recent_news']) && is_array($research_item['recent_news'])) {
			echo '<div class="research-content" style="background: #f9f9f9; padding: 20px; margin-bottom: 20px; padding-top: 0; border: 1px solid #c3c4c7;">';
			echo '<h3>Recent News</h3>';
			echo '<ul style="margin: 0; padding-left: 20px;">';
			foreach ($research_item['recent_news'] as $rn) {
				echo '<li style="margin-bottom: 10px;">';
				if (! empty($rn['query'])) {
					echo '<div><strong>Query:</strong> ' . esc_html($rn['query']) . '</div>';
				}
				if (! empty($rn['response'])) {
					echo '<div style="color: #555;">' . esc_html($rn['response']) . '</div>';
				}
				echo '</li>';
			}
			echo '</ul>';
			echo '</div>';
		}

		// Web search results
		if (! empty($research_item['web_search']) && is_array($research_item['web_search'])) {
			echo '<div class="research-content" style="background: #f9f9f9; padding: 20px; margin-bottom: 20px; padding-top: 0; border: 1px solid #c3c4c7;">';
			echo '<h3>Web Search</h3>';
			if (! empty($research_item['web_search']['query'])) {
				echo '<p><strong>Query:</strong> ' . esc_html($research_item['web_search']['query']) . '</p>';
			}
			if (! empty($research_item['web_search']['results']) && is_array($research_item['web_search']['results'])) {
				foreach ($research_item['web_search']['results'] as $result) {
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
		if (! empty($research_item['youtube']) && is_array($research_item['youtube'])) {
			echo '<div class="research-content" style="background: #f9f9f9; padding: 20px; margin-bottom: 20px; padding-top: 0; border: 1px solid #c3c4c7;">';
			echo '<h3>YouTube</h3>';
			if (! empty($research_item['youtube']['query'])) {
				echo '<p><strong>Query:</strong> ' . esc_html($research_item['youtube']['query']) . '</p>';
			}
			if (! empty($research_item['youtube']['results']) && is_array($research_item['youtube']['results'])) {
				foreach ($research_item['youtube']['results'] as $yt) {
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

		// Display article drafts if available
		if (! empty($research_item['article_drafts']) && is_array($research_item['article_drafts'])) {
			echo '<div class="research-content" style="background: #f9f9f9; padding: 20px; margin-bottom: 20px; border: 1px solid #c3c4c7;">';
			echo '<h3>Article Drafts (' . count($research_item['article_drafts']) . ')</h3>';

			foreach ($research_item['article_drafts'] as $index => $draft) {
				$draft_status = ! empty($draft['has_passed']) ? 'Passed' : 'Failed';
				$status_color = ! empty($draft['has_passed']) ? '#28a745' : '#dc3545';

				echo '<div style="background: white; padding: 15px; margin-bottom: 15px; border-left: 4px solid ' . esc_attr($status_color) . '; border-radius: 4px;">';
				echo '<div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">';
				echo '<h4 style="margin: 0; color: #333; font-size: 16px;">' . esc_html($draft['post_title'] ?? 'Untitled Draft') . '</h4>';
				echo '<span style="background: ' . esc_attr($status_color) . '; color: white; padding: 3px 8px; border-radius: 3px; font-size: 12px; font-weight: bold;">' . esc_html($draft_status) . '</span>';
				echo '</div>';

				if (! empty($draft['post_content_headline'])) {
					echo '<p style="margin: 8px 0; font-style: italic; color: #666;">' . esc_html($draft['post_content_headline']) . '</p>';
				}

				if (! empty($draft['post_excerpt'])) {
					echo '<p style="margin: 8px 0; color: #555; line-height: 1.5;">' . esc_html($draft['post_excerpt']) . '</p>';
				}

				if (! empty($draft['feedback'])) {
					echo '<div style="background: #f8f9fa; padding: 10px; margin-top: 10px; border-radius: 4px;">';
					echo '<strong style="color: #333;">Feedback:</strong><br>';
					echo '<span style="color: #555; font-size: 14px;">' . esc_html($draft['feedback']) . '</span>';
					echo '</div>';
				}

				if (! empty($draft['date'])) {
					echo '<p style="margin: 8px 0 0 0; font-size: 12px; color: #999;">Created: ' . esc_html(date('M j, Y g:i A', strtotime($draft['date']))) . '</p>';
				}
				echo '</div>';
			}
			echo '</div>';
		}

		// Raw research data preview (selected fields)
		$raw_preview = [
			'queries'     => $research_item['queries'] ?? null,
			'archives'    => $research_item['archives'] ?? null,
			'recent_news' => $research_item['recent_news'] ?? null,
			'web_search'  => $research_item['web_search'] ?? null,
			'youtube'     => $research_item['youtube'] ?? null,
		];
		echo '<div class="research-content" style="background: #f9f9f9; padding: 20px; margin-bottom: 20px; padding-top: 0; border: 1px solid #c3c4c7;">';
		echo '<h3>Raw Research Data</h3>';
		echo '<pre style="background: white; padding: 10px; border: 1px solid #ddd; overflow-x: auto; max-width: 100%; word-wrap: break-word; white-space: pre-wrap;">' . esc_html(json_encode($raw_preview, JSON_PRETTY_PRINT)) . '</pre>';
		echo '</div>';

		// // Display workflow and research IDs if available
		// if (!empty($queue_item['workflow_instance_id']) || !empty($queue_item['research_request_id'])) {
		// echo '<div class="queue-content" style="background: #f9f9f9; padding: 20px; margin-bottom: 20px; padding-top: 0; border: 1px solid #c3c4c7;">';
		// echo '<h3>System Information</h3>';
		// if (!empty($queue_item['workflow_instance_id'])) {
		// echo '<p><strong>Workflow Instance ID:</strong> ' . esc_html($queue_item['workflow_instance_id']) . '</p>';
		// }
		// if (!empty($queue_item['research_request_id'])) {
		// echo '<p><strong>Research Request ID:</strong> ' . esc_html($queue_item['research_request_id']) . '</p>';
		// }
		// echo '</div>';
		// }
		echo '</div>';

		// Keep - incase needed for future
		// Right column - Sidebar
		// echo '<div style="width: 300px; flex-shrink: 0;">';
		// echo '<div class="queue-content" style="background: #f9f9f9; padding: 20px; margin-bottom: 20px; border: 1px solid #c3c4c7;">';
		// echo '<div style="display: flex; flex-direction: column; gap: 10px;">';
		// echo '<button id="process-queue-item" class="button button-primary" style="width: 100%;" data-queue-id="' . esc_attr($queue_id) . '">Process Now</button>';
		// echo '<button id="delete-queue-item" class="button button-secondary" style="width: 100%;" data-queue-id="' . esc_attr($queue_id) . '">Delete</button>';
		// echo '<div id="process-status" style="display: none; margin-top: 10px; padding: 10px; border-radius: 4px;"></div>';
		// echo '</div>';
		// echo '</div>';

		// echo '<div class="queue-content" style="background: #f9f9f9; padding: 20px; margin-bottom: 20px; border: 1px solid #c3c4c7;">';
		// echo '<div id="ai-chat-container">';
		// echo '<div id="chat-messages"></div>';
		// echo '<div class="chat-input-area">';
		// echo '<textarea id="chat-input" rows="3"></textarea>';
		// echo '<button id="send-message">Send</button>';
		// echo '</div>';
		// echo '</div>';
		// echo '</div>';
		// echo '</div>';

		echo '</div>';

		echo '</div>';

		// Add JavaScript for real-time status updates
		$this->addRealtimeStatusScript($research_id, $research_item);
	}

	private function getResearchItemById(string $research_id): array
	{
		// Get configurable API URL
		$config  = AIFeedConfig::getInstance();
		$api_url = $config->getApiUrl('v1/api/research-request-combined/' . $research_id);
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

		if (json_last_error() !== JSON_ERROR_NONE) {
			return [];
		}

		// Handle API response structure
		if (isset($data['success']) && $data['success'] === true && isset($data['data'])) {
			$item = $data['data'];

			// Parse JSON fields if they exist and are strings
			foreach (['input', 'output', 'queries', 'archives', 'recent_news', 'web_search', 'youtube', 'article_drafts'] as $jsonField) {
				if (! empty($item[$jsonField]) && is_string($item[$jsonField])) {
					$decoded = json_decode($item[$jsonField], true);
					if (json_last_error() === JSON_ERROR_NONE) {
						$item[$jsonField] = $decoded;
					}
				}
			}

			return $item;
		}

		return [];
	}

	private function getStatusClass(string $status): string
	{
		return AIStatusHelpers::getStatusClass($status);
	}

	/**
	 * Format status display text
	 */
	private function formatStatusDisplay(string $status): string
	{
		return self::getStatusDisplayText($status);
	}

	/**
	 * Shared status display mapping
	 */
	private static function getStatusDisplayText(string $status): string
	{
		$normalized     = strtolower($status);
		$status_display = [
			'pending'           => 'Pending',
			'init_research'     => 'Researching',
			'running'           => 'Running',
			'completed'         => 'Completed',
			'research_complete' => 'Completed',
			'failed'            => 'Failed',
			'cancelled'         => 'Cancelled',
		];
		return $status_display[$normalized] ?? ucfirst($status);
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
			if (! empty($parsed['host'])) {
				return $parsed['host'] . (! empty($parsed['path']) ? $parsed['path'] : '');
			}
			return $item['url'];
		}

		return 'Research Item #' . ($item['id'] ?? 'Unknown');
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
	 * Display a Q&A pair with proper formatting
	 */
	private function displayQAPair(string $qa_text): void
	{
		// Split by "Question:" to get individual Q&A pairs
		$qa_pairs = preg_split('/Question: /', $qa_text, -1, PREG_SPLIT_NO_EMPTY);

		foreach ($qa_pairs as $qa_pair) {
			if (empty(trim($qa_pair))) {
				continue;
			}

			// Split by "Answer:" to separate question and answer
			$parts = explode("\nAnswer: ", $qa_pair, 2);

			if (count($parts) === 2) {
				$question = trim($parts[0]);
				$answer   = trim($parts[1]);

				// <div style="background: #f8f9fa; padding: 10px; padding-left: 20px; padding-right: 20px; border-left: 3px solid #007cba; margin-top: 5px; max-height: 300px; overflow-y: auto;">

				echo '<div style="margin-bottom: 25px; padding: 10px; padding-left: 20px; padding-right: 20px; background: #f8f9fa; border-left: 4px solid #007cba;">';
				echo '<h5 style="margin: 0 0 10px 0; color: #333; font-size: 16px;">' . esc_html($question) . '</h5>';
				echo '<div style="line-height: 1.6; color: #555;">';
				// Convert line breaks and format the answer
				$formatted_answer = nl2br(esc_html($answer));
				// Highlight references
				$formatted_answer = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $formatted_answer);
				$formatted_answer = preg_replace('/### ([^\n]+)/', '<h6 style="margin: 15px 0 10px 0; color: #333;">$1</h6>', $formatted_answer);
				$formatted_answer = preg_replace('/\*\*References:\*\*/', '<strong style="color: #007cba;">References:</strong>', $formatted_answer);
				echo $formatted_answer;
				echo '</div>';
				echo '</div>';
			} else {
				// Fallback for other string formats
				echo '<div style="margin-bottom: 15px; padding: 10px; background: #f8f9fa;">';
				echo '<div style="line-height: 1.6; color: #555;">' . nl2br(esc_html($qa_pair)) . '</div>';
				echo '</div>';
			}
		}
	}

	/**
	 * Calculate duration between start and end time
	 */
	private function calculateDuration(?string $start_time, ?string $end_time): string
	{
		return AIDateHelpers::calculateDuration($start_time, $end_time);
	}

	/**
	 * Add JavaScript for real-time status monitoring
	 */
	private function addRealtimeStatusScript(string $research_id, array $research_item): void
	{
		$config       = AIFeedConfig::getInstance();
		$api_base_url = $config->getApiBaseUrl();
		$api_key      = $config->getApiKey();

		// Determine if we should show real-time monitoring
		// Show for active research or recently completed ones (within last hour)
		$show_realtime = in_array($research_item['status'] ?? '', ['pending', 'running', 'processing', 'completed']);

		// For completed items, only show if they were completed recently
		if (($research_item['status'] ?? '') === 'completed') {
			$completed_time = strtotime($research_item['end_time'] ?? '');
			$one_hour_ago   = time() - 3600; // 1 hour ago
			$show_realtime  = $completed_time && $completed_time > $one_hour_ago;
		}

		// Get research instance ID if available
		$research_instance_id = $research_item['workflow_instance_id'] ?? $research_id;

		echo '<script type="text/javascript">';
		echo 'document.addEventListener("DOMContentLoaded", function() {';

		if ($show_realtime) {
			echo 'initRealtimeStatus("' . esc_js($research_id) . '", "' . esc_js($research_instance_id) . '", "' . esc_js($api_base_url) . '", "' . esc_js($api_key) . '");';
		}

		echo '});';

		echo <<<'JS'
        let autoRefreshInterval = null;
        let isAutoRefreshEnabled = true;
        let pageRefreshInterval = null;
        let isPageRefreshEnabled = true;

        function initRealtimeStatus(researchId, researchInstanceId, apiBaseUrl, apiKey) {
            const container = document.getElementById("realtime-status-container");
            if (!container) return;

            // Show the real-time container
            container.style.display = "block";

            // Start polling
            startPolling(researchId, researchInstanceId, apiBaseUrl, apiKey);

            // Start page refresh polling
            startPageRefresh(researchId, researchInstanceId, apiBaseUrl, apiKey);

            // Set up manual refresh button
            const refreshBtn = document.getElementById("refresh-status");
            if (refreshBtn) {
                refreshBtn.addEventListener("click", function() {
                    fetchResearchStatus(researchId, researchInstanceId, apiBaseUrl, apiKey);
                });
            }

            // Set up auto-refresh toggle
            const toggleBtn = document.getElementById("toggle-auto-refresh");
            if (toggleBtn) {
                toggleBtn.addEventListener("click", function() {
                    isAutoRefreshEnabled = !isAutoRefreshEnabled;
                    toggleBtn.textContent = isAutoRefreshEnabled ? "Auto-refresh: ON" : "Auto-refresh: OFF";

                    if (isAutoRefreshEnabled) {
                        startPolling(researchId, researchInstanceId, apiBaseUrl, apiKey);
                    } else {
                        stopPolling();
                    }
                });
            }

            // Set up page refresh toggle
            const pageRefreshBtn = document.getElementById("toggle-page-refresh");
            if (pageRefreshBtn) {
                pageRefreshBtn.addEventListener("click", function() {
                    isPageRefreshEnabled = !isPageRefreshEnabled;
                    pageRefreshBtn.textContent = isPageRefreshEnabled ? "Page Refresh: ON" : "Page Refresh: OFF";
                    pageRefreshBtn.className = isPageRefreshEnabled ? "button button-primary" : "button button-secondary";

                    if (isPageRefreshEnabled) {
                        startPageRefresh(researchId, researchInstanceId, apiBaseUrl, apiKey);
                    } else {
                        stopPageRefresh();
                    }
                });
            }
        }

        function startPolling(researchId, researchInstanceId, apiBaseUrl, apiKey) {
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
            }

            // Initial fetch
            fetchResearchStatus(researchId, researchInstanceId, apiBaseUrl, apiKey);

            // Set up polling every 3 seconds
            autoRefreshInterval = setInterval(function() {
                if (isAutoRefreshEnabled) {
                    fetchResearchStatus(researchId, researchInstanceId, apiBaseUrl, apiKey);
                }
            }, 3000);
        }

        function stopPolling() {
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
                autoRefreshInterval = null;
            }
        }

        function startPageRefresh(researchId, researchInstanceId, apiBaseUrl, apiKey) {
            if (pageRefreshInterval) {
                clearInterval(pageRefreshInterval);
            }

            // Check status every 10 seconds and refresh page if not complete
            pageRefreshInterval = setInterval(async function() {
                if (isPageRefreshEnabled) {
                    try {
                        const isComplete = await checkIfResearchComplete(researchId, researchInstanceId, apiBaseUrl, apiKey);
                        if (!isComplete) {
                            // Refresh the page
                            window.location.reload();
                        } else {
                            // Stop page refresh when complete
                            stopPageRefresh();
                        }
                    } catch (error) {
                        console.error("Error checking research completion:", error);
                    }
                }
            }, 10000); // Check every 10 seconds
        }

        function stopPageRefresh() {
            if (pageRefreshInterval) {
                clearInterval(pageRefreshInterval);
                pageRefreshInterval = null;
            }
        }

        async function checkIfResearchComplete(researchId, researchInstanceId, apiBaseUrl, apiKey) {
            try {
                let response = null;
                let data = null;

                // Try research instance ID first, then fall back to research ID
                const endpoints = [
                    apiBaseUrl + "/v1/workflows/status/" + researchInstanceId,
                    apiBaseUrl + "/v1/workflows/status/" + researchId
                ];

                for (const endpoint of endpoints) {
                    try {
                        response = await fetch(endpoint, {
                            method: "GET",
                            headers: {
                                "Authorization": "Bearer " + apiKey,
                                "Content-Type": "application/json"
                            }
                        });

                        if (response.ok) {
                            data = await response.json();
                            break;
                        }
                    } catch (e) {
                        console.log("Failed to fetch from " + endpoint + ":", e.message);
                        continue;
                    }
                }

            if (!response || !response.ok || !data || !data.success || !data.data || !data.data.status) {
                return false; // Assume not complete if we can't determine status
            }

            const status = data.data.status.status;
            return status === "completed" || status === "failed";

        } catch (error) {
            console.error("Error checking research completion:", error);
            return false; // Assume not complete on error
        }
        }

        // Fix: Ensure correct indentation for function definition
        async function fetchResearchStatus(researchId, researchInstanceId, apiBaseUrl, apiKey) {
        try {
            updateStatusIndicator("loading");
            updateLastUpdated();

            let response = null;
            let data = null;

            // Try research instance ID first, then fall back to research ID
            const endpoints = [
                apiBaseUrl + "/v1/workflows/status/" + researchInstanceId,
                apiBaseUrl + "/v1/workflows/status/" + researchId
            ];

            for (const endpoint of endpoints) {
                try {
                    response = await fetch(endpoint, {
                        method: "GET",
                        headers: {
                            "Authorization": "Bearer " + apiKey,
                            "Content-Type": "application/json"
                        }
                    });

                    if (response.ok) {
                        data = await response.json();
                        break;
                    }
                } catch (e) {
                    console.log("Failed to fetch from " + endpoint + ":", e.message);
                    continue;
                }
            }

            if (!response || !response.ok) {
                throw new Error("HTTP error! status: " + (response ? response.status : "No response"));
            }

            if (!data || !data.success || !data.data || !data.data.status) {
                throw new Error("Invalid response format");
            }

            updateResearchDisplay(data.data.status, researchId);

            // Stop polling and page refresh if research is completed or failed
            if (data.data.status.status === "completed" || data.data.status.status === "failed") {
                stopPolling();
                stopPageRefresh();
                updateStatusIndicator("completed");
                updateMainStatusBadge(data.data.status.status);
            }

        } catch (error) {
            console.error("Error fetching research status:", error);
            updateStatusIndicator("error");
            showError("Failed to fetch research status: " + error.message);
        }
        }

        function updateResearchDisplay(status, researchId) {
            const progressContainer = document.getElementById("progress-container");
            const stepsList = document.getElementById("steps-list");
            const errorContainer = document.getElementById("error-container");

            // Update progress
            if (status.progress !== undefined) {
                const progressBar = document.getElementById("progress-bar");
                const progressPercentage = document.getElementById("progress-percentage");
                const progressLabel = document.getElementById("progress-label");

                if (progressBar) {
                    progressBar.style.width = status.progress + "%";
                }
                if (progressPercentage) {
                    progressPercentage.textContent = Math.round(status.progress) + "%";
                }
                if (progressLabel) {
                    progressLabel.textContent = status.currentStep || "Processing...";
                }
            }

            // Update steps
            if (stepsList && status.steps) {
                stepsList.innerHTML = "";

                status.steps.forEach((step, index) => {
                    const stepDiv = document.createElement("div");
                    stepDiv.style.cssText = "display: flex; align-items: center; gap: 10px; padding: 8px 12px; margin: 5px 0; background: #f8f9fa; border-radius: 4px;";

                    // Status icon
                    const statusIcon = document.createElement("div");
                    statusIcon.style.cssText = "width: 20px; height: 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold;";

                    if (step.status === "completed") {
                        statusIcon.style.cssText += "background: #28a745; color: white;";
                        statusIcon.textContent = "✓";
                    } else if (step.status === "running") {
                        statusIcon.style.cssText += "background: #007cba; color: white;";
                        statusIcon.textContent = "⟳";
                    } else if (step.status === "failed") {
                        statusIcon.style.cssText += "background: #dc3545; color: white;";
                        statusIcon.textContent = "✗";
                    } else {
                        statusIcon.style.cssText += "background: #6c757d; color: white;";
                        statusIcon.textContent = "○";
                    }

                    // Step name
                    const stepName = document.createElement("span");
                    stepName.textContent = step.name || "Step " + (index + 1);
                    stepName.style.cssText = "flex: 1; font-weight: 500;";

                    // Step duration
                    if (step.startTime && step.endTime) {
                        const duration = new Date(step.endTime) - new Date(step.startTime);
                        const durationText = document.createElement("span");
                        durationText.textContent = Math.round(duration / 1000) + "s";
                        durationText.style.cssText = "color: #666; font-size: 12px;";
                        stepDiv.appendChild(durationText);
                    }

                    stepDiv.appendChild(statusIcon);
                    stepDiv.appendChild(stepName);
                    stepsList.appendChild(stepDiv);
                });
            }

            // Handle errors
            if (status.status === "failed" && status.error) {
                showError(status.error.message || "Research failed");
            } else {
                hideError();
            }
        }

        function updateStatusIndicator(status) {
            const indicator = document.getElementById("status-indicator");
            if (!indicator) return;

            switch (status) {
                case "loading":
                    indicator.style.cssText = "width: 12px; height: 12px; border-radius: 50%; background: #007cba; animation: pulse 1.5s infinite;";
                    break;
                case "completed":
                    indicator.style.cssText = "width: 12px; height: 12px; border-radius: 50%; background: #28a745;";
                    break;
                case "error":
                    indicator.style.cssText = "width: 12px; height: 12px; border-radius: 50%; background: #dc3545;";
                    break;
                default:
                    indicator.style.cssText = "width: 12px; height: 12px; border-radius: 50%; background: #ccc;";
            }
        }

        function updateLastUpdated() {
            const lastUpdated = document.getElementById("last-updated");
            if (lastUpdated) {
                lastUpdated.textContent = "Last updated: " + new Date().toLocaleTimeString();
            }
        }

        function updateMainStatusBadge(status) {
            const badge = document.getElementById("main-status-badge");
            if (badge) {
                // Map statuses to user-friendly display text
                let displayStatus = status;
                if (status.toLowerCase() === 'research_complete') {
                    displayStatus = 'Completed';
                } else if (status.toLowerCase() === 'init_research') {
                    displayStatus = 'Researching';
                } else {
                    displayStatus = status.charAt(0).toUpperCase() + status.slice(1);
                }

                badge.textContent = displayStatus;

                // Use completed class for research_complete
                const statusClass = status.toLowerCase() === 'research_complete' ? 'completed' : status.toLowerCase();
                badge.className = "status-badge status-" + statusClass;
            }
        }

        function showError(message) {
            const errorContainer = document.getElementById("error-container");
            const errorMessage = document.getElementById("error-message");

            if (errorContainer && errorMessage) {
                errorMessage.textContent = message;
                errorContainer.style.display = "block";
            }
        }

        function hideError() {
            const errorContainer = document.getElementById("error-container");
            if (errorContainer) {
                errorContainer.style.display = "none";
            }
        }
        JS;

		// Add CSS for pulse animation
		echo '<style>
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        </style>';

		echo '</script>';
	}
}
