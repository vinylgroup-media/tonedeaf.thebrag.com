<?php

function add_expires_header( $headers, $wp ) {
    $headers['X-Accel-Expires'] = '1440';
    return $headers;
}
add_filter( 'wp_headers', 'add_expires_header', 10, 2 );

function add_expires_header_json( $served, $result, $request ) {
    header('X-Accel-Expires: 120');
}
add_filter( 'rest_pre_serve_request', 'add_expires_header_json', 10, 3 );

/* Remove guttenburg blocks css */

function db_dequeue_block_styles(): void {
    wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
} 
add_action( 'wp_enqueue_scripts', 'db_dequeue_block_styles', 100 );

/* Replace jQuery CDN */

function load_jquery_from_google_cdn() {
    if (!is_admin()) {
        wp_deregister_script('jquery');
        wp_register_script('jquery', 'https://code.jquery.com/jquery-3.7.1.min.js', [], '3.7.1');
        wp_enqueue_script('jquery');
    }
}
add_action('wp_enqueue_scripts', 'load_jquery_from_google_cdn');

// define('ICONS_URL', get_template_directory_uri() . '/images/');
// define('CDN_URL', ICONS_URL);
define('ICONS_URL', 'https://cdn.thebrag.com/icons/');
define('CDN_URL', 'https://cdn-r2-2.thebrag.com/td/');
define('BRAG_API_KEY', '3ce4efdd-a39c-4141-80f7-08a828500831');
define('IMAGES_R2_CDN_URL', 'https://images-r2.thebrag.com/');

// Add default posts and comments RSS feed links to head.
// add_theme_support('automatic-feed-links');

/*
 * Let WordPress manage the document title.
 * By adding theme support, we declare that this theme does not use a
 * hard-coded <title> tag in the document head, and expect WordPress to
 * provide it for us.
 */
add_theme_support('title-tag');

/*
 * Enable support for Post Thumbnails on posts and pages.
 *
 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
 */
add_theme_support('post-thumbnails');

//add_image_size( 'thebrag-featured-image', 2000, 1200, true );
//add_image_size( 'thebrag-thumbnail-home', 150, 150, true );

// This theme uses wp_nav_menu() in two locations.
/*
 * To-do
 */
register_nav_menus(
    array(
        'top' => __('Top Menu', 'tonedeaf'),
    )
);

/*
 * Switch default core markup for search form, comment form, and comments
 * to output valid HTML5.
 */
add_theme_support('html5', array(
    'comment-form',
    'comment-list',
    'gallery',
    'caption',
    'search-form',
)
);

/*
 * This theme styles the visual editor to resemble the theme style,
 * specifically font, colors, and column width.
 */
add_editor_style(array('assets/css/editor-style.css', thebrag_fonts_url()));

/*
 * Added functions
 */

/**
 * Register custom fonts.
 */
function thebrag_fonts_url()
{
    $fonts_url = ''; {
        $font_families = array();

        $font_families[] = 'Droid Sans:400,700';

        $query_args = array(
            'family' => urlencode(implode('|', $font_families)),
        );

        $fonts_url = add_query_arg($query_args, 'https://fonts.googleapis.com/css');
    }
    return esc_url_raw($fonts_url);
}

/**
 * Handles JavaScript detection.
 *
 * Adds a `js` class to the root `<html>` element when JavaScript is detected.
 *
 * @since Twenty Seventeen 1.0
 */
function thebrag_javascript_detection()
{
    echo "<script>(function(html){html.className = html.className.replace(/\bno-js\b/,'js')})(document.documentElement);</script>\n";
}
add_action('wp_head', 'thebrag_javascript_detection', 0);

/**
 * Add a pingback url auto-discovery header for singularly identifiable articles.
 */
function thebrag_pingback_header()
{
    if (is_singular() && pings_open()) {
        printf('<link rel="pingback" href="%s">' . "\n", get_bloginfo('pingback_url'));
    }
}
add_action('wp_head', 'thebrag_pingback_header');

/**
 * If more than one page exists, return TRUE.
 */
function show_posts_nav()
{
    global $wp_query;
    return ($wp_query->max_num_pages > 1);
}

function string_limit_words($string, $word_limit)
{
    $words = explode(' ', $string, ($word_limit + 1));
    if (count($words) > $word_limit) {
        array_pop($words);
        return implode(' ', $words) . '...';
    }
    return implode(' ', $words);
}

// Article Category
register_taxonomy('style', array(''), array('hierarchical' => true, 'label' => 'Article Style', 'query_var' => true, 'rewrite' => array('slug' => 'style'), 'capabilities' => array('manage_terms' => 'manage_categories', 'edit_terms' => 'manage_categories', 'delete_terms' => 'manage_categories', 'assign_terms' => 'edit_posts', 'assign_terms' => 'edit_gallerys', 'assign_terms' => 'manage_categories'), 'show_ui' => true, 'public' => true));

register_taxonomy('venue', array('gallery'), array('hierarchical' => true, 'labels' => array('name' => 'Venues', 'singular_name' => 'Venue'), 'query_var' => true, 'rewrite' => array('slug' => 'venue'), 'capabilities' => array('manage_terms' => 'manage_categories', 'edit_terms' => 'manage_categories', 'delete_terms' => 'manage_categories', 'assign_terms' => 'edit_posts', 'assign_terms' => 'edit_gallerys', 'assign_terms' => 'manage_categories', 'assign_terms' => 'backstage_use'), 'show_ui' => true, 'public' => true));

register_meta( 'post', '_yoast_wpseo_focuskw', [ 'show_in_rest' => true, 'type'=> 'string', 'single'=>true ] );

register_taxonomy(
    'artist', 
    array('post'), 
    array(
        'hierarchical' => false, 
        'label' => 'Artist', 
        'query_var' => true, 
        'rewrite' => array(
            'slug' => 'about'
        ), 
        'capabilities' => array(
            'manage_terms' => 'manage_categories', 
            'edit_terms' => 'manage_categories', 
            'delete_terms' => 'manage_categories', 
            'assign_terms' => 'edit_posts', 
            'assign_terms' => 'manage_categories'
        ), 
        'show_ui' => true, 
        'public' => true,
        'show_in_rest' => true
    )
);


register_taxonomy(
    'genre',
    array('post'),
    array(
        'hierarchical' => true,
        'labels' => array(
            'name' => 'Genre',
            'singular_name' => 'Genre'
        ),
        'query_var' => true,
        'rewrite' => array('slug' => 'genre'),
        'capabilities' => array(
            'manage_terms' => 'edit_posts', 
            'edit_terms' => 'edit_posts', 
            'delete_terms' => 'edit_posts', 
            'assign_terms' => 'edit_posts', 
            'assign_terms' => 'edit_posts'
        ),
        'show_ui' => true,
        'public' => true,
        'show_in_rest' => true
    )
);


function load_js_css()
{
    wp_enqueue_script('scripts', CDN_URL . 'js/scripts.min.js', array('jquery'), '20240805.1', true);
    // wp_enqueue_script('scripts', get_template_directory_uri() . '/js/scripts.js', array('jquery'), time(), true);


    if (is_single()) {
        global $post;
        $args = array(
            'url' => admin_url('admin-ajax.php'),
            'exclude_posts' => isset($post) ? $post->ID : NULL,
            'current_post' => isset($post) ? $post->ID : NULL
        );
        wp_localize_script('scripts', 'tbm_load_next_post', $args);
    }

    $args = array(
        'ajax_url' => admin_url('admin-ajax.php')
    );
    wp_localize_script('scripts', 'global', $args);

    // wp_enqueue_script('lazysizes', get_template_directory_uri() . '/js/lazysizes.min.js', array(), '20181128', true);
}
add_action('wp_enqueue_scripts', 'load_js_css');

function get_post_excerpt_by_id($post_id)
{
    global $post;
    $post = get_post($post_id);
    setup_postdata($post);
    $the_excerpt = get_the_excerpt();
    wp_reset_postdata();
    return $the_excerpt;
}

/*
 * Theme Settings
 */
$themename = 'ToneDeaf';
$shortname = 'td';

$categories = get_categories('hide_empty=0&orderby=name');
$wp_cats = array();
foreach ($categories as $category_list) {
    $wp_cats[$category_list->cat_ID] = $category_list->cat_name;
}
//array_unshift( $wp_cats, 'Choose a category' );

/**
 * Removed custom query and used default WordPress feature to queried the post
 * @author Sushil Adhikari
 */
add_action('wp_ajax_nopriv_get_listing_news', 'ajax_listings_news');
add_action('wp_ajax_get_listing_news', 'ajax_listings_news');
function ajax_listings_news()
{
    $post_type = isset($_POST['type']) ? $_POST['type'] : 'post';
    $args = array(
        'post_type' => $post_type,
        'post_status' => 'publish',
        's' => $_POST['name'],
        'posts_per_page' => 10,
    );

    //    $args['date_query'] = array(
    //        'after' => date_i18n( 'Y-m-d', strtotime( '- 30 days' ) )
    //    );

    $query = get_posts($args);
    foreach ($query as $key => $post_data) {
        $return[] = array($post_data->ID, $post_data->post_title);
    }
    echo json_encode($return);
    wp_reset_postdata();
    wp_die();
}

//echo '<pre>' .print_r( $theme_options, true ) . '</pre>';

function td_theme_add_init()
{
    $file_dir = get_template_directory_uri(); //get_bloginfo('template_directory');

    wp_enqueue_style('admin', $file_dir . '/css/admin.css', false, '2.3', 'all');

    wp_enqueue_script('td-jquery-autocomplete', $file_dir . '/js/jquery.auto-complete.js', array('jquery'), '1.0', true);
    wp_enqueue_script('td-options-ajax-search', $file_dir . '/js/scripts-admin.js', array('jquery'), '1.1', true);
}

add_action('admin_init', 'td_theme_add_init');

/*
 * Author Social Media Links
 */
function td_author_contactmethods($contactmethods)
{
    $contactmethods['twitter'] = 'Twitter'; // Twitter
    $contactmethods['facebook'] = 'Facebook'; // Facebook
    $contactmethods['linkedin'] = 'LinkedIn'; // LinkedIn
    $contactmethods['instagram'] = 'Instagram'; // Instagram
    return $contactmethods;
}
add_filter('user_contactmethods', 'td_author_contactmethods', 10, 1);

add_action('init', 'fbInstantArticleRSS');
function fbInstantArticleRSS()
{
    add_feed('instant_articles', 'fbInstantArticle');
}
function fbInstantArticle()
{
    get_template_part('rss', 'instant_articles');
}
function iframe_wrapper_for_fb_instant($content)
{
    // match any iframes
    $pattern = '~<iframe.*</iframe>|<embed.*</embed>~';
    preg_match_all($pattern, $content, $matches);

    foreach ($matches[0] as $match) {
        // wrap matched iframe with figure
        $wrappedframe = '<figure class="op-interactive">' . $match . '</figure>';

        //replace original iframe with new in content
        $content = str_replace($match, $wrappedframe, $content);
    }

    return $content;
}
//add_filter('the_content', 'iframe_wrapper_for_fb_instant');

function td_remove_p_tags_around_iframes($content)
{
    $content = str_replace('&nbsp;', '', $content);
    $wraped_content = preg_replace(
        '/<p( style=\".*?\")?>(<iframe .*?><\/iframe>)(.*)?<\/p>/',
        '<figure class="op-interactive">$2</figure>',
        $content
    );
    $wraped_content = str_replace('width="100%"', 'width="400"', $wraped_content);
    return $wraped_content;
}
add_filter('the_content', 'td_remove_p_tags_around_iframes');

function td_remove_p_tags_around_script($content)
{
    $content = str_replace('&nbsp;', '', $content);
    $wraped_content = preg_replace(
        '/<p( style=\".*?\")?>(<script .*?><\/script>)(.*)?<\/p>/',
        '$2',
        $content
    );
    return $wraped_content;
}
add_filter('the_content', 'td_remove_p_tags_around_script');

function td_filter_ptags_on_images($content)
{
    if (function_exists('is_amp_endpoint') && !is_amp_endpoint()) {
        return preg_replace('/<p(.*)>(<img .* \/>)<\/p>/', '<figure>$2</figure>', $content);
    }
    return $content;
}
add_filter('the_content', 'td_filter_ptags_on_images');

function td_image_resize($attachment_id, $width, $height, $crop = true)
{
    $path = get_attached_file($attachment_id);
    if (!file_exists($path)) {
        return false;
    }

    $upload = wp_upload_dir();
    $path_info = pathinfo($path);
    $base_url = $upload['baseurl'] . str_replace($upload['basedir'], '', $path_info['dirname']);

    // Generate new size
    $resized = image_make_intermediate_size($path, $width, $height, $crop);
    if ($resized && !is_wp_error($resized)) {
        // Let metadata know about our new size.
        $key = sprintf('resized-%dx%d', $width, $height);
        $meta['sizes'][$key] = $resized;
        wp_update_attachment_metadata($attachment_id, $meta);
        return "{$base_url}/{$resized['file']}";
    }

    // Return original if fails
    return "{$base_url}/{$path_info['basename']}";
}

add_action('init', 'register_cpt_photo_gallery');

function register_cpt_photo_gallery()
{

    $labels = array(
        'name' => _x('Photo Galleries', 'photo_gallery'),
        'singular_name' => _x('Photo Gallery', 'photo_gallery'),
        'add_new' => _x('Add New', 'photo_gallery'),
        'add_new_item' => _x('Add New Photo Gallery', 'photo_gallery'),
        'edit_item' => _x('Edit Photo Gallery', 'photo_gallery'),
        'new_item' => _x('New Photo Gallery', 'photo_gallery'),
        'view_item' => _x('View Photo Gallery', 'photo_gallery'),
        'search_items' => _x('Search Photo Galleries', 'photo_gallery'),
        'not_found' => _x('No photo galleries found', 'photo_gallery'),
        'not_found_in_trash' => _x('No photo galleries found in Trash', 'photo_gallery'),
        'parent_item_colon' => _x('Parent Photo Gallery:', 'photo_gallery'),
        'menu_name' => _x('Photo Galleries', 'photo_gallery'),
    );

    $args = array(
        'labels' => $labels,
        'hierarchical' => false,
        'description' => 'Photos from gigs around Australia',
        'supports' => array('title', 'editor', 'thumbnail', 'author'),
        'taxonomies' => array('artist', 'venue', 'genre'),
        'public' => true,
        //        'capability_type'     => 'page',
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 5,
        'register_meta_box_cb' => 'add_gallery_metaboxes',
        'show_in_nav_menus' => true,
        'show_in_admin_bar' => true,
        'publicly_queryable' => true,
        'exclude_from_search' => false,
        'has_archive' => true,
        'query_var' => true,
        'can_export' => true,
        'rewrite' => array('slug' => 'photo', ),
        'capability_type' => array('page', 'photo_gallery'),
        'capabilities' => array(
            'publish_posts' => 'photo_gallery',
            'edit_posts' => 'photo_gallery',
            'edit_others_posts' => 'photo_gallery',
            'read_private_posts' => 'photo_gallery',
            'edit_post' => 'photo_gallery',
            'delete_post' => 'photo_gallery',
            'read_post' => 'photo_gallery',
            'publish_post' => 'photo_gallery',
        ),
    );

    register_post_type('photo_gallery', $args);

    // Add the Photo Gallery Meta Boxes
    function add_gallery_metaboxes()
    {
        add_meta_box('gallery_notice', 'Instructions', 'gallery_notice', 'photo_gallery', 'normal', 'high');
        add_meta_box('gallery_date', 'Date', 'gallery_date', 'photo_gallery', 'normal', 'high');
        //        add_meta_box('gallery_upload', 'Upload Photos', 'gallery_upload', 'photo_gallery', 'normal', 'high');
        add_meta_box('gallery_photographer', 'Photographer', 'gallery_photographer', 'photo_gallery', 'normal', 'high');
        add_meta_box('gallery_author', 'Author', 'gallery_author', 'photo_gallery', 'normal', 'high');
        add_meta_box('gallery_venue', 'Venue Override', 'gallery_venue', 'photo_gallery', 'side', 'low');
    }

    // The Gallery Notice Metabox
    function gallery_notice()
    {
        global $post;
        // Noncename needed to verify where the data originated
        echo '<input type="hidden" name="gallerymeta_noncename" id="gallerymeta_noncename" value="' .
            wp_create_nonce(plugin_basename(__FILE__)) . '" />';
        // Echo out the field
        echo '<p>Welcome to the photo gallery uploader for Tone Deaf. Please follow these instructions carefully so that we can publish your photos as quickly as possible.</p>';
        echo '<p>Firstly, please be sure to upload your photos by clicking the \'Attach Photos\' button, and not by clicking \'Add Media\'.</p>';
        echo '<p><strong>COMPRESS:</strong> Photos take up a lot of expensive disk space and use bandwidth, so please ensure you compress your photos before you upload them into the system.</p>';
        echo '<p><strong>DIMENSIONS:</strong> The width of your photos should be 1000px (that\'s width, not longest edge). This is a mandatory requirement for uploading photos.</p>';
        echo '<p><strong>RESOLUTION:</strong> Your photos must be 72 dpi (dots per inch) and no higher.</p>';
        echo '<p><strong>COLOURS:</strong> All your photos must use the RGB colour profile.</p>';
        echo '<p><strong>FILE FORMAT:</strong> All photos must be in the .jpg format.</p>';
        echo '<p><strong>LABELS:</strong> Please ensure each image is clearly labeled with the name of the act.</p>';
        echo '<p><strong>ORDER:</strong> Please upload your images either starting with the headliner and working backwards through the lineup, or jumbled up with your strongest shots at the top.</p>';
        echo '<p><strong>TAG VENUE:</strong> Select the venue/s the photos were taken at using the list to the right. You only need to select the venue, not the city. If the venue isn\'t available, please enter it manually in the box below the list.</p>';
        echo '<p><strong>TAG ARTISTS:</strong> Please tag all the artists whose photos you\'re uploading. Please use capitals for each word in their names, and no punctuation ie. \'King Gizzard And The Lizard Wizard\' rather than \'& the.</p>';
        echo '<p>If you are having any problems uploading your photos please email brandon.john@seventhstreet.media or poppy.reid@seventhstreet.media and we\'ll help get it sorted.</p>';
    }

    // The Gallery Upload Metabox
    function gallery_upload()
    {
        global $post;
        // Noncename needed to verify where the data originated
        echo '<input type="hidden" name="gallerymeta_noncename" id="gallerymeta_noncename" value="' .
            wp_create_nonce(plugin_basename(__FILE__)) . '" />';
        // Echo out the field
        $galleryid = $post->ID;
        echo '<p><a href="#" class="button insert-media add_media" data-editor="content" title="Add Media"><span class="wp-media-buttons-icon"></span>Upload Photos</a> </p>';
        echo '<p><em>Make sure to choose which photo you would like featured by selecting <strong>use as featured image</strong> in the uploader</em></p>';
    }

    // The Date Metabox
    function gallery_date()
    {
        global $post;
        // Noncename needed to verify where the data originated
        echo '<input type="hidden" name="gallerymeta_noncename" id="gallerymeta_noncename" value="' .
            wp_create_nonce(plugin_basename(__FILE__)) . '" />';
        // Get the location data if its already been entered
        $galleryfulldate = get_post_meta($post->ID, 'Full Date', true);
        // Echo out the field
        echo '<p>Enter the date the photos were taken in the following format: 31st March 2012</p>';
        echo '<input type="text" name="fulldate" value="' . $galleryfulldate . '" class="widefat" />';
    }

    // The Venue Metabox
    function gallery_venue()
    {
        global $post;
        // Noncename needed to verify where the data originated
        echo '<input type="hidden" name="gallerymeta_noncename" id="gallerymeta_noncename" value="' .
            wp_create_nonce(plugin_basename(__FILE__)) . '" />';
        // Get the location data if its already been entered
        $galleryvenue = get_post_meta($post->ID, 'Venue', true);
        // Echo out the field
        echo '<p>If the venue is not available from the list you can manually enter it here <strong>(ONLY USE THIS IF THE VENUE IS NOT AVAILABLE FROM THE VENUE LIST)</strong>:</p>';
        echo '<input type="text" name="Venue" value="' . $galleryvenue . '" class="widefat" />';
    }

    // The Photographer Name Metabox
    function gallery_photographer()
    {
        global $post;
        // Noncename needed to verify where the data originated
        echo '<input type="hidden" name="gallerymeta_noncename" id="gallerymeta_noncename" value="' .
            wp_create_nonce(plugin_basename(__FILE__)) . '" />';
        // Get the location data if its already been entered
        $galleryphotographer = get_post_meta($post->ID, 'Photographer', true);
        // Echo out the field
        //    echo '<p>If you did not take the photos you can override the photographer name here <strong>(ONLY USE THIS IF YOU DID NOT TAKE THE PHOTOS YOURSELF)</strong>:</p>';
        echo '<input type="text" name="Photographer" value="' . $galleryphotographer . '" class="widefat" />';
    }

    // The Author Name Metabox
    function gallery_author()
    {
        global $post;
        // Noncename needed to verify where the data originated
        echo '<input type="hidden" name="gallerymeta_noncename" id="gallerymeta_noncename" value="' .
            wp_create_nonce(plugin_basename(__FILE__)) . '" />';
        // Get the location data if its already been entered
        $galleryauthor = get_post_meta($post->ID, 'Author', true);
        // Echo out the field
        echo '<input type="text" name="Author" value="' . $galleryauthor . '" class="widefat" />';
    }

    // Save the Metabox Data
    function wpt_save_photographer_meta($post_id, $post)
    {
        // verify this came from the our screen and with proper authorization,
        // because save_post can be triggered at other times
        if (isset($_POST['gallerymeta_noncename']) && !wp_verify_nonce($_POST['gallerymeta_noncename'], plugin_basename(__FILE__))) {
            return; // $post->ID;
        }
        // Is the user allowed to edit the post or page?
        if (!current_user_can('edit_post', $post->ID))
            return $post->ID;
        // OK, we're authenticated: we need to find and save the data
        // We'll put it into an array to make it easier to loop though.
        $photographer_meta['Full Date'] = isset($_POST['fulldate']) ? $_POST['fulldate'] : '';
        $photographer_meta['Venue'] = isset($_POST['Venue']) ? $_POST['Venue'] : '';
        $photographer_meta['Photographer'] = isset($_POST['Photographer']) ? $_POST['Photographer'] : '';
        $photographer_meta['Author'] = isset($_POST['Author']) ? $_POST['Author'] : '';
        // Add values of $events_meta as custom fields
        foreach ($photographer_meta as $key => $value) { // Cycle through the $events_meta array!
            if ($post->post_type == 'revision')
                return; // Don't store custom data twice
            $value = implode(',', (array) $value); // If $value is an array, make it a CSV (unlikely)
            if (get_post_meta($post->ID, $key, FALSE)) { // If the custom field already has a value
                update_post_meta($post->ID, $key, $value);
            } else { // If the custom field doesn't have a value
                add_post_meta($post->ID, $key, $value);
            }
            if (!$value)
                delete_post_meta($post->ID, $key); // Delete if blank
        }
    }
    add_action('save_post', 'wpt_save_photographer_meta', 1, 2); // save the custom fields

    add_action("manage_posts_custom_column", "photo_gallery_custom_columns");
    add_filter("manage_edit-photo_gallery_columns", "photo_gallery_edit_columns");

    function photo_gallery_edit_columns($columns)
    {
        $columns = array(
            "cb" => "<input type=\"checkbox\" />",
            "title" => "Title",
            "photodate" => "Date",
            "photovenue" => "Venue",
            "photoartists" => "Artists",
            "genre" => "Genre"
        );

        return $columns;
    }
    function photo_gallery_custom_columns($column)
    {
        global $post;

        switch ($column) {
            case "photodate":
                $custom = get_post_custom();
                echo isset($custom["Full Date"]) ? date('M j, Y', strtotime($custom["Full Date"][0])) : the_time('M j, Y');
                ;
                break;
            case "photovenue":
                $custom = get_post_custom();
                echo get_the_term_list($post->ID, 'venue', '', ', ', '');
                break;
            case "photoartists":
                echo get_the_term_list($post->ID, 'artist', '', ', ', '');
                break;
        }
    }
}

function photo_gallery_attachments($attachments)
{
    $fields = array(
        array(
            'name' => 'title',                         // unique field name
            'type' => 'text',                          // registered field type
            'label' => __('Title', 'photos'),    // label to display
            'default' => 'title',                         // default value upon selection
        ),
        array(
            'name' => 'caption',                       // unique field name
            'type' => 'text',                      // registered field type
            'label' => __('Caption', 'photos'),  // label to display
            'default' => 'caption',                       // default value upon selection
        ),
    );
    $args = array(
        'label' => 'Photos', // title of the meta box (string)
        'post_type' => array('photo_gallery'), // all post types to utilize (string|array)
        'position' => 'normal', // meta box position (string) (normal, side or advanced)
        'priority' => 'high', // meta box priority (string) (high, default, low, core)
        'filetype' => null,  // no filetype limit // allowed file type(s) (array) (image|video|text|audio|application)
        'note' => 'Attach photos here!', // include a note within the meta box (string)
        'append' => true, // by default new Attachments will be appended to the list but you can have then prepend if you set this to false
        'button_text' => __('Attach Photos', 'photos'), // text for 'Attach' button in meta box (string)
        'modal_text' => __('Attach', 'photos'), // text for modal 'Attach' button (string)
        'router' => 'browse', // which tab should be the default in the modal (string) (browse|upload)
        'post_parent' => false, // whether Attachments should set 'Uploaded to' (if not already set)
        'fields' => $fields, // fields array
    );

    $attachments->register('photo_gallery_attachments', $args); // unique instance name
}

add_action('attachments_register', 'photo_gallery_attachments');

add_filter('attachments_default_instance', '__return_false');

// Get First Sentence of the $string
function tb_first_sentence($string)
{
    // First remove unwanted spaces
    $string = str_replace(" .", ".", $string);
    $string = str_replace(" ?", "?", $string);
    $string = str_replace(" !", "!", $string);
    // Find periods, exclamation- or questionmarks with a word before but not after.
    preg_match('/^.*[^\s](\.|\?|\!)/U', $string, $match);
    return isset($match[0]) ? $match[0] : '';
}

add_filter('document_title_separator', 'tb_document_title_separator');
function tb_document_title_separator($sep)
{
    $sep = "|";
    return $sep;
}

// Add Featured Image to RSS Feed Item
//add_action('rss2_item', 'add_my_rss_node');
function add_my_rss_node()
{
    global $post;
    if (has_post_thumbnail($post->ID)):
        $thumbnail = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'medium');
        echo ("<image>{$thumbnail[0]}</image>");
    endif;
}

// Add Custom Author to RSS Feed Item
//add_action('rss2_item', 'add_author_field_node');
function add_author_field_node()
{
    global $post;
    echo '<author>';
    if (get_field('Author', $post->ID)) {
        echo '<![CDATA[' . get_field('Author', $post->ID) . ']]>';
    } else {
        the_author_meta('display_name', $post->post_author);
    }
    echo '</author>';
}

// Defer JS files
function defer_parsing_of_js($url)
{
    if (!is_admin()) { //  && function_exists('is_amp_endpoint') && !is_amp_endpoint()) {
        if (FALSE === strpos($url, '.js'))
            return $url;
        if (strpos($url, 'jquery.js') || strpos($url, 'jquery.min.js') || strpos($url, 'fuseplatform') || strpos($url, 'amp'))
            return $url;
        if (strpos($url, 'amp'))
            return $url;
        return "$url' defer ";
    }
    return $url;
}
add_action('init', function () {
    add_filter('clean_url', 'defer_parsing_of_js', 11, 1);
});

// AMP - START */
add_action('amp_post_template_css', 'ssm_amp_additional_css_styles');
function ssm_amp_additional_css_styles($amp_template)
{
    // only CSS here please...
    ?>
    html { background: #ffffff; }
    body { font-family: 'Sans-serif', 'Arial'; background: #ffffff; }
    a, a:visited, a:hover, a:active, a:focus { color: #2982b3; }
    amp-img{max-width: 100%;height:auto;}
    .amp-wp-header {
    padding: 0;
    background: #fff;
    position: absolute; top: 0; margin: auto; width: 100%; z-index: 999999;
    }
    .amp-wp-header div.amp-wp-header-inner {
    position: fixed;
    top: 0;
    background: #fff;
    margin: auto;
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
    padding: 7px;
    border-bottom: 1px solid #ccc;
    }
    .amp-wp-header a {
    background-image: url( '
    <?php echo CDN_URL; ?>Tone-Deaf-100px.png' );
    background-repeat: no-repeat;
    background-size: contain;
    display: block;
    height: 30px;
    width: 170px;
    margin: 0 auto;
    text-indent: -9999px;
    background-position: center;
    }
    .amp-wp-title { color: #0f0c0c; }
    .amp-wp-article, .amp-wp-article-header { margin-top: 0; background: #fff; }
    .amp-wp-article { padding: 55px 0 10px 0; }
    #pagination { border-top: 1px solid #ccc; }
    #pagination .prev a, #pagination .next a {
    display: block;
    margin-bottom: 12px;
    background: #fefefe;
    text-decoration: none;
    font-size: 0.8rem;
    padding: 5px 15px;
    color: #666;
    }
    #pagination .prev a { text-align: left; }
    #pagination .next a { text-align: right; }
    .related-stories-wrap { background:#1fcabf; background: #fff; margin-top: 0px; padding: 10px 0; width: 100%; border-top:
    1px solid #cecece; }
    .related-stories-wrap .title { margin:0 0 15px 0; padding:0 10px; text-transform:uppercase; font-size:20px;
    line-height:22px; }
    .related-story { min-height: 100px; clear: both; border-bottom: 1px solid #dedede; padding: 10px 0; }
    .related-story .post-thumbnail { float: left; overflow: hidden; padding: 0 10px; }
    .related-story .post-thumbnail amp-img { width: 100px; height: auto; }
    .related-story .post-content { padding: 0 10px; margin-left: 160px; }
    .related-story .post-content .excerpt { font-size: 0.8rem; line-height: 1.2rem; }
    .related-story h2 { font-size: 1.2rem; line-height: 1rem; margin: 0 0 5px 0; }
    .related-story a { text-decoration: none; font-size: 14px; }
    .share-buttons-bottom {
    position:fixed; text-align: center; bottom: 0; padding-top: 10px; width: 100%; background: #fff; z-index: 9999;
    }
    .hamburger {
    position: relative;
    padding: 9px 10px;
    background-color: transparent;
    background-image: none;
    border: 1px solid transparent;
    border-radius: 4px;
    }
    .hamburger .icon-bar {
    display: block;
    width: 22px;
    height: 2px;
    border-radius: 1px;
    background-color: #888;
    }
    .hamburger .icon-bar+.icon-bar {
    margin-top: 4px;
    }
    amp-sidebar {
    width: 318px;
    background-color: #0f0c0c;
    }
    amp-sidebar .menu {
    margin: 0;
    background-color: #0f0c0c;
    box-shadow: 0 100vh 0 100vh #000;
    }
    amp-sidebar .menu li {
    padding: 0;
    border: none;
    border-bottom: 1px solid #303030;
    }
    amp-sidebar .menu li a {
    font-size: 1.25rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: flex-start;
    height: auto;
    padding: .8125rem 1.25rem;
    color: #fff;
    text-decoration: none;
    line-height: 1;
    }
    .comp-footer a {
    display: block;
    background-color: #fff;
    border-radius: 1rem;
    padding: 1rem;
    text-align: center;
    text-decoration: none;
    color: #1E81EF;
    border: 1px solid #1E81EF;
    box-shadow: 0 0 5px;
    margin: 2rem auto;
    }
    amp-social-share.rounded {
    border-radius: 50%;
    background-size: 80%;
    }
    <?php
}
add_filter('amp_content_max_width', 'ssm_amp_change_content_width');
function ssm_amp_change_content_width($content_max_width)
{
    return 940;
}

add_filter('amp_post_article_header_meta', 'ssm_amp_remove_time_meta');
function ssm_amp_remove_time_meta($meta_parts)
{
    foreach (array_keys($meta_parts, 'meta-time', true) as $key) {
        unset($meta_parts[$key]);
    }
    return $meta_parts;
}

add_filter('amp_post_template_metadata', 'ssm_amp_modify_json_metadata', 10, 2);
function ssm_amp_modify_json_metadata($metadata, $post)
{
    //    $metadata['@type'] = 'BlogPosting';
    if (!in_category('evergreen', $post)):
        $metadata['@type'] = 'NewsArticle';
    endif;
    if (get_field('author')) {
        $metadata['author']['name'] = get_field('author');
    } else if (get_field('Author')) {
        $metadata['author']['name'] = get_field('Author');
    }
    $metadata['publisher']['logo'] = array(
        '@type' => 'ImageObject',
        'url' => get_template_directory_uri() . '/images/Tone-Deaf-300px.png',
        'height' => 60,
        'width' => 225,
    );
    if (!isset($metadata['image'])) {
        $metadata['image'] = array(
            '@type' => 'ImageObject',
            'url' => get_template_directory_uri() . '/images/Tone-Deaf-300px.png',
            'height' => '80',
            'width' => '300',
        );
    }
    return $metadata;
}

/* AMP - END */

// Remove srcset for image html
add_filter('wp_calculate_image_srcset', '__return_false');


function addhttp($url)
{
    if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
        $url = "http://" . $url;
    }
    return $url;
}

function ssm_insert_after_paragraph($insertion, $paragraph_id, $content)
{
    $closing_p = '</p>';
    $paragraphs = explode($closing_p, $content);
    foreach ($paragraphs as $index => $paragraph) {
        if (trim($paragraph)) {
            $paragraphs[$index] .= $closing_p;
        }
        if ($paragraph_id == $index + 1) {
            $paragraphs[$index] .= $insertion;
        }
    }
    return implode('', $paragraphs);
}

function ssm_social_sharing_buttons_func($style, $show_text = true)
{
    global $post;
    $post_url = (get_permalink());

    $post_title = str_replace(' ', '%20', get_the_title());

    $twitterURL = 'https://twitter.com/intent/tweet?text=' . $post_title . '&amp;url=' . urlencode($post_url . '?utm_source=Twitter&amp;utm_content=Twitter_share_btn');
    $facebookURL = 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($post_url . '?utm_source=Facebook&utm_content=FB_share_btn');
    //    $googleURL = 'https://plus.google.com/share?url=' . $post_url . '&amp;text=' . $post_title . '&amp;hl=en_AU';
    $redditURL = 'https://reddit.com/submit?url=' . $post_url . '&amp;title=' . urlencode($post_title . '?utm_source=Reddit&utm_content=Reddit_share_btn');
    $whatsappURL = 'https://wa.me/?text=' . urlencode(get_the_title()) . ' ' . urlencode($post_url . '?utm_source=Whatsapp&utm_content=WA_share_btn');

    $content = '<div class="social-share-buttons-' . $style . ' nav">';
    $content .= '<a class="social-share-link social-share-facebook nav-link" id="social-share-facebook-' . $style . '" href="' . $facebookURL . '" target="_blank" data-type="share-fb"><span class="d-none d-lg-inline' . (!$show_text ? ' d-none' : '') . '">Share</span> <i class="fa fa-facebook"></i></a>';
    $content .= '<a class="social-share-link social-share-twitter nav-link" id="social-share-twitter-' . $style . '" href="' . $twitterURL . '" target="_blank" data-type="share-twitter"><span class="d-none d-lg-inline' . (!$show_text ? ' d-none' : '') . '">Tweet</span> <i class="fa fa-twitter"></i></a>';
    //    $content .= '<a class="social-share-link social-share-google nav-link" id="social-share-google-' . $style . '" href="' . $googleURL . '" target="_blank" data-type="share-google"><i class="fa fa-google-plus"></i></a>';
    $content .= '<a class="social-share-link social-share-reddit nav-link" id="social-share-reddit-' . $style . '" href="' . $redditURL . '" target="_blank" data-type="share-reddit"><i class="fa fa-reddit-alien"></i></a>';
    $content .= '<a class="social-share-link social-share-whatsapp nav-link" id="social-share-whatsapp-' . $style . '" href="' . $whatsappURL . '" target="_blank" data-type="share-whatsapp"><i class="fa fa-whatsapp"></i></a>';
    $content .= '</div>';
    echo $content;
}
;
add_action('ssm_social_sharing_buttons', 'ssm_social_sharing_buttons_func', 10, 2);

// Remove dashicons in frontend for unauthenticated users
add_action('wp_enqueue_scripts', 'bs_dequeue_dashicons');
function bs_dequeue_dashicons()
{
    if (!is_user_logged_in()) {
        wp_deregister_style('dashicons');
    }
}

//add_action('wp_print_styles', 'show_all_styles');
function show_all_styles()
{
    global $wp_styles;

    $wp_styles->all_deps($wp_styles->queue);

    $handles = $wp_styles->to_do;

    $css_code = '';

    $merged_file_location = get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'style-combined.css';

    foreach ($handles as $handle) {
        $src = strtok($wp_styles->registered[$handle]->src, '?');

        if (strpos($src, 'http') !== false) {
            $site_url = site_url();

            if (strpos($src, $site_url) !== false)
                $css_file_path = str_replace($site_url, '', $src);
            else
                $css_file_path = $src;

            $css_file_path = ltrim($css_file_path, '/');
        } else {
            $css_file_path = ltrim($src, '/');
        }
        if (file_exists($css_file_path)) {
            $css_code .= "/*** " . $handle . " ***/ \n" . file_get_contents($css_file_path) . "\n\n";
        }
    }

    file_put_contents($merged_file_location, $css_code);

    wp_enqueue_style('site-style', get_stylesheet_directory_uri() . '/css/style-combined.css');

    foreach ($handles as $handle) {
        wp_deregister_style($handle);
    }
}

/**
 * Enable unfiltered_html capability for Editors.
 *
 * @param  array  $caps    The user's capabilities.
 * @param  string $cap     Capability name.
 * @param  int    $user_id The user ID.
 * @return array  $caps    The user's capabilities, with 'unfiltered_html' potentially added.
 */
function km_add_unfiltered_html_capability_to_editors($caps, $cap, $user_id)
{
    if ('unfiltered_html' === $cap && user_can($user_id, 'editor')) {
        $caps = array('unfiltered_html');
    }
    return $caps;
}
add_filter('map_meta_cap', 'km_add_unfiltered_html_capability_to_editors', 1, 3);

/*
 * Restrict Image Upload Size
 */
add_filter('wp_handle_upload_prefilter', 'ssm_limit_image_size');
function ssm_limit_image_size($file)
{
    $errors = array();
    if (strpos($file['type'], 'image') !== false) {
        $filename = $file['name'];
        if (
            strpos(str_replace(array('-', '_', ' '), '', strtolower($filename)), 'screenshot') !== false
            ||
            strpos(str_replace(array('-', '_', ' '), '', strtolower($filename)), 'untitled') !== false
        ) {
            array_push($errors, '(+) Please rename the file before uploading.');
        }

        // Calculate the image size in KB
        $file_size = $file['size'] / 1024;

        $image = getimagesize($file['tmp_name']);
        $maximum = array(
            'width' => '2000',
            'height' => '2000'
        );
        $image_width = $image[0];
        $image_height = $image[1];

        if ($image_width > $maximum['width'] || $image_height > $maximum['height']) {
            array_push($errors, '(+) Image dimensions are too large. Maximum size is ' . $maximum['width'] . ' x ' . $maximum['height'] . ' pixels. Uploaded image is ' . $image_width . ' x ' . $image_height . ' pixels.');
        }

        // File size limit in KB
        $limit = 500;

        if (($file_size > $limit))
            array_push($errors, '(+) Uploaded file is too large. It has to be smaller than ' . $limit . 'KB');
    }
    if (!empty($errors)) {
        $file['error'] = implode(" ", $errors);
    }
    return $file;
}

// Add JS to make Alt Text compulsory
add_action('admin_footer', function () {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            var checkForAlt = function (showNotice) {
                var showNotice = (typeof showNotice !== 'undefined') ? showNotice : false;
                var $altText = $('.media-modal-content label[data-setting="alt"] input');
                if (!$altText.length) {
                    $altText = $('.media-frame-content .media-embed .embed-media-settings .column-settings label.alt-text input');
                }
                var $parent = $('.media-frame-toolbar .media-toolbar-primary');
                //                if ( ! $altText.length ) { // No image selected in the first place; bail out
                //                    return;
                //                }
                if (!$altText.length || $altText.val().length) {
                    $parent.addClass('ssm-has-alt-text');
                    $altText.removeClass('ssm-alt-error');
                    return true;
                } else {
                    $parent.removeClass('ssm-has-alt-text');
                    if (showNotice) {
                        alert('Missing Alt Text!');
                        $altText.focus();
                    }
                    $altText.addClass('ssm-alt-error');
                    return false;
                }
            };
            // Bind to keyup
            $('body').on('keyup', '.media-modal-content label[data-setting="alt"] input', function () {
                checkForAlt();
            });
            // Bind to the 'Inesert into post' button
            $('body').on('mouseenter mouseleave click', '.media-frame-toolbar .media-toolbar-primary', function (e) {
                checkForAlt(e.type === "click");
            });
        });
    </script>
    <style type="text/css">
        .media-frame-toolbar .media-toolbar-primary {
            position: relative;
        }

        .media-frame-toolbar .media-toolbar-primary:after {
            display: block;
            background: transparent;
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
        }

        .media-frame-toolbar .media-toolbar-primary.ssm-has-alt-text:after {
            display: none;
        }

        .ssm-alt-error {
            border: 1px solid #ff0000 !important;
        }
    </style>
<?php });

//add_action( 'wp_head', 'ssm_inject_alexa_code' );
function ssm_inject_alexa_code()
{
    echo '<script type="text/javascript">
_atrk_opts = { atrk_acct:"O3NOq1WyR620WR", domain:"thebrag.com",dynamic: true};
(function() { var as = document.createElement(\'script\'); as.type = \'text/javascript\'; as.async = true; as.src = "https://certify-js.alexametrics.com/atrk.js"; var s = document.getElementsByTagName(\'script\')[0];s.parentNode.insertBefore(as, s); })();
</script>
<noscript><img src="https://certify.alexametrics.com/atrk.gif?account=O3NOq1WyR620WR" style="display:none" height="1" width="1" alt="" /></noscript>';
}

add_action('init', 'ssm_rss_flipboard');
function ssm_rss_flipboard()
{
    add_feed('flipboard', 'ssm_rss_flipboard_func');
}
function ssm_rss_flipboard_func()
{
    get_template_part('rss', 'flipboard');
}

add_action('init', 'ssm_rss_external');
function ssm_rss_external()
{
    add_feed('external', 'ssm_rss_external_func');
}
function ssm_rss_external_func()
{
    get_template_part('rss', 'external');
}

// REST JSON number of articles published in year|month for GA report
function ssm_number_of_posts_func($data)
{
    $date_e = explode('|', urldecode($data['month_year']));
    $date = $date_e[0] . '-' . $date_e[1] . '-01';
    $posts = new WP_Query(
        array(
            'date_query' => array(
                'after' => date_i18n('Y-m-01', strtotime($date)),
                'before' => date_i18n('Y-m-t', strtotime($date)),
            ),
            'post_type' => array('post', 'freeshit', 'issue', 'podcast', 'snaps'),
            'post_status' => 'publish',
            'posts_per_page' => -1
        )
    );
    return $posts->post_count;
}
add_action('rest_api_init', function () {
    register_rest_route('ssm_posts/v1', '/month-year/(?P<month_year>\d+(\%7C)\d+)', array(
        'methods' => 'GET',
        'callback' => 'ssm_number_of_posts_func',
        'permission_callback' => '__return_true',
    )
    );
});

// Open links in new Window (or Tab)
function ssm_autoblank($content)
{
    $content = preg_replace('/(<a.*?)[ ]?target="(.*?)"(.*?)/', '$1$3', $content);
    $content = preg_replace("/<a(.*?)>/", "<a$1 target=\"_blank\">", $content);
    return $content;
}
// add_filter('the_content', 'ssm_autoblank');

function tbm_add_rel_to_links($content)
{
    $content = preg_replace_callback(
        '/<a[^>]*href=["|\']([^"|\']*)["|\'][^>]*>([^<]*)<\/a>/i',
        function ($m) {
            if ((strpos(strtolower($m[1]), $_SERVER['HTTP_HOST']) !== false) || (substr($m[1], 0, 1) == "#")) {
                // return $m[0];
                return '<a href="' . $m[1] . '" target="_blank">' . $m[2] . '</a>';
            } else {
                return '<a href="' . $m[1] . '" rel="noreferrer" target="_blank">' . $m[2] . '</a>';
            }
        },
        $content
    );

    return $content;
}

add_filter('the_content', 'tbm_add_rel_to_links', 100);

function get_fuse_tag($tag, $page = '')
{
    return render_ad_tag($tag, $page);
}

// Optional parameter to limit number of items in RSS2 feed
function feed_limit_ppp($query)
{
    if ($query->is_feed('rss2') && isset($_GET['size']) && '' != (int) $_GET['size']) {
        add_filter('option_posts_per_rss', function () {
            return (int) $_GET['size'];
        });
    }
}
add_action('pre_get_posts', 'feed_limit_ppp');

function shortcode_do($atts, $content = null)
{
    if ('pullquote' == $atts['action']) {
        return '<span class="pullquote">' . $content . '</span>';
    }
    return $content;
}
add_shortcode('do', 'shortcode_do');

add_filter('the_time', 'dynamictime');
function dynamictime()
{
    global $post;
    $date = $post->post_date;
    $time = get_post_time('G', true, $post);
    $mytime = time() - $time;
    if ($mytime > 0 && $mytime < 7 * 24 * 60 * 60)
        $mytimestamp = sprintf(__('%s ago'), human_time_diff($time));
    else
        $mytimestamp = date(get_option('date_format'), strtotime($date));
    return $mytimestamp;
}

add_post_type_support('page', 'excerpt');

add_action('init', 'ssm_chat_bot_rss');
function ssm_chat_bot_rss()
{
    add_feed('chat_bot', 'ssm_chat_bot_articles');
    add_feed('chat_bot2', 'ssm_chat_bot_articles');
    add_feed('chat_bot3', 'ssm_chat_bot_articles');
}
function ssm_chat_bot_articles()
{
    get_template_part('rss', 'chat_bot');
    get_template_part('rss', 'chat_bot2');
    get_template_part('rss', 'chat_bot3');
}

/*
 * Exclude Giveaways (more than 60 days old) from Yoast Sitemap
 * + Remove canonical URL from AMP pages
 * + Add Robots noindex for AMP pages
 */
/* add_filter('wpseo_exclude_from_sitemap_by_post_ids', function ($ex) {
    $args = array(
        'numberposts'    => -1,
        'post_type'    => 'post',
        'category'    => 3925,
        'date_query' => array(
            'before' => date('Y-m-d', strtotime('-60 days'))
        )
    );
    $posts = get_posts($args);
    $excludes = array();
    foreach ($posts as $post) {
        $excludes[] = $post->ID;
    }

    return array_merge($ex, $excludes);

    return $excludes;
}); */

add_action('amp_post_template_head', 'ssm_amp_post_template_add', 9);
function ssm_amp_post_template_add($amp_template)
{
    $post_id = $amp_template->post->ID;
    if (in_category(3925, $post_id) && strtotime($amp_template->post->post_date) < strtotime('-60 days')):
        echo '<meta name="robots" content="noindex">';
        remove_action('amp_post_template_head', 'amp_post_template_add_canonical');
    endif;
}

/*
 * Allow Photographer to publish Photo Galleries
 */
function modify_snaps_capability()
{
    $roles = array(
        get_role('photographer'),
        get_role('administrator'),
        get_role('editor'),
    );

    foreach ($roles as $role) {
        $role->add_cap('photo_gallery');
        $role->add_cap('upload_files');
    }
    $role = get_role('photographer');
    $role->add_cap('edit_posts', true);
    $role->add_cap('edit_others_posts', false);
    $role->add_cap('read_posts', false);
}
add_action('admin_init', 'modify_snaps_capability');


/*
 * Rekorderlig - Quiz
 */
/* Quiz 1 - Start */
add_action('wp_ajax_ssm_save_quiz_rekorderlig_result', 'ssm_save_quiz_rekorderlig_result');
add_action('wp_ajax_nopriv_ssm_save_quiz_rekorderlig_result', 'ssm_save_quiz_rekorderlig_result');
function ssm_save_quiz_rekorderlig_result()
{
    global $wpdb;
    $wpdb->insert(
        $wpdb->prefix . 'rekorderlig_quiz_1_results',
        array(
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'result' => $_POST['result'],
            'result_key' => $_POST['result_key'],
            'created_at' => current_time('mysql', 0)
        )
    );
    $id = $wpdb->insert_id;
    $url_suffix = md5($id . 'ssm-quiz-rekorderlig-1');
    $wpdb->update(
        $wpdb->prefix . 'rekorderlig_quiz_1_results',
        array(
            'url_suffix' => $url_suffix
        ),
        array(
            'id' => $id
        )
    );
    $page_url = $_POST['page_url'];
    $page_title = $_POST['page_title'];

    $page_url .= '?r=' . $url_suffix;
    $page_title = $_POST['result'];

    $page_url = urlencode($page_url);
    $page_title = str_replace(' ', '%20', $page_title);

    $facebookURL = 'https://www.facebook.com/sharer/sharer.php?u=' . $page_url;
    $twitterURL = 'https://twitter.com/intent/tweet?text=' . $page_title . '&amp;url=' . $page_url;
    $data = array(
        'fb_share_url' => $facebookURL,
        'twitter_share_url' => $twitterURL,
    );
    wp_send_json_success($data);
}
/* Quiz 1 - End */

/* Quiz 2 - Start */
add_action('wp_ajax_ssm_save_quiz_rekorderlig_result2', 'ssm_save_quiz_rekorderlig_result2');
add_action('wp_ajax_nopriv_ssm_save_quiz_rekorderlig_result2', 'ssm_save_quiz_rekorderlig_result2');
function ssm_save_quiz_rekorderlig_result2()
{
    global $wpdb;
    $wpdb->insert(
        $wpdb->prefix . 'rekorderlig_quiz_2_results',
        array(
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'result' => $_POST['result'],
            'result_key' => $_POST['result_key'],
            'created_at' => current_time('mysql', 0)
        )
    );
    $id = $wpdb->insert_id;
    $url_suffix = md5($id . 'ssm-quiz-rekorderlig-1');
    $wpdb->update(
        $wpdb->prefix . 'rekorderlig_quiz_2_results',
        array(
            'url_suffix' => $url_suffix
        ),
        array(
            'id' => $id
        )
    );
    $page_url = $_POST['page_url'];
    $page_title = $_POST['page_title'];

    $page_url .= '?r=' . $url_suffix;
    $page_title = $_POST['result'];

    $page_url = urlencode($page_url);
    $page_title = 'My new favourite Rekorderlig Sauna Sounds Artist is ' . str_replace(' ', '%20', $page_title);

    $facebookURL = 'https://www.facebook.com/sharer/sharer.php?u=' . $page_url;
    $twitterURL = 'https://twitter.com/intent/tweet?text=' . $page_title . '&amp;url=' . $page_url;
    $data = array(
        'fb_share_url' => $facebookURL,
        'twitter_share_url' => $twitterURL,
    );
    wp_send_json_success($data);
}
/* Quiz 2 - End */

add_filter('wpseo_opengraph_url', 'change_opengraph_url');
function change_opengraph_url($url)
{
    if (is_page_template('page-quiz-rekorderlig.php') && isset($_GET['r'])):
        return get_permalink() . '?r=' . $_GET['r'];
    endif;
    if (is_page_template('page-quiz-rekorderlig2.php') && isset($_GET['r'])):
        return get_permalink() . '?r=' . $_GET['r'];
    endif;
    return $url;
}

add_filter('wpseo_opengraph_title', 'change_opengraph_title');
function change_opengraph_title($title)
{
    if (is_page_template('page-quiz-rekorderlig.php') && isset($_GET['r'])):
        global $wpdb;
        $result = $wpdb->get_var(
            $wpdb->prepare("SELECT result FROM {$wpdb->prefix}rekorderlig_quiz_1_results WHERE url_suffix = %s", $_GET['r'])
        );
        return $result;
    endif;
    if (is_page_template('page-quiz-rekorderlig2.php') && isset($_GET['r'])):
        global $wpdb;
        $result = $wpdb->get_var(
            $wpdb->prepare("SELECT result FROM {$wpdb->prefix}rekorderlig_quiz_2_results WHERE url_suffix = %s", $_GET['r'])
        );
        return 'My new favourite Rekorderlig Sauna Sounds Artist is ' . $result;
    endif;
    return $title;
}

add_filter('wpseo_opengraph_image', 'change_opengraph_image_url');
function change_opengraph_image_url($url)
{
    if (is_page_template('page-quiz-rekorderlig.php') && isset($_GET['r'])):
        global $wpdb;
        $image = get_template_directory_uri() . '/images/quiz-rekorderlig/';
        $result = $wpdb->get_var(
            $wpdb->prepare("SELECT result_key FROM {$wpdb->prefix}rekorderlig_quiz_1_results WHERE url_suffix = %s", $_GET['r'])
        );
        $image .= 'Rekorderling_result' . ($result * 10) . '.jpg';
        return $image;
    endif;
    if (is_page_template('page-quiz-rekorderlig2.php') && isset($_GET['r'])):
        global $wpdb;
        $image = get_template_directory_uri() . '/images/quiz-rekorderlig/';
        $result = $wpdb->get_var(
            $wpdb->prepare("SELECT result_key FROM {$wpdb->prefix}rekorderlig_quiz_2_results WHERE url_suffix = %s", $_GET['r'])
        );
        $image .= 'Rekorderling_Q2_results' . $result . '.jpg';
        return $image;
    endif;
    return $url;
}
/*
 * Rekorderlig - Quiz End
 */

/*
 * Block Bad Referrers
 */
add_action('init', 'ssm_referrer_check');
function ssm_referrer_check()
{
    $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : NULL;

    if (!$referer)
        return;

    if (
        strpos($referer, 'redirect.') != false || strpos($referer, 'filter.') != false ||
        strpos($referer, 'catchthesun') != false || strpos($referer, 'myfitnesspal') != false
    ) {
        header('Status: 403 Forbidden', true, 403);
        die();
        exit;
    }
}

/*
 * Add custom dimension 'Author' to AMP
 */
add_filter('amp_post_template_analytics', 'ssm_amp_add_custom_analytics');
function ssm_amp_add_custom_analytics($analytics)
{
    global $amp_post_id;
    $post = get_post($amp_post_id);
    if (!is_array($analytics)) {
        $analytics = array();
    }

    if (get_field('author')) {
        $author = get_field('author');
    } else if (get_field('Author')) {
        $author = get_field('Author');
    } else {
        if ('' != get_the_author_meta('first_name', $post->post_author) && '' != get_the_author_meta('last_name', $post->post_author)) {
            $author = get_the_author_meta('first_name', $post->post_author) . ' ' . get_the_author_meta('last_name', $post->post_author);
        } else {
            $author = get_the_author_meta('display_name', $post->post_author);
        }
    }

    $categories = get_the_category(get_the_ID());
    $CategoryCD = '';
    if ($categories):
        foreach ($categories as $category):
            $CategoryCD .= $category->slug . ' ';
        endforeach; // For Each Category
    endif; // If there are categories for the post

    $analytics['ssm-googleanalytics'] = array(
        'type' => 'googleanalytics',
        'attributes' => array(
            // 'data-credentials' => 'include',
        ),
        'config_data' => array(
            'vars' => array(
                'account' => "UA-101631840-1"
            ),
            'triggers' => array(
                'trackPageview' => array(
                    'on' => 'visible',
                    'request' => 'pageview',
                    'extraUrlParams' => array(
                        'cd3' => str_replace('&', 'and', $author),
                        'cd4' => $CategoryCD,
                    )
                ),
            ),
        ),
    );

    $analytics['td-googleanalytics'] = array(
        'type' => 'googleanalytics',
        'attributes' => array(
            // 'data-credentials' => 'include',
        ),
        'config_data' => array(
            'vars' => array(
                'account' => "UA-306739-6"
            ),
            'triggers' => array(
                'trackPageview' => array(
                    'on' => 'visible',
                    'request' => 'pageview',
                    'extraUrlParams' => array(
                        'cd3' => str_replace('&', 'and', $author),
                        'cd4' => $CategoryCD,
                    )
                ),
            ),
        ),
    );

    $analytics['nielsen'] = array(
        'type' => 'nielsen',
        'attributes' => array(
            // 'data-credentials' => 'include',
        ),
        'config_data' => array(
            'vars' => array(
                "apid" => "DD902D41-39DF-457F-985D-9B4E4CDF3726",
                "apv" => "1.0",
                "apn" => "The Brag Network",
                "section" => "Tone Deaf",
                "segA" => "",
                "segB" => "",
                "segC" => "The Brag Network - Google AMP"
            ),
        ),
    );

    return $analytics;
}

/*
 * Inject FB Pixel
 */
add_action('wp_head', 'ssm_inject_fb_pixel');
function ssm_inject_fb_pixel()
{
    ?>
    <!-- Facebook Pixel Code -->
    <script>
        ! function (f, b, e, v, n, t, s) {
            if (f.fbq) return;
            n = f.fbq = function () {
                n.callMethod ?
                    n.callMethod.apply(n, arguments) : n.queue.push(arguments)
            };
            if (!f._fbq) f._fbq = n;
            n.push = n;
            n.loaded = !0;
            n.version = '2.0';
            n.queue = [];
            t = b.createElement(e);
            t.async = !0;
            t.src = v;
            s = b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t, s)
        }(window, document, 'script',
            'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '243859349395737');
        fbq('track', 'PageView');
        fbq.disablePushState = true;
    </script>
    <noscript><img height="1" width="1" style="display:none"
            src="https://www.facebook.com/tr?id=243859349395737&ev=PageView&noscript=1" /></noscript>
    <!-- End Facebook Pixel Code -->
    <?php
}

/*
 * YouTube Lazy Load
 */
function ssm_youtube_lazy_load($content)
{
    $pattern = '/<figure class=\"op-interactive\"><iframe(.*?)width=\"(.*)\"(.*?)height=\"(.*)\"(.*?)src=\"https:\/\/www.youtube.com\/embed\/(.*)\?(.*?)\" (.*)><\/iframe><\/figure>/';
    $replacement = '<div class="yt-lazy-load my-2" data-id="$6" id="yt-$6"><img src="https://i.ytimg.com/vi/$6/hqdefault.jpg" width="$2" height="$4" class="yt-img" loading="lazy" alt="YouTube Video"><img class="p-a-center play-button" src="' . ICONS_URL . 'controller-play.svg" alt="Play" title="Play" loading="lazy" width="100" height="100"></div>';
    $lazy_content = preg_replace($pattern, $replacement, $content);
    return $lazy_content;
}
add_filter('the_content', 'ssm_youtube_lazy_load');

add_filter('xmlrpc_enabled', '__return_false');

/*
 * Instagram Frame Embedder
 */
wp_embed_register_handler('instagram', '#https?://(www.)?instagr(\.am|am\.com)/p/([^/]+)#i', 'ssm_embed_handler_instagram');
function ssm_embed_handler_instagram($matches, $attr, $url, $rawattr)
{
    if (!empty($rawattr['width']) && !empty($rawattr['height'])) {
        $width = (int) $rawattr['width'];
        $height = (int) $rawattr['height'];
    } else {
        list($width, $height) = wp_expand_dimensions(575, 1200, $attr['width'], $attr['height']);
    }
    return apply_filters('embed_instagram', "<iframe src='https://instagram.com/p/" . esc_attr($matches[3]) . "/embed/captioned' width='{$width}' height='{$height}' frameborder='0' scrolling='no' allowtransparency='true' style='border: 1px solid rgb(219, 219, 219); border-radius: 3px;'></iframe>");
}

// RSS for Promoter
add_action('init', 'promoterFeedRSS');
function promoterFeedRSS()
{
    add_feed('promoter', 'promoterFeed');
}
function promoterFeed()
{
    get_template_part('rss', 'promoter_feed');
}

function add_additional_class_on_li($classes, $item, $args)
{
    if ($args->add_li_class) {
        $classes[] = $args->add_li_class;
    }
    return $classes;
}
add_filter('nav_menu_css_class', 'add_additional_class_on_li', 1, 3);

function add_menu_link_class($atts, $item, $args)
{
    if (property_exists($args, 'link_class')) {
        $atts['class'] = $args->link_class;
    }
    return $atts;
}
add_filter('nav_menu_link_attributes', 'add_menu_link_class', 1, 3);

/*
 * Country
 */
add_action('init', 'register_cpt_country');

function register_cpt_country()
{
    $labels = array(
        'name' => _x('Country', 'country'),
        'singular_name' => _x('Country', 'country'),
        'add_new' => _x('Add New', 'country'),
        'add_new_item' => _x('Add New Country Article', 'country'),
        'edit_item' => _x('Edit Country Article', 'country'),
        'new_item' => _x('New Country Article', 'country'),
        'view_item' => _x('View Country Article', 'country'),
        'search_items' => _x('Search Country Articles', 'country'),
        'not_found' => _x('No country articles found', 'country'),
        'not_found_in_trash' => _x('No country articles found in Trash', 'country'),
        'parent_item_colon' => _x('Parent Country Articles:', 'country'),
        'menu_name' => _x('Country Articles', 'country'),
    );

    $args = array(
        'labels' => $labels,
        'hierarchical' => false,
        'description' => 'Country Articles',
        'supports' => array('title', 'editor', 'thumbnail', 'author', 'excerpt'),
        'taxonomies' => array('category', 'post_tag'),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 5,
        'show_in_nav_menus' => true,
        'publicly_queryable' => true,
        'exclude_from_search' => false,
        'has_archive' => true,
        'query_var' => true,
        'can_export' => true,
        'rewrite' => array('slug' => 'country', ),
        'capability_type' => array('page', 'country'),
        'capabilities' => array(
            'publish_posts' => 'country',
            'edit_posts' => 'country',
            'edit_others_posts' => 'country',
            'read_private_posts' => 'country',
            'edit_post' => 'country',
            'delete_post' => 'country',
            'read_post' => 'country',
            'publish_post' => 'country',
        ),
    );

    register_post_type('country', $args);
}
/*
 * User Role for Country
 */
add_role('country', 'Country Contributor', array(
    'read' => true,
    'edit_posts' => true,
    'delete_posts' => true,
)
);
function modify_country_capability()
{
    $roles = array(
        get_role('country'),
        get_role('administrator'),
        get_role('editor'),
    );

    foreach ($roles as $role) {
        $role->add_cap('country');
        $role->add_cap('upload_files');
        $role->add_cap('unfiltered_html');
    }
    //    $role = get_role('country');
    //    $role->add_cap('edit_posts', false);
    //    $role->add_cap('read_posts', false);
}
add_action('admin_init', 'modify_country_capability');


function ssm_inject_ads($content)
{
    if (function_exists('is_amp_endpoint') && is_amp_endpoint()) {
        return $content;
    }
    // return tbm_inject_ads( $content );
    if ((function_exists('get_field') && get_field('paid_content')) || is_page_template('single-template-featured.php') || is_page_template('page-templates/brag-observer.php')):
        return $content;
    endif;

    if (is_page()) {
        return $content;
    }

    $count_articles = isset($_POST['count_articles']) ? (int) $_POST['count_articles'] : 1;

    $closing_p = '</p>';
    $after_para = 2;

    ob_start();
    render_ad_tag('incontent_1', $count_articles);
    $content_ad_tag = ob_get_contents();
    ob_end_clean();
    $content = ssm_insert_after_paragraph('<div class="my-2 text-center ad-mrec" id="ad-incontent-' . $count_articles . '">' . $content_ad_tag . '</div>', $after_para, $content);

    return $content;
}
add_filter('the_content', 'ssm_inject_ads');

function convert_seconds_to_redable($seconds)
{
    $t = round($seconds);
    return sprintf('%02d:%02d:%02d', ($t / 3600), ($t / 60 % 60), $t % 60);
}

/*
 * Nielsen
 */
add_action('wp_footer', 'inject_nielsen', 99, 2);
function inject_nielsen()
{
    $assetId = $_SERVER['REQUEST_URI'];

    $html = '<script type="text/JavaScript">
!function(t,n){t[n]=t[n]||
{
nlsQ:function(e,o,c,r,s,i)
{
return s=t.document,
r=s.createElement("script"), 
r.async=1,
r.src=("http:"===t.location.protocol?"http:":"https:")+"//cdn-gl.imrworldwide.com/conf/"+e+".js#name="+o+"&ns="+n,
i=s.getElementsByTagName("script")[0],
i.parentNode.insertBefore(r,i),
t[n][o]=t[n][o]||{g:c||{},
ggPM:function(e,c,r,s,i){(t[n][o].q=t[n][o].q||[]).push([e,c,r,s,i])} },
t[n][o]
}
}
}
(window,"NOLBUNDLE");
var nSdkInstance = NOLBUNDLE.nlsQ("P59D1CA7E-CA1C-4718-8E85-F8807D018FED","nSdkInstance");
var dcrStaticMetadata = {type:"static",dataSrc:"cms",assetid:"' . $assetId . '", section:"Tone Deaf",segA:"",segB:""}
nSdkInstance.ggPM("staticstart",dcrStaticMetadata);
</script>';
    echo $html;
}

/*
 * Change Default Email Address and From Name for the outgoing emails
 */
add_filter("wp_mail_content_type", "tbm_mail_content_type");
function tbm_mail_content_type()
{
    return "text/html";
}
add_filter('wp_mail_from', 'tbm_mail_from_address');
function tbm_mail_from_address($email)
{
    return 'noreply@thebrag.media';
}
add_filter('wp_mail_from_name', 'tbm_mail_from_name');
function tbm_mail_from_name($from_name)
{
    return "Tone Deaf";
}
add_action('phpmailer_init', 'tbm_send_smtp_email');
function tbm_send_smtp_email($phpmailer)
{
    // $phpmailer->isSMTP();
    // $phpmailer->Host       = 'smtp.gmail.com';
    // $phpmailer->SMTPAuth   = true;
    // $phpmailer->Port       = 587;
    // $phpmailer->Username   = 'noreply@thebrag.media';
    // $phpmailer->Password   = '<%QA5hXy1';
    // $phpmailer->SMTPSecure = 'tls';
    // $phpmailer->From       = 'noreply@thebrag.media';
    // $phpmailer->FromName   = 'Tone Deaf';

    $phpmailer->isSMTP();
    $phpmailer->Host = 'smtp.sparkpostmail.com';
    $phpmailer->SMTPAuth = true;
    $phpmailer->Port = 587;
    $phpmailer->Username = 'SMTP_Injection';
    $phpmailer->Password = '01211dd8bc574e89c2553dfb004fddbd5dafed6b';
    $phpmailer->SMTPSecure = 'tls';
    $phpmailer->From = 'noreply@mail.thebrag.media';
    $phpmailer->FromName = 'Tone Deaf';

    $phpmailer->IsSMTP();
}

/*
 * Get Next Post AJAX
 */
function tbm_ajax_load_next_post()
{
    global $post;
    $postID = get_the_ID();

    if ('single-template-featured.php' == get_page_template_slug($postID)):
        wp_die();
    endif;

    $count_articles = isset($_POST['count_articles']) ? absint($_POST['count_articles']) : 1;

    if (get_field('paid_content', $_POST['id']) && 2 == $count_articles):
        wp_die();
    endif;

    $exclude_posts = (!is_null($_POST['exclude_posts']) && $_POST['exclude_posts'] != '') ? $_POST['exclude_posts'] : '';
    $exclude_posts_array = explode(',', $exclude_posts);

    if (get_option('tbm_featured_infinite_ID') && $_POST['id'] != get_option('tbm_featured_infinite_ID') && !in_array(get_option('tbm_featured_infinite_ID'), $exclude_posts_array)):
        $prevPost = get_post(get_option('tbm_featured_infinite_ID'));
    else:
        $post = get_post($_POST['id']);
        $prevPost = get_previous_post();
    endif;

    if (
        in_array($prevPost->ID, $exclude_posts_array) ||
        strpos(strtolower($prevPost->post_title), 'quiz') !== false ||
        strpos(strtolower($prevPost->post_title), 'poll') !== false
    ) {
        $data['content'] = '';
        $data['loaded_post'] = $prevPost->ID;
        wp_send_json_success($data);
        wp_die();
    }
    if ($prevPost):
        $post = $prevPost;
        $data['exclude_post'] = $prevPost->ID;
        ob_start();
        $main_post = false;

        if ('single-template-featured.php' == get_page_template_slug($post->ID)) {
            get_template_part('template-parts/single/single', 'featured', ['count_articles' => $count_articles]);
        } else {
            get_template_part('template-parts/single/single', 'post', ['count_articles' => $count_articles]);
        }

        wp_reset_query();
        wp_reset_postdata();
        $data['content'] = ob_get_clean();
        $data['loaded_post'] = $prevPost->ID;
        $data['page_title'] = html_entity_decode(get_the_title($prevPost));
        $author = get_the_author_meta('first_name', $post->post_author) . ' ' . get_the_author_meta('last_name', $post->post_author);
        if (get_field('author', $prevPost->ID)) {
            $author = get_field('author', $prevPost->ID);
        } else if (get_field('Author', $prevPost->ID)) {
            $author = get_field('Author', $prevPost->ID);
        }
        $data['author'] = $author;

        $categories = get_the_category($prevPost->ID);
        if ($categories) {
            foreach ($categories as $category_obj):
                $category = $category_obj->slug;
                break;
            endforeach;
            $data['category'] = $category;
        }

        $pagepath = parse_url(get_the_permalink($prevPost->ID), PHP_URL_PATH);
        $pagepath = substr(str_replace('/', '', $pagepath), 0, 40);
        $data['pagepath'] = $pagepath;

        wp_send_json_success($data);
    endif;
    wp_die();
}
add_action('wp_ajax_tbm_ajax_load_next_post', 'tbm_ajax_load_next_post');
add_action('wp_ajax_nopriv_tbm_ajax_load_next_post', 'tbm_ajax_load_next_post');

/*
 * Force Focus keyphrase (Yoast) for the posts
 */
function tbm_admin_enqueue($hook)
{
    if (!in_array($hook, array('post.php', 'post-new.php'))) {
        return;
    }
    wp_enqueue_script('admin-validate-post', get_template_directory_uri() . '/js/admin-validate-post.js', array('jquery'), '20190819-2', true);
}
add_action('admin_enqueue_scripts', 'tbm_admin_enqueue');

/*
 * Reset (Tree) Category Checklist
 */
add_filter('wp_terms_checklist_args', 'tbm_checklist_args');
function tbm_checklist_args($args)
{
    $args['checked_ontop'] = false;
    return $args;
}

add_action("manage_posts_columns", "tbm_custom_columns");
add_filter("manage_posts_custom_column", "tbm_edit_columns", 10, 2);

function tbm_custom_columns($columns)
{
    $columns['genre'] = 'Genre';
    $columns['artist'] = 'Artist';
    return $columns;
}
function tbm_edit_columns($column, $post_id)
{
    switch ($column) {
        case "genre":
            $genres = get_the_terms($post_id, 'genre');
            if ($genres) {
                $output = array();
                foreach ($genres as $genre) {
                    $output[] = '<a href="' . get_term_link($genre->slug, 'genre') . '">' . $genre->name . '</a>';
                }
                echo join(', ', $output);
            }
            break;
        case "artist":
            $artists = get_the_terms($post_id, 'artist');
            if ($artists) {
                $output = array();
                foreach ($artists as $artist) {
                    if (!is_wp_error(get_term_link($artist->slug, 'artist'))) {
                        $output[] = '<a href="' . get_term_link($artist->slug, 'artist') . '">' . $artist->name . '</a>';
                    }
                }
                echo join(', ', $output);
            }
            break;
    }
}

/*
 * Force tags, etc. for the posts (using plugin hook - Require Post Category - https://en-au.wordpress.org/plugins/require-post-category/)
 */
function tbm_rpc_post_types($post_types)
{
    // Add a key to the $post_types array for each post type and list the slugs of the taxonomies you wish to require

    // Simplest usage
    // $post_types['post'] = array('category', 'post_tag', 'genre');
    $post_types['post'] = array('category', 'post_tag');

    // Always return $post_types after your modifications
    return $post_types;
}
add_filter('rpc_post_types', 'tbm_rpc_post_types');

/*
 * Set cookie
 */
add_action('wp_ajax_nopriv_tbm_set_cookie', 'ajax_tbm_set_cookie');
add_action('wp_ajax_tbm_set_cookie', 'ajax_tbm_set_cookie');
function ajax_tbm_set_cookie()
{
    $data = isset($_POST) ? $_POST : [];
    tbm_set_cookie($data);
    wp_die();
}

function tbm_set_cookie($data)
{
    if (!empty($data) && isset($data['key']) && isset($data['value']) && isset($data['duration'])):
        setcookie($data['key'], $data['value'], time() + (int) $data['duration'], '/', $_SERVER['HTTP_HOST']);
    endif;
}

function render_ad_tag($tag, $slot_no = 1)
{
    global $post;
    $postID = get_the_ID();
    $postID = isset($postID) ? $postID : 0;
    if (function_exists('get_field') && get_field('paid_content', $postID))
        return;
    if (!file_exists(WP_PLUGIN_DIR . '/tbm-adm/tbm-adm.php'))
        return;
    require_once WP_PLUGIN_DIR . '/tbm-adm/tbm-adm.php';
    $ads = TBMAds::get_instance();
    echo $ads->get_ad($tag, $slot_no, get_the_ID());
    return;
}

function is_mobile($kind = 'any', $caller = '')
{
    if (empty($_SERVER['HTTP_USER_AGENT'])) {
        $is_mobile = false;
    } elseif (
        strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'Silk/') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'Kindle') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'BlackBerry') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mini') !== false
    ) {
        $is_mobile = true;
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Mobile') !== false && strpos($_SERVER['HTTP_USER_AGENT'], 'iPad') == false) {
        $is_mobile = true;
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'iPad') !== false) {
        $is_mobile = false;
    } else {
        $is_mobile = false;
    }

    return $is_mobile;
}

/*
 * FIX for Password reset link not showing
 */
// add_filter( 'retrieve_password_message', 'tbm_custom_password_reset', 99, 4);
function tbm_custom_password_reset($message, $key, $user_login, $user_data)
{
    $message = "Someone has requested a password reset for the following account:
        " . sprintf(__('%s'), $user_data->user_email) . "
        If this was a mistake, just ignore this email and nothing will happen.
        To reset your password, visit the following address:
        " . network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login') . "\r\n";
    return $message;
}

function get_auth_error_message($error_code)
{
    switch ($error_code) {
        case 'empty_username':
            return 'You do have an email address, right?';

        case 'empty_password':
            return 'You need to enter a password to login.';

        case 'invalid_username':
            return "We don't have any users with that email address. Maybe you used a different one when signing up?";

        case 'incorrect_password':
            $err = "The password you entered wasn't quite right. <a href='%s'>Did you forget your password</a>?";
            return sprintf($err, wp_lostpassword_url());

        case 'empty_username':
            return 'You need to enter your email address to continue.';
        case 'invalid_email':
        case 'invalidcombo':
            return 'There are no users registered with this email address.';

        case 'expiredkey':
        case 'invalidkey':
            return 'The password reset link you used is not valid anymore.';

        case 'password_reset_mismatch':
            return "The two passwords you entered don't match.";

        case 'password_reset_empty':
            return "Sorry, we don't accept empty passwords.";

        default:
            break;
    }

    return 'An unknown error occurred. Please try again later.';
}

function getStates()
{
    return [
        "NSW" => "New South Wales",
        "VIC" => "Victoria",
        "QLD" => "Queensland",
        "TAS" => "Tasmania",
        "SA" => "South Australia",
        "WA" => "Western Australia",
        "NT" => "Northern Territory",
        "ACT" => "Australian Capital Territory",
        "NZ" => "New Zealand",
        "INT" => "Outside AU / NZ",
    ];
}

function getGenders()
{
    return [
        "Male",
        "Female",
        "Gender not listed here",
    ];
}


/*
 * Include featured image in RSS feed
 */
function tbm_post_thumbnails_in_feeds($content)
{
    global $post;
    if (has_post_thumbnail($post->ID)) {
        $img_src = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'full');
        $content = '<figure><img src="' . $img_src[0] . '" class="type:primaryImage"></figure>' . $content;
    }
    return $content;
}
add_filter('the_excerpt_rss', 'tbm_post_thumbnails_in_feeds');
add_filter('the_content_feed', 'tbm_post_thumbnails_in_feeds');


add_action('simple_jwt_login_jwt_payload_auth', function ($payload, $request) {

    $payload['aud'] = 'application-1-kaekd';
    $payload['sub'] = 'aeRNRPdIfLkBKBnBkRKoWwxhE4hGYZOS';
    // error_log( print_r( $payload, true ) );
    return $payload;
}, 10, 2);

function brands()
{
    $pub_logos = [
        'the-brag' => [
            'title' => 'The Brag',
            'link' => 'https://thebrag.com/',
            'logo_name' => 'the-brag-dark-202404',
            'width' => 100,
            'ext' => 'svg',
        ],
        'brag-jobs' => [
            'title' => 'The Brag Jobs',
            'link' => 'https://thebrag.com/jobs',
            'logo_name' => 'The-Brag-Jobs',
            'width' => 80,
            'ext' => 'png',
        ],
        /* 'dbu' => [
            'title' => 'Don\'t Bore Us',
            'link' => 'https://dontboreus.thebrag.com/',
            'logo_name' => 'Dont-Bore-Us',
            'ext' => 'svg',
        ], */
        /* 'tio' => [
            'title' => 'The Industry Observer',
            'link' => 'https://theindustryobserver.thebrag.com/',
            'logo_name' => 'The-Industry-Observer',
            'ext' => 'svg',
        ], */
        'rolling-stone-australia' => [
            'title' => 'Rolling Stone Australia',
            'link' => 'https://au.rollingstone.com/',
            'logo_name' => 'Rolling-Stone-Australia',
            'ext' => 'png',
        ],
        'tone-deaf' => [
            'title' => 'Tone Deaf',
            'link' => 'https://tonedeaf.thebrag.com/',
            'logo_name' => 'Tone-Deaf',
            'ext' => 'svg',
            'width' => 80
        ],
        'tmn' => [
            'title' => 'The Music Network',
            'link' => 'https://themusicnetwork.com/',
            'logo_name' => 'TMN',
            'ext' => 'svg',
            'width' => 80
        ],
        'variety-au' => [
            'title' => 'Variety Australia',
            'link' => 'https://au.variety.com/',
            'logo_name' => 'Variety-Australia',
            'ext' => 'svg',
            'width' => 120
        ],
    ];
    return $pub_logos;
} // brands()

function brands_network()
{
    $pub_logos = [
        /**
         * EPIC
         */
        'lwa' => [
            'title' => 'Life Without Andy',
            'link' => 'https://lifewithoutandy.com/',
            'logo_name' => 'lwa',
            'ext' => 'png',
            'width' => 60
        ],
        'hypebeast' => [
            'title' => 'Hypebeast',
            'link' => 'https://hypebeast.com/',
            'logo_name' => 'Hypebeast',
            'ext' => 'png',
        ],
        'funimation' => [
            'title' => 'Funimation',
            'link' => 'https://www.funimation.com/',
            'logo_name' => 'Funimation',
            'ext' => 'png',
        ],
        'crunchyroll' => [
            'title' => 'Crunchyroll',
            'link' => 'https://www.crunchyroll.com/en-gb',
            'logo_name' => 'Crunchyroll',
            'ext' => 'png',
        ],
        'enthusiast' => [
            'title' => 'Enthusiast Gaming',
            'link' => 'https://www.enthusiastgaming.com/',
            'logo_name' => 'enthusiast',
            'ext' => 'png',
        ],
        'gamelancer' => [
            'title' => 'Gamelancer',
            'link' => 'https://gamelancer.com/',
            'logo_name' => 'Gamelancer',
            'ext' => 'png',
        ],
        'toongoggles' => [
            'title' => 'ToonGoggles',
            'link' => 'https://www.toongoggles.com/',
            'logo_name' => 'ToonGoggles',
            'ext' => 'png',
        ],
        'kidoodle' => [
            'title' => 'kidoodle',
            'link' => 'https://www.kidoodle.tv/',
            'logo_name' => 'kidoodle',
            'ext' => 'png',
        ],

        /**
         * PMC
         */
        'artnews' => [
            'title' => 'ARTnews',
            'link' => 'https://www.artnews.com/',
            'logo_name' => 'ARTnews',
        ],
        'bgr' => [
            'title' => 'BGR',
            'link' => 'https://bgr.com/',
            'logo_name' => 'bgr',
            'width' => 80
        ],
        'billboard' => [
            'title' => 'Billboard',
            'link' => 'https://billboard.com/',
            'logo_name' => 'billboard',
        ],
        'deadline' => [
            'title' => 'Deadline',
            'link' => 'https://deadline.com/',
            'logo_name' => 'DEADLINE',
        ],
        'dirt' => [
            'title' => 'Dirt',
            'link' => 'https://www.dirt.com/',
            'logo_name' => 'Dirt',
            'width' => 80
        ],
        'footwear' => [
            'title' => 'Footwear News',
            'link' => 'https://footwearnews.com/',
            'logo_name' => 'FootwearNews',
            'width' => 60
        ],
        'gold-derby' => [
            'title' => 'Gold Derby',
            'link' => 'https://www.goldderby.com/',
            'logo_name' => 'GoldDerby',
        ],
        'indiewire' => [
            'title' => 'IndieWire',
            'link' => 'https://www.indiewire.com/',
            'logo_name' => 'IndieWire',
        ],
        'sheknows' => [
            'title' => 'SheKnows',
            'link' => 'https://www.sheknows.com/',
            'logo_name' => 'SheKnows',
        ],
        'sourcing-journal' => [
            'title' => 'Sourcing Journal',
            'link' => 'https://sourcingjournal.com/',
            'logo_name' => 'SourcingJournal',
        ],
        'sportico' => [
            'title' => 'Sportico',
            'link' => 'https://www.sportico.com/',
            'logo_name' => 'Sportico',
        ],
        'spy' => [
            'title' => 'Spy',
            'link' => 'https://spy.com/',
            'logo_name' => 'Spy',
            'width' => 120,
        ],
        'stylecaster' => [
            'title' => 'Stylecaster',
            'link' => 'https://stylecaster.com/',
            'logo_name' => 'Stylecaster',
        ],
        'the-hollywood-reporter' => [
            'title' => 'The Hollywood Reporter',
            'link' => 'https://www.hollywoodreporter.com/',
            'logo_name' => 'The-Hollywood-Reporter',
        ],
        'tvline' => [
            'title' => 'TVLine',
            'link' => 'https://tvline.com/',
            'logo_name' => 'TVLine',
            'width' => 120,
        ],
        /* 'variety' => [
            'title' => 'Variety',
            'link' => 'https://variety.com/',
            'logo_name' => 'Variety',
            'width' => 120,
        ], */
        'vibe' => [
            'title' => 'VIBE',
            'link' => 'https://www.vibe.com/',
            'logo_name' => 'Vibe',
            'width' => 120,
        ],
    ];
    return $pub_logos;
} // brands_network()

add_filter('next_posts_link_attributes', 'tbm_posts_link_attributes');
add_filter('previous_posts_link_attributes', 'tbm_posts_link_attributes');

function tbm_posts_link_attributes()
{
    return 'class="btn btn-dark"';
}


// Observer sub form
add_filter('the_content', function ($content) {
    if (function_exists('is_amp_endpoint') && is_amp_endpoint()) {
        return $content;
    }
    if ('single-template-featured.php' == get_page_template_slug(get_the_ID())) {
        return $content;
    }
    if (get_field('hide_observer_form'))
        return $content;

    if (shortcode_exists('observer_subscribe_genre')):
        ob_start();
        echo do_shortcode('[observer_subscribe_genre id="' . get_the_ID() . '"]');
        $content_shortcode = ob_get_contents();
        ob_end_clean();
        $content = ssm_insert_after_paragraph($content_shortcode, 7, $content);
    endif;
    return $content;
});


// add_action('wp_footer', 'inject_roymorgan', 99, 2);
function inject_roymorgan()
{
    ?>
    <script type="text/javascript">
        jQuery(function () {
            var cachebuster = Date.now();
            var script = document.createElement('script');
            script.src = 'https://pixel.roymorgan.com/stats_v2/Tress.php?u=k7b7oit54p&ca=20005195&a=6id59hbq' + '&cb=' + cachebuster;
            script.async = true;
            document.body.appendChild(script);
        });
    </script>
    <?php
}

add_action('wp_footer', 'inject_ga4', 99, 2);
function inject_ga4()
{
    ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-L8V4HEDPRH"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());
        gtag('config', 'G-L8V4HEDPRH');
    </script>
    <?php
}


/*
 * Show admin bar only for admins and editors
 */
if (!current_user_can('edit_posts')) {
    add_filter('show_admin_bar', '__return_false');
}

/**
 * Redirect non-admin users to home page
 */
add_action('admin_init', function () {
    $user = wp_get_current_user();
    if (!current_user_can('edit_posts') && !current_user_can('photo_gallery') && ('/wp-admin/admin-ajax.php' != $_SERVER['PHP_SELF'])) {
        wp_redirect(home_url());
        exit;
    }
}, 99);

/*
 * Add Comps link in article
 */
/* add_filter('the_content', function ($content) {
    if ((function_exists('get_field') && get_field('paid_content')) || is_page_template('single-template-featured.php')) :
        return $content;
    endif;

    if (!is_singular('post'))
        return $content;

    $content .= '<div class="comp-footer"><a href="https://thebrag.com/observer/competitions/" target="_blank" rel="noopener">Did you know we\'re constantly giving away <strong>FREE</strong> stuff? Check out our giveaways here.</a></div>';

    return $content;
}, 99); */

/**
 * Add RS Mag Subscribe link in article footer
 */
add_filter('the_content', function ($content) {
    if ((function_exists('get_field') && get_field('paid_content')) || is_page_template('single-template-featured.php')):
        return $content;
    endif;

    if (!is_singular('post'))
        return $content;

    if (function_exists('amp_is_request') && amp_is_request()) {
        return $content;
    }

    $mag_cover_res = wp_remote_get('https://au.rollingstone.com/wp-json/tbm_mag_sub/v1/next_issue_img_thumb');

    if (is_array($mag_cover_res) && !is_wp_error($mag_cover_res)) {
        $mag_cover = json_decode($mag_cover_res['body']);
    }

    $content .= '<a href="https://au.rollingstone.com/subscribe-magazine/" target="_blank" rel="noopener" class="d-flex flex-column flex-md-row align-items-start rs-subscribe-footer"><div class="d-flex">';
    if (isset($mag_cover) && '' != $mag_cover) {
        $content .= '<div class="flex-fill img-wrap"><img src="' . $mag_cover . '" width="100"></div>';
    }
    $content .= '<div>Get unlimited access to the coverage that shapes our culture.';
    $content .= '<div class="d-none d-md-block mt-1"><span class="subscribe">Subscribe</span> to <strong>Rolling Stone magazine</strong></div></div></div>';
    $content .= '<div class="d-block d-md-none mt-3 w-100"><span class="subscribe">Subscribe</span> to <strong>Rolling Stone magazine</strong></div>';
    $content .= '</a>';

    return $content;
}, 99);


// URL rewrite for (fake) list pages
add_action('init', function () {
    add_rewrite_rule('^([^/]*)/list/([^/]*)/?', 'index.php?name=$matches[1]', 'top');
}, 10, 0);

//Disable emojis in WordPress
add_action('init', 'tbm_disable_emojis');

function tbm_disable_emojis()
{
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
    add_filter('tiny_mce_plugins', 'tbm_disable_emojis_tinymce');
}

function tbm_disable_emojis_tinymce($plugins)
{
    if (is_array($plugins)) {
        return array_diff($plugins, array('wpemoji'));
    } else {
        return array();
    }
}

# Create excerpt at the end of a sentence

function tbm_the_excerpt($excerpt)
{
    $excerpt = str_replace(' [&hellip;]', '', $excerpt);
    $excerpt = str_replace('St. ', 'St*& ', $excerpt);

    $excerpt = str_replace('aka. ', 'aka*& ', $excerpt);
    $excerpt = str_replace('a.k.a. ', 'a*&a*&a ', $excerpt);
    $excerpt = str_replace('a.k.a ', 'a*&a*&a ', $excerpt);

    $excerpt = str_replace('M.I.A. ', 'm*&i*&a ', $excerpt);
    $excerpt = str_replace('M.I.A ', 'm*&i*&a ', $excerpt);

    $excerpt = str_replace('Dr. ', 'Dr*& ', $excerpt);

    $excerpt = explode('// ', $excerpt);
    $excerpt = count($excerpt) > 1 ? $excerpt[1] : $excerpt[0];
    $excerpt = explode('.', $excerpt);
    $excerpt = explode('!', $excerpt[0]);
    $excerpt = explode('?', $excerpt[0]);
    $excerpt = explode('…', $excerpt[0]);

    $excerpt = str_replace('St*& ', 'St. ', $excerpt[0]);
    $excerpt = str_replace('aka*& ', 'aka. ', $excerpt);
    $excerpt = str_replace('a*&a*&a ', 'a.k.a. ', $excerpt);
    $excerpt = str_replace('m*&i*&a ', 'M.I.A. ', $excerpt);
    $excerpt = str_replace('Dr*& ', 'Dr. ', $excerpt);

    return $excerpt . '.';
}