<?php

/**
 * Plugin Name: TBM Ads Manager
 * Plugin URI: https://thebrag.media/
 * Description:
 * Version: 1.0.0
 * Author: Sachin Patel
 * Author URI:
 */

class TBMAds
{

  protected $plugin_title;
  protected $plugin_name;
  protected $plugin_slug;

  protected static $_instance;

  public function __construct()
  {

    $this->plugin_title = 'TBM Ads';
    $this->plugin_name = 'tbm_ads';
    $this->plugin_slug = 'tbm-ads';

    add_action('wp_enqueue_scripts', [$this, 'action_wp_enqueue_scripts']);
    add_action('wp_head', [$this, 'action_wp_head']);
  }

  /*
   * Enqueue JS
   */
  public function action_wp_enqueue_scripts()
  {
      wp_enqueue_script(
          'adm-gpt',
          'https://securepubads.g.doubleclick.net/tag/js/gpt.js'
      );
  }

  /*
   * WP Head
   */
    public function action_wp_head()
    {
        $is_home     = is_home() || is_front_page();
        $is_category = is_category() || is_archive();
        $is_article  = is_single();

        ?>
        <script>
            window.googletag = window.googletag || {cmd: []};

            googletag.cmd.push(function () {
                const isMobile = window.innerWidth < 768;

                const leaderboardSizes = isMobile
                    ? [[300,50],[300,100],[320,100],[320,50]]
                    : [[970,250],[970,90],[728,90]];

                const mrecSizes       = ['fluid',[300,250],[336,280]];
                const incontentSizes  = ['fluid',[300,250],[336,280],[320,480]];
                const vrecSizes       = ['fluid',[300,250],[300,600]];
                const skinSizes       = [[1600,1200]];

                function slot(path, sizes, id, desktopOnly = false) {
                    if (desktopOnly && isMobile) return;
                    googletag.defineSlot(path, sizes, id).addService(googletag.pubads());
                }

                // ---------- HOMEPAGE ----------
                <?php if ($is_home): ?>
                slot('/22071836792/SSM_tonedeafbrag/homepage_header', leaderboardSizes, 'div-gpt-homepage_header');
                slot('/22071836792/SSM_tonedeafbrag/homepage_desktop_sticky', [[728,90]], 'div-gpt-homepage_desktop_sticky', true);
                slot('/22071836792/SSM_tonedeafbrag/homepage_skin', skinSizes, 'div-gpt-homepage_skin', true);

                // Incontent & MREC
                for (let i = 1; i <= 6; i++) {
                    slot(`/22071836792/SSM_tonedeafbrag/homepage_incontent_${i}`, incontentSizes, `div-gpt-homepage_incontent_${i}`);
                }
                for (let i = 1; i <= 7; i++) {
                    slot(`/22071836792/SSM_tonedeafbrag/homepage_vrec_${i}`, vrecSizes, `div-gpt-homepage_vrec_${i}`);
                }

                slot('/22071836792/SSM_tonedeafbrag/homepage_mob_sticky_footer',
                    [[1,1],[300,50],[320,50]],
                    'div-gpt-homepage_mob_sticky_footer'
                );
                <?php endif; ?>

                // ---------- CATEGORY ----------
                <?php if ($is_category): ?>
                slot('/22071836792/SSM_tonedeafbrag/category_leaderboard', leaderboardSizes, 'div-gpt-category_leaderboard');
                slot('/22071836792/SSM_tonedeafbrag/category_mrec', mrecSizes, 'div-gpt-category_mrec');
                slot('/22071836792/SSM_tonedeafbrag/category_vrec', vrecSizes, 'div-gpt-category_vrec');
                slot('/22071836792/SSM_tonedeafbrag/category_skin', skinSizes, 'div-gpt-category_skin', true);
                slot('/22071836792/SSM_tonedeafbrag/category_desktop_sticky', [[728,90]], 'div-gpt-category_desktop_sticky', true);

                slot('/22071836792/SSM_tonedeafbrag/category_mob_sticky_footer',
                    [[1,1],[300,50],[320,50]],
                    'div-gpt-category_mob_sticky_footer'
                );
                <?php endif; ?>

                // ---------- ARTICLE ----------
                <?php if ($is_article): ?>
                slot('/22071836792/SSM_tonedeafbrag/article_leaderboard', leaderboardSizes, 'div-gpt-article_leaderboard');
                slot('/22071836792/SSM_tonedeafbrag/article_mrec', mrecSizes, 'div-gpt-article_mrec');
                slot('/22071836792/SSM_tonedeafbrag/article_incontent_1', incontentSizes, 'div-gpt-article_incontent_1');
                slot('/22071836792/SSM_tonedeafbrag/article_incontent_2', incontentSizes, 'div-gpt-article_incontent_2');
                slot('/22071836792/SSM_tonedeafbrag/article_vrec', vrecSizes, 'div-gpt-article_vrec');

                slot('/22071836792/SSM_tonedeafbrag/article_skin', skinSizes, 'div-gpt-article_skin', true);
                slot('/22071836792/SSM_tonedeafbrag/article_sticky', [[728,90]], 'div-gpt-article_sticky', true);

                slot('/22071836792/SSM_tonedeafbrag/article_mob_sticky_footer',
                    [[1,1],[300,50],[320,50]],
                    'div-gpt-article_mob_sticky_footer'
                );
                <?php endif; ?>

                slot('/22071836792/SSM_tonedeafbrag/outofpage', [], 'div-gpt-outofpage');
                slot('/22071836792/SSM_tonedeafbrag/preroll', [[640,480]], 'div-gpt-preroll');

                googletag.pubads().enableSingleRequest();
                googletag.enableServices();
            });
        </script>
        <?php
    }

    /*
     * Singleton
     */
  public static function get_instance()
  {
    if (!isset(static::$_instance)) {
      static::$_instance = new TBMAds();
    }
    return static::$_instance;
  }

  /*
   * Get Ad Tag
   */
  public function get_ad($ad_location = '', $slot_no = 0, $post_id = null, $device = '', $ad_width = '')
  {
    if ('' == $ad_location)
      return;

    if (is_page_template('page-templates/page-solstice-2021.php') || is_page_template('page-quiz.php')):
      return;
    endif;
    if ($ad_location == 'leaderboard') {
        $ad_location = 'header';
    }
    $html = '';
    $fuse_tags = self::fuse_tags();

    if (isset($_GET['screenshot'])) {
      $pagepath = 'screenshot';
    } else if (isset($_GET['dfp_key'])) {
      $pagepath = $_GET['dfp_key'];
    } else if (is_home() || is_front_page()) {
      $pagepath = 'homepage';
    } else {
      $pagepath_uri = substr(str_replace(['/', 'beta'], '', $_SERVER['REQUEST_URI']), 0, 40);
      $pagepath_e = explode('?', $pagepath_uri);
      $pagepath = $pagepath_e[0];
    }

    if (function_exists('amp_is_request') && amp_is_request()) {
      if (isset($fuse_tags['amp'][$ad_location]['sticky']) && $fuse_tags['amp'][$ad_location]['sticky']) {
        $html .= '<amp-sticky-ad layout="nodisplay">';
      }
      $html .= '<amp-ad
        width=' . $fuse_tags['amp'][$ad_location]['width']
        . ' height=' . $fuse_tags['amp'][$ad_location]['height']
        . ' type="doubleclick"'
        . ' data-slot="' . $fuse_tags['amp']['network_id'] . $fuse_tags['amp'][$ad_location]['slot'] . '"'
        . '></amp-ad>';
      if (isset($fuse_tags['amp'][$ad_location]['sticky']) && $fuse_tags['amp'][$ad_location]['sticky']) {
        $html .= '</amp-sticky-ad>';
      }
      return $html;
    } else {

      if (in_array($ad_location, ['mrec1', 'mrec_1'])) {
        $ad_location = 'rail1';
      } elseif (in_array($ad_location, ['mrec2', 'mrec_2'])) {
        $ad_location = 'rail2';
      }

      $fuse_id = null;

      $post_type = get_post_type(get_the_ID());

      $section = 'homepage';
      if (is_home() || is_front_page()) {
        $section = 'homepage';
      } elseif (is_category() || is_tax('genre')) {
        $term = get_queried_object();
        if ($term) {
          $category_parent_id = $term->category_parent;
          if ($category_parent_id != 0) {
            $category_parent = get_term($category_parent_id, 'category');
            $category = $category_parent->slug;
          } else {
            $category = $term->slug;
          }
        }
        $section = 'category';
      } elseif (is_archive()) {
        $section = 'category';
      } elseif (in_array($post_type, ['post', 'snaps', 'photo_gallery', 'country'])) {
        $section = 'article';
        if ($slot_no == 2) {
          $section = 'second_article';
        }

        $categories = get_the_category($post_id);
        if ($categories) {
          foreach ($categories as $category_obj):
            $category = $category_obj->slug;
            break;
          endforeach;
        }
      }

      $tags = get_the_tags($post_id);
      if ($tags) {
        $tag_slugs = wp_list_pluck($tags, 'slug');
      }

      if (isset($section)) {
        if (isset($fuse_tags[$section][$ad_location])) {
          $fuse_id = $fuse_tags[$section][$ad_location];
        }

      } else {
        $fuse_id = $fuse_tags[$ad_location];
      }
        $gpt_id = $section . '_' . $ad_location;
        $html .= '<!--' . $post_id . ' | '  . $section . ' | ' . $ad_location . ' | ' . $slot_no . '-->';

        $html .= '<div id="div-gpt-' . $gpt_id . '" style="margin: auto; text-align: center">';
        $html .= '<script>googletag.cmd.push(function() { googletag.display("' . $gpt_id . '"); });</script>';
        $html .= '</div>';

        if ($slot_no > 1) {
            $html .= '<script>
                googletag.cmd.push(function() {
                  googletag.display("div-gpt-' . $gpt_id . '");
                });
                    </script>';
        } else {
            $html .= '<script type="text/javascript">
                window.googletag = window.googletag || {cmd: []};
                googletag.cmd.push(function() {';
            if (isset($category)) {
                $html .= 'googletag.pubads().setTargeting("fuse_category", ["' . $category . '"]);';
            }

            if (isset($tag_slugs)) {
                $html .= 'googletag.pubads().setTargeting("tbm_tags", ' . json_encode($tag_slugs) . ');';
            }

            if (isset($pagepath)) {
                $html .= 'googletag.pubads().setTargeting("pagepath", ["' . $pagepath . '"]);';
            }

            $html .= '
                </script>';
      }

      return $html;
    }
  }

  private static function fuse_tags()
  {
    return [
      'amp' => [
        'network_id' => '/22071836792/SSM_tonedeafbrag/',
        'header' => [
          'width' => 320,
          'height' => 50,
          'slot' => 'AMP_Header',
        ],
        'mrec_1' => [
          'width' => 300,
          'height' => 250,
          'slot' => 'AMP_mrec_1',
        ],
        'mrec_2' => [
          'width' => 300,
          'height' => 250,
          'slot' => 'AMP_mrec_2',
        ],
        'sticky_footer' => [
          'width' => 320,
          'height' => 50,
          'slot' => 'AMP_sticky_footer',
          'sticky' => true
        ]
      ],
      'article' => [
        'skin' => '22378678033',
        'leaderboard' => '22378619009',

        'mrec' => '22378619012',
        'rail1' => '22378619012',

        'vrec' => '22378678030',
        'rail2' => '22378678030',

        'incontent_1' => '22378564857',
        'inbody1' => '22378564857',

        'incontent_2' => '22378619015',
        'desktop_sticky' => '22378678339',
        'mob_sticky' => '22378566870',
          'oop' => '22779890848'
      ],
      'second_article' => [
        'skin' => '22378566867',
        'leaderboard' => '22378564860',

        'mrec' => '22378566873',
        'rail1' => '22378566873',

        'vrec' => '22378678342',
        'rail2' => '22378678342',

        'incontent_1' => '22378678345',
        'inbody1' => '22378678345',

        'incontent_2' => '22378566876',
              'oop' => '22779890848'
      ],
      'category' => [
        'skin' => '22378678021',

        'leaderboard' => '22378619003',

        'mrec' => '22378678018',
        'rail1' => '22378678018',
        'vrec_1' => '22378678018',

        'vrec' => '22378619006',
        'rail2' => '22378619006',
        'vrec_2' => '22378619006',

        'desktop_sticky' => '22378678027',
        'mob_sticky' => '22378678336',
              'oop' => '22779890848'
      ],
      'homepage' => [
        'desktop_sticky' => '22378677994',

        'vrec_1' => '22378677997',
        'rail1' => '22378677997',

        'vrec_2' => '22378678003',
        'rail2' => '22378678003',

        'vrec_3' => '22378618985',
        'vrec_4' => '22378618991',
        'vrec_5' => '22378678006',
        'vrec_6' => '22378618994',
        'vrec_7' => '22378564851',

        'header' => '22378618988',
        'leaderboard' => '22378618988',

        'skin' => '22378678000',

        'incontent_1' => '22378678009',
        'inbody1' => '22378678009',

        'incontent_2' => '22378619000',
        'inbody2' => '22378619000',

        'incontent_3' => '22378678015',

        'incontent_4' => '22378618997',
        'incontent_5' => '22378678012',

        'incontent_6' => '22378564854',

        'mob_sticky' => '22378678024',
              'oop' => '22779890848'
      ]
    ];
  }
}

TBMAds::get_instance();
