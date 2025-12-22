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

    public function fetchVectorDb( $post_id, $post ): void {
        if ( $post->post_status !== 'publish' ) {
            return;
        }

        $type = $post->post_type == 'post' ? 'posts' : $post->post_type;
        $type = $type == 'page' ? 'pages' : $type;

        if ( 'revision' === $type ) {
            return;
        }

        wp_remote_post(
            'https://collect.thebrag.media/api/v1/vectorize/posts/' . $post_id,
            array(
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'X-API-Key'    => 'WWSDE2khOwPN',
                ),
                'body'    => json_encode(
                    array(
                        'force' => true,
                    )
                ),
            )
        );
    }

    /**
        * Fetch LightRAG data for a post.
        *
        * @param int      $post_id The post ID.
        * @param WP_Post $post    The post object.
        *
        * @return void
        */
    public function fetchLightRAG( $post_id, $post ): void {
        if ( $post->post_status !== 'publish' ) {
            return;
        }

        $type = $post->post_type == 'post' ? 'posts' : $post->post_type;
        $type = $type == 'page' ? 'pages' : $type;

        if ( 'revision' === $type ) {
            return;
        }

        wp_remote_post(
            "https://search.tonedeaf.thebrag.com/api/v1/lightrag/articles/{$post_id}",
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-API-Key'    => 'WWSDE2khOwPN',
                ],
                'body'    => json_encode(
                    [
                        'force' => true,
                    ]
                ),
            ]
        );
    }
}
