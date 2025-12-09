<?php

namespace VM\AIFeed\Helpers;

/**
 * Status badge utilities
 *
 * @package VM\AIFeed
 * @since 1.0.0
 */
class AIStatusHelpers
{

	/**
	 * Get CSS class for status badge based on status string
	 *
	 * @param string $status Status string (e.g., 'completed', 'running', 'draft')
	 * @return string CSS class name (e.g., 'status-published')
	 */
	public static function getStatusClass(string $status): string
	{
		$status_lower = strtolower($status);

		// Map various status strings to CSS classes
		$status_map = [
			'completed'         => 'status-published',
			'published'         => 'status-published',
			'success'           => 'status-published',
			'research_complete' => 'status-published',
			'running'           => 'status-review',
			'in_progress'       => 'status-review',
			'processing'        => 'status-review',
			'generating'        => 'status-review',
			'draft'             => 'status-draft',
			'pending'           => 'status-pending',
			'init_research'     => 'status-pending',
			'failed'            => 'status-pending',
			'error'             => 'status-pending',
			'terminated'        => 'status-pending',
			'cancelled'         => 'status-pending',
		];

		return $status_map[$status_lower] ?? 'status-default';
	}

	/**
	 * Format progress percentage into a styled progress bar
	 *
	 * @param int|float|null $progress Progress value (0-100) or null
	 * @return string HTML progress bar element or 'N/A'
	 */
	public static function formatProgress($progress): string
	{
		if ($progress === null || $progress === '') {
			return 'N/A';
		}

		$progress_int = (int) $progress;
		$progress_int = max(0, min(100, $progress_int)); // Clamp to 0-100

		$color = self::getProgressBarColor($progress_int);

		return sprintf(
			'<div style="width: 100%%; max-width: 200px; background-color: #f0f0f0; border-radius: 4px; overflow: hidden;">
                <div style="width: %d%%; background-color: %s; color: white; text-align: center; padding: 2px 0; border-radius: 4px; font-size: 11px; font-weight: 600;">
                    %d%%
                </div>
            </div>',
			$progress_int,
			$color,
			$progress_int
		);
	}

	/**
	 * Get appropriate color for progress bar based on percentage
	 *
	 * @param int $progress Progress value (0-100)
	 * @return string Hex color code
	 */
	public static function getProgressBarColor(int $progress): string
	{
		if ($progress < 33) {
			return '#dc3545'; // Red
		} elseif ($progress < 66) {
			return '#ffc107'; // Yellow/Orange
		} else {
			return '#28a745'; // Green
		}
	}
}
