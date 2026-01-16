<?php

/**
 * Plugin Name: Vinyl Media: Fetch VectorDB
 * Description: Fetch article to VectorDB for AI
 * Version: 1.0
 * Author: Vinyl Media Dev
 */

// Exit if accessed directly

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/includes/class-vm-fetch-vector-db.php';
use VM\FetchVectorDb\VmFetchVectorDb;

// Set default API key on plugin activation
register_activation_hook(
    __FILE__,
    function (): void {
        // Only set the API key if it hasn't been set yet
        if ( ! get_option( 'vm_fetch_vector_db_api_key' ) ) {
            // Initialize with an empty value so the API key must be configured manually.
            add_option( 'vm_fetch_vector_db_api_key', '123' );
        }
    }
);

if ( is_admin() ) {
    add_action(
        'admin_init',
        function (): void {
            $vm_fetch_vector_db = VmFetchVectorDb::getInstance();
            $vm_fetch_vector_db->init();
        }
    );
}
