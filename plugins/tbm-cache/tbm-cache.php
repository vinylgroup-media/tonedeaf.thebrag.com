<?php

/**
* Plugin Name: The Brag Media: Cache
* Description: Purge various caching methods
* Version: 1.0
* Author: Toby Smith
*/

# Exit if accessed directly

if ( ! defined( 'ABSPATH' ) ) exit;

# Init!

if( is_admin() ) {
    add_action( 'admin_init', function() {
        require_once( dirname( __FILE__ ) . '/includes/class-tbm-cache.php' );

        $tbm_cache = TBM_Cache::get_instance();
        $tbm_cache->init();
    });
}
