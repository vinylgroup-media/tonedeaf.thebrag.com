<?php /* Template Name: Festivals ( List ) */ ?>
<?php get_header(); ?>

<div class="container artists">
    <div class="row">
        <h1 class="col-12 archive-title my-3"><?php the_title(); ?></h1>
    </div>
    <div class="row">
      <div class="col-12">
        <?php the_content(); ?>
      </div>
    </div>
    <div class="row posts">
        <?php
        global $wpdb;
        $artists = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}td_artist_urls WHERE url_slug = 'festival' ORDER BY artist_name ASC" );
        if ( count ( $artists ) > 0 ) :
            foreach ( $artists as $key => $artist ) :
            if ( isset( $artist->image_id ) && $artist->image_id > 0 ):
                $artist_img_src = wp_get_attachment_image_src( $artist->image_id, 'medium_large' );
        ?>
        <div class="col-md-4 col-6 mb-5">
            <a href="/<?php echo $artist->url_slug; ?>/<?php echo $artist->artist_slug; ?>" class="text-dark">
                <div class="post-thumbnail">
                    <img src="<?php echo $artist_img_src[0]; ?>" alt="<?php echo $artist->artist_name; ?>" title="<?php echo $artist->artist_name; ?>">
                </div>
                <h3 class="artist-name mt-1"><?php echo $artist->artist_name; ?></h3>
                <p><?php echo $artist->metadesc; ?></p>
            </a>
        </div>
        <?php endif;
            endforeach;
        endif;
        ?>
    </div>

    <div class="my-3 pb-2"><?php get_fuse_tag( 'hrec_2' ); ?></div>
</div>



<?php get_footer();
