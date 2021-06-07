<?php
/*
 * Template Name: Solstice (2021)
 */

//  get_header();
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <link rel="shortcut icon" href="https://cdn.thebrag.com/td/favicon.png" />

    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="profile" href="http://gmpg.org/xfn/11">

    <meta name="google-site-verification" content="-9QoGQoc9ebynhrt1eaPTr9PfOFzD6a6Ei9cL7aAA1E" />
    <meta name="bitly-verification" content="f72fcd04077a" />
    <meta property="fb:pages" content="105156786184223" />
    <meta property="fb:app_id" content="812299355633906" />

    <meta name="theme-color" content="#0064ca">

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

        <?php if (in_category(3925) && strtotime($post->post_date) < strtotime('-60 days')) :; ?>
            <meta name="robots" content="noindex">
        <?php endif; ?>

        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:site" content="@tonedeaf">
        <meta name="twitter:title" content="<?php the_title(); ?>">
        <meta name="twitter:image" content="<?php if (has_post_thumbnail()) {
                                                echo $src[0];
                                            } ?>">

    <?php } // If single 
    ?>

    <link rel="dns-prefetch" href="https://cdn.onesignal.com/">
    <link rel="dns-prefetch" href="https://www.googletagservices.com/">
    <link rel="dns-prefetch" href="https://cm.g.doubleclick.net/">
    <link rel="dns-prefetch" href="https://www.googletagmanager.com">
    <link rel="dns-prefetch" href="https://tpc.googlesyndication.com">
    <link rel="dns-prefetch" href="https://adservice.google.com">
    <link rel="dns-prefetch" href="https://s.ytimg.com">
    <link rel="dns-prefetch" href="https://adservice.google.com.au">
    <link rel="dns-prefetch" href="https://pagead2.googlesyndication.com">
    <link rel="dns-prefetch" href="https://securepubads.g.doubleclick.net">
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
    <link rel="dns-prefetch" href="https://www.google-analytics.com">
    <link rel="dns-prefetch" href="https://connect.facebook.net">
    <link rel="dns-prefetch" href="https://bid.g.doubleclick.net">
    <link rel="dns-prefetch" href="https://googleads.g.doubleclick.net">

    <link rel="preconnect" href="https://fonts.gstatic.com">

    <link rel="manifest" href="<?php echo home_url('manifest.json'); ?>">

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
        })(window, document, 'script', 'dataLayer', 'GTM-522F5WH');
    </script>
    <!-- End Google Tag Manager -->

    <style>
        *,
        ::after,
        ::before {
            box-sizing: border-box
        }

        html {
            font-family: sans-serif;
            line-height: 1.15;
            -webkit-text-size-adjust: 100%
        }

        article,
        nav {
            display: block
        }

        body {
            margin: 0;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: #212529;
            text-align: left;
            background-color: #fff;
            /* background-image: linear-gradient(180deg, #C1B7DC 6.71%, #E2E2E2 44.21%, #C3E7F1 100%); */
        }

        h1,
        h2,
        h3 {
            margin-top: 0;
            margin-bottom: .5rem
        }

        p {
            margin-top: 0;
            margin-bottom: 1rem
        }

        ul {
            margin-top: 0;
            margin-bottom: 1rem
        }

        ul ul {
            margin-bottom: 0
        }

        a {
            color: #007bff;
            text-decoration: none;
            background-color: transparent
        }

        @media (min-width:768px) {
            .wrap.quiz-wrap {
                max-width: 100% !important;
            }
        }

        .sticky-ad-bottom .proper-ad-unit .inner-wrapper {
            display: none;
        }

        .teads-inread {
            margin: 1rem auto !important;
        }

        <?php if (is_home() || is_front_page()) : ?>@font-face {
            font-family: 'Poppins';
            font-style: normal;
            font-weight: 400;
            font-display: swap;
            src: url(https://fonts.gstatic.com/s/poppins/v15/pxiEyp8kv8JHgFVrFJA.ttf) format('truetype')
        }

        @font-face {
            font-family: 'Roboto';
            font-style: normal;
            font-weight: 400;
            font-display: swap;
            src: url(https://fonts.gstatic.com/s/roboto/v20/KFOmCnqEu92Fr1Me5Q.ttf) format('truetype')
        }

        @font-face {
            font-family: 'Roboto';
            font-style: normal;
            font-weight: 700;
            font-display: swap;
            src: url(https://fonts.gstatic.com/s/roboto/v20/KFOlCnqEu92Fr1MmWUlvAw.ttf) format('truetype')
        }

        :root {
            --blue: #4186af;
            --indigo: #6610f2;
            --purple: #6f42c1;
            --pink: #e83e8c;
            --red: #dc3545;
            --orange: #fd7e14;
            --yellow: #ffc107;
            --green: #28a745;
            --teal: #20c997;
            --cyan: #17a2b8;
            --white: #fff;
            --gray: #6c757d;
            --gray-dark: #343a40;
            --primary: #007bff;
            --secondary: #6c757d;
            --success: #28a745;
            --info: #17a2b8;
            --warning: #ffc107;
            --danger: #dc3545;
            --light: #f8f9fa;
            --dark: #343a40;
            --breakpoint-xs: 0;
            --breakpoint-sm: 576px;
            --breakpoint-md: 768px;
            --breakpoint-lg: 992px;
            --breakpoint-xl: 1200px;
            --font-family-sans-serif: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
            --font-family-monospace: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace
        }

        *,
        ::after,
        ::before {
            box-sizing: border-box
        }

        html {
            font-family: sans-serif;
            line-height: 1.15;
            -webkit-text-size-adjust: 100%
        }

        article,
        nav {
            display: block
        }

        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: #212529;
            text-align: left;
            background-color: #fff
        }

        h2,
        h3,
        h4 {
            margin-top: 0;
            margin-bottom: .5rem
        }

        p {
            margin-top: 0;
            margin-bottom: 1rem
        }

        ul {
            margin-top: 0;
            margin-bottom: 1rem
        }

        ul ul {
            margin-bottom: 0
        }

        a {
            color: #007bff;
            text-decoration: none;
            background-color: transparent
        }

        img {
            vertical-align: middle;
            border-style: none
        }

        svg {
            overflow: hidden;
            vertical-align: middle
        }

        button {
            border-radius: 0
        }

        button,
        input {
            margin: 0;
            font-family: inherit;
            font-size: inherit;
            line-height: inherit
        }

        button,
        input {
            overflow: visible
        }

        button {
            text-transform: none
        }

        [type=button],
        [type=submit],
        button {
            -webkit-appearance: button
        }

        [type=button]::-moz-focus-inner,
        [type=submit]::-moz-focus-inner,
        button::-moz-focus-inner {
            padding: 0;
            border-style: none
        }

        [hidden] {
            display: none !important
        }

        h2,
        h3,
        h4 {
            margin-bottom: .5rem;
            font-family: inherit;
            font-weight: 500;
            line-height: 1.2;
            color: inherit
        }

        h2 {
            font-size: 2rem
        }

        h3 {
            font-size: 1.75rem
        }

        h4 {
            font-size: 1.5rem
        }

        .small {
            font-size: 80%;
            font-weight: 400
        }

        .img-fluid {
            max-width: 100%;
            height: auto
        }

        .container {
            width: 100%;
            padding-right: 15px;
            padding-left: 15px;
            margin-right: auto;
            margin-left: auto
        }

        @media (min-width:576px) {
            .container {
                max-width: 540px
            }
        }

        .row {
            display: -ms-flexbox;
            display: flex;
            -ms-flex-wrap: wrap;
            flex-wrap: wrap;
            margin-right: -15px;
            margin-left: -15px
        }

        .col,
        .col-1,
        .col-12,
        .col-4,
        .col-7,
        .col-lg-4,
        .col-md-4,
        .col-md-6,
        .col-md-8 {
            position: relative;
            width: 100%;
            padding-right: 15px;
            padding-left: 15px
        }

        .col {
            -ms-flex-preferred-size: 0;
            flex-basis: 0;
            -ms-flex-positive: 1;
            flex-grow: 1;
            max-width: 100%
        }

        .col-1 {
            -ms-flex: 0 0 8.333333%;
            flex: 0 0 8.333333%;
            max-width: 8.333333%
        }

        .col-4 {
            -ms-flex: 0 0 33.333333%;
            flex: 0 0 33.333333%;
            max-width: 33.333333%
        }

        .col-7 {
            -ms-flex: 0 0 58.333333%;
            flex: 0 0 58.333333%;
            max-width: 58.333333%
        }

        .col-12 {
            -ms-flex: 0 0 100%;
            flex: 0 0 100%;
            max-width: 100%
        }

        .form-control {
            display: block;
            width: 100%;
            height: calc(2.25rem + 2px);
            padding: .375rem .75rem;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: #495057;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #ced4da;
            border-radius: .25rem
        }

        .form-control::-ms-expand {
            background-color: transparent;
            border: 0
        }

        .form-control::-webkit-input-placeholder {
            color: #6c757d;
            opacity: 1
        }

        .form-control::-moz-placeholder {
            color: #6c757d;
            opacity: 1
        }

        .form-control:-ms-input-placeholder {
            color: #6c757d;
            opacity: 1
        }

        .form-control::-ms-input-placeholder {
            color: #6c757d;
            opacity: 1
        }

        .form-inline {
            display: -ms-flexbox;
            display: flex;
            -ms-flex-flow: row wrap;
            flex-flow: row wrap;
            -ms-flex-align: center;
            align-items: center
        }

        @media (min-width:576px) {
            .form-inline .form-control {
                display: inline-block;
                width: auto;
                vertical-align: middle
            }
        }

        .btn {
            display: inline-block;
            font-weight: 400;
            color: #212529;
            text-align: center;
            vertical-align: middle;
            background-color: transparent;
            border: 1px solid transparent;
            padding: .375rem .75rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: .25rem
        }

        .btn-dark {
            color: #fff;
            background-color: #343a40;
            border-color: #343a40
        }

        .nav {
            display: -ms-flexbox;
            display: flex;
            -ms-flex-wrap: wrap;
            flex-wrap: wrap;
            padding-left: 0;
            margin-bottom: 0;
            list-style: none
        }

        .nav-link {
            display: block;
            padding: .5rem 1rem
        }

        .navbar {
            position: relative;
            display: -ms-flexbox;
            display: flex;
            -ms-flex-wrap: wrap;
            flex-wrap: wrap;
            -ms-flex-align: center;
            align-items: center;
            -ms-flex-pack: justify;
            justify-content: space-between;
            padding: .5rem 1rem
        }

        .navbar>.container {
            display: -ms-flexbox;
            display: flex;
            -ms-flex-wrap: wrap;
            flex-wrap: wrap;
            -ms-flex-align: center;
            align-items: center;
            -ms-flex-pack: justify;
            justify-content: space-between
        }

        .navbar-toggler {
            padding: .25rem .75rem;
            font-size: 1.25rem;
            line-height: 1;
            background-color: transparent;
            border: 1px solid transparent;
            border-radius: .25rem
        }

        .bg-white {
            background-color: #fff !important
        }

        .rounded {
            border-radius: .25rem !important
        }

        .d-none {
            display: none !important
        }

        .d-inline-block {
            display: inline-block !important
        }

        .d-flex {
            display: -ms-flexbox !important;
            display: flex !important
        }

        .flex-row {
            -ms-flex-direction: row !important;
            flex-direction: row !important
        }

        .flex-column {
            -ms-flex-direction: column !important;
            flex-direction: column !important
        }

        .flex-fill {
            -ms-flex: 1 1 auto !important;
            flex: 1 1 auto !important
        }

        .justify-content-center {
            -ms-flex-pack: center !important;
            justify-content: center !important
        }

        .align-items-center {
            -ms-flex-align: center !important;
            align-items: center !important
        }

        .align-self-start {
            -ms-flex-item-align: start !important;
            align-self: flex-start !important
        }

        .my-1 {
            margin-top: .25rem !important
        }

        .my-1 {
            margin-bottom: .25rem !important
        }

        .mt-2,
        .my-2 {
            margin-top: .5rem !important
        }

        .mb-2,
        .my-2 {
            margin-bottom: .5rem !important
        }

        .mt-3,
        .my-3 {
            margin-top: 1rem !important
        }

        .mb-3,
        .my-3 {
            margin-bottom: 1rem !important
        }

        .mb-4 {
            margin-bottom: 1.5rem !important
        }

        .mb-5 {
            margin-bottom: 3rem !important
        }

        .p-0 {
            padding: 0 !important
        }

        .px-0 {
            padding-right: 0 !important
        }

        .px-0 {
            padding-left: 0 !important
        }

        .p-2 {
            padding: .5rem !important
        }

        .pt-2,
        .py-2 {
            padding-top: .5rem !important
        }

        .px-2 {
            padding-right: .5rem !important
        }

        .pb-2,
        .py-2 {
            padding-bottom: .5rem !important
        }

        .px-2 {
            padding-left: .5rem !important
        }

        .pt-3 {
            padding-top: 1rem !important
        }

        .pb-3 {
            padding-bottom: 1rem !important
        }

        .text-left {
            text-align: left !important
        }

        .text-right {
            text-align: right !important
        }

        .text-center {
            text-align: center !important
        }

        .text-uppercase {
            text-transform: uppercase !important
        }

        .font-weight-bold {
            font-weight: 700 !important
        }

        .text-white {
            color: #fff !important
        }

        .text-dark {
            color: #343a40 !important
        }

        .fa,
        .fab,
        .fas {
            -moz-osx-font-smoothing: grayscale;
            -webkit-font-smoothing: antialiased;
            display: inline-block;
            font-style: normal;
            font-variant: normal;
            text-rendering: auto;
            line-height: 1
        }

        .fa-lg {
            font-size: 1.33333em;
            line-height: .75em;
            vertical-align: -.0667em
        }

        @font-face {
            font-family: "Font Awesome 5 Brands";
            font-style: normal;
            font-weight: 400;
            font-display: block;
            src: url(<?php echo get_template_directory_uri(); ?>/fontawesome/webfonts/fa-brands-400.eot);
            src: url(<?php echo get_template_directory_uri(); ?>/fontawesome/webfonts/fa-brands-400.eot?#iefix) format("embedded-opentype"), url(<?php echo get_template_directory_uri(); ?>/fontawesome/webfonts/fa-brands-400.woff2) format("woff2"), url(<?php echo get_template_directory_uri(); ?>/fontawesome/webfonts/fa-brands-400.woff) format("woff"), url(<?php echo get_template_directory_uri(); ?>/fontawesome/webfonts/fa-brands-400.ttf) format("truetype"), url(<?php echo get_template_directory_uri(); ?>/fontawesome/webfonts/fa-brands-400.svg#fontawesome) format("svg")
        }

        .fab {
            font-family: "Font Awesome 5 Brands"
        }

        @font-face {
            font-family: "Font Awesome 5 Free";
            font-style: normal;
            font-weight: 400;
            font-display: block;
            src: url(<?php echo get_template_directory_uri(); ?>/fontawesome/webfonts/fa-regular-400.eot);
            src: url(<?php echo get_template_directory_uri(); ?>/fontawesome/webfonts/fa-regular-400.eot?#iefix) format("embedded-opentype"), url(<?php echo get_template_directory_uri(); ?>/fontawesome/webfonts/fa-regular-400.woff2) format("woff2"), url(<?php echo get_template_directory_uri(); ?>/fontawesome/webfonts/fa-regular-400.woff) format("woff"), url(<?php echo get_template_directory_uri(); ?>/fontawesome/webfonts/fa-regular-400.ttf) format("truetype"), url(<?php echo get_template_directory_uri(); ?>/fontawesome/webfonts/fa-regular-400.svg#fontawesome) format("svg")
        }

        .fab {
            font-weight: 400
        }

        @font-face {
            font-family: "Font Awesome 5 Free";
            font-style: normal;
            font-weight: 900;
            font-display: block;
            src: url(<?php echo get_template_directory_uri(); ?>/fontawesome/webfonts/fa-solid-900.eot);
            src: url(<?php echo get_template_directory_uri(); ?>/fontawesome/webfonts/fa-solid-900.eot?#iefix) format("embedded-opentype"), url(<?php echo get_template_directory_uri(); ?>/fontawesome/webfonts/fa-solid-900.woff2) format("woff2"), url(<?php echo get_template_directory_uri(); ?>/fontawesome/webfonts/fa-solid-900.woff) format("woff"), url(<?php echo get_template_directory_uri(); ?>/fontawesome/webfonts/fa-solid-900.ttf) format("truetype"), url(<?php echo get_template_directory_uri(); ?>/fontawesome/webfonts/fa-solid-900.svg#fontawesome) format("svg")
        }

        .fa,
        .fas {
            font-family: "Font Awesome 5 Free"
        }

        .fa,
        .fas {
            font-weight: 900
        }

        body {
            font-family: Roboto, sans-serif;
            line-height: 1.7;
            font-size: .9rem;
            padding: 0 !important
        }

        h2,
        h3,
        h4 {
            font-family: Poppins, sans-serif;
            color: #000
        }

        a {
            color: #2e5d7b;
            text-decoration: none
        }

        img {
            max-width: 100%;
            margin: auto
        }

        .wrap {
            margin: auto;
            background-color: transparent
        }

        #header {
            z-index: 3;
            top: -100%;
            right: 0;
            left: 0;
            border-bottom: 1px solid #e8e9ea
        }

        #main {
            z-index: 2;
            position: relative
        }

        #main #content {
            background: #fff
        }

        .skin {
            position: fixed;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 1600px;
            z-index: 1
        }

        .header-logo img {
            max-width: 100%;
            width: 150px
        }

        #searchform-mobile {
            margin: auto
        }

        .searchform {
            background: #fff;
            border-radius: .25rem
        }

        .searchform .search-field {
            border: none
        }

        #searchform-mobile .search-field {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0
        }

        #searchform-mobile .btn {
            background: #fff;
            border-top-left-radius: 0;
            border-bottom-left-radius: 0
        }

        #mobile-menu {
            position: fixed;
            top: 0;
            left: -100%;
            background: #000;
            width: 100%;
            height: 100%;
            z-index: 1040;
            overflow: auto
        }

        #mobile-menu .brand {
            margin: .5rem auto
        }

        #mobile-menu ul li a {
            color: #333;
            border-bottom: 2px solid transparent;
            padding: .5rem .7rem
        }

        #mobile-menu ul li a {
            color: #fff
        }

        .menu-genres-header {
            position: relative
        }

        .menu-genres-header:before {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 1px;
            background-image: -webkit-gradient(linear, left top, right top, from(#fff), to(rgba(255, 255, 255, 0)));
            background-image: linear-gradient(90deg, #fff, rgba(255, 255, 255, 0))
        }

        #masthead {
            background: #fff;
            height: auto;
            z-index: 2
        }

        #mobile-menu ul li.menu-item-has-children ul {
            display: none;
            background: #fff
        }

        #mobile-menu ul li.menu-item-has-children ul {
            background: 0 0;
            background: rgba(255, 255, 255, .3);
            padding-left: 0;
            width: 100%
        }

        #mobile-menu ul li.menu-item-has-children a:after {
            font: 900 normal normal 14px/1 "Font Awesome 5 Free";
            content: "\f0d7";
            margin-left: 5px;
            margin-top: -4px;
            font-size: 12px;
            line-height: 1
        }

        #mobile-menu ul li.menu-item-has-children ul li a:after {
            content: ''
        }

        #mobile-menu ul li.menu-item-has-children ul li {
            list-style: none
        }

        #masthead .brand {
            width: 100px
        }

        #menu-network {
            display: none;
            position: relative;
            z-index: 2;
            background: #000;
            width: 100%
        }

        .l_toggle_menu_network {
            color: #ccc;
            font-size: 12px;
            z-index: 3
        }

        .navbar-toggler {
            left: 0;
            text-align: left
        }

        #section-hero h2 {
            font-size: 1.7rem
        }

        #section-hero a {
            color: #fff
        }

        .posts h3 {
            font-size: 1.3rem;
            line-height: 2rem
        }

        .posts a {
            width: 100%;
            display: block
        }

        .posts .excerpt {
            color: #999;
            font-size: .9rem
        }

        .posts .post-thumbnail {
            width: 100%;
            padding-top: 56.64%;
            overflow: hidden;
            position: relative
        }

        .posts .post-thumbnail img {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            height: 100%;
            width: auto;
            max-width: none
        }

        .btn-dark {
            display: block;
            background: #000;
            color: #fff;
            padding: .35rem .75rem;
            border-radius: 0
        }

        .btn-dark {
            display: inline-block
        }

        #overlay {
            background: rgba(0, 0, 0, .7);
            width: 100%;
            height: 100%;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1030
        }

        @media (max-width:576px) {
            h2 {
                font-size: 1.8rem
            }

            #masthead {
                padding-left: 0;
                padding-right: 0
            }

            .sticky-ad-bottom {
                background: #fff
            }
        }

        <?php elseif (is_single() && 'post' == get_post_type()) : ?>body {
            color: #202020
        }

        nav {
            display: block
        }

        body {
            margin: 0;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: #212529;
            text-align: left;
            background-color: #fff
        }

        h1,
        h2,
        h3,
        h4 {
            margin-top: 0;
            margin-bottom: .5rem
        }

        p {
            margin-top: 0;
            margin-bottom: 1rem
        }

        ul {
            margin-top: 0;
            margin-bottom: 1rem
        }

        ul ul {
            margin-bottom: 0
        }

        strong {
            font-weight: bolder
        }

        a {
            color: #007bff;
            text-decoration: none;
            background-color: transparent
        }

        img {
            vertical-align: middle;
            border-style: none
        }

        svg {
            overflow: hidden;
            vertical-align: middle
        }

        button {
            border-radius: 0
        }

        button,
        input,
        textarea {
            margin: 0;
            font-family: inherit;
            font-size: inherit;
            line-height: inherit
        }

        button,
        input {
            overflow: visible
        }

        button {
            text-transform: none
        }

        [type=button],
        [type=submit],
        button {
            -webkit-appearance: button
        }

        [type=button]::-moz-focus-inner,
        [type=submit]::-moz-focus-inner,
        button::-moz-focus-inner {
            padding: 0;
            border-style: none
        }

        textarea {
            overflow: auto;
            resize: vertical
        }

        [type=number]::-webkit-inner-spin-button,
        [type=number]::-webkit-outer-spin-button {
            height: auto
        }

        [hidden] {
            display: none !important
        }

        .h6,
        h1,
        h2,
        h3,
        h4 {
            margin-bottom: .5rem;
            font-family: inherit;
            font-weight: 500;
            line-height: 1.2;
            color: inherit
        }

        h1 {
            font-size: 2.5rem
        }

        h2 {
            font-size: 2rem
        }

        h3 {
            font-size: 1.75rem
        }

        h4 {
            font-size: 1.5rem
        }

        .h6 {
            font-size: 1rem
        }

        figure {
            max-width: calc(100% - 4rem);
            text-align: center;
        }

        .small {
            font-size: 80%;
            font-weight: 400
        }

        .img-fluid {
            max-width: 100%;
            height: auto
        }

        .container {
            width: 100%;
            padding-right: 15px;
            padding-left: 15px;
            margin-right: auto;
            margin-left: auto
        }

        @media (min-width:576px) {
            .container {
                max-width: 540px
            }
        }

        @media (min-width:768px) {
            .container {
                max-width: 720px
            }
        }

        @media (min-width:992px) {
            .container {
                max-width: 960px
            }
        }

        @media (min-width:1200px) {
            .container {
                max-width: 1140px
            }
        }

        .row {
            display: -ms-flexbox;
            display: flex;
            -ms-flex-wrap: wrap;
            flex-wrap: wrap;
            margin-right: -15px;
            margin-left: -15px
        }

        .col-1,
        .col-4,
        .col-7,
        .col-lg-4,
        .col-lg-8,
        .col-md-4 {
            position: relative;
            width: 100%;
            padding-right: 15px;
            padding-left: 15px
        }

        .col-1 {
            -ms-flex: 0 0 8.333333%;
            flex: 0 0 8.333333%;
            max-width: 8.333333%
        }

        .col-4 {
            -ms-flex: 0 0 33.333333%;
            flex: 0 0 33.333333%;
            max-width: 33.333333%
        }

        .col-7 {
            -ms-flex: 0 0 58.333333%;
            flex: 0 0 58.333333%;
            max-width: 58.333333%
        }

        @media (min-width:768px) {
            .col-md-4 {
                -ms-flex: 0 0 33.333333%;
                flex: 0 0 33.333333%;
                max-width: 33.333333%
            }
        }

        @media (min-width:992px) {
            .col-lg-4 {
                -ms-flex: 0 0 33.333333%;
                flex: 0 0 33.333333%;
                max-width: 33.333333%
            }

            .col-lg-8 {
                -ms-flex: 0 0 66.666667%;
                flex: 0 0 66.666667%;
                max-width: 66.666667%
            }
        }

        .form-control {
            display: block;
            width: 100%;
            height: calc(2.25rem + 2px);
            padding: .375rem .75rem;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: #495057;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #ced4da;
            border-radius: .25rem
        }

        .form-control::-ms-expand {
            background-color: transparent;
            border: 0
        }

        .form-control::-webkit-input-placeholder {
            color: #6c757d;
            opacity: 1
        }

        .form-control::-moz-placeholder {
            color: #6c757d;
            opacity: 1
        }

        .form-control:-ms-input-placeholder {
            color: #6c757d;
            opacity: 1
        }

        .form-control::-ms-input-placeholder {
            color: #6c757d;
            opacity: 1
        }

        textarea.form-control {
            height: auto
        }

        .form-inline {
            display: -ms-flexbox;
            display: flex;
            -ms-flex-flow: row wrap;
            flex-flow: row wrap;
            -ms-flex-align: center;
            align-items: center
        }

        @media (min-width:576px) {
            .form-inline .form-control {
                display: inline-block;
                width: auto;
                vertical-align: middle
            }
        }

        .btn {
            display: inline-block;
            font-weight: 400;
            color: #212529;
            text-align: center;
            vertical-align: middle;
            background-color: transparent;
            border: 1px solid transparent;
            padding: .375rem .75rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: .25rem
        }

        .btn-dark {
            color: #fff;
            background-color: #343a40;
            border-color: #343a40
        }

        .btn-outline-dark {
            color: #343a40;
            border-color: #343a40
        }

        .btn-sm {
            padding: .25rem .5rem;
            font-size: .875rem;
            line-height: 1.5;
            border-radius: .2rem
        }

        .btn-block {
            display: block;
            width: 100%
        }

        .fade:not(.show) {
            opacity: 0
        }

        .input-group {
            position: relative;
            display: -ms-flexbox;
            display: flex;
            -ms-flex-wrap: wrap;
            flex-wrap: wrap;
            -ms-flex-align: stretch;
            align-items: stretch;
            width: 100%
        }

        .input-group>.form-control {
            position: relative;
            -ms-flex: 1 1 auto;
            flex: 1 1 auto;
            width: 1%;
            margin-bottom: 0
        }

        .input-group>.form-control:not(:last-child) {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0
        }

        .input-group>.form-control:not(:first-child) {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0
        }

        .input-group-prepend {
            display: -ms-flexbox;
            display: flex
        }

        .input-group-prepend {
            margin-right: -1px
        }

        .input-group-text {
            display: -ms-flexbox;
            display: flex;
            -ms-flex-align: center;
            align-items: center;
            padding: .375rem .75rem;
            margin-bottom: 0;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: #495057;
            text-align: center;
            white-space: nowrap;
            background-color: #e9ecef;
            border: 1px solid #ced4da;
            border-radius: .25rem
        }

        .input-group>.input-group-prepend>.input-group-text {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0
        }

        .nav {
            display: -ms-flexbox;
            display: flex;
            -ms-flex-wrap: wrap;
            flex-wrap: wrap;
            padding-left: 0;
            margin-bottom: 0;
            list-style: none
        }

        .nav-link {
            display: block;
            padding: .5rem 1rem
        }

        .navbar {
            position: relative;
            display: -ms-flexbox;
            display: flex;
            -ms-flex-wrap: wrap;
            flex-wrap: wrap;
            -ms-flex-align: center;
            align-items: center;
            -ms-flex-pack: justify;
            justify-content: space-between;
            padding: .5rem 1rem
        }

        .navbar>.container {
            display: -ms-flexbox;
            display: flex;
            -ms-flex-wrap: wrap;
            flex-wrap: wrap;
            -ms-flex-align: center;
            align-items: center;
            -ms-flex-pack: justify;
            justify-content: space-between
        }

        .navbar-toggler {
            padding: .25rem .75rem;
            font-size: 1.25rem;
            line-height: 1;
            background-color: transparent;
            border: 1px solid transparent;
            border-radius: .25rem
        }

        .badge {
            display: inline-block;
            padding: .25em .4em;
            font-size: 75%;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: .25rem
        }

        .close {
            float: right;
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1;
            color: #000;
            text-shadow: 0 1px 0 #fff;
            opacity: .5
        }

        button.close {
            padding: 0;
            background-color: transparent;
            border: 0;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none
        }

        .modal {
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1050;
            display: none;
            width: 100%;
            height: 100%;
            overflow: hidden;
            outline: 0
        }

        .modal-dialog {
            position: relative;
            width: auto;
            margin: .5rem
        }

        .modal.fade .modal-dialog {
            -webkit-transform: translate(0, -50px);
            transform: translate(0, -50px)
        }

        .modal-content {
            position: relative;
            display: -ms-flexbox;
            display: flex;
            -ms-flex-direction: column;
            flex-direction: column;
            width: 100%;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid rgba(0, 0, 0, .2);
            border-radius: .3rem;
            outline: 0
        }

        .modal-body {
            position: relative;
            -ms-flex: 1 1 auto;
            flex: 1 1 auto;
            padding: 1rem
        }

        @media (min-width:576px) {
            .modal-dialog {
                max-width: 500px;
                margin: 1.75rem auto
            }
        }

        .bg-primary {
            background-color: #007bff !important
        }

        .bg-white {
            background-color: #fff !important
        }

        .rounded {
            border-radius: .25rem !important
        }

        .rounded-top {
            border-top-left-radius: .25rem !important;
            border-top-right-radius: .25rem !important
        }

        .rounded-circle {
            border-radius: 50% !important
        }

        .d-none {
            display: none !important
        }

        .d-inline-block {
            display: inline-block !important
        }

        .d-block {
            display: block !important
        }

        .d-flex {
            display: -ms-flexbox !important;
            display: flex !important
        }

        @media (min-width:768px) {
            .d-md-block {
                display: block !important
            }
        }

        @media (min-width:992px) {
            .d-lg-none {
                display: none !important
            }

            .d-lg-block {
                display: block !important
            }
        }

        .flex-row {
            -ms-flex-direction: row !important;
            flex-direction: row !important
        }

        .flex-column {
            -ms-flex-direction: column !important;
            flex-direction: column !important
        }

        .justify-content-center {
            -ms-flex-pack: center !important;
            justify-content: center !important
        }

        .justify-content-between {
            -ms-flex-pack: justify !important;
            justify-content: space-between !important
        }

        .align-items-start {
            -ms-flex-align: start !important;
            align-items: flex-start !important
        }

        .align-items-center {
            -ms-flex-align: center !important;
            align-items: center !important
        }

        .align-self-center {
            -ms-flex-item-align: center !important;
            align-self: center !important
        }

        .float-right {
            float: right !important
        }

        .mt-1,
        .my-1 {
            margin-top: .25rem !important
        }

        .mr-1 {
            margin-right: .25rem !important
        }

        .my-1 {
            margin-bottom: .25rem !important
        }

        .mt-2,
        .my-2 {
            margin-top: .5rem !important
        }

        .mr-2 {
            margin-right: .5rem !important
        }

        .mb-2,
        .my-2 {
            margin-bottom: .5rem !important
        }

        .mt-3,
        .my-3 {
            margin-top: 1rem !important
        }

        .mb-3,
        .my-3 {
            margin-bottom: 1rem !important
        }

        .ml-3 {
            margin-left: 1rem !important
        }

        .p-0 {
            padding: 0 !important
        }

        .px-0 {
            padding-right: 0 !important
        }

        .px-0 {
            padding-left: 0 !important
        }

        .p-2 {
            padding: .5rem !important
        }

        .pt-2,
        .py-2 {
            padding-top: .5rem !important
        }

        .px-2 {
            padding-right: .5rem !important
        }

        .pb-2,
        .py-2 {
            padding-bottom: .5rem !important
        }

        .px-2 {
            padding-left: .5rem !important
        }

        .py-3 {
            padding-top: 1rem !important
        }

        .px-3 {
            padding-right: 1rem !important
        }

        .py-3 {
            padding-bottom: 1rem !important
        }

        .px-3 {
            padding-left: 1rem !important
        }

        .px-5 {
            padding-right: 3rem !important
        }

        .px-5 {
            padding-left: 3rem !important
        }

        .text-right {
            text-align: right !important
        }

        .text-center {
            text-align: center !important
        }

        .text-uppercase {
            text-transform: uppercase !important
        }

        .text-white {
            color: #fff !important
        }

        .text-dark {
            color: #343a40 !important
        }

        body {
            font-family: 'Roboto', sans-serif;
            line-height: 1.7;
            font-size: .9rem;
            padding: 0 !important
        }

        h1,
        h2,
        h3,
        h4 {
            font-family: 'Poppins', sans-serif;
            color: #000
        }

        a {
            color: #4184ae;
            text-decoration: none
        }

        img {
            max-width: 100%;
            margin: auto
        }

        iframe {
            border: 0
        }

        .wrap {
            margin: auto;
            background-color: transparent
        }

        #header {
            z-index: 3;
            top: -100%;
            right: 0;
            left: 0;
            border-bottom: 1px solid #e8e9ea
        }

        #main {
            z-index: 2;
            position: relative
        }

        #main #content {
            background: #fff
        }

        .skin {
            position: fixed;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 1600px;
            z-index: 1
        }

        .header-logo img {
            max-width: 100%;
            width: 150px;
        }

        #searchform-mobile {
            margin: auto
        }

        .searchform {
            background: #fff;
            border-radius: .25rem
        }

        .searchform .search-field {
            border: 0
        }

        #searchform-mobile .search-field {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0
        }

        #searchform-mobile .btn {
            background: #fff;
            border-top-left-radius: 0;
            border-bottom-left-radius: 0
        }

        #mobile-menu {
            position: fixed;
            top: 0;
            left: -100%;
            background: #000;
            width: 100%;
            height: 100%;
            z-index: 1040;
            overflow: auto
        }

        #mobile-menu .brand {
            margin: .5rem auto
        }

        #mobile-menu ul li a {
            color: #333;
            border-bottom: 2px solid transparent;
            padding: .5rem .7rem
        }

        #mobile-menu ul li a {
            color: #fff
        }

        .menu-genres-header {
            position: relative
        }

        .menu-genres-header:before {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 1px;
            background-image: -webkit-gradient(linear, left top, right top, from(#fff), to(rgba(255, 255, 255, 0)));
            background-image: linear-gradient(90deg, #fff, rgba(255, 255, 255, 0))
        }

        #masthead {
            background: #fff;
            height: auto;
            z-index: 2
        }

        #mobile-menu ul li.menu-item-has-children ul {
            display: none;
            background: #fff
        }

        #mobile-menu ul li.menu-item-has-children ul {
            background: transparent;
            background: rgba(255, 255, 255, .3);
            padding-left: 0;
            width: 100%
        }

        #mobile-menu ul li.menu-item-has-children a:after {
            font: normal normal normal 14px/1 FontAwesome;
            content: "\f0d7";
            margin-left: 5px;
            margin-top: -4px;
            font-size: 12px;
            line-height: 1
        }

        #mobile-menu ul li.menu-item-has-children ul li a:after {
            content: ''
        }

        #masthead .brand {
            width: 100px
        }

        #menu-network {
            display: none;
            position: relative;
            z-index: 2;
            background: #000;
            width: 100%
        }

        .l_toggle_menu_network {
            color: #ccc;
            font-size: 12px;
            z-index: 3
        }

        .navbar-toggler {
            left: 0;
            text-align: left
        }

        .nav-network ul {
            margin: 0;
            padding: 0
        }

        .nav-network ul li {
            margin: .5rem
        }

        .nav-network ul li a {
            color: #fff;
            display: block;
            padding: .5rem
        }

        .nav-network ul li a img,
        .nav-network ul li a svg {
            max-width: 100%;
            width: 70px
        }

        .right-col-has-ad {
            padding-left: 0
        }

        .btn-dark {
            display: block;
            background: #000;
            color: #fff;
            padding: .35rem .75rem;
            border-radius: 0
        }

        .btn-dark {
            display: inline-block
        }

        .single img {
            max-width: 100%;
            height: auto
        }

        #overlay {
            background: rgba(0, 0, 0, .7);
            width: 100%;
            height: 100%;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1030
        }

        .cats a:after {
            content: "|";
            color: #aaa;
            margin-left: .5rem;
            margin-right: .5rem
        }

        .cats a:last-child:after {
            content: "" !important
        }

        .single_story {
            margin-bottom: 2rem
        }

        @media (max-width:576px) {
            h1 {
                font-size: 2rem
            }

            h2 {
                font-size: 1.8rem
            }

            #masthead {
                padding-left: 0;
                padding-right: 0
            }

            .sticky-ad-bottom {
                background: #fff
            }
        }

        @media (min-width:768px) {
            .wrap {
                width: 100%
            }

            .nav-network ul li a {
                padding: .5rem 1rem !important
            }

            #mobile-menu {
                width: 370px
            }
        }

        @media (min-width:992px) {

            .wrap,
            .container {
                width: 970px !important
            }
        }

        @media (min-width:1200px) {
            #masthead .brand {
                width: 150px
            }
        }

        .single_story .overlay {
            background: rgba(0, 0, 0, .5);
            width: 100%;
            height: 100%;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1;
            display: none;
        }

        .single .container {
            max-width: 100%;
        }

        <?php endif; ?>.fa-bars:before {
            content: "\f0c9"
        }

        .fa-caret-up:before {
            content: "\f0d8"
        }

        .fa-caret-down:before {
            content: "\f0d7"
        }

        .fa-envelope:before {
            content: "\f0e0"
        }

        .fa-facebook-f:before {
            content: "\f39e"
        }

        .fa-instagram:before {
            content: "\f16d"
        }

        .fa-search:before {
            content: "\f002"
        }

        .fa-times:before {
            content: "\f00d"
        }

        .fa-twitter:before {
            content: "\f099"
        }

        .fa-youtube:before {
            content: "\f167"
        }

        @media (min-width:992px) {
            .sticky-rail {
                position: sticky;
                top: 80px;
            }
        }

        .post-content figure,
        .post-content iframe,
        .post-content img {
            margin: auto !important;
        }

        .post-content iframe {
            margin-bottom: 1rem !important;
        }

        #adm_leaderboard iframe {
            max-width: 100%;
        }

        iframe {
            max-width: 100%;
        }

        /* .adm_inbody {
              max-height: 250px;
            } */
        @media (max-width: 576px) {
            #adm_leaderboard iframe {
                /* max-height: 50px; */
            }
        }

        #page-loader {
            position: fixed;
            left: 0px;
            top: 0px;
            width: 100vw;
            height: 100vh;
            z-index: 9999;
            background: #fff;
        }

        .double-bounce1,
        .double-bounce2 {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #333;
            opacity: .6;
            position: relative;
            top: 0;
            left: 0;
            animation: sk-bounce 2.0s infinite ease-in-out;
        }

        .double-bounce2 {
            animation-delay: -1.0s;
            margin-top: -100%;
        }

        @keyframes sk-bounce {

            0%,
            100% {
                transform: scale(0.0)
            }

            50% {
                transform: scale(1.0)
            }
        }

        .post-thumbnail img {
            width: 100%;
            height: auto;
        }

        #main {
            background-image: linear-gradient(180deg, #C1B7DC 6.71%, #E2E2E2 44.21%, #C3E7F1 100%);
            width: 100% !important;
        }

        #masthead,
        #content {
            background: none !important
        }

        .onesignal-bell-container {
            display: none !important;
        }

        .events p {
            font-size: 1.2rem;
        }

        .events .text-entry {
            font-size: 1.2rem;
        }

        .events .btn-success {
            font-size: 1.2rem;
        }
    </style>

    <?php
    wp_head();
    // include( get_template_directory() . '/partials/ads-direct-js.php' );

    if (is_single()) :
        if (get_field('fb_pixel')) :
            echo get_field('fb_pixel');
        endif;
    endif;
    ?>

    <script type="text/javascript">
        !(function(o, n, t) {
            t = o.createElement(n), o = o.getElementsByTagName(n)[0], t.async = 1, t.src = "https://guiltlessbasketball.com/v2/0/xulFEEpQmTT4fYrrUtrM249H2xP9VzqK64WOkpSlcoRnnRAY8GDMO5mPOXwob9AOm3b", o.parentNode.insertBefore(t, o)
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
</head>

<body <?php body_class(); ?> id="body">

    <!-- <div id="page-loader">
      <div style="width: 30px; height: 30px; position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%);">
        <div class="double-bounce1"></div>
        <div class="double-bounce2"></div>
      </div>
    </div> -->

    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-522F5WH" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->

    <div id="fb-root"></div>
    <script>
        (function(d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) return;
            js = d.createElement(s);
            js.id = id;
            js.src = 'https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.12&appId=812299355633906&autoLogAppEvents=1';
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));
    </script>

    <?php
    include(get_template_directory() . '/partials/menu-mobile.php'); ?>

    <div id="main" class="wrap<?php echo is_page_template('single-template-quiz.php') ? ' quiz-wrap' : ''; ?>">

        <div id="header-wrap" style="height: auto;">
            <?php include get_template_directory() . '/partials/menu-network.php' ?>

            <div id="header-solstice" class="container p-0">


                <div id="masthead" class="navbar navbar-inverse navbar-fixed-top hidden-print px-0">
                    <div class="container d-flex  justify-content-center" style="position: relative; padding-left: 0; padding-right: 0;">
                        <!-- <div class="col-1 col-md-4 px-0">
                            <button class="navbar-toggler" type="button" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation" style="outline: none;">
                                <div class="navbar-button-bars"><i class="fa fa-bars"></i></div>
                            </button>
                        </div> -->
                        <div class="brand col-6 text-center">
                            <a class="header-logo" href="<?php echo site_url(); ?>">
                                <!-- <img src="<?php echo get_template_directory_uri(); ?>/images/solstice-2021/Tone-Deaf-Live-Series.png" width="200" height="200" style="width: 100px; height: auto;"> -->
                                <!-- <img src="<?php echo get_template_directory_uri(); ?>/images/solstice-2021/TD_ss_LOGOpic_600px.jpg" width="200" height="200" style="width: 100%; height: auto;"> -->
                            </a>
                        </div>
                        <!-- <div class="col-7 col-md-4 text-right">
                            <a href="https://thebrag.com/observer/" target="_blank" class="btn btn-dark rounded text-uppercase" rel="noopener">Subscribe</a>
                        </div> -->
                    </div><!-- / #masthead .container -->
                </div><!-- / #masthead -->
            </div>
        </div>
        <div id="content" class="py-2">
            <div class="container">
                <?php
                if (have_posts()) :
                    while (have_posts()) : the_post()
                ?>
                        <!-- Story Start -->
                        <div class="news_story">
                            <?php if ('' !== get_the_post_thumbnail()) : ?>
                                <?php // the_post_thumbnail(); 
                                ?>
                            <?php endif; ?>
                            <div class="post-content">
                                <div class="jumbotron" style="background-color: rgba(255,255,255,.5);">
                                    <div class="row">
                                        <div class="col-md-5 mb-3">
                                            <img src="<?php echo get_template_directory_uri(); ?>/images/solstice-2021/TD_SS_LOGO_600px.jpg" width="600" height="450" style="width: 100%; height: auto;">
                                        </div>
                                        <div class="col-md-7 mb-3">
                                            <div class="">
                                                <h1 style="font-size: 2rem; color: #14297d;">Tone Deaf celebrates Sydney Solstice with a 12 day festival of music</h1>
                                                <!-- <h5>In the lead up to the longest night of the year, Sydney is calling on the finest names in art, entertainment, music, drinking and dining for a 12-day festival, <a href="https://thebrag.com/sydney-solstice-set-to-fill-the-vivid-void-this-winter/" target="_blank" rel="noopener" style="color: rgb(0, 117, 232);">Sydney Solstice</a>.</h5>
                                        <h5>From June 8 to June 20, the city will be lit up over four zones surrounding the CBD, Darling Harbour, Oxford Street and Newtown with over 80 events and performances.</h5> -->
                                                <h6 class="mt-3">
                                                    Tone Deaf is set to celebrate the Sydney Solstice with the inaugural Tone Deaf Live Series. Featuring seven performances from a bunch of the finest names in Australian music. Whether your tipple of choice is good ol’ fashion meat-and-potatoes rock’n’roll or an evening of serene Jazz, there’s something that caters to all. Check out everything going on below.
                                                </h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- <h1 id="story_title" class="text-center"> -->
                                <?php // the_title(); 
                                ?>
                                <!-- </h1> -->
                                <?php // the_content(); 
                                ?>

                                <div class="events">

                                    <div class="row align-items-center">
                                        <div class="col-md-4 my-3 align-self-stretch">
                                            <div style="position: sticky; top: 1rem;">
                                                <img src="<?php echo get_template_directory_uri(); ?>/images/solstice-2021/Tone-Deaf_STAR_RUBY.jpg" style="border-radius: 1rem;">
                                            </div>
                                        </div>
                                        <div class="col-md-8 my-3">
                                            <h3>Space 44 Live: Ruby Fields, Pist Idiots, Adam Newling + Brown Snake</h3>
                                            <h4>Saturday, June 19th</h4>
                                            <hr>
                                            <p>Space 44, the beating heart of Cronulla’s underground art and music scene, is set to celebrate the Sydney Solstice with a curated live performance featuring some of the most beloved names on their roster. Ruby Fields, Pist Idiots, Adam Newling will headline The Star Sydney on Saturday, June 19th.</p>
                                            <p>Ruby Fields is undoubtedly one of the most cherished acts in the Australian music scene. Since the release of her debut single ‘I Want’ at just seventeen, she’s dominated Australia’s consciousness. In the years since, she’s soundtracked adolescence in all its messy glory with a slew of cracking releases, her debut <a href="https://tonedeaf.thebrag.com/ruby-fields-your-ads-opinion-for-dinner/" target="_blank" rel="noopener">Your Dad’s Opinion For Dinner</a> in 2018, and Permanent Hermit – with the song <a href="https://tonedeaf.thebrag.com/ruby-fields-new-song/" target="_blank" rel="noopener">“Dinosaurs”</a> making it to the top ten of <a href="https://tonedeaf.thebrag.com/triple-j-hottest-2018-live-updates/" target="_blank" rel="noopener">triple j’s Hottest 100</a> for that year.</p>
                                            <p>Joining the fold are the Pist Idiots, with their irresistible pub rock wiles. The band released their debut album, Ticker, in 2019 and have been riding a high since. Their latest single, ‘Juliette’, is a testament to their versatility as a band — a kernel of romance amongst all the sweaty, call-to-arms singalongs.</p>
                                            <p>They’ll be joined by folk-rock troubadour Adam Newling, and Brown Snake.</p>
                                            <p>Entry: $40.80</p>
                                            <div><iframe width="560" height="315" src="https://www.youtube.com/embed/MYE2xhO56L4" title="RUBY FIELDS - PRETTY GRIM" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen style="width: 100%"></iframe></div>
                                            <em>A percentage of proceeds from each ticket sale will be donated to <a href="https://www.wirringabaiya.org.au/" target="_blank" rel="noopener">Wirringa Baiya Aboriginal Womens Legal Centre</a></em>
                                            <a href=" https://premier.ticketek.com.au/shows/show.aspx?sh=SOLSTICE21" class="btn btn-lg btn-block mt-3" target="_blank" style="background-color: rgb(150,49,56); color: rgb(233,209,73);" rel="noopener">Buy tickets</a>
                                        </div>
                                    </div>
                                    <hr>

                                    <div class="row align-items-center">
                                        <div class="col-md-4 my-3 align-self-stretch">
                                            <div style="position: sticky; top: 1rem;">
                                                <img src="<?php echo get_template_directory_uri(); ?>/images/solstice-2021/TD_STAR_Nathaniel_600x849.jpg" style="border-radius: 0rem;">
                                            </div>
                                        </div>
                                        <div class="col-md-8 my-3">
                                            <h3>Nathaniel + Special Guests</h3>
                                            <h4>Friday, June 11<sup>th</sup>, 7 pm</h4>
                                            <h5>Rocklily, The Star</h5>
                                            <hr>
                                            <p>Kicking off the proceedings is a free performance from pop stalwart Nathanial, multi-instrumentalist Dom Helson and K-Note, who will perform a free show at The Star’s Rocklily on Friday, June 11th.</p>
                                            <div><iframe width="560" height="315" src="https://www.youtube.com/embed/AdZUpmXAYVE" title="Nathaniel - You" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen style="width: 100%"></iframe></div>
                                            <div class="d-flex flex-row align-items-center">
                                                <span class="mr-2 text-entry">Entry:</span>
                                                <span class="btn btn-success">FREE</span>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>

                                    <div class="row align-items-center">
                                        <div class="col-md-4 my-3 align-self-stretch">
                                            <div style="position: sticky; top: 1rem;">
                                                <img src="<?php echo get_template_directory_uri(); ?>/images/solstice-2021/TD_ThandiPhoenix_600px.jpg" style="border-radius: 0rem;">
                                            </div>
                                        </div>
                                        <div class="col-md-8 my-3">
                                            <h3>Thandi Phoenix + Special Guests</h3>
                                            <h4>Saturday, June 12<sup>th</sup>, 6 pm</h4>
                                            <h5>Rocklily, The Star</h5>
                                            <hr>
                                            <p>Thandi Phoenix captured global attention with her contribution to Rudimental‘s 2018 track, ‘My Way’, back in 2019. Since then, she’s released her excellent self-titled debut EP and a slew of striking singles. Thandi will be joined by soul musician Lana Rita and G Wizard for a free show at Rocklily on Saturday, June 12th.</p>
                                            <div><iframe width="560" height="315" src="https://www.youtube.com/embed/jalfxYeW9xc" title="Thandi Phoenix - My Way (Produced by Rudimental)" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen style="width: 100%"></iframe></div>
                                            <div class="d-flex flex-row align-items-center">
                                                <span class="mr-2 text-entry">Entry:</span>
                                                <span class="btn btn-success">FREE</span>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row align-items-center">
                                        <div class="col-md-4 my-3 align-self-stretch">
                                            <div style="position: sticky; top: 1rem;">
                                                <img src="<?php echo get_template_directory_uri(); ?>/images/solstice-2021/TD_MikeChampion_600px.jpg" style="border-radius: 0rem;">
                                            </div>
                                        </div>
                                        <div class="col-md-8 my-3">
                                            <h3>Mike Champion + Special Guests</h3>
                                            <h4>Sunday, June 13<sup>th</sup>, 7pm</h4>
                                            <h5>Rocklily, The Star</h5>
                                            <hr>
                                            <p>Mike Champion and his band have carved out a reputation as a truly dynamic live act. Champion brings funk influences to his irreverent brand of R&B and neo-soul. Johnny Boy will close out the free performance with a DJ set to take you into the thick of the night.</p>
                                            <div><iframe width="560" height="315" src="https://www.youtube.com/embed/MECph3cl84c" title="Mike Champion - Back In The Day (Music Video)" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen style="width: 100%"></iframe></div>
                                            <div class="d-flex flex-row align-items-center">
                                                <span class="mr-2 text-entry">Entry:</span>
                                                <span class="btn btn-success">FREE</span>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row align-items-center">
                                        <div class="col-md-4 my-3 align-self-stretch">
                                            <div style="position: sticky; top: 1rem;">
                                                <img src="<?php echo get_template_directory_uri(); ?>/images/solstice-2021/TD_TheWeirdAssembly_600px.jpg" style="border-radius: 0rem;">
                                            </div>
                                        </div>
                                        <div class="col-md-8 my-3">
                                            <h3>Weird Assembly + Special Guests</h3>
                                            <h4>Friday, June 18<sup>th</sup>, 6 pm</h4>
                                            <h5>Rocklily, The Star</h5>
                                            <hr>
                                            <p>Saxophonist David Weird has been cutting his teeth professionally for over three decades. With the help of his band the Weird Assembly a free evening of sumptuous jazz and blues will take over Rock Lily. With support from Dom Helson and K-Note.</p>
                                            <div><iframe width="560" height="315" src="https://www.youtube.com/embed/4vzoQ3jhFXM" title="Weird Assembly - Manly Jazz Online" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen style="width: 100%"></iframe></div>
                                            <div class="d-flex flex-row align-items-center">
                                                <span class="mr-2 text-entry">Entry:</span>
                                                <span class="btn btn-success">FREE</span>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>


                                    <div class="row align-items-center">
                                        <div class="col-md-4 my-3 align-self-stretch">
                                            <div style="position: sticky; top: 1rem;">
                                                <img src="<?php echo get_template_directory_uri(); ?>/images/solstice-2021/TD_BrownSugar_600px.jpg" style="border-radius: 0rem;">
                                            </div>
                                        </div>
                                        <div class="col-md-8 my-3">
                                            <h3>Brown Sugar + Special Guests</h3>
                                            <h4>Saturday, June 19<sup>th</sup>, 7 pm</h4>
                                            <h5>Rocklily, The Star</h5>
                                            <hr>
                                            <p>Brown Sugar is undoubtedly one of Sydney’s most beloved R&B & Soul bands. Fronted by powerhouse vocalist, Angel Tupai, the band have backed a number of the most revered names in Australian music, including the likes of John Butler Trio, Delta Goodrem, Natalie Bassingwaighte and Brian McFadden. Brown Sugar will be joined by an as-yet-announced acoustic act and DJ Trey.</p>
                                            <div><iframe width="560" height="315" src="https://www.youtube.com/embed/uP9VwSOd4Mc" title="Lyric McFarland Sings Let's Stay Together: The Voice Australia Season 2" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen style="width: 100%"></iframe></div>
                                            <div class="d-flex flex-row align-items-center">
                                                <span class="mr-2 text-entry">Entry:</span>
                                                <span class="btn btn-success">FREE</span>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>


                                    <div class="row align-items-center">
                                        <div class="col-md-4 my-3 align-self-stretch">
                                            <div style="position: sticky; top: 1rem;">
                                                <img src="<?php echo get_template_directory_uri(); ?>/images/solstice-2021/TD_KarenLeeAndrews_600px.jpg" style="border-radius: 0rem;">
                                            </div>
                                        </div>
                                        <div class="col-md-8 my-3">
                                            <h3>Karen Lee Andrews + Special Guests</h3>
                                            <h4>Sunday, June 20<sup>th</sup>, 7pm</h4>
                                            <h5>Rocklily, The Star</h5>
                                            <hr>
                                            <p>Karen Lee Andrews voice is an anomaly, a push-pull between mellifluous elegance and guttural power. Andrews found her voice in church halls, performing gospel hymns in her youth. She’s since released two albums and an EP, crystallising her singular Oceanic Blues sound.</p>
                                            <div><iframe width="560" height="315" src="https://www.youtube.com/embed/qbPUMxSMltc" title="Karen Lee Andrews - Higher (Live in Sydney)" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen style="width: 100%"></iframe></div>
                                            <div class="d-flex flex-row align-items-center">
                                                <span class="mr-2 text-entry">Entry:</span>
                                                <span class="btn btn-success">FREE</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Story End -->
                <?php
                    endwhile;

                endif;
                ?>
            </div>

            <?php
            get_footer();
