<?php
/*
   Plugin Name: SSM Google Analytics
   Plugin URI:
   description:
   Version: 1.0
   Author: Sachin Patel
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
use Google\Analytics\Data\V1beta\RunReportResponse;
use Google\Analytics\Data\V1beta\Filter\NumericFilter;
use Google\Analytics\Data\V1beta\Filter\NumericFilter\Operation;
use Google\Analytics\Data\V1beta\NumericValue;

function ssm_ga_func($data)
{
    //    var_dump( $_GET );
    $return = array();

    // $from = isset($_GET['from']) ? date_i18n('Y-m-d', strtotime('-1 day', strtotime(trim($_GET['from'])))) : NULL;
    // $to = isset($_GET['to']) ? date_i18n('Y-m-d', strtotime('+1 day', strtotime(trim($_GET['to'])))) : NULL;

    $from = isset($_GET['from']) ? date_i18n('c', strtotime(trim($_GET['from']))) : NULL;
    $to = isset($_GET['to']) ? date_i18n('c', strtotime(trim($_GET['to']))) : NULL;

    $query_author = isset($_GET['author']) ? trim($_GET['author']) : NULL;
    if (is_null($from) || is_null($to))
        return $return;


    $args = array(
        'date_query' => array(
            'after' => date('c', strtotime($from)),
            'before' => date('c', strtotime($to)),
        ),
        'post_type' => $post_types,
        'post_status' => 'publish',
        'posts_per_page' => -1
    );

    $posts = new WP_Query($args);

    global $post;
    if ($posts->have_posts()) {
        while ($posts->have_posts()) {
            $posts->the_post();
            $url = get_the_permalink();
            $url_parsed = parse_url($url);
            $author = get_field('Author') ? get_field('Author') : (get_field('author') ? get_field('author') : get_the_author());
            $post_categories = wp_get_post_categories(get_the_ID());
            $category_names = array();
            if (count($post_categories) > 0) :
                foreach ($post_categories as $c) :
                    $cat = get_category($c);
                    array_push($category_names, $cat->name);
                endforeach;
            endif;

            if (!is_null($query_author)) {
                if (strpos(strtolower($author), strtolower($query_author)) === false) {
                    continue;
                }
            }

            $return[] = array(
                'ID' => get_the_ID(),
                'url' => $url,
                'path' => $url_parsed['path'],
                'publish_date' => get_the_date(),
                'publish_datetime' => get_the_time('Y-m-d H:i:s'),
                'author' => $author,
                'categories' => $category_names
            );
        }
    }
    return $return; //$posts->post_count;
}

function ssm_ga_article_author_func($data)
{
    $return = array();

    $post_name = isset($_GET['article']) ? trim($_GET['article']) : NULL;

    $post_name_e = explode('/', $post_name);
    $post_name = $post_name_e[1];

    if (!is_null($post_name)) {
        global $post;
        $post = get_page_by_path($post_name, OBJECT, 'post');
        if (!$post)
            $post = get_page_by_path($post_name, OBJECT, 'page');
        $return['author'] = get_field('Author', $post->ID) ? get_field('Author', $post->ID) : (get_field('author', $post->ID) ? get_field('author', $post->ID) : get_the_author_meta('display_name', get_post_field('post_author', $post->ID)));
    }

    return $return;
}

function ssm_ga_articles_count($data)
{
    //    var_dump( $_GET );
    $return = array();
    $from = isset($_GET['from']) ? date_i18n('Y-m-d', strtotime('-1 day', strtotime(trim($_GET['from'])))) : NULL;
    $to = isset($_GET['to']) ? date_i18n('Y-m-d', strtotime('+1 day', strtotime(trim($_GET['to'])))) : NULL;
    $query_author = isset($_GET['author']) ? trim($_GET['author']) : NULL;
    if (is_null($from) || is_null($to))
        return $return;
    $posts = new WP_Query(array(
        'date_query' => array(
            'after' => $from,
            'before' => $to,
        ),
        'post_type' => array('post', 'photo_gallery'),
        'post_status' => 'publish',
        'posts_per_page' => -1
    ));

    global $post;
    if ($posts->have_posts()) {
        return $posts->post_count;
    }
    return $return; //$posts->post_count;
}

add_action('rest_api_init', function () {
    //    register_rest_route( 'ssm_ga/v1', '/articles/(?P<year_month>\d+-\d+)', array(
    register_rest_route('ssm_ga/v1', '/articles', array(
        'methods' => 'GET',
        'callback' => 'ssm_ga_func',
    ));

    register_rest_route('ssm_ga/v1', '/article_author', array(
        'methods' => 'GET',
        'callback' => 'ssm_ga_article_author_func',
    ));

    register_rest_route('ssm_ga/v1', '/articles_count', array(
        'methods' => 'GET',
        'callback' => 'ssm_ga_articles_count',
    ));
});

/*
 * Activation
 */
register_activation_hook(__FILE__, 'activate_ssm_ga');
function activate_ssm_ga()
{
    if (!wp_next_scheduled('cron_tbm_ga_update_pageviews', array(NULL, NULL))) {
        wp_schedule_event(time(), 'hourly', 'cron_tbm_ga_update_pageviews', array(NULL, NULL));
    }
}

/*
 * DeActivation
 */
register_deactivation_hook(__FILE__, 'deactivate_ssm_ga');
function deactivate_ssm_ga()
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

add_action('cron_tbm_ga_update_pageviews', 'tbm_ga_update_pageviews');

/*
 * Get pageviews and add the article with highest pageviews to the options
 */
add_action('admin_menu', 'admin_menu_tbm_ga_update_pageviews');
function admin_menu_tbm_ga_update_pageviews()
{
    add_management_page('Update Pageviews from GA', 'Update Pageviews from GA', 'administrator', 'tbm_ga_update_pageviews', 'tbm_ga_update_pageviews');
}
function tbm_ga_update_pageviews()
{
    // Load the Google API PHP Client Library.
    require_once __DIR__ . '/vendor/autoload.php';

    update_option('cron_run_most_viewed_yesterday', date('Y-m-d h:i:s'));

    $response = initializeAnalytics();

    // $response = getReport($analytics);
    updateDB($response);

    // $response_country = getReport($analytics, 'country');
    // updateDB($response_country, 'country', 'country');

    exit;
}


/**
 * Initializes an Analytics Reporting API V4 service object.
 *
 * @return An authorized Analytics Reporting API V4 service object.
 */
function initializeAnalytics()
{
    // $KEY_FILE_LOCATION = __DIR__ . '/service-account-credentials.json';

    // // Create and configure a new client object.
    // $client = new Google_Client();
    // $client->setApplicationName("Page with most views in last 24 hours");
    // $client->setAuthConfig($KEY_FILE_LOCATION);
    // $client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
    // $analytics = new Google_Service_AnalyticsReporting($client);

    // return $analytics;

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
                    'value' => 'au.rollingstone.com',
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
    $response = $client->runReport($request);

    return $response;
}


/**
 * Queries the Analytics Reporting API V4.
 *
 * @param service An authorized Analytics Reporting API V4 service object.
 * @return The Analytics Reporting API V4 response.
 */
function getReport($analytics, $page_filter = NULL)
{

    date_default_timezone_set('Australia/Sydney');

    // Replace with your view ID, for example XXXX.
    $VIEW_ID = "151705567";

    // Create the ReportRequest object.
    $request = new Google_Service_AnalyticsReporting_ReportRequest();

    $request->setViewId($VIEW_ID);

    $current_hour = date('H');

    // Create the DateRange object.
    $dateRange = new Google_Service_AnalyticsReporting_DateRange();
    if ($current_hour >= 8) :
        $dateRange->setStartDate('today');
    else :
        $dateRange->setStartDate('yesterday');
    endif;
    $dateRange->setEndDate('today');

    $request->setDateRanges($dateRange);

    // Create the Metrics object.
    $pageviews = new Google_Service_AnalyticsReporting_Metric();
    $pageviews->setExpression("ga:pageViews");
    $pageviews->setAlias("pageViews");

    $request->setMetrics(array($pageviews));

    // Sorting (Ordering)
    $ordering = new Google_Service_AnalyticsReporting_OrderBy();
    $ordering->setFieldName("ga:pageviews");
    $ordering->setOrderType("VALUE");
    $ordering->setSortOrder("DESCENDING");

    $request->setOrderBys($ordering);

    // Set Country Filter
    $dimensionFilterCountry = new Google_Service_AnalyticsReporting_SegmentDimensionFilter();
    $dimensionFilterCountry->setDimensionName("ga:country");
    $dimensionFilterCountry->setOperator("EXACT");
    $dimensionFilterCountry->setExpressions(array('Australia'));
    // Create the DimensionFilterClauses
    $ga_dimensionFilterClause_country = new Google_Service_AnalyticsReporting_DimensionFilterClause();
    $ga_dimensionFilterClause_country->setFilters(array($dimensionFilterCountry));

    $dimensionFilterClauses[] = $ga_dimensionFilterClause_country;

    if (!is_null($page_filter)) :
        // Create the DimensionFilter.
        $dimensionFilter = new Google_Service_AnalyticsReporting_DimensionFilter();
        $dimensionFilter->setDimensionName('ga:pagePath');
        $dimensionFilter->setOperator('REGEXP');
        $dimensionFilter->setExpressions('/' . $page_filter . '/');
        //    $dimensionFilter->setNot(TRUE);
        // Create the DimensionFilterClauses
        $dimensionFilterClause = new Google_Service_AnalyticsReporting_DimensionFilterClause();
        $dimensionFilterClause->setFilters(array($dimensionFilter));

        $dimensionFilterClauses[] = $dimensionFilterClause;
    endif;

    $request->setDimensionFilterClauses($dimensionFilterClauses);

    //    $request->setDateRanges($dateRange);

    $request->setPageSize(40);

    // Creating multiple segments with function createSegment()
    $segments = [];

    if ($current_hour >= 8) :
        $segments[] = ssm_ga_createSegment((string) ($current_hour - 7), (string) $current_hour, 'After Midnight');
    else :
        $segments[] = ssm_ga_createSegment((string)(17 + $current_hour), '23', 'Before Midnight');
        $segments[] = ssm_ga_createSegment('00', (string) ($current_hour), 'After Midnight');
    endif;

    //    $segments[] = ssm_ga_createSegment('15', '23', 'Before Midnight');
    //    $segments[] = ssm_ga_createSegment('00', '10', 'After Midnight');

    //Create the hour dimension.
    $hour = new Google_Service_AnalyticsReporting_Dimension();
    $hour->setName("ga:hour");

    // Create the segment dimension.
    $segmentDimensions = new Google_Service_AnalyticsReporting_Dimension();
    $segmentDimensions->setName("ga:segment");

    //Create the Dimensions object.
    $pageDimension = new Google_Service_AnalyticsReporting_Dimension();
    $pageDimension->setName("ga:pagePath");

    //    $request->setDimensions(array($pageDimension));

    // Set the Segment to the ReportRequest object.
    $request->setDimensions(array($pageDimension, $hour, $segmentDimensions));
    $request->setSegments($segments);

    $body = new Google_Service_AnalyticsReporting_GetReportsRequest();
    $body->setReportRequests(array($request));
    return $analytics->reports->batchGet($body);
}

/*
 * Create Segment function for GA
 */
function ssm_ga_createSegment($min, $max, $name)
{

    // Set a segment for hourly time range. For daily time range there is no neen id a segment.
    $dimensionFilter = new Google_Service_AnalyticsReporting_SegmentDimensionFilter();
    $dimensionFilter->setDimensionName('ga:hour');
    $dimensionFilter->setOperator('NUMERIC_BETWEEN');

    // $dimensionFilter->setExpressions($alert->get_expressions());
    $dimensionFilter->setMinComparisonValue($min);
    $dimensionFilter->setMaxComparisonValue($max);

    // Create Segment Filter Clause.
    $segmentFilterClause = new Google_Service_AnalyticsReporting_SegmentFilterClause();
    $segmentFilterClause->setDimensionFilter($dimensionFilter);

    // Create the Or Filters for Segment.
    $orFiltersForSegment = new Google_Service_AnalyticsReporting_OrFiltersForSegment();
    $orFiltersForSegment->setSegmentFilterClauses(array($segmentFilterClause));

    // Create the Simple Segment.
    $simpleSegment = new Google_Service_AnalyticsReporting_SimpleSegment();
    $simpleSegment->setOrFiltersForSegment(array($orFiltersForSegment));

    // Create the Segment Filters.
    $segmentFilter = new Google_Service_AnalyticsReporting_SegmentFilter();
    $segmentFilter->setSimpleSegment($simpleSegment);

    // Create the Segment Definition.
    $segmentDefinition = new Google_Service_AnalyticsReporting_SegmentDefinition();
    $segmentDefinition->setSegmentFilters(array($segmentFilter));

    // Create the Dynamic Segment.
    $dynamicSegment = new Google_Service_AnalyticsReporting_DynamicSegment();
    $dynamicSegment->setSessionSegment($segmentDefinition);
    $dynamicSegment->setName($name);

    // Create the Segments object.
    $segment = new Google_Service_AnalyticsReporting_Segment();
    $segment->setDynamicSegment($dynamicSegment);

    return $segment;
}


/**
 * Parses and prints the Analytics Reporting API V4 response.
 *
 * @param An Analytics Reporting API V4 response.
 */

 function updateDB($reports, $slug_filter = NULL, $post_type = 'post')
{

    // foreach ($response->getRows() as $row) {
    //     echo $row->getDimensionValues()[0]->getValue() . $row->getMetricValues()[0]->getValue() . '<br>';
    // }

    global $wpdb;
    // foreach ($response->getRows() as $row) {
        // $header = $report->getColumnHeader();
        // $rows = $report->getData()->getRows();

        $pagePaths_pageViews = array();

        foreach ($reports->getRows() as $row) {
            $dimensions = $row->getDimensionValues()[0]->getValue();
            $metrics = $row->getMetricValues()[0]->getValue();
            $pagePath = ltrim(rtrim($dimensions, '/'), '/');
            $pageViews = $metrics;

            $pagePath_e = explode('/', $pagePath);


            if (
                !isset($pagePath_e[0]) ||
                in_array($pagePath_e[0], array('', 'bluesfestvip', '100-greatest-movies-of-all-time')) ||
                !isset($pagePath_e[2])
            ) :
                continue;
            endif;

            // echo '<pre>'; print_r( $pagePath_e ); echo '</pre>';

            if (is_null($slug_filter)) :
                $pagePath_e2 = explode('-', $pagePath_e[2]);
                $post_id = end($pagePath_e2);
                if (is_numeric($post_id)) :
                    $pagePaths_pageViews[$post_id] = $pageViews;
                else :
                    if( empty( $pagePaths_pageViews[$post_id] ) ) :
                        $pagePaths_pageViews[$post_id] = $pageViews;
                    else :
                        $pagePaths_pageViews[$post_id] += $pageViews;
                    endif;
                    // $pagePaths_pageViews[$post_id] += $pageViews;
                endif;
            else :
            // if ( isset( $pagePath_e[1] ) ) :
            //     if ( ! isset( $pagePaths_pageViews[ $pagePath_e[1] ] ) ) :
            //         $pagePaths_pageViews[ $pagePath_e[1] ] = $pageViews;
            //     else :
            //         $pagePaths_pageViews[ $pagePath_e[1] ] += $pageViews;
            //     endif;
            // endif;
            endif;
            // print_r( $dimensions ); echo '<pre>'; echo( $metrics[0]->values[0] ); echo '</pre><br>';
        }

        arsort($pagePaths_pageViews);

        foreach ($pagePaths_pageViews as $pagePath => $pageViews) :
            if (is_null($pagePath) || '' == $pagePath) :
                unset($pagePaths_pageViews[$pagePath]);
                continue;
            endif;
            $post = get_post($pagePath); // get_page_by_path( $pagePath, OBJECT, $post_type );
            if (!is_null($post) && $post_type == $post->post_type && 'publish' == $post->post_status) :
                $wpdb->insert(
                    $wpdb->prefix . 'tbm_trending',
                    array(
                        'post_id' => $post->ID,
                        'post_type' => $post->post_type,
                        'pageviews' => $pageViews,
                    ),
                    array(
                        '%d', '%s', '%d'
                    )
                );
            endif;
        endforeach;

        $array_keys_pagePaths_pageViews = array_keys($pagePaths_pageViews);

        foreach ($pagePaths_pageViews as $article_id => $pageViews) :
            $top_article = get_post($article_id);
            if (!is_null($top_article) && $top_article->post_status == 'publish') :
                if (is_null($slug_filter) && !get_option('force_most_viewed')) :
                    update_option('most_viewed_yesterday', $top_article->ID);
                else :
                // update_option( 'most_viewed_yesterday_' . $slug_filter, $top_article->ID );
                endif;
                break;
            endif;
        endforeach;



        echo '<h1>Result:</h1><pre>';
        print_r($pagePaths_pageViews);
        echo '</pre>';
    //    exit;
}

// function updateDB($reports, $slug_filter = NULL, $post_type = 'post')
// {
//     global $wpdb;
//     for ($reportIndex = 0; $reportIndex < count($reports); $reportIndex++) {
//         $report = $reports[$reportIndex];
//         $header = $report->getColumnHeader();
//         $rows = $report->getData()->getRows();

//         $pagePaths_pageViews = array();

//         for ($rowIndex = 0; $rowIndex < count($rows); $rowIndex++) {
//             $row = $rows[$rowIndex];
//             $dimensions = $row->getDimensions();
//             $metrics = $row->getMetrics();
//             $pagePath = ltrim(rtrim($dimensions[0], '/'), '/');
//             $pageViews = $metrics[0]->values[0];

//             $pagePath_e = explode('/', $pagePath);

//             if (is_null($slug_filter)) :
//                 if (!isset($pagePaths_pageViews[$pagePath_e[0]])) :
//                     $pagePaths_pageViews[$pagePath_e[0]] = $pageViews;
//                 else :
//                     $pagePaths_pageViews[$pagePath_e[0]] += $pageViews;
//                 endif;
//             else :
//                 if (isset($pagePath_e[1])) :
//                     if (!isset($pagePaths_pageViews[$pagePath_e[1]])) :
//                         $pagePaths_pageViews[$pagePath_e[1]] = $pageViews;
//                     else :
//                         $pagePaths_pageViews[$pagePath_e[1]] += $pageViews;
//                     endif;
//                 endif;
//             endif;

//             //            print_r( $dimensions ); echo '<pre>'; echo( $metrics[0]->values[0] ); echo '</pre><br>';
//         }

//         arsort($pagePaths_pageViews);

//         foreach ($pagePaths_pageViews as $pagePath => $pageViews) :
//             if (is_null($pagePath) || '' == $pagePath) :
//                 unset($pagePaths_pageViews[$pagePath]);
//                 continue;
//             endif;
//             $post = get_page_by_path($pagePath, OBJECT, $post_type);
//             if (!is_null($post) && $post_type == $post->post_type && 'publish' == $post->post_status && !get_field('not_brand_safe', $post->ID)) :
//                 $wpdb->insert(
//                     $wpdb->prefix . 'tbm_trending',
//                     array(
//                         'post_id' => $post->ID,
//                         'post_type' => $post->post_type,
//                         'pageviews' => $pageViews,
//                     ),
//                     array(
//                         '%d', '%s', '%d'
//                     )
//                 );
//             endif;
//         endforeach;

//         $array_keys_pagePaths_pageViews = array_keys($pagePaths_pageViews);
//         $top_article_slug = isset($array_keys_pagePaths_pageViews[0]) ? $array_keys_pagePaths_pageViews[0] : NULL;

//         $top_article = get_page_by_path($top_article_slug, OBJECT, $post_type);

//         if (!$top_article || 'publish' != $top_article->post_status) {
//             $top_article_slug = isset($array_keys_pagePaths_pageViews[1]) ? $array_keys_pagePaths_pageViews[1] : NULL;
//             $top_article = get_page_by_path($top_article_slug, OBJECT, $post_type);
//         }

//         if (!$top_article || 'publish' != $top_article->post_status) {
//             $top_article_slug = isset($array_keys_pagePaths_pageViews[2]) ? $array_keys_pagePaths_pageViews[2] : NULL;
//             $top_article = get_page_by_path($top_article_slug, OBJECT, $post_type);
//         }


//         if ($top_article && 'publish' == $top_article->post_status) :


//             echo '<pre>';
//             print_r($pagePaths_pageViews);
//             echo '</pre>';


//             if (!is_null($top_article)) :
//                 if (is_null($slug_filter)) :
//                     if (!get_option('force_most_viewed')) :
//                         update_option('most_viewed_yesterday', $top_article->ID);
//                         echo $top_article->ID . ' | ' . $top_article_slug . '<br><br>';
//                         break;
//                     endif;
//                 else :
//                     update_option('most_viewed_yesterday_' . $slug_filter, $top_article->ID);
//                     echo $top_article->ID . ' | ' . $top_article_slug . '<br><br>';
//                     break;
//                 endif;
//             endif;
//         endif; // If $top_article_slug is NOT null i.e. found first key in the pageviews array



//     }
//     //    exit;
// }
