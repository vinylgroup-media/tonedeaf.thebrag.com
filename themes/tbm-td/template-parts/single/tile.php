<?php extract($args); ?>
<article class="my-1 my-md-3">
    <div class="mb-4 mx-0 mx-md-3">
        <a href="<?php the_permalink(); ?>" class="d-flex flex-row flex-md-column align-items-start">
            <?php if (isset($genre_name)) : ?>
                <div class="mb-2 text-uppercase cat d-none d-md-block">
                    <?php echo isset($genre_name) && '' != $genre_name ? $genre_name : '&nbsp;'; ?>
                </div>
            <?php endif; ?>
            <div class="post-thumbnail p-r">
                <?php
                if ('' !== get_the_post_thumbnail()) :
                    the_post_thumbnail('thumbnail');
                else :
                    echo '<img src="' . IMAGES_R2_CDN_URL . 'td/3/placeholder.png">';
                endif;
                ?>
            </div>
            <div class="pl-2 post-content align-self-start col-auto">
                <div class="mb-2 text-uppercase cat d-block d-md-none">
                    <?php echo isset($genre_name) && '' != $genre_name ? $genre_name : ''; ?>
                </div>
                <h3 class="my-2"><?php the_title(); ?></h3>
                <p class="excerpt d-none d-md-block">
                    <?php
                    $author_name =
                        get_field('photographer') ? get_field('photographer') : (get_field('author') ? get_field('author') : (get_field('Author') ? get_field('Author') : get_the_author_meta('first_name', $post->post_author) . ' ' . get_the_author_meta('last_name', $post->post_author)));

                    $metadesc = get_post_meta(get_the_ID(), '_yoast_wpseo_metadesc', true);
                    $excerpt = trim($metadesc) != '' ? $metadesc : string_limit_words(get_the_excerpt(), 25);
                    echo $excerpt;
                    ?>
                </p>
            </div>
        </a>
    </div>
</article>