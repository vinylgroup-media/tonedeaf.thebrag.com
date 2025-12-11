<?php

/**
 * Research Data Formatter
 *
 * Formats research response data (Archives and Recent News) from JSON to readable HTML.
 * Handles both JSON-formatted responses and markdown responses.
 *
 * @package VM\AIFeed
 * @since 1.0.0
 */

namespace VM\AIFeed\Helpers;

use VM\AIFeed\Content\AIMarkdown;

/**
 * Formats research data into user-friendly HTML
 *
 * @since 1.0.0
 */
class ResearchDataFormatter
{

    /**
     * Format research response data
     *
     * Detects whether the response contains JSON research data or markdown,
     * and formats accordingly.
     *
     * @param string $response The response content to format
     * @return string Formatted HTML
     */
    public static function formatResearchResponse(string $response): string
    {
        // Check if this looks like our structured research data format
        if (self::isResearchDataFormat($response)) {
            return self::parseAndFormatResearchData($response);
        }

        // Fall back to markdown conversion for regular content
        return AIMarkdown::convert($response);
    }

    /**
     * Check if response is in research data format
     *
     * @param string $response The response to check
     * @return bool True if it appears to be research data format
     */
    private static function isResearchDataFormat(string $response): bool
    {
        // Look for key markers that indicate research data format
        return (
            strpos($response, 'Knowledge Graph Data (Entity)') !== false ||
            strpos($response, 'Knowledge Graph Data (Relationship)') !== false ||
            strpos($response, 'Document Chunks') !== false ||
            strpos($response, 'Reference Document List') !== false
        );
    }

    /**
     * Parse and format research data
     *
     * @param string $response The research data to parse and format
     * @return string Formatted HTML
     */
    private static function parseAndFormatResearchData(string $response): string
    {
        $html = '<div class="research-data-container">';

        // Extract and render Knowledge Graph Entities
        $entities = self::extractJsonSection($response, 'Knowledge Graph Data (Entity)');
        if (!empty($entities)) {
            $html .= self::renderEntitiesSection($entities);
        }

        // Extract and render Knowledge Graph Relationships
        $relationships = self::extractJsonSection($response, 'Knowledge Graph Data (Relationship)');
        if (!empty($relationships)) {
            $html .= self::renderRelationshipsSection($relationships);
        }

        // Extract and render Document Chunks
        $chunks = self::extractJsonSection($response, 'Document Chunks');
        $references = self::extractReferences($response);
        if (!empty($chunks)) {
            $html .= self::renderDocumentChunksSection($chunks, $references);
        }

        // Render References
        if (!empty($references)) {
            $html .= self::renderReferencesSection($references);
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Extract JSON section from response
     *
     * @param string $response The full response text
     * @param string $sectionName The section name to extract
     * @return array Parsed JSON data or empty array
     */
    private static function extractJsonSection(string $response, string $sectionName): array
    {
        // Find section header
        $pattern = '/' . preg_quote($sectionName, '/') . ':\s*```json\s*(.*?)\s*```/s';

        if (preg_match($pattern, $response, $matches)) {
            $jsonStr = trim($matches[1]);
            $decoded = json_decode($jsonStr, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return array();
    }

    /**
     * Extract references from response
     *
     * @param string $response The full response text
     * @return array Array of references with id => url mapping
     */
    private static function extractReferences(string $response): array
    {
        $references = array();

        // Find Reference Document List section
        if (preg_match('/Reference Document List.*?```\s*(.*?)\s*```/s', $response, $matches)) {
            $refText = $matches[1];

            // Parse lines like: [1] https://example.com
            if (preg_match_all('/\[(\d+)\]\s+(.+)/m', $refText, $refMatches, PREG_SET_ORDER)) {
                foreach ($refMatches as $match) {
                    $references[$match[1]] = trim($match[2]);
                }
            }
        }

        return $references;
    }

    /**
     * Render entities section
     *
     * @param array $entities Array of entity objects
     * @return string HTML output
     */
    private static function renderEntitiesSection(array $entities): string
    {
        $count = count($entities);

        $html = '<details class="research-data-section" open>';
        $html .= '<summary><strong>Knowledge Graph Entities</strong> <span class="count">(' . esc_html($count) . ')</span></summary>';
        $html .= '<div class="research-data-content">';
        $html .= '<table class="research-data-table wp-list-table widefat fixed striped">';
        $html .= '<thead><tr>';
        $html .= '<th style="width: 25%;">Entity</th>';
        $html .= '<th style="width: 15%;">Type</th>';
        $html .= '<th style="width: 60%;">Description</th>';
        $html .= '</tr></thead>';
        $html .= '<tbody>';

        foreach ($entities as $entity) {
            $html .= '<tr>';
            $html .= '<td><strong>' . esc_html($entity['entity'] ?? 'Unknown') . '</strong></td>';
            $html .= '<td><span class="entity-type-badge">' . esc_html($entity['type'] ?? 'unknown') . '</span></td>';
            $html .= '<td>' . esc_html($entity['description'] ?? 'No description') . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';
        $html .= '</div></details>';

        return $html;
    }

    /**
     * Render relationships section
     *
     * @param array $relationships Array of relationship objects
     * @return string HTML output
     */
    private static function renderRelationshipsSection(array $relationships): string
    {
        $count = count($relationships);

        $html = '<details class="research-data-section">';
        $html .= '<summary><strong>Knowledge Graph Relationships</strong> <span class="count">(' . esc_html($count) . ')</span></summary>';
        $html .= '<div class="research-data-content">';
        $html .= '<table class="research-data-table wp-list-table widefat fixed striped">';
        $html .= '<thead><tr>';
        $html .= '<th style="width: 22%;">Entity 1</th>';
        $html .= '<th style="width: 22%;">Entity 2</th>';
        $html .= '<th style="width: 56%;">Relationship</th>';
        $html .= '</tr></thead>';
        $html .= '<tbody>';

        foreach ($relationships as $rel) {
            $html .= '<tr>';
            $html .= '<td><strong>' . esc_html($rel['entity1'] ?? 'Unknown') . '</strong></td>';
            $html .= '<td><strong>' . esc_html($rel['entity2'] ?? 'Unknown') . '</strong></td>';
            $html .= '<td>' . esc_html($rel['description'] ?? 'No description') . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';
        $html .= '</div></details>';

        return $html;
    }

    /**
     * Render document chunks section
     *
     * @param array $chunks Array of document chunk objects
     * @param array $references Reference URL mapping
     * @return string HTML output
     */
    private static function renderDocumentChunksSection(array $chunks, array $references): string
    {
        $count = count($chunks);

        $html = '<details class="research-data-section">';
        $html .= '<summary><strong>Document Chunks</strong> <span class="count">(' . esc_html($count) . ')</span></summary>';
        $html .= '<div class="research-data-content">';

        foreach ($chunks as $chunk) {
            $refId = $chunk['reference_id'] ?? '';
            $content = $chunk['content'] ?? '';

            // Parse content fields (Title, Summary, Content, Source URL, Published)
            $parsed = self::parseDocumentChunkContent($content);

            $html .= '<div class="research-document-card">';

            // Header with title and reference
            $html .= '<div class="document-header">';
            if (!empty($parsed['title'])) {
                $html .= '<h4 class="document-title">' . esc_html($parsed['title']) . '</h4>';
            }
            if (!empty($refId) && !empty($references[$refId])) {
                $html .= '<a href="' . esc_url($references[$refId]) . '" target="_blank" rel="noopener noreferrer" class="document-source-link">';
                $html .= esc_html(self::getDomainFromUrl($references[$refId])) . ' ↗</a>';
            }
            $html .= '</div>';

            // Metadata
            if (!empty($parsed['published']) || !empty($parsed['source_url'])) {
                $html .= '<div class="document-meta">';
                if (!empty($parsed['published'])) {
                    $html .= '<span class="document-date">' . esc_html($parsed['published']) . '</span>';
                }
                if (!empty($refId)) {
                    $html .= '<span class="document-ref">Ref #' . esc_html($refId) . '</span>';
                }
                $html .= '</div>';
            }

            // Summary
            if (!empty($parsed['summary'])) {
                $html .= '<div class="document-summary">';
                $html .= '<strong>Summary:</strong> ' . esc_html($parsed['summary']);
                $html .= '</div>';
            }

            // Content excerpt (limited to 500 chars)
            if (!empty($parsed['content'])) {
                $excerpt = mb_substr($parsed['content'], 0, 500);
                if (mb_strlen($parsed['content']) > 500) {
                    $excerpt .= '...';
                }
                $html .= '<div class="document-excerpt">';
                $html .= esc_html($excerpt);
                $html .= '</div>';
            }

            $html .= '</div>';
        }

        $html .= '</div></details>';

        return $html;
    }

    /**
     * Parse document chunk content into structured fields
     *
     * @param string $content Raw content string
     * @return array Parsed fields
     */
    private static function parseDocumentChunkContent(string $content): array
    {
        $fields = array(
            'title' => '',
            'summary' => '',
            'content' => '',
            'source_url' => '',
            'published' => ''
        );

        // Parse Title
        if (preg_match('/Title:\s*(.+?)(?:\n|$)/s', $content, $matches)) {
            $fields['title'] = trim($matches[1]);
        }

        // Parse Summary
        if (preg_match('/Summary:\s*(.+?)(?:\n\n|Content:|Source URL:|Published:|$)/s', $content, $matches)) {
            $fields['summary'] = trim($matches[1]);
        }

        // Parse Content
        if (preg_match('/Content:\s*(.+?)(?:\n\nSource URL:|Published:|$)/s', $content, $matches)) {
            $fields['content'] = trim($matches[1]);
        }

        // Parse Source URL
        if (preg_match('/Source URL:\s*(.+?)(?:\n|$)/s', $content, $matches)) {
            $fields['source_url'] = trim($matches[1]);
        }

        // Parse Published date
        if (preg_match('/Published:\s*(.+?)(?:\n|$)/s', $content, $matches)) {
            $fields['published'] = trim($matches[1]);
        }

        return $fields;
    }

    /**
     * Render references section
     *
     * @param array $references Array of reference URLs
     * @return string HTML output
     */
    private static function renderReferencesSection(array $references): string
    {
        $count = count($references);

        $html = '<details class="research-data-section">';
        $html .= '<summary><strong>Reference Documents</strong> <span class="count">(' . esc_html($count) . ')</span></summary>';
        $html .= '<div class="research-data-content">';
        $html .= '<ul class="research-references-list">';

        foreach ($references as $id => $url) {
            $domain = self::getDomainFromUrl($url);
            $html .= '<li>';
            $html .= '<span class="ref-id">[' . esc_html($id) . ']</span> ';
            $html .= '<a href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer">';
            $html .= esc_html($domain) . '</a>';
            $html .= '</li>';
        }

        $html .= '</ul>';
        $html .= '</div></details>';

        return $html;
    }

    /**
     * Extract domain from URL
     *
     * @param string $url The URL to extract domain from
     * @return string Domain or full URL if parsing fails
     */
    private static function getDomainFromUrl(string $url): string
    {
        $parsed = parse_url($url);

        if (isset($parsed['host'])) {
            return $parsed['host'];
        }

        return $url;
    }
}
