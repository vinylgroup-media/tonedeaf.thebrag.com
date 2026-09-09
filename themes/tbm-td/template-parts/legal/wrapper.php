<?php
/**
 * Shared shell for the legal pages (Terms of Use, Privacy Policy).
 *
 * Expects $legal_doc (partial name under template-parts/legal) to be set by
 * the calling page template.
 */

$legal_doc = isset($legal_doc) ? $legal_doc : '';
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
            <?php get_template_part('template-parts/legal/' . $legal_doc); ?>
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
