</div>
<div class="clear"></div>
</div>

<script>
    var BASE = "<?php echo home_url() ?>";
    var SITE_NAME = "<?php echo html_entity_decode(get_bloginfo('name'), ENT_QUOTES); ?>";
    var window_width = jQuery(window).width();
    var window_height = jQuery(window).height();
</script>

<script src="https://www.youtube.com/iframe_api" defer></script>
<script>
    jQuery(document).ready(function($) {
        $('body').on('click', '.yt-lazy-load', function() {
            var video_id = $(this).data('id');
            var player_id = $(this).prop('id');
            var player_height = $(this).height();

            var player;
            player = new YT.Player(player_id, {
                height: player_height,
                videoId: video_id,
                events: {
                    'onReady': onPlayerReady,
                }
            });
            function onPlayerReady(event) {
                event.target.playVideo();
            }
        });

//        if ( $(window).outerHeight() >= 1200 ) {
//            $('body').addClass('fixed-bg');
//        }
        $(window).scroll(function() {
            if ( ( $(window).outerHeight() + $(window).scrollTop() ) >= 1200 ) {
                if ( ! $('body').hasClass('fixed-bg') )
                    $('body').addClass('fixed-bg');
            } else {
                if ( $('body').hasClass('fixed-bg') )
                    $('body').removeClass('fixed-bg');
            }
        });
    });
</script>


<noscript id="deferred-styles">
<link href="https://fonts.googleapis.com/css?family=Vollkorn:400,400i,600,600i" rel="stylesheet">
<link href="https://use.typekit.net/aps1dbw.css" rel="stylesheet">
<link rel='stylesheet' id='style-css' href='<?php echo get_template_directory_uri(); ?>/css/style-combined.min.css?v=20190123' type='text/css' media='all' />
</noscript>

<script>
    var loadDeferredStyles = function() {
      var addStylesNode = document.getElementById("deferred-styles");
      var replacement = document.createElement("div");
      replacement.innerHTML = addStylesNode.textContent;
      document.body.appendChild(replacement)
      addStylesNode.parentElement.removeChild(addStylesNode);
    };
    var raf = window.requestAnimationFrame || window.mozRequestAnimationFrame ||
        window.webkitRequestAnimationFrame || window.msRequestAnimationFrame;
    if (raf) raf(function() { window.setTimeout(loadDeferredStyles, 0); });
    else window.addEventListener('load', loadDeferredStyles);
</script>

<?php wp_footer(); ?>

</div>

<div class="skin-kraken">
    <div id="skin-ad">
      <a href="https://www.krakenrum.com/" target="_blank">
        <img src="<?php echo get_template_directory_uri(); ?>/images/skin-kraken.jpg">
      </a>
        <!-- <div data-fuse="21789204053"></div> -->
    </div>
</div>

<?php if ( is_single() ) :
$post_featured_images = array();
$img_src1 = wp_get_attachment_image_src( get_post_thumbnail_id( $post ), 'full' );
$img_src2 = wp_get_attachment_image_src( get_post_thumbnail_id( $post ), 'thumbnail' );
$img_src3 = wp_get_attachment_image_src( get_post_thumbnail_id( $post ), 'medium' );
array_push( $post_featured_images, '"' . $img_src1[0] . '"', '"' . $img_src2[0] . '"', '"' . $img_src3[0] . '"' );

if ( get_field( 'author' ) ) {
    $author = get_field( 'author' );
} else if ( get_field( 'Author' ) ) {
    $author =get_field( 'Author' );
} else {
    if ( '' != get_the_author_meta( 'first_name', $post->post_author ) && '' != get_the_author_meta( 'last_name', $post->post_author ) ) {
        $author = get_the_author_meta( 'first_name', $post->post_author ) . ' ' . get_the_author_meta( 'last_name', $post->post_author );
    } else {
        $author = get_the_author_meta( 'display_name', $post->post_author );
    }
}
?>
<script type="application/ld+json">
{
  "@context": "http://schema.org",
  "@type": "<?php echo ! in_category( 'evergreen', $post ) ? "NewsArticle" : "BlogPosting"; ?>",
  "mainEntityOfPage": {
    "@type": "WebPage",
    "@id": "<?php echo get_permalink(); ?>"
  },
  "headline": "<?php echo get_the_title(); ?>",
  "image": [
    <?php if ( count( $post_featured_images ) > 0 ): echo implode( ',', $post_featured_images ); endif; ?>
   ],
  "datePublished": "<?php echo date( 'Y-m-d\TH:i:s+10:00', get_the_time( 'U' ) ); ?>",
  "dateModified": "<?php echo the_modified_date( 'Y-m-d\TH:i:s+10:00' ); ?>",
  "author": {
    "@type": "Person",
    "name": "<?php echo $author; ?>"
  },
   "publisher": {
    "@type": "Organization",
    "name": "<?php echo get_bloginfo( 'name' ); ?>",
    "logo": {
      "@type": "ImageObject",
      "url": "<?php echo get_template_directory_uri(); ?>/images/logo-tondeaf-blue-200px.png"
    }
  },
  "description": "<?php echo get_bloginfo( 'description' ); ?>"
}
</script>
<?php endif; ?>

</body>
</html>
