<?php
/**
 * Shared shell for the legal pages (Terms of Use, Privacy Policy).
 *
 * The copy lives in each page's own content so it can be edited in
 * wp-admin; this file supplies only the layout and typography.
 */
?>

<div class="ad-billboard ad-billboard-top container py-1 py-md-2">
    <div class="mx-auto text-center">
        <?php render_ad_tag('leaderboard'); ?>
    </div>
</div>

<section class="container bg-white p-2 p-md-4">
    <article class="legal">
        <h1><?php the_title(); ?></h1>

        <div class="post-content">
            <?php the_content(); ?>
        </div>
    </article>
</section>

<style>
    .legal {
        max-width: 50rem;
        margin: 0 auto;
    }

    .legal h1 {
        font-size: 2rem;
        margin-bottom: 2rem;
    }

    .legal h2 {
        font-size: 1.125rem;
        margin: 2.5rem 0 .75rem;
        line-height: 1.35;
    }

    .legal h2 .num {
        color: #1e81ef;
        margin-right: .25rem;
    }

    .legal p {
        margin: 0 0 1rem;
    }

    .legal ol,
    .legal ul {
        margin: 0 0 1rem;
        padding-left: 1.75rem;
    }

    .legal ol {
        list-style: decimal;
    }

    .legal ol[type="a"] {
        list-style: lower-alpha;
    }

    .legal ol[type="i"] {
        list-style: lower-roman;
    }

    .legal ul {
        list-style: disc;
    }

    .legal li {
        margin-bottom: .75rem;
    }

    .legal li > ol,
    .legal li > ul {
        margin-top: .75rem;
    }

    .legal li > p {
        margin: .75rem 0 0;
    }
</style>
