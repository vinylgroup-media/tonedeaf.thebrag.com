<?php

/**
 * Social Media Embed Processing
 *
 * Extracts and renders social media embeds from custom tags in markdown.
 *
 * @package VM\AIFeed
 * @since 1.0.0
 */

namespace VM\AIFeed\Content;

/**
 * Social media embed extractor and renderer
 *
 * Converts custom <embed> tags into embeds for:
 * - YouTube videos (using WordPress native oEmbed)
 * - Twitter/X posts (using WordPress native oEmbed)
 * - Instagram posts (using HTML iframe - oEmbed has API restrictions)
 * - Facebook posts (using HTML iframe - oEmbed has API restrictions)
 *
 * Leverages WordPress native oEmbed where it works well (YouTube, Twitter),
 * and falls back to HTML iframe embeds for Meta platforms where oEmbed
 * has limitations or requires authentication.
 *
 * @since 1.0.0
 */
class AIEmbeds
{

	/**
	 * Extract custom <embed type="..." src="..."> tags, replace with tokens
	 * and return [markdownWithoutEmbeds, tokenMap].
	 */
	public static function extract(string $markdown): array
	{
		$embeds = array();
		$i      = 0;
		$out    = preg_replace_callback(
			'/<embed\s+[^>]*?>/i',
			function ($m) use (&$embeds, &$i) {
				$tag  = $m[0];
				$type = null;
				$src  = null;
				if (preg_match('/type="(youtube|twitter|instagram|facebook)"/i', $tag, $typeMatch)) {
					$type = strtolower($typeMatch[1]);
				}
				if (preg_match('/src="([^"]+)"/i', $tag, $srcMatch)) {
					$src = trim($srcMatch[1]);
				}
				if ($type && $src) {
					$token            = '[[EMBED_' . (++$i) . ']]';
					$embeds[$token] = array(
						'type' => $type,
						'url'  => $src,
					);
					return $token;
				}
				// If not matched, return the original tag unchanged
				return $tag;
			},
			$markdown
		);

		return array($out, $embeds);
	}

	/** Inject rendered embeds back into the HTML using the token map. */
	public static function inject(string $html, array $embeds): string
	{
		foreach ($embeds as $token => $e) {
			$embedHtml = self::render($e['type'] ?? '', $e['url'] ?? '');
			$html      = str_replace($token, $embedHtml, $html);
		}
		return $html;
	}

	/**
	 * Inject embeds as WordPress blocks using the token map.
	 *
	 * @param string $content Block content with embed tokens
	 * @param array  $embeds  Map of tokens to embed data
	 * @return string Content with embeds replaced by WordPress block markup
	 */
	public static function injectIntoBlocks(string $content, array $embeds): string
	{
		foreach ($embeds as $token => $e) {
			$blockHtml = \VM\AIFeed\Content\AIBlockConverter::createEmbedBlock($e['type'] ?? '', $e['url'] ?? '');
			// Remove token from paragraphs and replace with standalone block
			$content = preg_replace(
				'/<!-- wp:paragraph -->\s*<p>\s*' . preg_quote($token, '/') . '\s*<\/p>\s*<!-- \/wp:paragraph -->/',
				$blockHtml,
				$content
			);
			// Fallback: replace token directly if not in paragraph
			$content = str_replace($token, "\n\n" . $blockHtml . "\n\n", $content);
		}
		return $content;
	}

	/**
	 * Render provider-specific embed markup.
	 *
	 * Uses WordPress native oEmbed for YouTube and Twitter (X) where it works well.
	 * Falls back to HTML iframe embeds for Meta platforms (Instagram, Facebook)
	 * where oEmbed API has restrictions or doesn't work reliably.
	 */
	private static function render(string $type, string $url): string
	{
		switch ($type) {
			case 'youtube':
				// Use WordPress native oEmbed for YouTube
				$oembed_result = self::tryWordPressOEmbed($url);
				if ($oembed_result) {
					return $oembed_result;
				}
				// Fallback to custom implementation if oEmbed fails
				$id = self::youtubeId($url);
				if (! $id) {
					return self::linkFallback($url);
				}
				$src = 'https://www.youtube.com/embed/' . rawurlencode($id);
				return self::iframe($src, 560, 315, 'Embedded content from YouTube', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share');

			case 'twitter':
				// Use WordPress native oEmbed for Twitter
				$oembed_result = self::tryWordPressOEmbed($url);
				if ($oembed_result) {
					return $oembed_result;
				}
				// Fallback to scriptless proxy if oEmbed fails
				$src = 'https://tf.rita.moe/show?url=' . rawurlencode($url);
				return self::iframe($src, 550, 700, 'Embedded content from Twitter');

			case 'instagram':
				// Meta platforms: Use HTML iframe embed (oEmbed has API restrictions)
				$src = rtrim($url, '/') . '/embed';
				return self::iframe($src, 540, 700, 'Embedded content from Instagram');

			case 'facebook':
				// Meta platforms: Use HTML iframe embed (oEmbed has API restrictions)
				$src = 'https://www.facebook.com/plugins/post.php?href=' . rawurlencode($url) . '&show_text=true&width=552';
				return self::iframe($src, 552, 700, 'Embedded content from Facebook');

			default:
				return self::linkFallback($url);
		}
	}

	/**
	 * Try to use WordPress native oEmbed to render the URL.
	 * Returns the embed HTML on success, or null on failure.
	 *
	 * @param string $url The URL to embed
	 * @return string|null The embed HTML or null if oEmbed fails
	 */
	private static function tryWordPressOEmbed(string $url): ?string
	{
		// WordPress oEmbed handler
		$oembed_result = wp_oembed_get($url);

		if ($oembed_result && ! is_wp_error($oembed_result)) {
			// Sanitize the oEmbed result for security
			$oembed_result = wp_kses($oembed_result, self::allowedHtml());
			// Wrap in vm-embed div for consistent styling
			return '<div class="vm-embed">' . $oembed_result . '</div>';
		}

		return null;
	}

	private static function iframe(string $src, int $w, int $h, string $title, string $allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture'): string
	{
		$srcAttr   = esc_url($src);
		$allowAttr = esc_attr($allow);
		$titleAttr = esc_attr($title);
		return '<div class="vm-embed"><iframe src="' . $srcAttr . '" width="' . (int) $w . '" height="' . (int) $h . '" title="' . $titleAttr . '" style="border:0;" loading="lazy" allow="' . $allowAttr . '" allowfullscreen referrerpolicy="no-referrer-when-downgrade"></iframe></div>';
	}

	private static function linkFallback(string $url): string
	{
		$u = esc_url($url);
		return '<p><a href="' . $u . '" target="_blank" rel="nofollow noopener noreferrer">' . $u . '</a></p>';
	}

	/** Parse common YouTube URL patterns and return the video ID if found. */
	private static function youtubeId(string $url): ?string
	{
		// youtu.be/<id>
		if (preg_match('~https?://(?:www\.)?youtu\.be/([a-zA-Z0-9_-]{11})~', $url, $m)) {
			return $m[1];
		}
		// youtube.com/watch?v=<id>
		if (preg_match('~[?&]v=([a-zA-Z0-9_-]{11})~', $url, $m)) {
			return $m[1];
		}
		// youtube.com/embed/<id>
		if (preg_match('~https?://(?:www\.)?youtube\.com/embed/([a-zA-Z0-9_-]{11})~', $url, $m)) {
			return $m[1];
		}
		return null;
	}

	/** Allowed tags/attributes for wp_kses after injection. */
	public static function allowedHtml(): array
	{
		$allowed           = wp_kses_allowed_html('post');
		$allowed['iframe'] = array(
			'src'             => true,
			'width'           => true,
			'height'          => true,
			'style'           => true,
			'loading'         => true,
			'allow'           => true,
			'allowfullscreen' => true,
			'referrerpolicy'  => true,
			'frameborder'     => true,
			'title'           => true,
		);
		$allowed['div']    = isset($allowed['div']) ? array_merge(
			$allowed['div'],
			array(
				'class' => true,
				'style' => true,
			)
		) : array(
			'class' => true,
			'style' => true,
		);
		return $allowed;
	}
}
