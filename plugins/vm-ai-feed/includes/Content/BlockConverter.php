<?php

/**
 * WordPress Block Converter
 *
 * Converts Markdown content to WordPress blocks (Gutenberg) format.
 *
 * @package VM\AIFeed
 * @since 1.0.0
 */

namespace VM\AIFeed\Content;

// Import related classes for better IDE support
use VM\AIFeed\Content\AIEmbeds;

/**
 * Markdown to WordPress Blocks converter
 *
 * Provides conversion from Markdown to WordPress block editor (Gutenberg) format,
 * parsing common Markdown elements into their corresponding block representations.
 *
 * @since 1.0.0
 */
class AIBlockConverter
{

    /**
     * Convert Markdown content to WordPress blocks format
     *
     * This method processes Markdown and converts it to WordPress block markup.
     * It handles common elements like headings, paragraphs, lists, and embeds.
     *
     * @since 1.0.0
     * @param string $markdown The Markdown content to convert
     * @return string Block editor HTML markup
     */
    public static function convertToBlocks(string $markdown): string
    {
        // Extract embeds first
        [$md, $embeds] = AIEmbeds::extract($markdown);

        // Split into lines for processing
        $lines = explode("\n", $md);
        $blocks = [];
        $in_list = false;
        $list_ordered = false;
        $list_items = [];
        $current_paragraph = '';

        foreach ($lines as $line) {
            $trimmed = trim($line);

            // Empty line - end current paragraph or list
            if (empty($trimmed)) {
                if ($in_list) {
                    $blocks[] = self::createListBlock($list_items, $list_ordered);
                    $list_items = [];
                    $in_list = false;
                    $list_ordered = false;
                }
                if (! empty($current_paragraph)) {
                    $blocks[] = self::createParagraphBlock($current_paragraph);
                    $current_paragraph = '';
                }
                continue;
            }

            // Heading - levels 1-6
            if (preg_match('/^(#{1,6})\s+(.+)$/', $trimmed, $matches)) {
                // End current paragraph if any
                if (! empty($current_paragraph)) {
                    $blocks[] = self::createParagraphBlock($current_paragraph);
                    $current_paragraph = '';
                }
                // End list if in one
                if ($in_list) {
                    $blocks[] = self::createListBlock($list_items, $list_ordered);
                    $list_items = [];
                    $in_list = false;
                    $list_ordered = false;
                }
                $level = strlen($matches[1]);
                $content = self::processInlineMarkdown($matches[2]);
                $blocks[] = self::createHeadingBlock($content, $level);
                continue;
            }

            // Unordered list item
            if (preg_match('/^[\*\-\+]\s+(.+)$/', $trimmed, $matches)) {
                // End current paragraph if any
                if (! empty($current_paragraph)) {
                    $blocks[] = self::createParagraphBlock($current_paragraph);
                    $current_paragraph = '';
                }
                // If switching list types, end previous list
                if ($in_list && $list_ordered) {
                    $blocks[] = self::createListBlock($list_items, $list_ordered);
                    $list_items = [];
                }
                $in_list = true;
                $list_ordered = false;
                $list_items[] = self::processInlineMarkdown($matches[1]);
                continue;
            }

            // Ordered list item
            if (preg_match('/^\d+\.\s+(.+)$/', $trimmed, $matches)) {
                // End current paragraph if any
                if (! empty($current_paragraph)) {
                    $blocks[] = self::createParagraphBlock($current_paragraph);
                    $current_paragraph = '';
                }
                // If switching list types, end previous list
                if ($in_list && ! $list_ordered) {
                    $blocks[] = self::createListBlock($list_items, $list_ordered);
                    $list_items = [];
                }
                $in_list = true;
                $list_ordered = true;
                $list_items[] = self::processInlineMarkdown($matches[1]);
                continue;
            }

            // Regular paragraph line
            if ($in_list) {
                $blocks[] = self::createListBlock($list_items, $list_ordered);
                $list_items = [];
                $in_list = false;
                $list_ordered = false;
            }

            // Accumulate paragraph content
            if (! empty($current_paragraph)) {
                $current_paragraph .= ' ' . $trimmed;
            } else {
                $current_paragraph = $trimmed;
            }
        }

        // Handle remaining content
        if ($in_list) {
            $blocks[] = self::createListBlock($list_items, $list_ordered);
        }
        if (! empty($current_paragraph)) {
            $blocks[] = self::createParagraphBlock($current_paragraph);
        }

        // Join all blocks
        $block_content = implode("\n\n", $blocks);

        // Inject embeds back into the block content
        $block_content = AIEmbeds::injectIntoBlocks($block_content, $embeds);

        return $block_content;
    }

    /**
     * Create a heading block
     *
     * @since 1.0.0
     * @param string $content The heading content (already processed for inline markdown)
     * @param int $level The heading level (1-6)
     * @return string Block markup
     */
    private static function createHeadingBlock(string $content, int $level): string
    {
        // Content already processed by processInlineMarkdown, no need to escape again
        return sprintf(
            '<!-- wp:heading {"level":%d} -->' . "\n" .
                '<h%d class="wp-block-heading">%s</h%d>' . "\n" .
                '<!-- /wp:heading -->',
            $level,
            $level,
            $content,
            $level
        );
    }

    /**
     * Create a paragraph block
     *
     * @since 1.0.0
     * @param string $content The paragraph content
     * @return string Block markup
     */
    private static function createParagraphBlock(string $content): string
    {
        $content = self::processInlineMarkdown($content);
        return sprintf(
            '<!-- wp:paragraph -->' . "\n" .
                '<p>%s</p>' . "\n" .
                '<!-- /wp:paragraph -->',
            $content
        );
    }

    /**
     * Create a list block
     *
     * @since 1.0.0
     * @param array $items Array of list item content (already processed for inline markdown)
     * @param bool $ordered Whether to create an ordered list (true) or unordered (false)
     * @return string Block markup
     */
    private static function createListBlock(array $items, bool $ordered = false): string
    {
        if (empty($items)) {
            return '';
        }

        $list_html = '';
        foreach ($items as $item) {
            $list_html .= '<li>' . $item . '</li>';
        }

        if ($ordered) {
            return sprintf(
                '<!-- wp:list {"ordered":true} -->' . "\n" .
                    '<ol>%s</ol>' . "\n" .
                    '<!-- /wp:list -->',
                $list_html
            );
        }

        return sprintf(
            '<!-- wp:list -->' . "\n" .
                '<ul>%s</ul>' . "\n" .
                '<!-- /wp:list -->',
            $list_html
        );
    }

    /**
     * Create an embed block for various media types
     *
     * Creates WordPress native embed blocks that leverage oEmbed for YouTube and Twitter.
     * The blocks themselves contain just the URL; WordPress handles the oEmbed resolution
     * when the block is rendered. For Meta platforms (Instagram, Facebook), falls back to
     * HTML embed blocks since oEmbed API has restrictions.
     *
     * @since 1.0.0
     * @param string $type The embed type (youtube, twitter, instagram, facebook)
     * @param string $url The embed URL
     * @return string Block markup
     */
    public static function createEmbedBlock(string $type, string $url): string
    {
        // Store original URL for rawurlencode operations
        $original_url = $url;
        $url = esc_url($url);

        // YouTube and Twitter: Use WordPress core embed blocks with oEmbed support
        if ($type === 'youtube') {
            return sprintf(
                '<!-- wp:embed {"url":%s,"type":"video","providerNameSlug":"youtube","responsive":true,"className":"wp-embed-aspect-16-9 wp-has-aspect-ratio"} -->' . "\n" .
                    '<figure class="wp-block-embed is-type-video is-provider-youtube wp-block-embed-youtube wp-embed-aspect-16-9 wp-has-aspect-ratio"><div class="wp-block-embed__wrapper">' . "\n" .
                    '%s' . "\n" .
                    '</div></figure>' . "\n" .
                    '<!-- /wp:embed -->',
                wp_json_encode($url),
                $url
            );
        }

        if ($type === 'twitter') {
            return sprintf(
                '<!-- wp:embed {"url":%s,"type":"rich","providerNameSlug":"twitter","responsive":true} -->' . "\n" .
                    '<figure class="wp-block-embed is-type-rich is-provider-twitter wp-block-embed-twitter"><div class="wp-block-embed__wrapper">' . "\n" .
                    '%s' . "\n" .
                    '</div></figure>' . "\n" .
                    '<!-- /wp:embed -->',
                wp_json_encode($url),
                $url
            );
        }

        // Meta platforms (Instagram, Facebook): Use HTML embed blocks
        // oEmbed API has restrictions/limitations for these platforms
        if ($type === 'instagram') {
            // Use HTML block with iframe for Instagram
            $embed_src = rtrim($original_url, '/') . '/embed';
            return self::createHtmlIframeBlock($embed_src, 540, 700, 'Embedded content from Instagram');
        }

        if ($type === 'facebook') {
            // Use HTML block with iframe for Facebook
            $embed_src = 'https://www.facebook.com/plugins/post.php?href=' . rawurlencode($original_url) . '&show_text=true&width=552';
            return self::createHtmlIframeBlock($embed_src, 552, 700, 'Embedded content from Facebook');
        }

        // Fallback for unknown types: generic embed block
        return sprintf(
            '<!-- wp:embed {"url":%s,"type":"rich","providerNameSlug":"embed","responsive":true} -->' . "\n" .
                '<figure class="wp-block-embed is-type-rich"><div class="wp-block-embed__wrapper">' . "\n" .
                '%s' . "\n" .
                '</div></figure>' . "\n" .
                '<!-- /wp:embed -->',
            wp_json_encode($url),
            $url
        );
    }

    /**
     * Create an HTML block with an iframe for embeds that don't support oEmbed
     *
     * @since 1.0.0
     * @param string $src The iframe src URL
     * @param int $width The iframe width
     * @param int $height The iframe height
     * @param string $title The iframe title for accessibility
     * @return string Block markup
     */
    private static function createHtmlIframeBlock(string $src, int $width, int $height, string $title): string
    {
        $src_attr = esc_url($src);
        $title_attr = esc_attr($title);
        return sprintf(
            '<!-- wp:html -->' . "\n" .
                '<div class="vm-embed"><iframe src="%s" width="%d" height="%d" title="%s" style="border:0;" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen referrerpolicy="no-referrer-when-downgrade"></iframe></div>' . "\n" .
                '<!-- /wp:html -->',
            $src_attr,
            $width,
            $height,
            $title_attr
        );
    }

    /**
     * Process inline Markdown syntax (bold, italic, links)
     *
     * Converts inline Markdown elements to HTML while preserving security through proper escaping.
     * The order of operations is: parse markdown → convert to HTML → escape content selectively.
     *
     * @since 1.0.0
     * @param string $text Text with inline Markdown
     * @return string HTML with inline elements converted and properly escaped
     */
    private static function processInlineMarkdown(string $text): string
    {
        // Process links first (before escaping) to preserve URL structure
        $text = preg_replace_callback(
            '/\[([^\]]+)\]\(([^\)]+)\)/',
            function ($matches) {
                $link_text = esc_html($matches[1]);
                $url = esc_url($matches[2]);
                return sprintf('<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>', $url, $link_text);
            },
            $text
        );

        // Now we need to escape HTML but preserve our link tags
        // Split on link tags to process parts separately
        $parts = preg_split('/(<a[^>]*>.*?<\/a>)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        $result = '';
        foreach ($parts as $i => $part) {
            // Even indices are non-link content, odd indices are links
            if ($i % 2 === 0) {
                // Escape and process markdown on non-link content
                $part = esc_html($part);
                // Bold **text** or __text__
                $part = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $part);
                $part = preg_replace('/__(.+?)__/', '<strong>$1</strong>', $part);
                // Italic *text* or _text_ (but not inside words)
                $part = preg_replace('/(?<!\w)\*(.+?)\*(?!\w)/', '<em>$1</em>', $part);
                $part = preg_replace('/(?<!\w)_(.+?)_(?!\w)/', '<em>$1</em>', $part);
            }
            $result .= $part;
        }

        return $result;
    }
}
