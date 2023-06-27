<?php
/**
 * Plugin Name: TBM NZ Content
 * Plugin URI: https://thebrag.media/
 * Description:
 * Version: 1.0.0
 * Author: Toby Smith
 * Author URI:
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define TBM_NZ_CONTENT_PLUGIN_FILE.
if ( ! defined( 'TBM_NZ_CONTENT_PLUGIN_FILE' ) ) {
    define( 'TBM_NZ_CONTENT_PLUGIN_FILE', __FILE__ );
}

// Include the main TBM_NZ_CONTENT class.
if ( ! class_exists( 'TBM_NZ_CONTENT' ) ) {
    include_once dirname( __FILE__ ) . '/includes/class-tbm-nz-content.php';
}

/**
 * Main instance of TBM_NZ_CONTENT.
 *
 * Returns the main instance of TBM_NZ_CONTENT to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object TBM_NZ_CONTENT
 */

function TBM_NZ_CONTENT() {
    return TBM_NZ_CONTENT::get_instance();
}

TBM_NZ_CONTENT();