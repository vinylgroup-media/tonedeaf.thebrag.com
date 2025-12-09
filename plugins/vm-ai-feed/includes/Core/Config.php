<?php

/**
 * AI Feed Configuration Management
 *
 * Handles API configuration including environment switching, URL management,
 * and API key storage using WordPress options.
 *
 * @package VM\AIFeed
 * @since 1.0.0
 */

namespace VM\AIFeed\Core;

class AIFeedConfig
{

	/**
	 * Singleton instance
	 *
	 * @var AIFeedConfig|null
	 */
	private static ?AIFeedConfig $_instance = null;

	/**
	 * Configuration array with default values
	 *
	 * @var array{
	 *     api_urls: array{local: string, production: string},
	 *     current_environment: string,
	 *     api_key: string,
	 *     editor_type: string
	 * }
	 */
	private array $config = array(
		'api_urls'            => array(
			'local'      => 'https://vinyl-media.vinyl-media.workers.dev',
			'production' => 'https://vinyl-media.vinyl-media.workers.dev',
		),
		'current_environment' => 'local',
		'api_key'             => '',
		'editor_type'         => 'classic',
	);

	/**
	 * Get singleton instance
	 *
	 * @since 1.0.0
	 * @return self Singleton instance
	 */
	public static function getInstance(): self
	{
		if (! isset(self::$_instance) || ! (self::$_instance instanceof self)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Private constructor to enforce singleton pattern
	 *
	 * Loads configuration from WordPress options on instantiation.
	 *
	 * @since 1.0.0
	 */
	private function __construct()
	{
		// Allow WordPress options to override default config
		$this->loadFromWordPressOptions();
	}

	/**
	 * Load configuration from WordPress options
	 *
	 * Merges saved WordPress options with default configuration values.
	 * Validates and sanitizes all loaded values for security.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function loadFromWordPressOptions(): void
	{
		$saved_config = get_option('vm_ai_feed_config', array());

		if (! empty($saved_config)) {
			// Validate structure and sanitize values
			if (isset($saved_config['api_key'])) {
				$saved_config['api_key'] = sanitize_text_field($saved_config['api_key']);
			}
			if (isset($saved_config['current_environment'])) {
				$saved_config['current_environment'] = in_array(
					$saved_config['current_environment'],
					array('local', 'production')
				)
					? $saved_config['current_environment']
					: 'local';
			}
			if (isset($saved_config['editor_type'])) {
				$saved_config['editor_type'] = in_array(
					$saved_config['editor_type'],
					array('classic', 'blocks')
				)
					? $saved_config['editor_type']
					: 'classic';
			}
			// Only merge known keys to prevent injection
			$allowed_keys = array('api_urls', 'current_environment', 'api_key', 'editor_type');
			$saved_config = array_intersect_key($saved_config, array_flip($allowed_keys));

			$this->config = array_merge($this->config, $saved_config);
		}
	}

	/**
	 * Get the current API base URL based on environment
	 *
	 * @since 1.0.0
	 * @return string Base URL for current environment
	 */
	public function getApiBaseUrl(): string
	{
		$environment = $this->getCurrentEnvironment();
		return $this->config['api_urls'][$environment] ?? $this->config['api_urls']['local'];
	}

	/**
	 * Get full API URL for a specific endpoint
	 *
	 * Automatically prepends 'v1/' to endpoints if not already present.
	 *
	 * @since 1.0.0
	 * @param string $endpoint Optional endpoint path (without leading slash)
	 * @return string Complete API URL with endpoint
	 */
	public function getApiUrl(string $endpoint = ''): string
	{
		$base_url = $this->getApiBaseUrl();
		$endpoint = ltrim($endpoint, '/');

		// Always prepend v1/ to the endpoint if it's not already there
		if ($endpoint && ! str_starts_with($endpoint, 'v1/')) {
			$endpoint = 'v1/' . $endpoint;
		}

		return $endpoint ? $base_url . '/' . $endpoint : $base_url;
	}

	/**
	 * Get current environment setting
	 *
	 * @since 1.0.0
	 * @return string Current environment ('local' or 'production')
	 */
	public function getCurrentEnvironment(): string
	{
		return $this->config['current_environment'];
	}

	/**
	 * Set current environment
	 *
	 * Validates environment value and persists to WordPress options.
	 *
	 * @since 1.0.0
	 * @param string $environment Environment name ('local' or 'production')
	 * @return void
	 */
	public function setCurrentEnvironment(string $environment): void
	{
		if (in_array($environment, array('local', 'production'))) {
			$this->config['current_environment'] = $environment;
			$this->saveToWordPressOptions();
		}
	}

	/**
	 * Get API key
	 *
	 * @since 1.0.0
	 * @return string API key or empty string if not set
	 */
	public function getApiKey(): string
	{
		return $this->config['api_key'] ?? '';
	}

	/**
	 * Set API key
	 *
	 * @since 1.0.0
	 * @param string $api_key API authentication key
	 * @return void
	 */
	public function setApiKey(string $api_key): void
	{
		$this->config['api_key'] = $api_key;
		$this->saveToWordPressOptions();
	}

	/**
	 * Get all configuration
	 *
	 * @since 1.0.0
	 * @return array Complete configuration array
	 */
	public function getAllConfig(): array
	{
		return $this->config;
	}

	/**
	 * Update configuration
	 *
	 * @since 1.0.0
	 * @param array $new_config New configuration values to merge
	 * @return void
	 */
	public function updateConfig(array $new_config): void
	{
		$this->config = array_merge($this->config, $new_config);
		$this->saveToWordPressOptions();
	}

	/**
	 * Save configuration to WordPress options
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function saveToWordPressOptions(): void
	{
		update_option('vm_ai_feed_config', $this->config);
	}

	/**
	 * Check if we're in local environment
	 *
	 * @since 1.0.0
	 * @return bool True if local environment
	 */
	public function isLocal(): bool
	{
		return $this->getCurrentEnvironment() === 'local';
	}

	/**
	 * Check if we're in production environment
	 *
	 * @since 1.0.0
	 * @return bool True if production environment
	 */
	public function isProduction(): bool
	{
		return $this->getCurrentEnvironment() === 'production';
	}

	/**
	 * Get environment display name
	 *
	 * @since 1.0.0
	 * @return string Human-readable environment name
	 */
	public function getEnvironmentDisplayName(): string
	{
		return $this->getCurrentEnvironment() === 'local' ? 'Local Development' : 'Production';
	}

	/**
	 * Get editor type setting
	 *
	 * @since 1.0.0
	 * @return string Editor type ('classic' or 'blocks')
	 */
	public function getEditorType(): string
	{
		return $this->config['editor_type'] ?? 'classic';
	}

	/**
	 * Set editor type
	 *
	 * Validates editor type value and persists to WordPress options.
	 *
	 * @since 1.0.0
	 * @param string $editor_type Editor type ('classic' or 'blocks')
	 * @return void
	 */
	public function setEditorType(string $editor_type): void
	{
		if (in_array($editor_type, array('classic', 'blocks'))) {
			$this->config['editor_type'] = $editor_type;
			$this->saveToWordPressOptions();
		}
	}

	/**
	 * Check if using classic editor
	 *
	 * @since 1.0.0
	 * @return bool True if classic editor
	 */
	public function isClassicEditor(): bool
	{
		return $this->getEditorType() === 'classic';
	}

	/**
	 * Check if using block editor
	 *
	 * @since 1.0.0
	 * @return bool True if block editor
	 */
	public function isBlockEditor(): bool
	{
		return $this->getEditorType() === 'blocks';
	}

	/**
	 * Get WordPress user ID from AI config
	 *
	 * @param string $ai_config_name The AI config name to look up
	 * @return int WordPress user ID, falls back to current user if not found or on error
	 */
	public function getWordPressUserIdFromAIConfig(string $ai_config_name): int
	{
		$api_url = $this->getApiUrl('api/ai-configs/by-name/' . $ai_config_name);

		$response = wp_remote_get(
			$api_url,
			array(
				'timeout' => 30,
			)
		);

		if (is_wp_error($response)) {
			// Log the error
			error_log(
				sprintf(
					'VM AI Feed: Failed to fetch AI config "%s" - %s',
					$ai_config_name,
					$response->get_error_message()
				)
			);

			// Fallback to current user if API fails
			return get_current_user_id();
		}

		$response_code = wp_remote_retrieve_response_code($response);
		$body          = wp_remote_retrieve_body($response);
		$data          = json_decode($body, true);

		if ($response_code === 200 && isset($data['data']['wordpress_user_id'])) {
			$user_id = \intval($data['data']['wordpress_user_id']);

			// Validate positive integer
			if ($user_id > 0) {
				return $user_id;
			}

			// Log invalid user ID
			error_log(
				sprintf(
					'VM AI Feed: Invalid wordpress_user_id (%s) returned for AI config "%s"',
					$user_id,
					$ai_config_name
				)
			);
		}

		// Fallback to current user if config not found or invalid
		return get_current_user_id();
	}
}
