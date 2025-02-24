<?php
class TBM_Cache {

    private static mixed $instance;

    private function __construct() {}

    public static function get_instance() {
        if ( !isset( self::$instance ) ) {
            $c = __CLASS__;
            self::$instance = new $c();
        }

        return self::$instance;
    }


    # Initialise

    public function init(): void {
        add_action( 'edit_post', [ $this, '_delete_cached' ], 100, 2 );
    }

    public function get_domain(): string {
        $site_url = site_url();

        return preg_replace("(^https?://)", "", $site_url );
    }

    public function _delete_cached($post_id, $post): void {
        if($post->post_status !== 'publish') {
            return;
        }

        $type = $post->post_type == 'post' ? 'posts' : $post->post_type;
        $type = $type == 'page' ? 'pages' : $type;

        if('revision' === $type) {
            return;
        }

       wp_remote_get('http://45.79.238.137:6001/purge/' . $this->get_domain() . '/' . $post_id . '/' . $type);
       wp_remote_get('http://172.105.183.4:6001/purge/' . $this->get_domain() . '/' . $post_id . '/' . $type );
    }
}
