<?php
/*
   Plugin Name: TBM Google Analytics
   Plugin URI:
   description:
   Version: 1.0
   Author: Toby Smith
   Author URI:
*/

use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Filter;
use Google\Analytics\Data\V1beta\Filter\StringFilter;
use Google\Analytics\Data\V1beta\FilterExpression;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\OrderBy;
use Google\Analytics\Data\V1beta\OrderBy\MetricOrderBy;
use Google\Analytics\Data\V1beta\RunReportRequest;
use Google\Analytics\Data\V1beta\Filter\NumericFilter;
use Google\Analytics\Data\V1beta\Filter\NumericFilter\Operation;
use Google\Analytics\Data\V1beta\NumericValue;
use Google\Analytics\Data\V1beta\RunReportResponse;
use Google\ApiCore\ApiException;
use JetBrains\PhpStorm\NoReturn;

# Activation
register_activation_hook(__FILE__, 'activate_tbm_ga');
function activate_tbm_ga(): void
{
    if (!wp_next_scheduled('cron_tbm_ga_update_pageviews', [NULL, NULL])) {
        wp_schedule_event(time(), 'hourly', 'cron_tbm_ga_update_pageviews', [NULL, NULL]);
    }
}

# DeActivation
register_deactivation_hook(__FILE__, 'deactivate_tbm_ga');
function deactivate_tbm_ga(): void
{
    $crons = _get_cron_array();
    if (empty($crons)) {
        return;
    }
    $hook = 'cron_tbm_ga_update_pageviews';
    foreach ($crons as $timestamp => $cron) {
        if (!empty($cron[$hook])) {
            unset($crons[$timestamp][$hook]);
        }
        if (empty($crons[$timestamp])) {
            unset($crons[$timestamp]);
        }
    }
    _set_cron_array($crons);
}

# Add actions
add_action('rest_api_init', function () {
    register_rest_route('tbm_ga/v1', '/articles', [
        'methods' => 'GET',
        'callback' => 'tbm_ga_articles',
    ]);

    register_rest_route('tbm_ga/v1', '/article_author', [
        'methods' => 'GET',
        'callback' => 'tbm_ga_article_author',
    ]);

    register_rest_route('tbm_ga/v1', '/articles_count', [
        'methods' => 'GET',
        'callback' => 'tbm_ga_articles_count',
    ]);
});

function tbm_ga_articles(): array
{
    $return = [];

    $from = isset($_GET['from']) ? date_i18n('c', strtotime(trim($_GET['from']))) : NULL;
    $to = isset($_GET['to']) ? date_i18n('c', strtotime(trim($_GET['to']))) : NULL;

    $query_author = isset($_GET['author']) ? trim($_GET['author']) : NULL;
    if (is_null($from) || is_null($to)) {
        return $return;
    }

    $args = [
        'date_query' => [
            'after' => date('c', strtotime($from)),
            'before' => date('c', strtotime($to)),
        ],
        'post_status' => 'publish',
        'posts_per_page' => -1
    ];

    $posts = new WP_Query($args);

    global $post;

    if ($posts->have_posts()) {
        while ($posts->have_posts()) {
            $posts->the_post();
            $url = get_the_permalink();
            $url_parsed = parse_url($url);
            $author = get_field('Author') ? get_field('Author') : (get_field('author') ? get_field('author') : get_the_author());
            $post_categories = wp_get_post_categories(get_the_ID());
            $category_names = [];
            if (count($post_categories) > 0) :
                foreach ($post_categories as $c) :
                    $cat = get_category($c);
                    $category_names[] = $cat->name;
                endforeach;
            endif;

            if (!is_null($query_author)) {
                if (!str_contains(strtolower($author), strtolower($query_author))) {
                    continue;
                }
            }

            $return[] = [
                'ID' => get_the_ID(),
                'url' => $url,
                'path' => $url_parsed['path'],
                'publish_date' => get_the_date(),
                'publish_datetime' => get_the_time('Y-m-d H:i:s'),
                'author' => $author,
                'categories' => $category_names
            ];
        }
    }
    return $return;
}

function tbm_ga_article_author($data): array
{
    $return = [];

    $post_name = isset($_GET['article']) ? trim($_GET['article']) : NULL;

    $post_name_e = explode('/', $post_name);
    $post_name = $post_name_e[1];

    if (!is_null($post_name)) {
        global $post;
        $post = get_page_by_path($post_name, OBJECT, 'post');
        if (!$post)
            $post = get_page_by_path($post_name);
        $return['author'] = get_field('Author', $post->ID) ? get_field('Author', $post->ID) : (get_field('author', $post->ID) ? get_field('author', $post->ID) : get_the_author_meta('display_name', get_post_field('post_author', $post->ID)));
    }

    return $return;
}

function tbm_ga_articles_count($data): int|array
{
    $return = [];
    $from = isset($_GET['from']) ? date_i18n('Y-m-d', strtotime('-1 day', strtotime(trim($_GET['from'])))) : NULL;
    $to = isset($_GET['to']) ? date_i18n('Y-m-d', strtotime('+1 day', strtotime(trim($_GET['to'])))) : NULL;

    $query_author = isset($_GET['author']) ? trim($_GET['author']) : NULL;

    if (is_null($from) || is_null($to)) {
        return $return;
    }

    $posts = new WP_Query([
        'date_query' => [
            'after' => $from,
            'before' => $to,
        ],
        'post_type' => ['post', 'photo_gallery'],
        'post_status' => 'publish',
        'posts_per_page' => -1
    ]);

    global $post;

    if ($posts->have_posts()) {
        return $posts->post_count;
    }

    return $return;
}

# Add admin menu
add_action('admin_menu', 'admin_menu_tbm_ga_update_pageviews');
function admin_menu_tbm_ga_update_pageviews(): void
{
    add_management_page('Update Pageviews from GA', 'Update Pageviews from GA', 'administrator', 'tbm_ga_update_pageviews', 'tbm_ga_update_pageviews');
}

add_action('cron_tbm_ga_update_pageviews', 'tbm_ga_update_pageviews');

/**
 * @throws ApiException
 */
#[NoReturn] function tbm_ga_update_pageviews(): void
{
    // Load the Google API PHP Client Library.
    require_once __DIR__ . '/vendor/autoload.php';

    update_option('cron_run_most_viewed_yesterday', date('Y-m-d h:i:s'));

    $response = initializeAnalytics();

    updateDB($response);

    wp_die();
}


/**
 * Initializes an Analytics Reporting API V4 service object.
 * @throws ApiException
 */
function initializeAnalytics(): RunReportResponse
{
    $client = new BetaAnalyticsDataClient();

    // Make an API call.
    $request = (new RunReportRequest())
        ->setProperty( 'properties/' . TBM_GA4_PROPERTY )
        ->setDimensions([new Dimension(['name' => 'pagePath'])])
        ->setMetrics([new Metric(['name' => 'screenPageViews'])])
        ->setDateRanges([new DateRange([
                'start_date' => '2daysAgo',
                'end_date' => 'yesterday',
            ])
        ])
        ->setMetricFilter(new FilterExpression([
            'filter' => new Filter([
                'field_name' => 'screenPageViews',
                'numeric_filter' => new NumericFilter([
                    'operation' => Operation::GREATER_THAN,
                    'value' => new NumericValue([
                        'int64_value' => 100,
                    ]),
                ]),
            ]),
        ]))
        ->setDimensionFilter(new FilterExpression([
            'filter' => new Filter([
                'field_name' => 'hostName',
                'string_filter' => new StringFilter([
                    'value' => 'tonedeaf.thebrag.com',
                ]),
            ]),
        ]))
        ->setOrderBys([
            new OrderBy([
                'metric' => new MetricOrderBy([
                    'metric_name' => 'screenPageViews',
                ]),
                'desc' => true,
            ]),
        ]);
    return $client->runReport($request);
}

function updateDB($reports, $slug_filter = NULL, $post_type = 'post'): void
{
    global $wpdb;

    $pagePaths_pageViews = [];

    foreach ($reports->getRows() as $row) {
        $dimensions = $row->getDimensionValues()[0]->getValue();
        $metrics = $row->getMetricValues()[0]->getValue();
        $pagePath = ltrim(rtrim($dimensions, '/'), '/');
        $pageViews = $metrics;

        $pagePath_e = explode('/', $pagePath);

        if (is_null($slug_filter)) :
            if (!isset($pagePaths_pageViews[$pagePath_e[0]])) :
                $pagePaths_pageViews[$pagePath_e[0]] = $pageViews;
            else :
                $pagePaths_pageViews[$pagePath_e[0]] += $pageViews;
            endif;
        else :
            if (isset($pagePath_e[1])) :
                if (!isset($pagePaths_pageViews[$pagePath_e[1]])) :
                    $pagePaths_pageViews[$pagePath_e[1]] = $pageViews;
                else :
                    $pagePaths_pageViews[$pagePath_e[1]] += $pageViews;
                endif;
            endif;
        endif;
    }

    arsort($pagePaths_pageViews);

    foreach ($pagePaths_pageViews as $pagePath => $pageViews) :
        if ('' == $pagePath) :
            unset($pagePaths_pageViews[$pagePath]);
            continue;
        endif;
        $post = get_page_by_path($pagePath, OBJECT, $post_type);
        if (!is_null($post) && $post_type == $post->post_type && 'publish' == $post->post_status && !get_field('not_brand_safe', $post->ID)) :
            $wpdb->insert(
                $wpdb->prefix . 'tbm_trending',
                [
                    'post_id' => $post->ID,
                    'post_type' => $post->post_type,
                    'pageviews' => $pageViews,
                ],
                [
                    '%d', '%s', '%d'
                ]
            );
        endif;
    endforeach;

    $array_keys_pagePaths_pageViews = array_keys($pagePaths_pageViews);
    $top_article_slug = $array_keys_pagePaths_pageViews[0] ?? NULL;

    $top_article = get_page_by_path($top_article_slug, OBJECT, $post_type);

    if (!$top_article || 'publish' != $top_article->post_status) {
        $top_article_slug = $array_keys_pagePaths_pageViews[1] ?? NULL;
        $top_article = get_page_by_path($top_article_slug, OBJECT, $post_type);
    }

    if (!$top_article || 'publish' != $top_article->post_status) {
        $top_article_slug = $array_keys_pagePaths_pageViews[2] ?? NULL;
        $top_article = get_page_by_path($top_article_slug, OBJECT, $post_type);
    }


    if ($top_article && 'publish' == $top_article->post_status) :
        echo '<pre>';
        print_r($pagePaths_pageViews);
        echo '</pre>';

        if (is_null($slug_filter)) :
            if (!get_option('force_most_viewed')) :
                update_option('most_viewed_yesterday', $top_article->ID);
                echo $top_article->ID . ' | ' . $top_article_slug . '<br><br>';
                exit;
            endif;
        else :
            update_option('most_viewed_yesterday_' . $slug_filter, $top_article->ID);
            echo $top_article->ID . ' | ' . $top_article_slug . '<br><br>';
            exit;
        endif;
    endif; // If $top_article_slug is NOT null i.e. found first key in the pageviews array
}
