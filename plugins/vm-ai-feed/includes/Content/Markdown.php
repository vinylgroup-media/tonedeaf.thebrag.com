<?php

/**
 * Markdown Processing
 *
 * Converts Markdown to HTML using league/commonmark with security features.
 *
 * @package VM\AIFeed
 * @since 1.0.0
 */

namespace VM\AIFeed\Content;

use League\CommonMark\Environment\Environment;
use League\CommonMark\MarkdownConverter;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;

/**
 * Markdown to HTML converter with embed support
 *
 * Provides secure Markdown conversion that:
 * - Strips raw HTML for security
 * - Adds target="_blank" to all links
 * - Integrates with embed extraction/injection
 * - Falls back gracefully when library unavailable
 *
 * @since 1.0.0
 */
class AIMarkdown
{

	private static ?MarkdownConverter $converter = null;

	/**
	 * Convert Markdown to HTML using league/commonmark v2, stripping raw HTML.
	 * Falls back to basic formatting if the library is unavailable.
	 */
	public static function convert(string $markdown): string
	{
		// Graceful fallback when dependency isn't installed
		if (! class_exists(MarkdownConverter::class)) {
			$escaped = esc_html($markdown);
			// basic paragraphs for readability
			$escaped = preg_replace("/\n{2,}/", "\n\n", $escaped);
			$parts   = array_map('trim', preg_split("/\n\n/", $escaped));
			$html    = '';
			foreach ($parts as $p) {
				if ($p === '') {
					continue;
				}
				$html .= '<p>' . nl2br($p) . '</p>';
			}
			return $html;
		}

		if (! self::$converter) {
			$config = array(
				'html_input'         => 'strip',
				'allow_unsafe_links' => false,
			);
			$env    = new Environment($config);
			// Register the CommonMark core extension to provide renderers
			$env->addExtension(new CommonMarkCoreExtension());
			self::$converter = new MarkdownConverter($env);
		}

		$html = (string) self::$converter->convert($markdown);
		return self::ensureAnchorTargets($html);
	}

	/** Ensure all anchor links open in a new tab. */
	private static function ensureAnchorTargets(string $html): string
	{
		// Add target="_blank" to any <a> without an explicit target
		return preg_replace_callback(
			'/<a\b([^>]*)>/i',
			function ($m) {
				$attrs = $m[1] ?? '';
				if (stripos($attrs, 'target=') !== false) {
					return '<a' . $attrs . '>';
				}
				// insert target before closing
				$attrsTrimmed = rtrim($attrs);
				if ($attrsTrimmed === '') {
					return '<a target="_blank" rel="noopener noreferrer">';
				}
				return '<a' . $attrsTrimmed . ' target="_blank" rel="noopener noreferrer">';
			},
			$html
		);
	}

	/**
	 * Escape Markdown special characters to prevent injection attacks.
	 * This is useful when concatenating user-supplied text into Markdown.
	 *
	 * @param string $text The text to escape
	 * @return string The escaped text safe for Markdown concatenation
	 */
	public static function escapeMarkdown(string $text): string
	{
		// Escape Markdown special characters that could be exploited
		// Note: Backslash must be escaped first to avoid double-escaping
		$replacements = array(
			'\\' => '\\\\',
			'`'  => '\\`',
			'*'  => '\\*',
			'_'  => '\\_',
			'{'  => '\\{',
			'}'  => '\\}',
			'['  => '\\[',
			']'  => '\\]',
			'('  => '\\(',
			')'  => '\\)',
			'#'  => '\\#',
			'+'  => '\\+',
			'-'  => '\\-',
			'.'  => '\\.',
			'!'  => '\\!',
			'|'  => '\\|',
			'<'  => '\\<',
			'>'  => '\\>',
		);

		return strtr($text, $replacements);
	}

	/**
	 * Extract embeds, convert markdown to HTML, and inject embeds back.
	 * This is a convenience method that combines extract, convert, and inject.
	 *
	 * Embeds are processed using WordPress native oEmbed for YouTube and Twitter,
	 * and HTML iframe embeds for Meta platforms (Instagram, Facebook) where
	 * oEmbed has restrictions.
	 *
	 * @param string $raw Markdown content that may contain embed tags
	 * @return string HTML with processed markdown and rendered embeds
	 */
	public static function renderWithEmbeds(string $raw): string
	{
		list($md, $embeds) = AIEmbeds::extract($raw);
		$html              = self::convert($md);
		$html              = AIEmbeds::inject($html, $embeds);
		return wp_kses($html, AIEmbeds::allowedHtml());
	}
}
