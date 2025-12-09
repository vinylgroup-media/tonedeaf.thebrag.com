<?php

namespace VM\AIFeed\Helpers;

/**
 * Date and time utility helpers for AI Feed plugin
 *
 * @package VM\AIFeed
 * @since 1.0.0
 */
class AIDateHelpers
{

	/**
	 * Format a date string in a human-readable format
	 *
	 * @param string|null $date_string ISO 8601 date string
	 * @return string Formatted date or 'N/A' if invalid
	 */
	public static function formatDate(?string $date_string): string
	{
		if (empty($date_string)) {
			return 'N/A';
		}

		$date = \DateTime::createFromFormat('Y-m-d\TH:i:s.u\Z', $date_string);
		if (! $date) {
			$date = \DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $date_string);
		}
		if (! $date) {
			$date = \DateTime::createFromFormat(\DateTime::ISO8601, $date_string);
		}

		return $date ? $date->format('Y-m-d H:i:s') : 'N/A';
	}

	/**
	 * Calculate duration between two timestamps in human-readable format
	 *
	 * @param string|null $start_time Start timestamp (ISO 8601)
	 * @param string|null $end_time End timestamp (ISO 8601)
	 * @return string Duration string (e.g., "2m 30s") or 'N/A' if invalid
	 */
	public static function calculateDuration(?string $start_time, ?string $end_time): string
	{
		if (empty($start_time) || empty($end_time)) {
			return 'N/A';
		}

		$start = strtotime($start_time);
		$end   = strtotime($end_time);

		if (! $start || ! $end) {
			return 'N/A';
		}

		$duration = $end - $start;

		if ($duration < 60) {
			return $duration . 's';
		} elseif ($duration < 3600) {
			$minutes = floor($duration / 60);
			$seconds = $duration % 60;
			return $minutes . 'm ' . $seconds . 's';
		} else {
			$hours   = floor($duration / 3600);
			$minutes = floor(($duration % 3600) / 60);
			return $hours . 'h ' . $minutes . 'm';
		}
	}
}
