<?php

/**
 * Plugin Name: Vinyl Media Newsletters
 * Plugin URI: https://vinyl.media/
 * Description: Adds subscribe form widget to content based on category topic.
 * Version: 1.0.0
 * Author: Vinyl Media
 */

class VinylMediaNewsletters
{
    protected string $api_url;

    public function __construct()
    {
        // TODO move API url to another domain
        $this->api_url = 'https://thebrag.com/wp-json/brag_observer_airship/v1';

        // shortcodes
        add_shortcode('vinyl_newsletter_subscribe', [ $this, 'shortcode_subscribe_form' ]);

        // REST API Endpoints
        add_action('wp_ajax_subscribe_observer', [ $this, 'ajax_subscribe_newsletter' ] );
        add_action('wp_ajax_nopriv_subscribe_observer', [ $this, 'ajax_subscribe_newsletter' ] );

        // term meta
        add_action('category_add_form_fields', [ $this, 'add_category_add_form_fields' ], 10, 2);
        add_action('category_edit_form_fields', [ $this, 'add_category_edit_form_fields' ], 10, 2);
        add_action('edited_category', [ $this, 'update_edited_category' ], 10, 2);

        // add css/js
        add_action('wp_enqueue_scripts', [ $this, 'enqueue_scripts' ]);

        // add to content
        add_filter('the_content', [ $this, 'the_content' ], 10, 2);
    }

    function enqueue_scripts(): void {
        wp_register_style( 'vinyl_media_newsletters', plugins_url( 'css/style.css', __FILE__  ), [], '20250503' );
        wp_enqueue_style( 'vinyl_media_newsletters' );
        wp_enqueue_script( 'vinyl_media_newsletters', plugins_url( 'js/scripts.js', __FILE__  ), [ 'jquery' ], '20250503', true );
    }

    function the_content($content) {
        if (function_exists('is_amp_endpoint') && is_amp_endpoint()) {
            return $content;
        }

        if ('single-template-featured.php' == get_page_template_slug(get_the_ID())) {
            return $content;
        }

        if( function_exists('get_field') ) {
            if (get_field('hide_observer_form')) {
                return $content;
            }
        }

        if (shortcode_exists('vinyl_newsletter_subscribe')):
            ob_start();
            echo do_shortcode('[vinyl_newsletter_subscribe id="' . get_the_ID() . '"]');
            $content_shortcode = ob_get_contents();
            ob_end_clean();
            $content = $this->insert_after_paragraph($content_shortcode, 7, $content);
        endif;
        return $content;
    }

    public function insert_after_paragraph($insertion, $paragraph_id, $content): string {
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

    public function ajax_subscribe_newsletter(): void {
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            if ( isset( $_POST['formData'] ) ) {
                parse_str( $_POST['formData'], $formData );
            } else {
                $formData = $_POST;
            }

            if ( is_user_logged_in() ) :
                $current_user      = wp_get_current_user();
                $formData['email'] = $current_user->user_email;
            endif;

            if ( ! is_numeric( $formData['list'] ) ) {
                error_log( 'Newsletter List _' . $formData['list'] . '_is not numeric' );
                wp_mail( 'dev@vinyl.media', 'Newsletter Error', 'Newsletter List _' . $formData['list'] . '_is not numeric : ' . print_r( $formData, true ) );
                wp_send_json_error( [ 'error' => [ 'message' => 'Something went wrong' ] ] );
            }

            $brag_api_url = $this->api_url . '/sub_unsub/';

            $response = wp_remote_post(
                $brag_api_url,
                [
                    'method'    => 'POST',
                    'body'      => [
                        'email'  => $formData['email'],
                        'list'   => $formData['list'],
                        'source' => $formData['source'],
                        'status' => 'subscribed'
                    ],
                    'sslverify' => ! in_array( $_SERVER['REMOTE_ADDR'], [ '127.0.0.1', '::1' ] ),
                ]
            );

            $responseBody = wp_remote_retrieve_body( $response );
            $responseJson = json_decode( $responseBody );

            if ( isset( $responseJson->success ) && $responseJson->success == 1 ) {
                wp_send_json_success( $responseJson->data );
            }
            wp_send_json_error( [ 'error' => [ 'message' => $responseJson->data->error->message ] ] );
        }
    }

    public function get_newsletter_topics($topic = NULL)
    {
        $responseJson = wp_remote_get( $this->api_url . '/get_topics' );
        $response = json_decode($responseJson['body']);
        $topics = $response->data;

        if (!is_null($topic)) {
            foreach ($topics as $t) {
                if ($topic == $t->id) {
                    return [$t];
                }
            }
        }
        return $topics;
    }

    public function shortcode_subscribe_form($atts): string {
        $genre_atts = shortcode_atts([
            'id' => NULL,
        ], $atts);

        if (is_null($genre_atts['id']))
            return '';

        $post_id = $genre_atts['id'];
        $categories = get_the_terms($post_id, 'category');

        if (!$categories) {
            return '';
        }

        $primary_genre = null;

        foreach ($categories as $category) {
            if (get_post_meta($post_id, '_yoast_wpseo_primary_genre', true) == $category->term_id) {
                $primary_genre = $category;
                break;
            }
        }

        if (is_null($primary_genre)) {
            $primary_genre = $categories[0];
        }

        $topic_id = get_term_meta($primary_genre->term_id, 'primary-newsletter-topic', true);

        if (!$topic_id) {
            $topic_id = get_term_meta($primary_genre->term_id, 'newsletter-topic', true);
        }

        ob_start();
        ?>
        <?php
        if ($topic_id) {
            $topics = $this->get_newsletter_topics($topic_id);
            $topic = $topics[0];

            if (!$topic) {
                return '';
            }

            if( $topic->title == 'The Music Network' ) {
                $topic->title = 'Industry News';
            }

            if( $topic->title == 'Tone Deaf' ) {
                $topic->title = 'Music';
            }

            if( $topic->title == 'Rolling Stone AU/NZ' ) {
                $topic->title = 'Music';
            }

            if( $topic->title == 'Variety Australia' ) {
                $topic->title = 'Film & TV';
            }

            load_template( plugin_dir_path( __FILE__ ) . 'partials/form-subscribe.php', true, [
                'post_id' => $post_id,
                'topic_id' => $topic_id,
                'image_url' => $topic->image_url,
                'title' => $topic->title,
                'description' => $topic->description,
            ] );
        }
        $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }

    public function add_category_add_form_fields(): void
    {
        $topics = wp_list_pluck($this->get_newsletter_topics(), 'title', 'id');
        asort($topics);

        ?>
        <div class="form-field term-group">
        <label>Newsletter topic</label>
        <table>
            <tr>
                <?php $count = 1;
                foreach ($topics as $id => $title) : ?>
                    <td>
                        <label>
                            <input type="checkbox" name="newsletter-topic[]" value="<?php echo $id; ?>">
                            <?php echo $title; ?>
                        </label>
                    </td>
                    <?php echo $count % 3 === 0 ? '</tr><trd>' : ''; ?>
                    <?php $count++;
                endforeach; ?>
            </tr>
        </table>
        </div><?php
    }

    public function add_category_edit_form_fields($term): void
    {
        $topics = wp_list_pluck($this->get_newsletter_topics(), 'title', 'id');
        asort($topics);

        // get current topic
        $genre_primary_topic = get_term_meta($term->term_id, 'primary-newsletter-topic', true);
        $genre_topics = get_term_meta($term->term_id, 'newsletter-topic');

        ?>
        <tr class="form-field term-group-wrap">
        <th scope="row">
            <label for="newsletter-topic">Newsletter topic</label>
        </th>
        <td>
            <div>
                Primary topic
                <label for="primary-newsletter-topic"></label>
                <select class="postform" id="primary-newsletter-topic" name="primary-newsletter-topic">
                    <option value="">None</option>
                    <?php foreach ($topics as $id => $title) : ?>
                        <option value="<?php echo $id; ?>" <?php selected($genre_primary_topic, $id); ?>><?php echo $title; ?></option>
                    <?php endforeach; ?>
                </select>
                <table>
                    <tr>
                        <?php $count = 1;
                        foreach ($topics as $id => $title) : ?>
                            <td style="padding: 5px;">
                                <label>
                                    <input type="checkbox" name="newsletter-topic[]"
                                           value="<?php echo $id; ?>" <?php echo in_array($id, $genre_topics) ? ' checked' : ''; ?>>
                                    <?php echo $title; ?>
                                </label>
                            </td>
                            <?php echo $count % 3 === 0 ? '</tr><trd>' : ''; ?>
                            <?php $count++;
                        endforeach; ?>
                    </tr>
                </table>
            </div>
        </td>
        </tr><?php
    }

    public function update_edited_category($term_id): void
    {
        if (isset($_POST['primary-newsletter-topic']) && '' !== $_POST['primary-newsletter-topic']) {
            update_term_meta($term_id, 'primary-newsletter-topic', $_POST['primary-newsletter-topic']);
        }
        if (isset($_POST['newsletter-topic']) && '' !== $_POST['newsletter-topic']) {
            if (is_array($_POST['newsletter-topic'])) {
                delete_term_meta($term_id, 'newsletter-topic');
                foreach ($_POST['newsletter-topic'] as $post_topic) {
                    $topic = sanitize_title($post_topic);
                    add_term_meta($term_id, 'newsletter-topic', $topic);
                }
            }
        } else {
            delete_term_meta($term_id, 'newsletter-topic');
        }
    }
}

new VinylMediaNewsletters();
