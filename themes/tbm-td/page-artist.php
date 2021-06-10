<?php /* Template Name: Artist Profile */ ?>
<?php get_header(); ?>

<div class="ad-billboard ad-billboard-top container py-1 py-md-2">
    <div class="mx-auto text-center">
        <?php render_ad_tag('leaderboard'); ?>
    </div>
</div>

<section class="container px-2 bg-white">
    <div class="row">
        <?php
        $pa = get_query_var('paged');
        global $wp_query;
        global $wpdb;
        $artistnameslug = $wp_query->query_vars['artistnameslug'];

        if (is_null($artistnameslug))
            wp_redirect('/');

        $artist = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}td_artist_urls WHERE artist_slug = '{$artistnameslug}' LIMIT 1");

        if (!is_null($artist)) :

            if (isset($artist->image_id) && $artist->image_id > 0) :
                $artist_img_src = wp_get_attachment_image_src($artist->image_id, 'full');
                $alt_text = get_post_meta($artist->image_id, '_wp_attachment_image_alt', true);
                if ($alt_text == '') {
                    $alt_text = $artist->artist_name;
                }
        ?>

                <div class="post-thumbnail col-12">
                    <img src="<?php echo $artist_img_src[0]; ?>" id="artist-header-src" style="display:block;" alt="<?php echo $alt_text; ?>" title="<?php echo $alt_text; ?>">
                </div>

            <?php endif; // If there is an artist header image 
            ?>

            <h1 class="col-12 m-3"><?php echo $artist->artist_name; ?></h1>

            <div class="col-12">
                <ul class="nav flex-row">
                    <?php if ($artist->facebook) : ?>
                        <li class="l_social facebook"><a href="<?php echo addhttp($artist->facebook); ?>" target="_blank" class="text-dark px-2"><i class="fa fa-facebook fa-lg" aria-hidden="true"></i></a></li>
                    <?php endif; ?>
                    <?php if ($artist->twitter) : ?>
                        <li class="l_social twitter"><a href="<?php echo addhttp($artist->twitter); ?>" target="_blank" class="text-dark px-2"><i class="fa fa-twitter fa-lg" aria-hidden="true"></i></a></li>
                    <?php endif; ?>
                    <?php if ($artist->instagram) : ?>
                        <li class="l_social instagram"><a href="<?php echo addhttp($artist->instagram); ?>" target="_blank" class="text-dark px-2"><i class="fa fa-instagram fa-lg" aria-hidden="true"></i></a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="col-12">
                <p><?php echo wpautop($artist->intro_para); ?></p>
            </div>

            <?php
            $discographies = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}td_artist_discography WHERE artist_id = '{$artist->artist_ID}' ORDER BY album_release_year");
            if (count($discographies) > 0) :
            ?>
                <div class="col-12">
                    <h2>Discography</h2>
                    <table class="table table-striped table-sm">
                        <?php foreach ($discographies as $discography) : ?>
                            <tr>
                                <?php if (isset($discography->image_id) && $discography->image_id > 0) :
                                    $album_img_src = wp_get_attachment_image_src($discography->image_id, 'medium_large');
                                    $alt_text = get_post_meta($discography->image_id, '_wp_attachment_image_alt', true);
                                    if ($alt_text == '') {
                                        $alt_text = $discography->album_title;
                                    }
                                ?>
                                    <td width="100"><img src="<?php echo $album_img_src[0]; ?>" alt="<?php echo $alt_text; ?>" title="<?php echo $alt_text; ?>"></td>
                                    <td>
                                        <p><?php echo $discography->album_title; ?></p>
                                        <p><?php echo $discography->album_release_year; ?></p>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            <?php endif; ?>

            <?php
            $args = array(
                's' => $artist->artist_name,
                //            'showposts' => 5,
                'paged' => $pa,
                'post_status' => 'publish',
                'sentence' => true,
            );
            query_posts($args);
            if (have_posts()) :
                $count = 1;
            ?>
                <div class="col-12">
                    <div class="row posts">

                        <?php
                        $show_cats = true;
                        while (have_posts()) :
                            the_post();
                            get_template_part('template-parts/single/tile');
                            $count++;
                        endwhile;
                        ?>
                    </div>
                </div>
            <?php endif; // If there are posts 
            ?>
    </div>
<?php endif; // If $artist_name is NOT NULL  
?>

</section>

<?php get_footer();
