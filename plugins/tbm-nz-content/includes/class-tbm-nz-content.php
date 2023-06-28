<?php

/**
 * Class TBM_NZ_CONTENT
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'TBM_NZ_CONTENT' ) ) {
    class TBM_NZ_CONTENT {
        private static $instance;

        public static function get_instance() {
            if (null === self::$instance) {
                self::$instance = new self();
            }
    
            return self::$instance;
        }

        private function __construct() {
            // Actions
            add_action( 'rest_api_init', [ $this, '_rest_api_init' ] );
        }

        public function _rest_api_init() {
            register_rest_route('api/v1', '/articles/nz', [
                'methods' => 'GET',
                'callback' => [ $this, 'articles_nz_json_func' ],
            ]);
        }

        public function articles_nz_json_func( $data ) {
            $return = array();

            $posts_per_page = isset( $_GET['size'] ) ? (int) $_GET['size'] : 11;
            $paged = isset( $_GET['page'] ) ? (int) $_GET['page'] : 1;
            $offset = isset( $_GET['offset'] ) ? (int) $_GET['offset'] : 0;

            $timezone = new DateTimeZone( 'Australia/Sydney' );

            $args = [
                'post_status' => 'publish',
                'has_password'   => FALSE,
                'post_type' => ['pmc-nz', 'post'],
                'paged' => $paged,
                'posts_per_page' => $posts_per_page,
                'meta_query' => [
                    [
                        'key'   => 'add_to_nz_content',
                        'value' => '1',
                    ]
                ]
            ];

            if ( $offset > 0 ) {
                $args['offset'] = $offset;
            }

            $posts = new WP_Query( $args );

            global $post;
            
            if ($posts->have_posts()) {
                while ($posts->have_posts()) {
                    $posts->the_post();
                    $url = get_the_permalink();
                    $author = get_field('author') ? get_field('author') : get_the_author();
                    $category_names = $tag_names = array();

                    $post_categories = wp_get_post_categories( get_the_ID() );
                    
                    if ( count( $post_categories ) > 0) :
                        foreach ( $post_categories as $c ) :
                            $cat = get_category( $c );
                            array_push( $category_names, $cat->name );
                        endforeach;
                    endif;

                    $post_tags = wp_get_post_tags( get_the_ID() );
                    
                    if ( count( $post_tags ) > 0 ) :
                        foreach ( $post_tags as $t ) :
                            $tag = get_tag( $t );
                            
                            array_push( $tag_names, $tag->name );
                        endforeach;
                    endif;

                    $content = apply_filters( 'the_content', get_the_content() );

                    $src = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' );

                    $return[] = array(
                        'ID' => get_the_ID(),
                        'title' => get_the_title(),
                        'link' => $url,
                        'guid' => get_the_guid(),
                        'publish_date' => mysql2date( 'c', get_post_time('c', true), false ),
                        'description' => get_the_excerpt(),
                        'image' => $src[0],
                        'author' => $author,
                        'categories' => $category_names,
                        'tags' => $tag_names,
                        // 'content' => $content,
                        'site' => get_bloginfo( 'name' ) . ' NZ',
                    );
                }
            }
            
            return $return;
        }
    }
}