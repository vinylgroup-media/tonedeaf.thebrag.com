<?php

namespace VM\FetchVectorDb;

class VmFetchVectorDb {

    private static VmFetchVectorDb $instance;

    public static function getInstance(): self {
        if ( ! isset( self::$instance ) || ! ( self::$instance instanceof self ) ) {
            $c              = __CLASS__;
            self::$instance = new $c();
        }

        return self::$instance;
    }

    public function init(): void {
        add_action( 'edit_post', [ $this, 'fetchVectorDb' ], 100, 2 );
        add_action( 'edit_post', [ $this, 'fetchLightRAG' ], 100, 2 );
    }

    /**
     * Fetch vector database data for a post.
     *
     * Triggered on the 'edit_post' action to send the post to the vectorization API.
     *
     * @param int     $post_id The post ID.
     * @param WP_Post $post    The post object.
     *
     * @return void
     */
    public function fetchVectorDb( $post_id, $post ): void {
        // Sanitize post_id to ensure it's a positive integer
        $post_id = absint( $post_id );

        if ( $post->post_status !== 'publish' ) {
            return;
        }

        $type = $post->post_type === 'post' ? 'posts' : $post->post_type;
        $type = $type === 'page' ? 'pages' : $type;

        if ( 'revision' === $type ) {
            return;
        }

        // Get API key from WordPress options
        $api_key = get_option( 'vm_fetch_vector_db_api_key', '' );

        // Return early if no API key is configured
        if ( empty( $api_key ) ) {
            error_log( 'VectorDB API Error: API key not configured' );
            return;
        }

        $response = wp_remote_post(
            "https://collect.thebrag.media/api/v1/vectorize/{$type}/{$post_id}",
            array(
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'X-API-Key'    => $api_key,
                ),
                'body'    => json_encode(
                    array(
                        'force' => true,
                    )
                ),
            )
        );

        // Check for errors and log if necessary
        if ( is_wp_error( $response ) ) {
            error_log( 'VectorDB API Error: ' . $response->get_error_message() );
        }
    }

    /**
     * Fetch LightRAG data for a post.
     *
     * Triggered on the 'edit_post' action to send the post to the LightRAG API.
     *
     * @param int     $post_id The post ID.
     * @param WP_Post $post    The post object.
     *
     * @return void
     */
    public function fetchLightRAG( $post_id, $post ): void {
        // Sanitize post_id to ensure it's a positive integer
        $post_id = absint( $post_id );

        if ( $post->post_status !== 'publish' ) {
            return;
        }

        $type = $post->post_type === 'post' ? 'posts' : $post->post_type;
        $type = $type === 'page' ? 'pages' : $type;

        if ( 'revision' === $type ) {
            return;
        }

        // Get API key from WordPress options
        $api_key = get_option( 'vm_fetch_vector_db_api_key', '' );

        // Return early if no API key is configured
        if ( empty( $api_key ) ) {
            error_log( 'LightRAG API Error: API key not configured' );
            return;
        }

        $response = wp_remote_post(
            "https://search.tonedeaf.thebrag.com/api/v1/lightrag/articles/{$post_id}",
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-API-Key'    => $api_key,
                ],
                'body'    => json_encode(
                    [
                        'force' => true,
                    ]
                ),
            ]
        );

        // Check for errors and log if necessary
        if ( is_wp_error( $response ) ) {
            error_log( 'LightRAG API Error: ' . $response->get_error_message() );
        }
    }
}
