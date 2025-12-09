<?php

namespace VM\AIFeed\Helpers;

use VM\AIFeed\Core\AIFeedConfig;

/**
 * UI Helper functions for the AI Feed plugin
 */
class AIFeedUIHelpers
{

	/**
	 * Get the color for a progress bar based on the progress percentage
	 *
	 * @param int $progress The progress percentage (0-100)
	 * @return string The hex color code
	 */
	public static function getProgressBarColor(int $progress): string
	{
		return $progress >= 100 ? '#28a745' : '#007cba';
	}

	/**
	 * Convert category data (JSON array, array, or integer) to human-readable category names
	 *
	 * @param mixed $category_data Category data in various formats (JSON string, array, or integer)
	 * @return string Comma-separated category names or 'Uncategorized'
	 */
	public static function getCategoryNames($category_data): string
	{
		if (empty($category_data) || $category_data === '[]') {
			return 'Uncategorized';
		}

		// Handle JSON array string like "[7, 2]"
		if (is_string($category_data)) {
			$decoded = json_decode($category_data, true);

			if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && ! empty($decoded)) {
				$category_ids = $decoded;
			} else {
				// Try as single integer
				$cat_int = (int) $category_data;
				if ($cat_int > 0) {
					$category_ids = array($cat_int);
				} else {
					return 'Uncategorized';
				}
			}
		} elseif (is_array($category_data)) {
			$category_ids = $category_data;
		} else {
			$cat_int = (int) $category_data;
			if ($cat_int > 0) {
				$category_ids = array($cat_int);
			} else {
				return 'Uncategorized';
			}
		}

		$category_names = array();
		foreach ($category_ids as $cat_id) {
			$category = get_category($cat_id);
			if ($category && ! is_wp_error($category)) {
				$category_names[] = $category->name;
			}
		}

		return ! empty($category_names) ? implode(', ', $category_names) : 'Uncategorized';
	}

	/**
	 * Get author display name from WordPress user ID
	 *
	 * @param int $author_id WordPress user ID
	 * @return string Author display name or 'Unknown' if not found
	 */
	public static function getAuthorName(int $author_id): string
	{
		if ($author_id <= 0) {
			return 'Unknown';
		}

		$author = get_userdata($author_id);
		return $author ? $author->display_name : 'Unknown';
	}

	/**
	 * Render AI config dropdown for selecting writer profiles
	 *
	 * @param string $selected_config Currently selected config name
	 * @return void Echoes HTML dropdown
	 */
	public static function renderAIConfigDropdown(string $selected_config = 'default'): void
	{
		$config  = AIFeedConfig::getInstance();
		$api_url = $config->getApiUrl('api/ai-configs');
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

		$configs = array();
		if (! is_wp_error($response)) {
			$body = wp_remote_retrieve_body($response);
			$data = json_decode($body, true);
			if (isset($data['data']) && is_array($data['data'])) {
				$configs = $data['data'];
			}
		}

		echo '<select name="ai_config_name" id="ai_config_name" required>';
		foreach ($configs as $cfg) {
			$name          = esc_attr($cfg['name'] ?? '');
			$label         = esc_html($cfg['label'] ?? $name);
			$selected_attr = ($name === $selected_config) ? ' selected' : '';
			echo '<option value="' . $name . '"' . $selected_attr . '>' . $label . '</option>';
		}
		echo '</select>';
	}
}
