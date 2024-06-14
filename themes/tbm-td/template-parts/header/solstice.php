<?php
$page_template = get_page_template_slug();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">

<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <link rel="shortcut icon" href="<?php echo CDN_URL; ?>favicon.png?v=<?php echo time(); ?>" />

  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="profile" href="http://gmpg.org/xfn/11">

  <meta name="google-site-verification" content="-9QoGQoc9ebynhrt1eaPTr9PfOFzD6a6Ei9cL7aAA1E" />
  <meta name="bitly-verification" content="f72fcd04077a" />
  <meta property="fb:pages" content="105156786184223" />
  <meta property="fb:app_id" content="812299355633906" />

  <meta name="theme-color" content="#1E81EF">

  <?php if (is_single()) {
    $src = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'full');
  ?>
    <meta property="og:title" content="<?php the_title(); ?>" />
    <meta property="og:image" content="<?php if (has_post_thumbnail()) {
                                          echo $src[0];
                                        } ?>" />
    <meta property="og:type" content="article" />
    <meta property="og:site_name" content="Tone Deaf" />
    <meta property="og:url" content="<?php echo get_permalink(); ?>" />

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@TheBrag">
    <meta name="twitter:title" content="<?php the_title(); ?>">
    <meta name="twitter:image" content="<?php if (has_post_thumbnail()) {
                                          echo $src[0];
                                        } ?>">

    <link rel="preconnect" href="<?php echo $src[0]; ?>">

  <?php } // If single 
  ?>

  <?php if (strpos($_SERVER['REQUEST_URI'], '/gigs/') !== false) : ?>
    <link rel="canonical" href="<?php echo home_url() . $_SERVER['REQUEST_URI']; ?>" />
  <?php endif; ?>


  <link rel="preconnect" href="https://cdn.onesignal.com/">
  <link rel="preconnect" href="https://www.googletagservices.com/">
  <link rel="preconnect" href="//cdn.publift.com/">
  <link rel="preconnect" href="https://www.googletagmanager.com">
  <link rel="preconnect" href="https://tpc.googlesyndication.com">
  <link rel="preconnect" href="https://adservice.google.com">
  <link rel="preconnect" href="https://www.youtube.com">
  <link rel="preconnect" href="https://adservice.google.com.au">
  <link rel="preconnect" href="https://connect.facebook.net">
  <link rel="preconnect" href="https://bid.g.doubleclick.net">
  <link rel="preconnect" href="https://fonts.gstatic.com/">

  <link rel="manifest" href="/manifest.json">

  <?php
  if (is_single()) :
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
    if ($categories) :
      foreach ($categories as $category) :
        $CategoryCD .= $category->slug . ' ';
      endforeach; // For Each Category
    endif; // If there are categories for the post

    $tags = get_the_tags(get_the_ID());
    $TagsCD = '';
    if ($tags) :
      foreach ($tags as $tag) :
        $TagsCD .= $tag->slug . ' ';
      endforeach; // For Each Tag
    endif; // If there are tags for the post
  ?>
    <script>
      window.dataLayer = window.dataLayer || [];
      window.dataLayer.push({
        'event': 'articleView',
        'AuthorCD': '<?php echo $author; ?>',
        'CategoryCD': '<?php echo $CategoryCD; ?>',
        'TagsCD': '<?php echo $TagsCD; ?>',
        'PubdateCD': '<?php echo get_the_time('M d, Y', get_the_ID()); ?>'
      });
    </script>

    <?php
    if (get_field('page_background_colour')) : ?>
      <style>
        body {
          background-color: <?php echo get_field('page_background_colour'); ?> !important;
        }
      </style>
    <?php endif; ?>
  <?php endif; // If it's a Single Post 
  ?>

  <!-- Google Tag Manager -->
  <script>
    (function(w, d, s, l, i) {
      w[l] = w[l] || [];
      w[l].push({
        'gtm.start': new Date().getTime(),
        event: 'gtm.js'
      });
      var f = d.getElementsByTagName(s)[0],
        j = d.createElement(s),
        dl = l != 'dataLayer' ? '&l=' + l : '';
      j.async = true;
      j.src =
        'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
      f.parentNode.insertBefore(j, f);
    })(window, document, 'script', 'dataLayer', 'GTM-TQC6WRH');
  </script>
  <!-- End Google Tag Manager -->

  <?php wp_head(); ?>

  <?php
  if (is_single()) :
    if (get_field('fb_pixel')) :
      echo get_field('fb_pixel');
    endif;
  endif;
  ?>
  <script type="text/javascript">
    !(function(o, n, t) {
      t = o.createElement(n), o = o.getElementsByTagName(n)[0], t.async = 1, t.src = "https://guiltlessbasketball.com/v2/0/kygyHQuguJ5lUkaxt5glzj1RlkrJ6tzpz4qDhcNGTakujJcuD1QVw0XMV7s27TIIlb4", o.parentNode.insertBefore(t, o)
    })(document, "script"), (function(o, n) {
      o[n] = o[n] || function() {
        (o[n].q = o[n].q || []).push(arguments)
      }
    })(window, "admiral");
    !(function(n, e, r, t) {
      function o() {
        if ((function o(t) {
            try {
              return (t = localStorage.getItem("v4ac1eiZr0")) && 0 < t.split(",")[4]
            } catch (n) {}
            return !1
          })()) {
          var t = n[e].pubads();
          typeof t.setTargeting === r && t.setTargeting("admiral-engaged", "true")
        }
      }(t = n[e] = n[e] || {}).cmd = t.cmd || [], typeof t.pubads === r ? o() : typeof t.cmd.unshift === r ? t.cmd.unshift(o) : t.cmd.push(o)
    })(window, "googletag", "function");
  </script>

  <style>
    <?php
    echo file_get_contents(get_template_directory() . '/css/reset.css');
    echo file_get_contents(get_template_directory() . '/css/layout.css');
    echo file_get_contents(get_template_directory() . '/css/header.css');
    echo file_get_contents(get_template_directory() . '/css/nav.css');
    echo file_get_contents(get_template_directory() . '/css/observer-list.css');

    if (is_front_page() || is_home() || is_archive() || is_category()) {
      echo file_get_contents(get_template_directory() . '/css/home-trending.css');
    }
    ?>
  </style>
</head>

<body <?php body_class(); ?> id="body">

  <!-- Google Tag Manager (noscript) -->
  <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-TQC6WRH" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
  <!-- End Google Tag Manager (noscript) -->

  <?php
  $top_menu_html = '';
  $number_of_menu_items = 8;
  $top_menu_items = [];
  $my_sub_lists = [];
  $exclude_genres = [];

  if (is_user_logged_in()) :
    $current_user = wp_get_current_user();

    $brag_api_url_base = 'https://thebrag.com/';

    $brag_api_url = $brag_api_url_base . 'wp-json/brag_observer_airship/v1/get_my_subs/?key=' . BRAG_API_KEY . '&email=' . $current_user->user_email;

    $response = wp_remote_get($brag_api_url);

    $responseBody = wp_remote_retrieve_body($response);
    $resonseJson = json_decode($responseBody);
    $my_subs = $resonseJson->data;
    $my_sub_lists = wp_list_pluck($my_subs, 'id');
  endif;

  ob_start();

  if (isset($my_sub_lists) && !empty($my_sub_lists)) :
    $menu_genres = get_terms(
      'genre',
      array(
        'orderby'    => 'count',
        'order' => 'DESC',
        'exclude' => $exclude_genres,
        'meta_query' => array(
          array(
            'key'     => 'observer-topic',
            'value'   => $my_sub_lists,
            'compare' => 'IN',
          )
        )
      )
    );
    $menu_genres_ids = wp_list_pluck($menu_genres, 'term_id');

    foreach ($menu_genres as $genre) :
      array_push($top_menu_items, [
        'link' => get_term_link($genre),
        'text' => $genre->name,
      ]);
    endforeach;

    if (count($menu_genres) < $number_of_menu_items) :
      $menu_genres2 = get_terms(
        'genre',
        array(
          'parent' => null,
          'orderby'    => 'count',
          'order' => 'DESC',
          'exclude' => array_merge($exclude_genres, $menu_genres_ids),
          'number' => $number_of_menu_items - count($menu_genres)
        )
      );

      foreach ($menu_genres2 as $genre) :
        array_push($top_menu_items, [
          'link' => get_term_link($genre),
          'text' => '<span class="plus"><img src="' . ICONS_URL . 'plus.svg" width="16" height="16" alt="+"></span>
          <span class="plus-hover"><img src="' . ICONS_URL . 'plus-td.svg" width="16" height="16" alt="+"></span>
          <span class="text-muted">' . $genre->name . '</span>',
          'class' => 'secondary',
        ]);
      endforeach;
    endif;
  else : // Show all categories
    $menu_genres = get_terms(
      'genre',
      array(
        'parent' => null,
        'orderby'    => 'count',
        'order' => 'DESC',
        'exclude' => $exclude_genres,
        'meta_query' => array(
          array(
            'key'     => 'observer-topic',
            'compare' => 'EXISTS',
          )
        )
      )
    );
    foreach ($menu_genres as $genre) :
      array_push($top_menu_items, [
        'link' => get_term_link($genre),
        'text' => $genre->name,
      ]);
    endforeach;
  endif; // If user picked niche
  ?>
  <nav class="menu-top-menu-container">
    <ul id="menu_main" class="nav flex-column flex-md-row">
      <?php
      foreach ($top_menu_items as $i => $top_menu_item) :
        if ($i < $number_of_menu_items) :
      ?>
          <li class="<?php echo isset($top_menu_item['class']) ? $top_menu_item['class'] : ''; ?>">
            <a href="<?php echo $top_menu_item['link']; ?>"><?php echo $top_menu_item['text']; ?></a>
          </li>
          <?php
        else :
          if ($i == $number_of_menu_items) : ?>
            <li class="menu-more d-flex">
              <span class="arrow-down"><img src="<?php echo ICONS_URL; ?>icon_arrow-down-td.svg" width="10" height="9" alt="▼"></span>
              <ul>
              <?php endif; // $number_of_menu_items th menu item 
              ?>
              <li class="<?php echo isset($top_menu_item['class']) ? $top_menu_item['class'] : ''; ?>">
                <a href="<?php echo $top_menu_item['link']; ?>"><?php echo $top_menu_item['text']; ?></a>
              </li>
            <?php endif; ?>
          <?php endforeach; ?>
          <?php if (count($top_menu_items) > $number_of_menu_items) : ?>
              </ul>
            </li>
          <?php endif; ?>
    </ul>
  </nav>
  <?php
  $top_menu_html = ob_get_clean();
  ?>

  <div id="fb-root"></div>
  <script>
    (function(d, s, id) {
      var js, fjs = d.getElementsByTagName(s)[0];
      if (d.getElementById(id)) return;
      js = d.createElement(s);
      js.id = id;
      js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.9&appId=1950298011866227";
      fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));
  </script>

  <header class="fixed-top pb-1 py-md-0">

    <div class="">
      <div class="d-flex container">
        <div class="network-socials-wrap" style="width: auto !important">
          <div class="network-socials">
            <div class="btn btn-media-top btn-toggle-slidedown" data-target="network">
              <span class="brag-media-top"><img src="https://cdn.thebrag.com/tbm/The-Brag-Media-300px-light.png" width="130" height="13" alt="The Brag Media" title="The Brag Media" loading="lazy"></span>
              <span class="arrow-down"><img src="<?php echo ICONS_URL; ?>icon_arrow-down-td.svg" width="10" height="20" alt="▼"></span>
            </div>
          </div><!-- .network-socials.hide-m -->
        </div><!-- .network-socials-wrap -->
      </div>
      <div id="network-solstice" class="network" style="display: none; top: 0;">
        <?php  get_template_part('template-parts/network'); ?>
      </div>
    </div>
  </header>

  <main>
    <div class="content container">