<?php

/**
 * Category AI Flag Metadata
 *
 * Adds 'ai_enabled' custom field to WordPress categories and exposes via REST API.
 *
 * @package VM\AIFeed
 * @since 1.0.0
 */

namespace VM\AIFeed\Taxonomy;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Category metadata handler for AI enablement flag
 *
 * Extends WordPress categories with an 'ai_enabled' boolean field that:
 * - Appears in category add/edit forms
 * - Persists to term meta
 * - Exposes via REST API for external consumption
 *
 * @since 1.0.0
 */
class CategoryAIMeta
{

	/**
	 * Initialize the category AI metadata functionality.
	 *
	 * Registers hooks for admin UI fields, save handlers, and REST API exposure.
	 *
	 * @return void
	 */
	public static function init(): void
	{
		// Admin UI fields for Add/Edit Category.
		add_action('category_add_form_fields', [self::class, 'render_add_field']);
		add_action('category_edit_form_fields', [self::class, 'render_edit_field']);

		// Save handlers on create/update.
		add_action('created_category', [self::class, 'save_term_meta']);
		add_action('edited_category', [self::class, 'save_term_meta']);

		// Expose in REST API as top-level field.
		add_action('rest_api_init', [self::class, 'register_rest_field']);
	}

	/**
	 * Render AI enabled checkbox field on category add screen.
	 *
	 * @return void
	 */
	public static function render_add_field(): void
	{
		// Default for new categories is false (unchecked).
		echo '<div class="form-field">';
		echo '<label for="vm_ai_enabled">AI enabled</label>';
		echo '<input type="checkbox" name="vm_ai_enabled" id="vm_ai_enabled" value="1" />';
		wp_nonce_field('vm_ai_category_add', 'vm_ai_category_nonce');
		echo '<p class="description">Allow this category to be used for AI features.</p>';
		echo '</div>';
	}

	/**
	 * Render AI enabled checkbox field on category edit screen.
	 *
	 * @param \WP_Term $term The term being edited.
	 * @return void
	 */
	public static function render_edit_field(\WP_Term $term): void
	{
		$value   = get_term_meta($term->term_id, 'ai_enabled', true);
		$checked = ! empty($value) && '0' !== $value ? 'checked' : '';

		echo '<tr class="form-field">';
		echo '<th scope="row"><label for="vm_ai_enabled">AI enabled</label></th>';
		echo '<td>';
		echo '<label><input type="checkbox" name="vm_ai_enabled" id="vm_ai_enabled" value="1" ' . esc_attr($checked) . ' /> Enable AI usage for this category</label>';
		wp_nonce_field("vm_ai_category_edit_{$term->term_id}", 'vm_ai_category_nonce');
		echo '<p class="description">Defaults to disabled if not set.</p>';
		echo '</td>';
		echo '</tr>';
	}

	/**
	 * Save AI enabled meta field when category is created or updated.
	 *
	 * @param int $term_id The term ID being saved.
	 * @return void
	 */
	public static function save_term_meta(int $term_id): void
	{
		// Verify nonce.
		if (! isset($_POST['vm_ai_category_nonce'])) {
			return;
		}

		$nonce = sanitize_text_field(wp_unslash($_POST['vm_ai_category_nonce'] ?? ''));

		// Check for add or edit action.
		$is_valid_add  = wp_verify_nonce($nonce, 'vm_ai_category_add');
		$is_valid_edit = wp_verify_nonce($nonce, "vm_ai_category_edit_{$term_id}");

		if (! $is_valid_add && ! $is_valid_edit) {
			return;
		}

		if (! current_user_can('manage_categories')) {
			return;
		}

		$ai_enabled = isset($_POST['vm_ai_enabled']) && sanitize_text_field(wp_unslash($_POST['vm_ai_enabled'])) === '1' ? '1' : '0';
		update_term_meta($term_id, 'ai_enabled', $ai_enabled);
	}

	/**
	 * Register AI enabled field in REST API for category taxonomy.
	 *
	 * @return void
	 */
	public static function register_rest_field(): void
	{
		register_rest_field(
			'category',
			'ai_enabled',
			[
				'get_callback' => function ($term): bool {
					$id = \is_array($term) ? (int) ($term['id'] ?? 0) : (int) ($term->id ?? 0);
					if (! $id) {
						return false;
					}
					$value = get_term_meta($id, 'ai_enabled', true);
					return ! empty($value) && '0' !== $value;
				},
				'schema'       => [
					'description' => 'Whether the category is enabled for AI usage',
					'type'        => 'boolean',
					'context'     => ['view', 'embed'],
				],
			]
		);
	}
}
