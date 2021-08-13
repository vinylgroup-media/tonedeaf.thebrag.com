<?php extract($args); ?>
<div id="<?php echo isset($container_id) ? $container_id : 'observer-list-top'; ?>" class="observer-list p-2 bg-dark text-white" <?php echo isset($show_container) && $show_container ? '' : 'style="display: none;"'; ?>>
  <h2><strong>Pick your niche.</strong> Follow the topics you want.</h2>
  <p class="font-primary desc">Tick to subscribe, untick to unsubscribe from any newsletters below:</p>

  <?php
  $my_sub_lists = [];

  $brag_api_url_base = 'https://thebrag.com/';

  if (is_user_logged_in()) :
    $current_user = wp_get_current_user();
    $brag_api_url = $brag_api_url_base . 'wp-json/brag_observer/v1/get_topics/?key=' . BRAG_API_KEY . '&email=' . $current_user->user_email . '&site=tonedeaf.thebrag.com';
  else :
    $brag_api_url = $brag_api_url_base . 'wp-json/brag_observer/v1/get_topics/?key=' . BRAG_API_KEY . '&site=tonedeaf.thebrag.com';
  endif;

  $response = wp_remote_get($brag_api_url);
  $responseBody = wp_remote_retrieve_body($response);
  $resonseJson = json_decode($responseBody);
  $lists = $resonseJson->data;

  if ($lists) :
  ?>
    <div class="d-flex flex-row flex-wrap justify-content-start topics <?php echo is_user_logged_in() ? 'topics-active' : ''; ?>">
      <?php foreach ($lists as $index => $list) : ?>
        <a href="http://thebrag.com/wp-login.php?redirect_to=<?php echo urlencode($list->link); ?>" class="d-flex <?php echo is_user_logged_in() ? (isset($list->subscribed) && $list->subscribed ? 'subscribed' : '') : 'not-logged-in'; ?>" target="_blank" data-list="<?php echo $list->id; ?>" rel="noreferrer">
          <span class="text-primary tick mr-1"><img src="<?php echo ICONS_URL; ?>check.svg" width="16" height="16" alt="-"></span>
          <span class="text-primary plus mr-1"><img src="<?php echo ICONS_URL; ?>plus-td.svg" width="16" height="16" alt="+"></span>
          <span class="text-primary plus-hover mr-1"><img src="<?php echo ICONS_URL; ?>plus.svg" width="16" height="16" alt="+"></span>
          <span><?php echo !in_array($list->id, [4,]) ? trim(str_ireplace('Observer', '', $list->title)) : trim($list->title); ?></span>
        </a>
      <?php endforeach; // For Each $list in $lists 
      ?>
    </div>
  <?php
  endif; // If $lists
  ?>
</div>