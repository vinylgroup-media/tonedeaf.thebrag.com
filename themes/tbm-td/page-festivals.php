<?php /* Template Name: Festivals ( List ) */ ?>
<?php get_header(); ?>

<section class="container latest px-2 bg-white">
    <div class="row">
        <h1 class="col-12 archive-title my-3"><?php the_title(); ?></h1>
    </div>
    <div class="row">
        <div class="col-12">
            <?php the_content(); ?>
        </div>
    </div>
    <div class="d-flex flex-wrap align-items-start mt-2">
        <?php
        global $wpdb;
        $artists = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}td_artist_urls WHERE url_slug = 'festival' ORDER BY artist_name ASC");
        if (count($artists) > 0) :
            foreach ($artists as $key => $artist) :
                if (isset($artist->image_id) && $artist->image_id > 0) :
                    $artist_img_src = wp_get_attachment_image_src($artist->image_id, 'medium_large');
        ?>
                    <div class="article-wrap col-12 col-md-4">
                        <article class="my-1 my-md-3">
                            <div class="mb-4 mx-0 mx-md-3">
                                <a href="/<?php echo $artist->url_slug; ?>/<?php echo $artist->artist_slug; ?>" class="d-flex flex-row flex-md-column align-items-start">
                                    <div class="post-thumbnail p-r">
                                        <img src="<?php echo $artist_img_src[0]; ?>" alt="<?php echo $artist->artist_name; ?>" title="<?php echo $artist->artist_name; ?>">
                                    </div>
                                    <div class="pl-2 post-content align-self-start col-auto">
                                        <h3 class="mb-2"><?php echo $artist->artist_name; ?></h3>
                                        <p><?php echo $artist->metadesc; ?></p>
                                    </div>
                                </a>
                            </div>
                        </article>
                    </div>
        <?php endif;
            endforeach;
        endif;
        ?>
    </div>
</section>
<?php get_footer();
