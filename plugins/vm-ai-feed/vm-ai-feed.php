<?php

/**
 * Plugin Name: Vinyl Media: AI Feed
 * Description: AI-powered content management system for research, article generation, and WordPress publishing
 * Version: 1.0.0
 * Author: Vinyl Media Dev
 * Author URI: https://vinyl.media
 *
 * @package VM\AIFeed
 * @since 1.0.0
 */

if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

// Load plugin-local Composer autoloader if available.
$plugin_autoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($plugin_autoload)) {
	require_once $plugin_autoload;
}

// Load core classes.
require_once __DIR__ . '/includes/Core/Config.php';
require_once __DIR__ . '/includes/Core/Plugin.php';

// Load helpers.
require_once __DIR__ . '/includes/Helpers/UIHelpers.php';
require_once __DIR__ . '/includes/Helpers/DateHelpers.php';
require_once __DIR__ . '/includes/Helpers/StatusHelpers.php';

// Load content processors.
require_once __DIR__ . '/includes/Content/Markdown.php';
require_once __DIR__ . '/includes/Content/Embeds.php';
require_once __DIR__ . '/includes/Content/BlockConverter.php';

// Load taxonomy extensions.
require_once __DIR__ . '/includes/Taxonomy/CategoryAIMeta.php';

// Load admin components.
require_once __DIR__ . '/includes/Admin/Tables/ArticlesTable.php';
require_once __DIR__ . '/includes/Admin/Tables/QueueTable.php';
require_once __DIR__ . '/includes/Admin/Views/ArticleView.php';
require_once __DIR__ . '/includes/Admin/Views/QueueView.php';
require_once __DIR__ . '/includes/Admin/Pages/AIFeedAIConfigs.php';

use VM\AIFeed\Core\AIFeed;

// Initialize REST API fields (must run outside is_admin()).
\VM\AIFeed\Taxonomy\CategoryAIMeta::init();

// Admin-only initialization.
if ( is_admin() ) {
	$ai_feed = AIFeed::getInstance();
	$ai_feed->init();
}
