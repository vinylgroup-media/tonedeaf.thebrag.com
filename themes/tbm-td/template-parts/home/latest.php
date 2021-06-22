<?php
extract($args);
$posts_per_page = 1 === $paged ? 5 : 11;
$news_args = array(
    'post_status' => 'publish',
    'post_type' => array('post', 'photo_gallery'),
    'ignore_sticky_posts' => 1,
    // 'post__not_in' => $exclude_posts,
    'posts_per_page' => $posts_per_page,
    'paged' => $paged,
);
$news_query = new WP_Query($news_args);
$no_of_columns = 2;
if ($news_query->have_posts()) :
    $count = 1;
?>
    <section class="container latest pb-3<?php echo $paged !== 1 ? ' rounded-top' : ''; ?>">
        <div class="m-2">
            <?php if (1 === $paged) { ?>
                <h2 class="p-1 pt-0 mb-0 mx-0 mx-md-1 h-latest border-bottom">Latest</h2>
            <?php } ?>
            <div class="d-flex flex-wrap align-items-start mt-2">
                <?php
                while ($news_query->have_posts()) :
                    $news_query->the_post();
                    $post_id = get_the_ID();

                    $genre_name = '';

                    $genres = get_the_terms($post_id, 'genre');
                    if ($genres) :
                        $genre_name = $genres[0]->name;
                    endif;
                ?>
                    <div class="col-12 col-md-4">
                        <?php get_template_part('template-parts/single/tile', null, ['genre_name' => $genre_name]); ?>
                    </div>
                <?php
                    $count++;
                endwhile; ?>
                <div class="col-6 col-md-4">
                    <article class="my-3">
                        <div class="ad-mrec mt-5">
                            <div class="mx-auto text-center">
                                <?php render_ad_tag('vrec_2'); ?>
                            </div>
                        </div>
                    </article>
                </div>
            </div>
        </div>
        <div class="d-flex">
            <?php if (1 === $paged) { ?>
                <a href="<?php echo home_url('/page/2'); ?>" class="btn btn-dark text-uppercase">More Stories</a>
            <?php } else { ?>
                <div class="col-12 col-lg-6 offset-lg-3 page-nav">
                    <div class="d-flex justify-content-center my-4">
                        <div class="m-3"><?php previous_posts_link('Newer', $news_query->max_num_pages); ?></div>
                        <div class="m-3"><?php next_posts_link('Older', $news_query->max_num_pages); ?> </div>
                    </div>
                <?php } ?>
                </div>
    </section>
<?php endif;
