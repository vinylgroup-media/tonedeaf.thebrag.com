<?php

namespace VM\AIFeed\Admin\Tables;

use VM\AIFeed\Admin\Pages\AIFeedAIConfigs;
use VM\AIFeed\Core\AIFeedConfig;
use VM\AIFeed\Helpers\AIDateHelpers;
use VM\AIFeed\Helpers\AIStatusHelpers;
use VM\AIFeed\Helpers\AIFeedUIHelpers;

/**
 * Table for displaying Research items in the AI Feed admin page
 */
class AIFeedQueueTable
{

	public function display(): void
	{
		$research_items = $this->fetchResearchFromAPI();

		echo '<table class="wp-list-table widefat fixed striped" style="table-layout: fixed;">';
		echo '<thead><tr>';
		echo '<th style="width: 50px; text-align: center;">ID</th>';
		echo '<th style="width: 25%;">Title</th>';
		echo '<th style="width: 100px;">Status</th>';
		echo '<th style="width: 80px;">Progress</th>';
		echo '<th style="width: 80px;">Type</th>';
		echo '<th style="width: 100px;">Author</th>';
		echo '<th style="width: 120px;">Research Time</th>';
		echo '<th style="width: 150px;">Message</th>';
		echo '<th style="width: 100px;">Actions</th>';
		echo '</tr></thead>';
		echo '<tbody>';

		if (! empty($research_items)) {
			foreach ($research_items as $item) {
				$view_url     = admin_url('admin.php?page=vm-ai-feed-queue&action=view-queue-item&queue_id=' . urlencode($item['research_request_id']));
				$status       = $item['status'] ?? 'pending';
				$status_class = $this->getStatusClass($status);

				// Extract title from URL or input
				$title = $this->getResearchItemTitle($item);

				// Calculate duration between start and end time
				$duration = $this->calculateDuration($item['start_time'], $item['end_time']);

				// Get progress percentage
				$progress      = $item['progress_percentage'] ?? null;
				$progress_html = $this->formatProgress($progress);

				// Format status display
				$status_display = $this->formatStatusDisplay($status);

				echo '<tr>';
				echo '<td style="text-align: center;">' . esc_html($item['id'] ?? 'N/A') . '</td>';
				echo '<td style="word-wrap: break-word; overflow-wrap: break-word;"><a href="' . esc_url($view_url) . '">' . esc_html($title) . '</a></td>';
				echo '<td><span class="status-badge ' . esc_attr($status_class) . '">' . esc_html($status_display) . '</span></td>';
				echo '<td>' . $progress_html . '</td>';
				echo '<td>' . esc_html(ucfirst($item['type'] ?? 'unknown')) . '</td>';
				echo '<td>' . esc_html($this->getAuthorName($item['post_author'] ?? 0)) . '</td>';
				echo '<td>' . esc_html($duration) . '</td>';
				echo '<td>' . esc_html($item['message'] ?? 'N/A') . '</td>';
				echo '<td>';

				// Show restart and delete buttons for failed/errored research
				if (in_array(strtolower($status), ['failed', 'error', 'cancelled'])) {
					// Store original research_request_id for tracking purposes
					// Note: API may overwrite research_request_id on restart, so we preserve it here
					echo '<button class="button button-small button-primary restart-research" data-queue-id="' . esc_attr($item['id']) . '" data-workflow-instance-id="' . esc_attr($item['research_request_id']) . '" data-original-request-id="' . esc_attr($item['research_request_id']) . '" data-url="' . esc_attr($item['url'] ?? '') . '">Restart</button> ';
					echo '<button class="button button-small button-secondary delete-research-item" data-queue-id="' . esc_attr($item['id']) . '">Delete</button>';
				}

				echo '</td>';
				echo '</tr>';
			}
		} else {
			echo '<tr><td colspan="9">No research items found</td></tr>';
		}

		echo '</tbody></table>';
	}

	private function fetchResearchFromAPI(): array
	{
		// Get configurable API URL
		$config  = AIFeedConfig::getInstance();
		$api_url = $config->getApiUrl('api/research-requests');
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

		$status_code = wp_remote_retrieve_response_code($response);
		if ($status_code < 200 || $status_code >= 300) {
			return [];
		}

		$body = wp_remote_retrieve_body($response);

		$body = wp_remote_retrieve_body($response);
		$data = json_decode($body, true);

		if (json_last_error() !== JSON_ERROR_NONE) {
			return [];
		}

		$research_items = [];

		// Handle API response structure
		if (isset($data['success']) && $data['success'] === true && isset($data['data'])) {
			$items = is_array($data['data']['research_requests']) ? $data['data']['research_requests'] : [];

			foreach ($items as $item) {
				// Parse JSON fields if they exist and are strings
				if (! empty($item['input']) && is_string($item['input'])) {
					$item['input'] = json_decode($item['input'], true);
				}
				if (! empty($item['output']) && is_string($item['output'])) {
					$item['output'] = json_decode($item['output'], true);
				}
				$research_items[] = $item;
			}
		}

		return $research_items;
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
		// Map init_research to Researching
		if (strtolower($status) === 'init_research') {
			return 'Researching';
		}

		// Map research_complete to Completed
		if (strtolower($status) === 'research_complete') {
			return 'Completed';
		}

		return ucfirst($status);
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
		// Fall back to URL domain or path
		if (! empty($item['url'])) {
			$parsed = parse_url($item['url']);
			if ($parsed !== false && ! empty($parsed['host'])) {
				return $parsed['host'] . (! empty($parsed['path']) ? $parsed['path'] : '');
			}
			return $item['url'];
		}
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
	 * Calculate duration between start and end time
	 */
	private function calculateDuration(?string $start_time, ?string $end_time): string
	{
		return AIDateHelpers::calculateDuration($start_time, $end_time);
	}

	/**
	 * Format messages for display in the table
	 */
	private function formatMessages(array $messages): string
	{
		if (empty($messages)) {
			return 'None';
		}

		$count = count($messages);
		if ($count === 1) {
			return '1 message';
		}

		return $count . ' messages';
	}

	/**
	 * Format progress percentage with visual bar
	 */
	private function formatProgress($progress): string
	{
		if ($progress === null || $progress === '') {
			return '<span style="color: #666; font-size: 12px;">N/A</span>';
		}

		$progress = (int) $progress;
		$color    = AIFeedUIHelpers::getProgressBarColor($progress);

		return '<div style="display: flex; align-items: center; gap: 5px;">' .
			'<div style="flex: 1; background: #f0f0f0; border-radius: 3px; height: 6px; overflow: hidden;">' .
			'<div style="background: ' . esc_attr($color) . '; height: 100%; width: ' . esc_attr($progress) . '%;"></div>' .
			'</div>' .
			'<span style="font-size: 11px; color: #666; min-width: 30px;">' . esc_html($progress) . '%</span>' .
			'</div>';
	}

	/**
	 * Display the generate article form
	 */
	public function displayGenerateForm(): void
	{
?>
		<div class="card" style="max-width: 100%; margin-bottom: 20px;">
			<h2>Research & Write Article from URL</h2>
			<form id="generate-article-form" method="post">
				<?php wp_nonce_field('generate_article_nonce', 'generate_article_nonce'); ?>

				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="article_url">Article URL</label>
						</th>
						<td>
							<input type="url"
								id="article_url"
								name="article_url"
								class="regular-text"
								placeholder="https://example.com/article"
								required>
							<p class="description">Enter the URL of the article you want researched and written.</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="article_context">Context (Optional)</label>
						</th>
						<td>
							<textarea id="article_context"
								name="article_context"
								class="large-text"
								rows="6"
								style="max-width: 350px; width: 100%;"
								placeholder="Provide additional context to guide the article writing process... or leave blank to let the writer decide."></textarea>
							<p class="description">Optional context that will be used as a guide when writing the article.</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="ai_config_name">Writer</label>
						</th>
						<td>
							<?php $this->renderAIConfigDropdown(); ?>
							<p class="description">Select a writer for this article.</p>
						</td>
					</tr>
					<tr>
						<th></th>
						<td>
							<button type="submit" id="generate-article-submit" class="button button-primary">
								Write Article
							</button>
							<span id="generate-article-loading" style="display: none;">
								<span class="spinner is-active" style="float: none; margin-left: 10px;"></span>
							</span>
						</td>
					</tr>
				</table>
			</form>

			<div id="generate-article-result" style="display: none; margin-top: 10px;"></div>
		</div>

		<script>
			jQuery(document).ready(function($) {
				$('#generate-article-form').on('submit', function(e) {
					e.preventDefault();

					var url = $('#article_url').val();
					var aiConfigName = $('#ai_config_name').val();
					var context = $('#article_context').val().trim();
					var nonce = $('#generate_article_nonce').val();

					if (!url) {
						alert('Please enter a valid URL');
						return;
					}

					if (!aiConfigName) {
						alert('Please select a writer');
						return;
					}

					// Show loading state
					$('#generate-article-submit').prop('disabled', true);
					$('#generate-article-loading').show();
					$('#generate-article-result').hide();

					// Build data object, including context only if provided
					var ajaxData = {
						action: 'generate_article',
						url: url,
						ai_config_name: aiConfigName,
						nonce: nonce
					};

					if (context) {
						ajaxData.context = context;
					}

					// Submit to backend endpoint
					$.ajax({
						url: ajaxurl,
						type: 'POST',
						data: ajaxData,
						success: function(response) {
							if (response.success) {
								// Show success message
								$('#generate-article-result').html(
									'<div class="notice notice-success"><p>Article submitted successfully! Refreshing research...</p></div>'
								).show();
								$('#article_url').val('');
								$('#article_context').val('');

								// Reload the page to show the new research item
								setTimeout(function() {
									window.location.reload();
								}, 1500);
							} else {
								$('#generate-article-submit').prop('disabled', false);
								$('#generate-article-loading').hide();
								$('#generate-article-result').html(
									'<div class="notice notice-error"><p>Error: ' + response.data + '</p></div>'
								).show();
							}
						},
						error: function() {
							$('#generate-article-submit').prop('disabled', false);
							$('#generate-article-loading').hide();
							$('#generate-article-result').html(
								'<div class="notice notice-error"><p>An error occurred while processing your request.</p></div>'
							).show();
						}
					});
				});

				// Handle restart research button clicks
				$('.restart-research').on('click', function(e) {
					e.preventDefault();

					var button = $(this);
					var researchInstanceId = button.data('workflow-instance-id');
					var originalRequestId = button.data('original-request-id');

					console.log('Restart button clicked');
					console.log('Research Instance ID:', researchInstanceId);
					console.log('Original Request ID:', originalRequestId);
					console.log('Button data attributes:', button.data());

					if (!researchInstanceId) {
						alert('No research instance ID found. Check browser console for details.');
						return;
					}

					// Disable button and show loading
					button.prop('disabled', true).text('Restarting...');

					// Call the restart research endpoint
					$.ajax({
						url: ajaxurl,
						type: 'POST',
						data: {
							action: 'restart_research',
							workflow_instance_id: researchInstanceId,
							nonce: $('#generate_article_nonce').val()
						},
						success: function(response) {
							console.log('Restart response:', response);

							// Defensive check: Warn if API returned a different instance ID
							if (response.success && response.data.new_instance_id && response.data.new_instance_id !== researchInstanceId) {
								console.warn('API returned different instance ID on restart!');
								console.warn('Original research_request_id:', researchInstanceId);
								console.warn('New research instance ID:', response.data.new_instance_id);
								console.warn('This may indicate research_request_id was overwritten by API');
							}

							if (response.success) {
								// Show success message and reload page to see updated status
								alert('Research restarted successfully!');
								window.location.reload();
							} else {
								alert('Error: ' + (response.data || 'Failed to restart research'));
								button.prop('disabled', false).text('Restart');
							}
						},
						error: function(xhr, status, error) {
							console.error('Restart error:', xhr, status, error);
							console.error('Response text:', xhr.responseText);
							console.error('Response JSON:', xhr.responseJSON);

							var errorMsg = 'An error occurred while restarting the research.';
							if (xhr.responseJSON && xhr.responseJSON.data) {
								errorMsg = xhr.responseJSON.data;
							} else if (xhr.responseText) {
								errorMsg += '\n\nServer response: ' + xhr.responseText.substring(0, 500);
							}
							alert(errorMsg);
							button.prop('disabled', false).text('Restart');
						}
					});
				});

				// Handle delete research item button clicks
				$('.delete-research-item').on('click', function(e) {
					e.preventDefault();

					var button = $(this);
					var researchId = button.data('queue-id');

					if (!researchId) {
						alert('No research ID found');
						return;
					}

					// Confirm deletion
					if (!confirm('Are you sure you want to delete this research request? This action cannot be undone.')) {
						return;
					}

					// Disable button and show loading
					button.prop('disabled', true).text('Deleting...');

					// Call the delete research endpoint
					$.ajax({
						url: ajaxurl,
						type: 'POST',
						data: {
							action: 'delete_research',
							workflow_id: researchId,
							nonce: $('#generate_article_nonce').val()
						},
						success: function(response) {
							if (response.success) {
								// Show success message and reload page
								alert('Research request deleted successfully!');
								window.location.reload();
							} else {
								alert('Error: ' + (response.data || 'Failed to delete research request'));
								button.prop('disabled', false).text('Delete');
							}
						},
						error: function(xhr, status, error) {
							var errorMsg = 'An error occurred while deleting the research request.';
							if (xhr.responseJSON && xhr.responseJSON.data) {
								errorMsg = xhr.responseJSON.data;
							}
							alert(errorMsg);
							button.prop('disabled', false).text('Delete');
						}
					});
				});
			});
		</script>
<?php
	}

	/**
	 * Render AI config dropdown
	 */
	private function renderAIConfigDropdown(): void
	{
		$configs = AIFeedAIConfigs::fetchAIConfigsForDropdown();

		echo '<select id="ai_config_name" name="ai_config_name" class="regular-text" required>';
		echo '<option value="">Select a writer...</option>';

		$default_selected = false;
		foreach ($configs as $config) {
			$selected = '';
			// Select the default writer if it exists and hasn't been selected yet
			if (! $default_selected && ! empty($config['is_default']) && $config['is_default'] === true) {
				$selected         = ' selected';
				$default_selected = true;
			}
			echo '<option value="' . esc_attr($config['name']) . '"' . $selected . '>';
			echo esc_html($config['display_name']);
			echo '</option>';
		}

		echo '</select>';
	}
}
