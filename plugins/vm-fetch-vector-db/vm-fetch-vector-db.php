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

if ( is_admin() ) {
    add_action(
        'admin_init',
        function (): void {
            $vm_fetch_vector_db = VmFetchVectorDb::getInstance();
            $vm_fetch_vector_db->init();
        }
    );
}
