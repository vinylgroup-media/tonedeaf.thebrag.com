<?php

namespace VM\AIFeed\Admin\Tables;

use VM\AIFeed\Core\AIFeedConfig;
use VM\AIFeed\Helpers\AIDateHelpers;
use VM\AIFeed\Helpers\AIStatusHelpers;
use VM\AIFeed\Helpers\AIFeedUIHelpers;
use VM\AIFeed\Admin\Pages\AIFeedAIConfigs;

/**
 * Table for displaying Articles in the AI Feed admin page
 */
class AIFeedArticlesTable
{

	public function display(): void
	{
		$this->displayTable();
	}

	public function displayTable(): void
	{
		// Get current page and filters from URL
		$current_page = isset($_GET['paged']) ? max(1, \intval($_GET['paged'])) : 1;
		$filters      = [
			'status'    => isset($_GET['filter_status']) ? sanitize_text_field($_GET['filter_status']) : '',
			'type'      => isset($_GET['filter_type']) ? sanitize_text_field($_GET['filter_type']) : '',
			'sortBy'    => isset($_GET['sortBy']) ? sanitize_text_field($_GET['sortBy']) : 'created_at',
			'sortOrder' => isset($_GET['sortOrder']) ? sanitize_text_field($_GET['sortOrder']) : 'DESC',
		];

		// Fetch both articles and research requests, then merge them
		$articles_data  = $this->fetchArticlesFromAPI($current_page, $filters);
		$articles       = $articles_data['articles'] ?? [];
		$total_articles = $articles_data['total'] ?? 0;
		$total_pages    = $articles_data['total_pages'] ?? 1;

		$research_requests = $this->fetchResearchRequestsFromAPI();
		$merged_items      = $this->mergeArticlesAndResearch($articles, $research_requests);

		// Add nonce field for AJAX requests
		wp_nonce_field('create_article_nonce', 'create_article_nonce');
		wp_nonce_field('generate_article_nonce', 'generate_article_nonce');

		// Render filters and pagination together at top
		$this->renderFiltersAndPagination($filters, $current_page, $total_pages, $total_articles);

		echo '<table class="wp-list-table widefat fixed striped" style="table-layout: fixed;">';
		echo '<thead><tr>';
		echo '<th style="width: 50px; text-align: center;">ID</th>';
		echo '<th style="width: 25%;">Title</th>';
		echo '<th style="width: 100px;">Status</th>';
		echo '<th style="width: 80px;">Progress</th>';
		echo '<th style="width: 100px;">Author</th>';
		echo '<th style="width: 100px;">Time to Write</th>';
		echo '<th style="width: 80px;">Revisions</th>';
		echo '<th style="width: 100px;">Actions</th>';
		echo '</tr></thead>';
		echo '<tbody>';

		if (! empty($merged_items)) {
			foreach ($merged_items as $item) {
				$is_research = isset($item['is_research']) && $item['is_research'] === true;

				if ($is_research) {
					// Research request item
					$research_request_id = $item['research_request_id'] ?? '';
					$view_url            = admin_url('admin.php?page=vm-ai-feed-articles&action=view-research&research_request_id=' . urlencode($research_request_id));
					$status              = $item['status'] ?? 'pending';
					$status_class        = $this->getStatusClass($status);
					$title               = $this->getResearchItemTitle($item);
					$progress            = $item['progress_percentage'] ?? null;
					$progress            = ($progress === null || $progress === '') ? -1 : (int) $progress;
					$progress_html       = $this->formatProgress($progress, 'queue');

					// Format status display for research
					$status_display = $this->formatResearchStatusDisplay($status);

					echo '<tr>';
					echo '<td style="text-align: center;">R-' . esc_html($item['id'] ?? 'N/A') . '</td>';
					echo '<td style="word-wrap: break-word; overflow-wrap: break-word;"><a href="' . esc_url($view_url) . '">' . esc_html($title) . '</a></td>';
					echo '<td><span class="status-badge ' . esc_attr($status_class) . '">' . esc_html($status_display) . '</span></td>';
					echo '<td>' . $progress_html . '</td>';
					echo '<td>' . esc_html($this->getAuthorName($item['post_author'] ?? 0)) . '</td>';
					echo '<td>' . esc_html($this->calculateDuration($item['start_time'] ?? null, $item['end_time'] ?? null)) . '</td>';
					echo '<td>—</td>'; // No revisions for research items
					echo '<td>';
					// Show restart and delete buttons for failed/errored research
					if (in_array(strtolower($status), ['failed', 'error', 'cancelled'])) {
						echo '<button class="button button-small button-primary restart-research" data-workflow-instance-id="' . esc_attr($research_request_id) . '" data-original-request-id="' . esc_attr($research_request_id) . '">Restart</button> ';
					}
					echo '<button class="button button-small button-secondary delete-research-item" data-queue-id="' . esc_attr($research_request_id) . '">Delete</button>';
					echo '</td>';
					echo '</tr>';
				} else {
					// Article item
					$article_id = $item['id'] ?? '';
					$view_url   = '';
					$status     = $item['status'] ?? 'draft';

					// Link should only be visible if article_id exists AND status allows link visibility
					$has_link = ! empty($article_id) && $this->isStatusLinkVisible($status);

					if ($has_link) {
						// Use article_id directly in URL instead of looking it up
						$view_url = admin_url('admin.php?page=vm-ai-feed-articles&action=view-article&article_id=' . urlencode($article_id));
					}

					$status_class = $this->getStatusClass($status);

					// Get progress percentage - only show for queue-type articles
					$article_type = $item['type'] ?? 'queue'; // Default to queue for backward compatibility
					$progress     = $item['progress_percentage'] ?? null;
					// Convert progress to int, use -1 as sentinel for missing/null data
					$progress      = ($progress === null || $progress === '') ? -1 : (int) $progress;
					$progress_html = $this->formatProgress($progress, $article_type);

					echo '<tr>';
					echo '<td style="text-align: center;">' . esc_html($item['id'] ?? 'N/A') . '</td>';
					echo '<td style="word-wrap: break-word; overflow-wrap: break-word;">';
					if ($has_link) {
						echo '<a href="' . esc_url($view_url) . '">' . esc_html($item['post_title'] ?? 'Untitled') . '</a>';
					} else {
						$status_lower = strtolower($status);
						$tooltip = '';

						if ($status_lower === 'generating') {
							$tooltip = ' title="Article is being generated"';
						} elseif ($status_lower === 'error') {
							$tooltip = ' title="Generation error - article not available"';
						} elseif ($status_lower === 'pending') {
							$tooltip = ' title="Generation pending"';
						}

						$style = 'color: #999;' . ($tooltip ? ' font-style: italic;' : '');
						echo '<span style="' . $style . '"' . $tooltip . '>' . esc_html($item['post_title'] ?? 'Untitled') . '</span>';
					}
					echo '</td>';
					echo '<td><span class="status-badge ' . esc_attr($status_class) . '">' . esc_html(ucfirst($status)) . '</span></td>';
					echo '<td>' . $progress_html . '</td>';
					echo '<td>' . esc_html($this->getAuthorName($item['post_author'] ?? 0)) . '</td>';
					echo '<td>' . esc_html($this->calculateDuration($item['start_time'] ?? null, $item['completed_time'] ?? null)) . '</td>';
					echo '<td>' . esc_html($item['iterations'] ?? '0') . '</td>';
					echo '<td>';
					echo '<button class="button button-small button-secondary delete-article" data-article-id="' . esc_attr($item['id']) . '" data-article-title="' . esc_attr($item['post_title'] ?? 'Untitled') . '">Delete</button>';
					echo '</td>';
					echo '</tr>';
				}
			}
		} else {
			echo '<tr><td colspan="8">No articles or research items found</td></tr>';
		}

		echo '</tbody></table>';

		// Render pagination controls
		$this->renderPagination($current_page, $total_pages, $total_articles);

		// Render delete article JavaScript handler
		$this->renderDeleteArticleScript();

		// Render research item JavaScript handlers
		$this->renderResearchItemScripts();
	}

	private function fetchArticlesFromAPI(int $page = 1, array $filters = []): array
	{
		// Get configurable API URL
		$config  = AIFeedConfig::getInstance();
		$api_url = $config->getApiUrl('api/articles');
		$api_key = $config->getApiKey();

		// Build query parameters
		$params = [
			'limit' => 15,
			'page'  => $page,
		];

		// Add filters if provided
		if (! empty($filters['status'])) {
			$params['status'] = $filters['status'];
		}
		if (! empty($filters['type'])) {
			$params['type'] = $filters['type'];
		}
		if (! empty($filters['sortBy'])) {
			$params['sortBy'] = $filters['sortBy'];
		}
		if (! empty($filters['sortOrder'])) {
			$params['sortOrder'] = $filters['sortOrder'];
		}

		$api_url = add_query_arg($params, $api_url);

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
			return [
				'articles'    => [],
				'total'       => 0,
				'total_pages' => 1,
			];
		}

		$body = wp_remote_retrieve_body($response);
		$data = json_decode($body, true);

		if (json_last_error() !== JSON_ERROR_NONE) {
			return [
				'articles'    => [],
				'total'       => 0,
				'total_pages' => 1,
			];
		}

		$articles    = [];
		$total       = 0;
		$total_pages = 1;

		// Handle API response structure
		if (isset($data['success']) && $data['success'] === true && isset($data['data'])) {
			// Check if data contains articles array
			if (isset($data['data']['articles']) && is_array($data['data']['articles'])) {
				$articles = $data['data']['articles'];

				// Extract pagination metadata
				if (isset($data['data']['pagination'])) {
					$pagination = $data['data']['pagination'];
					$total      = $pagination['total'] ?? count($articles);
					$limit      = $pagination['limit'] ?? 15;
					// Calculate total pages from total and limit
					$total_pages = $limit > 0 ? ceil($total / $limit) : 1;
				} else {
					$total       = count($articles);
					$total_pages = 1;
				}
			}
			// Fallback: data is directly an array of articles
			elseif (is_array($data['data'])) {
				$articles    = $data['data'];
				$total       = count($articles);
				$total_pages = 1;
			}
		}

		return [
			'articles'    => $articles,
			'total'       => $total,
			'total_pages' => $total_pages,
		];
	}

	/**
	 * Fetch research requests from API that haven't created articles yet
	 */
	private function fetchResearchRequestsFromAPI(): array
	{
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
		$data = json_decode($body, true);

		if (json_last_error() !== JSON_ERROR_NONE) {
			return [];
		}

		$research_items = [];

		// Handle API response structure
		if (isset($data['success']) && $data['success'] === true && isset($data['data'])) {
			$items = is_array($data['data']['research_requests']) ? $data['data']['research_requests'] : [];

			foreach ($items as $item) {
				// Include research requests that are active (pending, running, processing, completed, research_complete)
				// Exclude only failed, error, cancelled, or terminated statuses
				$status = strtolower($item['status'] ?? '');
				// $excluded_statuses = array( 'failed', 'error', 'cancelled', 'terminated' );

				// Include all statuses, as UI expects to handle them
				// Parse JSON fields if they exist and are strings
				if (! empty($item['input']) && is_string($item['input'])) {
					$item['input'] = json_decode($item['input'], true);
				}
				if (! empty($item['output']) && is_string($item['output'])) {
					$item['output'] = json_decode($item['output'], true);
				}
				$item['is_research'] = true; // Mark as research item
				$research_items[]    = $item;
			}
		}

		return $research_items;
	}

	/**
	 * Merge articles and research requests, avoiding duplicates
	 * Research requests that have created articles are excluded
	 */
	private function mergeArticlesAndResearch(array $articles, array $research_requests): array
	{
		// Get all research_request_ids from articles (use research_request_id as primary key)
		$article_research_ids = [];
		foreach ($articles as $article) {
			// Use research_request_id as the primary identifier (articles link back via this field)
			$research_id = $article['research_request_id'] ?? null;
			if (! empty($research_id)) {
				// Normalize the research_request_id for comparison (convert to string, trim)
				$research_id = trim((string) $research_id);
				if (! empty($research_id)) {
					$article_research_ids[] = $research_id;
				}
			}
		}

		// Filter out research requests that already have articles
		// Use research_request_id as the primary matching key
		$filtered_research = [];
		foreach ($research_requests as $research) {
			// Use research_request_id as the primary identifier
			$research_id = $research['research_request_id'] ?? '';
			// Normalize for comparison (convert to string, trim)
			$research_id = trim((string) $research_id);

			// Include research request if:
			// 1. It has a research_request_id AND no article exists with that research_request_id, OR
			// 2. research_request_id is empty (might be newly created, include it)
			// Only include research requests with empty research_request_id if no article exists with empty research_request_id
			if (
				(empty($research_id) && ! in_array('', $article_research_ids, true)) ||
				(! empty($research_id) && ! in_array($research_id, $article_research_ids, true))
			) {
				$filtered_research[] = $research;
			}
		}

		// Combine articles and filtered research requests
		$merged = array_merge($filtered_research, $articles);

		// Sort by created_at/start_time descending (newest first)
		usort(
			$merged,
			function ($a, $b) {
				$a_time = $a['created_at'] ?? $a['start_time'] ?? '';
				$b_time = $b['created_at'] ?? $b['start_time'] ?? '';
				if (empty($a_time) && empty($b_time)) {
					return 0;
				}
				if (empty($a_time)) {
					return 1;
				}
				if (empty($b_time)) {
					return -1;
				}
				return strtotime($b_time) - strtotime($a_time);
			}
		);

		return $merged;
	}

	/**
	 * Render filters and pagination together in a flex layout
	 */
	private function renderFiltersAndPagination(array $filters, int $current_page, int $total_pages, int $total_items): void
	{
		$base_url = admin_url('admin.php?page=vm-ai-feed-articles');

		// Preserve filter parameters in pagination URLs
		$filter_params = [];
		if (! empty($_GET['filter_status'])) {
			$filter_params['filter_status'] = sanitize_text_field($_GET['filter_status']);
		}
		if (! empty($_GET['filter_type'])) {
			$filter_params['filter_type'] = sanitize_text_field($_GET['filter_type']);
		}
		if (! empty($_GET['sortBy'])) {
			$filter_params['sortBy'] = sanitize_text_field($_GET['sortBy']);
		}
		if (! empty($_GET['sortOrder'])) {
			$filter_params['sortOrder'] = sanitize_text_field($_GET['sortOrder']);
		}

		echo '<div class="tablenav top" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">';

		// Left side: Filters
		echo '<div class="alignleft actions">';

		// Status filter
		echo '<select name="filter_status" id="filter_status">';
		echo '<option value=""' . selected($filters['status'], '', false) . '>All Statuses</option>';
		echo '<option value="generating"' . selected($filters['status'], 'generating', false) . '>Generating</option>';
		echo '<option value="draft"' . selected($filters['status'], 'draft', false) . '>Draft</option>';
		echo '<option value="published"' . selected($filters['status'], 'published', false) . '>Published</option>';
		echo '<option value="archived"' . selected($filters['status'], 'archived', false) . '>Archived</option>';
		echo '<option value="error"' . selected($filters['status'], 'error', false) . '>Error</option>';
		echo '</select>';

		// Type filter
		echo '<select name="filter_type" id="filter_type" style="margin-left: 5px;">';
		echo '<option value=""' . selected($filters['type'], '', false) . '>All Types</option>';
		echo '<option value="queue"' . selected($filters['type'], 'queue', false) . '>Queue</option>';
		echo '<option value="chat"' . selected($filters['type'], 'chat', false) . '>Chat</option>';
		echo '</select>';

		// Sort by
		echo '<select name="sortBy" id="sortBy" style="margin-left: 5px;">';
		echo '<option value="created_at"' . selected($filters['sortBy'], 'created_at', false) . '>Created Date</option>';
		echo '<option value="updated_at"' . selected($filters['sortBy'], 'updated_at', false) . '>Updated Date</option>';
		echo '<option value="id"' . selected($filters['sortBy'], 'id', false) . '>ID</option>';
		echo '<option value="post_title"' . selected($filters['sortBy'], 'post_title', false) . '>Title</option>';
		echo '</select>';

		// Sort order
		echo '<select name="sortOrder" id="sortOrder" style="margin-left: 5px;">';
		echo '<option value="DESC"' . selected($filters['sortOrder'], 'DESC', false) . '>Descending</option>';
		echo '<option value="ASC"' . selected($filters['sortOrder'], 'ASC', false) . '>Ascending</option>';
		echo '</select>';

		echo '<button type="button" id="apply-filters" class="button" style="margin-left: 5px;">Apply Filters</button>';
		echo '<button type="button" id="reset-filters" class="button" style="margin-left: 5px;">Reset</button>';

		echo '</div>'; // .alignleft

		// Right side: Pagination
		if ($total_pages > 1) {
			echo '<div class="tablenav-pages">';
			echo '<span class="displaying-num">' . sprintf(_n('%s item', '%s items', $total_items), number_format_i18n($total_items)) . '</span>';
			echo '<span class="pagination-links">';

			// First page
			if ($current_page > 1) {
				$first_url = add_query_arg(array_merge($filter_params, ['paged' => 1]), $base_url);
				$prev_url  = add_query_arg(array_merge($filter_params, ['paged' => max(1, $current_page - 1)]), $base_url);
				echo '<a class="first-page button" href="' . esc_url($first_url) . '"><span aria-hidden="true">&laquo;</span></a> ';
				echo '<a class="prev-page button" href="' . esc_url($prev_url) . '"><span aria-hidden="true">&lsaquo;</span></a> ';
			} else {
				echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span> ';
				echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span> ';
			}

			// Current page indicator
			echo '<span class="paging-input">';
			echo '<span class="tablenav-paging-text">' . sprintf(_x('%1$s of %2$s', 'paging'), number_format_i18n($current_page), number_format_i18n($total_pages)) . '</span>';
			echo '</span> ';

			// Next/Last page
			if ($current_page < $total_pages) {
				$next_url = add_query_arg(array_merge($filter_params, ['paged' => min($total_pages, $current_page + 1)]), $base_url);
				$last_url = add_query_arg(array_merge($filter_params, ['paged' => $total_pages]), $base_url);
				echo '<a class="next-page button" href="' . esc_url($next_url) . '"><span aria-hidden="true">&rsaquo;</span></a> ';
				echo '<a class="last-page button" href="' . esc_url($last_url) . '"><span aria-hidden="true">&raquo;</span></a>';
			} else {
				echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span> ';
				echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>';
			}

			echo '</span>'; // .pagination-links
			echo '</div>'; // .tablenav-pages
		}

		echo '</div>'; // .tablenav

		// Add JavaScript to handle filter changes
?>
		<script>
			jQuery(document).ready(function($) {
				$('#apply-filters').on('click', function() {
					var url = '<?php echo esc_js($base_url); ?>';
					var params = [];

					var status = $('#filter_status').val();
					if (status) {
						params.push('filter_status=' + encodeURIComponent(status));
					}

					var type = $('#filter_type').val();
					if (type) {
						params.push('filter_type=' + encodeURIComponent(type));
					}

					var sortBy = $('#sortBy').val();
					if (sortBy) {
						params.push('sortBy=' + encodeURIComponent(sortBy));
					}

					var sortOrder = $('#sortOrder').val();
					if (sortOrder) {
						params.push('sortOrder=' + encodeURIComponent(sortOrder));
					}

					if (params.length > 0) {
						url += '&' + params.join('&');
					}

					window.location.href = url;
				});

				$('#reset-filters').on('click', function() {
					window.location.href = '<?php echo esc_js($base_url); ?>';
				});

				// Allow Enter key to apply filters
				$('#filter_status, #filter_type, #sortBy, #sortOrder').on('keypress', function(e) {
					if (e.which === 13) {
						$('#apply-filters').click();
					}
				});
			});
		</script>
	<?php
	}

	/**
	 * Render pagination controls
	 */
	private function renderPagination(int $current_page, int $total_pages, int $total_items): void
	{
		if ($total_pages <= 1) {
			return; // No pagination needed
		}

		$base_url = admin_url('admin.php?page=vm-ai-feed-articles');

		// Preserve filter parameters in pagination URLs
		$filter_params = [];
		if (! empty($_GET['filter_status'])) {
			$filter_params['filter_status'] = sanitize_text_field($_GET['filter_status']);
		}
		if (! empty($_GET['filter_type'])) {
			$filter_params['filter_type'] = sanitize_text_field($_GET['filter_type']);
		}
		if (! empty($_GET['sortBy'])) {
			$filter_params['sortBy'] = sanitize_text_field($_GET['sortBy']);
		}
		if (! empty($_GET['sortOrder'])) {
			$filter_params['sortOrder'] = sanitize_text_field($_GET['sortOrder']);
		}

		echo '<div class="tablenav bottom">';
		echo '<div class="tablenav-pages">';
		echo '<span class="displaying-num">' . sprintf(_n('%s item', '%s items', $total_items), number_format_i18n($total_items)) . '</span>';
		echo '<span class="pagination-links">';

		// First page
		if ($current_page > 1) {
			$first_url = add_query_arg(array_merge($filter_params, ['paged' => 1]), $base_url);
			$prev_url  = add_query_arg(array_merge($filter_params, ['paged' => max(1, $current_page - 1)]), $base_url);
			echo '<a class="first-page button" href="' . esc_url($first_url) . '"><span aria-hidden="true">&laquo;</span></a> ';
			echo '<a class="prev-page button" href="' . esc_url($prev_url) . '"><span aria-hidden="true">&lsaquo;</span></a> ';
		} else {
			echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span> ';
			echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span> ';
		}

		// Current page indicator
		echo '<span class="paging-input">';
		echo '<span class="tablenav-paging-text">' . sprintf(_x('%1$s of %2$s', 'paging'), number_format_i18n($current_page), number_format_i18n($total_pages)) . '</span>';
		echo '</span> ';

		// Next/Last page
		if ($current_page < $total_pages) {
			$next_url = add_query_arg(array_merge($filter_params, ['paged' => min($total_pages, $current_page + 1)]), $base_url);
			$last_url = add_query_arg(array_merge($filter_params, ['paged' => $total_pages]), $base_url);
			echo '<a class="next-page button" href="' . esc_url($next_url) . '"><span aria-hidden="true">&rsaquo;</span></a> ';
			echo '<a class="last-page button" href="' . esc_url($last_url) . '"><span aria-hidden="true">&raquo;</span></a>';
		} else {
			echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span> ';
			echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>';
		}

		echo '</span>'; // .pagination-links
		echo '</div>'; // .tablenav-pages
		echo '</div>'; // .tablenav
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
	 * Format status display text for research items
	 */
	private function formatResearchStatusDisplay(string $status): string
	{
		// Map research_complete to Completed
		if (strtolower($status) === 'research_complete') {
			return 'Research Complete';
		}

		// Map other statuses
		$status_map = [
			'pending'       => 'Research Pending',
			'init_research' => 'Researching',
			'running'       => 'Research in Progress',
			'processing'    => 'Research in Progress',
			'completed'     => 'Research Complete',
			'failed'        => 'Research Failed',
			'error'         => 'Research Error',
			'cancelled'     => 'Research Cancelled',
		];

		$status_lower = strtolower($status);
		return $status_map[$status_lower] ?? ucfirst($status);
	}

	/**
	 * Check if article status allows link visibility
	 * Link should only be visible for statuses that indicate article generation is complete:
	 * only 'draft', 'published', and 'archived' are considered complete; incomplete or error states are excluded.
	 *
	 * @param string $status Article status
	 * @return bool True if link should be visible, false otherwise
	 */
	private function isStatusLinkVisible(string $status): bool
	{
		$status_lower     = strtolower($status);
		$visible_statuses = ['draft', 'published', 'archived'];
		return in_array($status_lower, $visible_statuses, true);
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
	 * Calculate duration between start and end time
	 */
	private function calculateDuration(?string $start_time, ?string $end_time): string
	{
		return AIDateHelpers::calculateDuration($start_time, $end_time);
	}

	/**
	 * Format date in human-readable format
	 */
	private function formatDate(?string $date_string): string
	{
		return AIDateHelpers::formatDate($date_string);
	}

	/**
	 * Format progress percentage with visual bar
	 * Only shows progress for queue-type articles
	 */
	private function formatProgress(int $progress, string $type = 'queue'): string
	{
		// Only show progress for queue-type articles
		if ($type !== 'queue') {
			return '<span style="color: #666; font-size: 12px;">—</span>';
		}

		// Show N/A for sentinel value -1 (indicates null or empty from database)
		if ($progress === -1) {
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
	 * Render delete article JavaScript handler
	 */
	private function renderDeleteArticleScript(): void
	{
	?>
		<script>
			jQuery(document).ready(function($) {
				// Handle delete article button clicks
				$('.delete-article').on('click', function(e) {
					e.preventDefault();

					var button = $(this);
					var articleId = button.data('article-id');
					var articleTitle = button.data('article-title');

					if (!articleId) {
						alert('No article ID found');
						return;
					}

					// Confirm deletion
					if (!confirm('Are you sure you want to delete the article "' + articleTitle + '"?\n\nThis action cannot be undone.')) {
						return;
					}

					// Disable button and show loading
					button.prop('disabled', true).text('Deleting...');

					// Get nonce (check multiple possible nonce fields)
					var nonce = $('#create_article_nonce').val() || $('#generate_article_nonce').val();

					// Call the delete article endpoint
					$.ajax({
						url: ajaxurl,
						type: 'POST',
						data: {
							action: 'delete_article',
							article_id: articleId,
							nonce: nonce
						},
						success: function(response) {
							if (response.success) {
								// Show success message and reload page
								alert('Article deleted successfully!');
								window.location.reload();
							} else {
								alert('Error: ' + (response.data || 'Failed to delete article'));
								button.prop('disabled', false).text('Delete');
							}
						},
						error: function(xhr, status, error) {
							var errorMsg = 'An error occurred while deleting the article.';
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
									'<div class="notice notice-success"><p>Article submitted successfully! Refreshing articles...</p></div>'
								).show();
								$('#article_url').val('');
								$('#article_context').val('');

								// Wait briefly, then refresh the current page
								setTimeout(function() {
									window.location.reload();
								}, 2000);
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

	/**
	 * Render JavaScript handlers for research items (restart, delete)
	 */
	private function renderResearchItemScripts(): void
	{
	?>
		<script>
			jQuery(document).ready(function($) {
				// Handle restart research button clicks
				$('.restart-research').on('click', function(e) {
					e.preventDefault();

					var button = $(this);
					var researchInstanceId = button.data('workflow-instance-id');
					var originalRequestId = button.data('original-request-id');

					if (!researchInstanceId) {
						alert('No research instance ID found.');
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
							if (response.success) {
								alert('Research restarted successfully!');
								window.location.reload();
							} else {
								alert('Error: ' + (response.data || 'Failed to restart research'));
								button.prop('disabled', false).text('Restart');
							}
						},
						error: function(xhr, status, error) {
							var errorMsg = 'An error occurred while restarting the research.';
							if (xhr.responseJSON && xhr.responseJSON.data) {
								errorMsg = xhr.responseJSON.data;
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

					// Confirm deletion with cascade warning
					// NOTE: If you change the cascade deletion logic in the backend (see the delete_research action handler), update this message accordingly.
					if (!confirm('Are you sure you want to delete this research request?\n\nThis will also delete:\n- All associated articles\n- All article revisions\n- All research memories\n\nThis action cannot be undone.')) {
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
								var message = response.data && response.data.message ? response.data.message : 'Research request deleted successfully!';
								alert(message);
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
}
