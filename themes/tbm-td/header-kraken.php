<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
    <head>
        <meta charset="<?php bloginfo( 'charset' ); ?>">
        <link rel="shortcut icon" href="<?php echo get_template_directory_uri(); ?>/images/favicon.png" />

        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="profile" href="http://gmpg.org/xfn/11">

        <meta name="google-site-verification" content="-9QoGQoc9ebynhrt1eaPTr9PfOFzD6a6Ei9cL7aAA1E" />
        <meta name="bitly-verification" content="f72fcd04077a"/>
        <meta property="fb:pages" content="105156786184223" />
        <meta property="fb:app_id" content="812299355633906" />

        <meta name="theme-color" content="#0064ca">

        <?php if (is_single()) { ?>
        <meta property="og:title" content="<?php the_title(); ?>"/>
        <meta property="og:image" content="<?php $src = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' ); if ( has_post_thumbnail() ) { echo $src[0]; } ?>"/>
        <meta property="og:type" content="article"/>
        <meta property="og:site_name" content="Tone Deaf"/>
        <meta property="og:url" content="<?php echo get_permalink(); ?>"/>

        <?php if ( in_category( 3925 ) && strtotime( $post->post_date ) < strtotime( '-60 days' ) ) : ;?>
        <meta name="robots" content="noindex">
        <?php endif; ?>

        <?php } ?>

        <link rel="dns-prefetch" href="https://cdn.onesignal.com/">
        <link rel="dns-prefetch" href="https://www.googletagservices.com/">
        <link rel="dns-prefetch" href="//cdn.publift.com/">
        <link rel="dns-prefetch" href="https://idsync.rlcdn.com/">
        <link rel="dns-prefetch" href="https://x.bidswitch.net/">
        <link rel="dns-prefetch" href="https://ad.yieldmanager.com/">
        <link rel="dns-prefetch" href="https://cm.g.doubleclick.net/">
        <link rel="dns-prefetch" href="https://dpm.demdex.net/">
        <link rel="dns-prefetch" href="https://fw.adsafeprotected.com/">
        <link rel="dns-prefetch" href="https://www.googletagmanager.com">
        <link rel="dns-prefetch" href="https://tpc.googlesyndication.com">
        <link rel="dns-prefetch" href="https://adservice.google.com">
        <link rel="dns-prefetch" href="https://s.ytimg.com">
        <link rel="dns-prefetch" href="https://adservice.google.com.au">
        <link rel="dns-prefetch" href="https://pagead2.googlesyndication.com">
        <link rel="dns-prefetch" href="https://securepubads.g.doubleclick.net">
        <link rel="dns-prefetch" href="https://fonts.googleapis.com">
        <link rel="dns-prefetch" href="https://certify-js.alexametrics.com">
        <link rel="dns-prefetch" href="https://www.google-analytics.com">
        <link rel="dns-prefetch" href="https://connect.facebook.net">
        <link rel="dns-prefetch" href="https://bs.serving-sys.com">
        <link rel="dns-prefetch" href="https://bid.g.doubleclick.net">
        <link rel="dns-prefetch" href="https://gum.criteo.com">
        <link rel="dns-prefetch" href="https://sc.iasds01.com">
        <link rel="dns-prefetch" href="https://dt.adsafeprotected.com">
        <link rel="dns-prefetch" href="https://googleads.g.doubleclick.net">
        <link rel="dns-prefetch" href="https://secure-ds.serving-sys.com">

        <link rel="manifest" href="/manifest.json">

        <?php if ( is_single() ) :
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
        <script>
            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push({
                'AuthorCD': '<?php echo $author; ?>'
            });
        </script>
        <?php endif; ?>

        <!-- Google Tag Manager -->
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GTM-522F5WH');</script>
        <!-- End Google Tag Manager -->



        <style>
@font-face{font-family:'BEBAS';src:url('<?php echo get_template_directory_uri(); ?>/fonts/BEBAS.ttf')  format('truetype')}
            *,:after,:before{
    box-sizing:border-box
}
html,body{
    margin:0;
    background-color: #040404;
}
body{
    font-family: Vollkorn, Helvetica, serif;
    font-size: 18px;
    line-height: normal;
    background-attachment:fixed!important;
    background-repeat:no-repeat;
    background-position:center top
}

body.post-template-single-kraken h1,
body.post-template-single-kraken h2,
body.post-template-single-kraken h3,
body.post-template-single-kraken h4,
body.post-template-single-kraken h5,
body.post-template-single-kraken h6
{
    font-family: 'proxima-nova',sans-serif;
}
/*body.post-template-single-kraken {
    background: #040404;
}*/
body.post-template-single-kraken #story_title {
    color: #fff;
    text-align: center;
    margin-left: 100px;
    margin-right: 100px;
    position: relative;
    font-family: 'BEBAS','Lato',sans-serif;
    text-transform: uppercase;
    word-spacing: 7px;
    line-height: 1.3;
}
body.post-template-single-kraken #story_title:before, body.post-template-single-kraken #story_title:after {
    content: "";
    width: 66px;
    height: 22px;
    position: absolute;
    display: block;
    top: 10px;
}
body.post-template-single-kraken #story_title:before {
    background: url(<?php echo get_template_directory_uri(); ?>/images/kraken/ornament_left.png) no-repeat center center;
    left: -66px;
}
body.post-template-single-kraken #story_title:after {
    background: url(<?php echo get_template_directory_uri(); ?>/images/kraken/ornament_right.png) no-repeat center center;
    right: -66px;
}
body.post-template-single-kraken .first-paragraph {
    font-size: 24px;
    line-height: 35px;
}
h2,h3{
    font-family:'Lato',sans-serif;
    font-weight:normal
}
iframe,img{
    max-width:100%;
    margin:auto
}
iframe{
    border:0
}
a{
    color:#0064ca;
    text-decoration:none
}
.content-wrap.container{
    z-index:2;
    position:relative;
    padding:0!important
}
.container img{
    max-width:100%;
    height:auto
}
.clear{
    clear:both
}
.container{
    padding-left:15px;
    padding-right:15px;
    margin:auto;
    color: #bdbdbd;
}
#main{
    position:relative;
    margin-top:0
}
#section-hero{
    height:319px
}
.skin{position:fixed;top:0;left:50%;transform:translateX(-50%);width:1600px}
.skin-kraken {
    position: fixed;
    top: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 1600px;
}
body.post-template-single-kraken {
    background: #040404; /* url(<?php echo get_template_directory_uri(); ?>/images/kraken/skin.png) no-repeat center top; */
    background-attachment: scroll !important;
}

@media screen and (min-height:1200px){
    body.post-template-single-kraken {
        background-attachment: fixed !important;
        background-position: center bottom;
    }
}

body.post-template-single-kraken.fixed-bg {
    background-attachment: fixed !important;
    background-position: center bottom;
}

.col-left,.col-right{
    float:left
}
.col-right{
    width:300px
}
.col-left{
    width:calc(100% - 315px);
    margin:auto 15px 15px auto
}
.post-thumbnail {
    text-align: center;
    padding: 20px 10px;
}
.post-thumbnail img{
    width: auto !important;
    max-width: 300px !important;
    margin-right: 0 !important;
    height:auto;
    max-height: 250px;
}
#header{
    margin:auto;
    height:auto;
    z-index:99999;
    top:0;
    background: transparent !important;
}
#header .container .head_wrap{
    padding-top:10px
}
#header #logo{
    float:left;
    width:180px;
    margin-right:10px;
    margin-top:10px
}
#header #logo #logo-white, #logo icon-white{
    display:none
}
body.post-template-single-kraken #header.fixed .container .head_wrap, #header.fixed {
    background: #525252 !important;
}
#header #head_right{
    float:left;
    width:calc(100% - 190px)
}

body.post-template-single-kraken .search-top,
body.post-template-single-kraken .search-top .search-field {
    background: transparent;
}
body.post-template-single-kraken .search-top {
    border: 1px solid #525252;
}
body.post-template-single-kraken .search-top .button {
    color: #525252;
}
body.post-template-single-kraken .l_social.subscribe a:after {
    background: #525252;
}

.toggle_head_nav{
    display:none
}
.head_menu{
    margin-top:10px
}
.menu li{
    display:inline;
    list-style-type:none;
    line-height:14px;
    font-size:13px;
    text-transform:uppercase;
    padding:0 30px 0 0;
    margin:0;
    font-family:'Lato',sans-serif
}
.menu li{
    display:inline;
    list-style-type:none;
    text-transform:uppercase;
    padding:0 30px 0 0;
    margin:0;
    position:relative
}
.menu li ul{
    background-color:#7ed6df
}
body.post-template-single-kraken .menu li a{
    color: #525252;
    text-decoration:none
}
body.post-template-single-kraken .menu li:hover,
body.post-template-single-kraken .current-menu-item,
body.post-template-single-kraken .menu li ul{
    background-color: #525252;
}
body.post-template-single-kraken .menu li:hover a,
body.post-template-single-kraken .menu li ul li a {
    color: #fff !important;
}
body.expanded{max-height:100%;overflow:hidden}
a {
    color: #bdbdbd !important;
    transition: .25s all linear;
}
a:hover {
    color: #525252 !important;
}

.menu li ul{
    display:none;
    position:absolute;
    top:100%;
    left:0;
    padding:10px 10px 0;
    z-index:20;
    min-width:112px;
    box-shadow:0 6px 12px rgba(0,0,0,.175)
}
.menu li ul li,#menu_main li ul li{
    float:none;
    display:block;
    padding:0
}
.menu li ul li a{
    display:block;
    padding:10px 0!important
}
#menu_main{
    margin:0;
    padding:0
}
#menu_main li{
    padding:0;
    display:inline-block
}
#menu_main li a{
    padding:6px;
    display:block
}
.menu-item-has-children a:after{
    content:"\f0d7";
    font-family:"FontAwesome";
    display:inline-block;
    padding-left:4px
}
.menu-item-has-children ul li a:after{
    content:''
}
.social_menu{
    height:27px;
    float:right
}
#menu_social{
    margin:0;
    padding:0 0 2px
}
#menu_social li{
    font-size:.6rem;
    padding:0;
    margin:0 3px
}
#menu_social li:hover{
    background-color:transparent
}
#menu_social li:last-child{
    padding-right:0;
    margin-right:0
}
.l_social.facebook a,.l_social.facebook a i, .l_social.twitter a,.l_social.twitter a i, .l_social.instagram a,.l_social.instagram a i, .l_social.youtube a i{
    color:#525252
}
#menu_social li a i.fa-lg{
    font-size:1.7em
}
.search-top{
    float:right;
    margin-right:10px;
    background:#fff;
    border:1px solid #ccc
}
.search-top .search-field{
    outline:0;
    display:inline-block;
    width:auto;
    height:24px;
    padding:1px 5px;
    border:0
}
.search-top .button{
    display:inline-block;
    border:0;
    background:0;
    font-size:1em;
    border:0
}
.search-top .button i{
    color:inherit
}
#toggle-search-top{
    margin-left:15px;
    float:left;
    display:none;
    border:0;
    background:0;
    font-size:1em;
    border:0;
    padding:2px;
    outline:0
}
#menu_main{
    margin:0;
    padding:0
}

#onesignal-bell-container,
.onesignal-bell-container {
    display: none;
}

@font-face{
    font-family:'FontAwesome';
    src:url('<?php echo get_template_directory_uri(); ?>/font-awesome/fonts/fontawesome-webfont.eot?v=4.7.0');
    src:url('<?php echo get_template_directory_uri(); ?>/font-awesome/fonts/fontawesome-webfont.eot?#iefix&v=4.7.0') format('embedded-opentype'),url('<?php echo get_template_directory_uri(); ?>/font-awesome/fonts/fontawesome-webfont.woff2?v=4.7.0') format('woff2'),url('<?php echo get_template_directory_uri(); ?>/font-awesome/fonts/fontawesome-webfont.woff?v=4.7.0') format('woff'),url('<?php echo get_template_directory_uri(); ?>/font-awesome/fonts/fontawesome-webfont.ttf?v=4.7.0') format('truetype'),url('<?php echo get_template_directory_uri(); ?>/font-awesome/fonts/fontawesome-webfont.svg?v=4.7.0#fontawesomeregular') format('svg');
    font-weight:normal;
    font-style:normal
}
.fa{
    display:inline-block;
    font:normal normal normal 14px/1 FontAwesome;
    font-size:inherit;
    text-rendering:auto
}
.fa-lg{
    font-size:1.33333333em;
    line-height:.75em;
    vertical-align:-15%
}
.fa-search:before{
    content:"\f002"
}
.fa-close:before{
    content:"\f00d"
}
.fa-twitter:before{
    content:"\f099"
}
.fa-facebook:before{
    content:"\f09a"
}
.fa-facebook-square:before{
    content:"\f082"
}
.fa-envelope:before{content:"\f0e0"}
#header.fixed .search-top{
    display:none;
}
#header.fixed .search-top.expanded{
    display: block;
}
 .fa-google-plus:before{
    content:"\f0d5"
}
 .fa-reddit-alien:before{
    content:"\f281"
}
 .fa-whatsapp:before{
    content:"\f232"
}
 .fa-bars:before{
    content:"\f0c9"
}
.fa-remove:before,.fa-close:before,.fa-times:before{
    content:"\f00d"
}
.fa-caret-down:before{
    content:"\f0d7"
}
.fa-youtube:before{
    content:"\f167"
}
.fa-instagram:before{
    content:"\f16d"
}
.fa-envelope:before{
    content:"\f0e0"
}
@media(min-width:768px) and (max-width:991px) {
    #header.fixed #head_right .menu_wrap {
        width: calc(100% - 20px) !important;
    }
    #toggle-search-top{
        color: #525252;
    }
}
 @media screen and (max-width:767px){
     .container{
        width:100%
    }
    #section-hero{
        height:auto
    }
    #featured-video .badge,.cover_story .badge,.gigs-home .badge{
        font-size:.8rem;
        padding:0 3px
    }
    .cover_story{
        height:250px
    }
    .cover_story .post-content h2{
        padding:0;
        font-size:1rem;
        line-height:1.4rem
    }
    .cover_story img{
        top:50%;
        left:50%;
        transform:translateX(-50%) translateY(-50%);
        height:100%;
        width:auto;
        max-width:none
    }
    .col-left,.col-right{
        float:none;
        width:100%!important
    }
    .home-article{
        width:100%!important;
        margin-left:0!important;
        margin-right:0!important
    }
    #featured-video{
        margin-bottom:15px
    }
/*    .toggle_head_nav{
        display:block;
        float:left;
        font-family:'Lato',sans-serif
    }*/
    .home-article{
        position:relative
    }
    .home-article .post-content{
        padding:0 10px 10px
    }
    .home-article .post-content h2{
        font-size:1rem;
        margin-bottom:30px;
        margin-top:15px
    }
    .home-article .post-thumbnail{
        height:200px
    }
    .home-article .post-content .post-excerpt{
        display:none
    }
    .home-article .post-content .post-time{
        position:absolute;
        bottom:0
    }
    .home-article .post-thumbnail img{
        width:100%;
        height:auto;
        transform:translateX(-50%) translateY(-50%) scale(1)
    }
    #header{
        background:#fff
    }
    #header .container{
        padding:0
    }
/*    .head_menu{
        background:rgba(0,0,0,.5);
        position:absolute;
        left:-100%;
        width:100%;
        top:100px;
        margin-top:0;
        overflow:scroll;
        height:100vh
    }*/
/*    .menu_wrap{
        position:absolute;
        left:-100%;
        top:50px;
        width:100%;
        padding:15px 0;
        height:60px;
        background: transparent !important;
    }*/
    #menu_social li a i.fa-lg{
        color:#fff!important
    }
/*     #header #logo{
        float:none;
        width:80px;
        position:absolute;
        top:15px;
        left:45px;
        transform:none;
        z-index:2;
        margin-right:0;
        margin-top:0
    }
     #header #head_right{
        float:none;
        top:0;
        left:0;
        width:100%;
        padding:5px 20px 0;
        z-index:1;
        margin-top:-5px
    }*/
    #header *{
        z-index:3
    }
    #header #logo{
        top:10px
    }
    .social_menu{
        margin-right:20px
    }
    #toggle-search-top{
        display:none
    }
    .search-top{
        display:none;
        float:left;
        margin-left:15px
    }
    #menu_main{
        /*box-shadow:0 6px 12px rgba(0,0,0,.175);*/
        padding-left:10px;
    }
    #menu_main li{
        padding:0;
        /*display:block*/
    }
    #menu_main li a{
        padding:10px;
        color:#fff
    }
    .menu li ul li,#menu_main li ul li{
        padding:0
    }
    .menu li ul{
        position:relative;
        margin-left:20px;
        background:transparent;
        box-shadow:none;
        padding-top:0
    }
    .toggle_head_nav {
        color: #525252;
    }
    .col-right{
        display:none
    }
    .gig{
        margin-left:-15px;
        margin-right:-15px
    }
}
 @media (min-width:768px) and (max-width:991px){
    .container{
        width:750px
    }
    /*
    .toggle_head_nav{
        display:block;
        float:left;
        font-family:'Lato',sans-serif;
        margin-top:-2px
    }
    */
    .cover_story img{
        top:50%;
        left:50%;
        transform:translateX(-50%) translateY(-50%);
        height:100%;
        width:auto;
        max-width:none
    }
    .home-article{
        width:100%!important;
        margin-left:auto!important;
        position:relative
    }
    .home-article .post-content{
        padding:0 10px 10px
    }
    .home-article .post-thumbnail{
        height:180px
    }
    #header{
        height:50px
    }
    #header{
        background:#fff
    }
    #header .container{
        padding:0
    }
    /*
    .head_menu{
        background:rgba(0,0,0,.5);
        background:#0064ca;
        position:absolute;
        left:-100%;
        width:50%;
        top:50px;
        margin-top:0;
        overflow:scroll;
        height:100vh
    }
    #header #logo{
        float:none;
        width:120px;
        position:absolute;
        top:5px;
        left:50%;
        transform:translateX(-50%);
        z-index:2
    }
    #header #head_right{
        float:none;
        top:0;
        left:0;
        width:100%;
        padding:15px 20px 0;
        z-index:1;
        margin-top:-10px
    }
    #toggle-search-top{
        display:block
    }
    .search-top{
        display:none
    }
    #menu_main{
        padding-left:15px;
        background:#0064ca
    }
    #menu_main li{
        padding:0;
        display:block
    }
    #menu_main li a{
        color:#fff;
        padding:10px
    }
    .menu li ul li,#menu_main li ul li{
        padding:0
    }
    .menu li ul{
        position:relative;
        margin-left:20px;
        background:transparent;
        box-shadow:none;
        padding-top:0
    }
    */
    body{
        background-size:1230px
    }
}
@media (min-width:992px) and (max-width:1200px){
    .container{
        width: 970px;
    }
    body{
        background-size:1555px
    }
}
@media (min-width:1201px){
    .container{
        width: 970px;
    }
    body{
        background-size:1680px
    }
    body{
        background-size:1555px
    }
}
 .social-share-buttons-stacked{
    position:fixed;
    width:50px;
    margin-left:-7.5px;
    z-index:1
}
 .social-share-buttons-row{
    display:flex;
    padding:10px 50px;
    text-align:center
}
 .social-share-buttons-stacked a,.social-share-buttons-row a{
    padding:10px;
    margin:0 auto 5px auto;
    border:0;
    text-decoration:none;
    color:#fff;
    display:block;
    text-align:center;
    font-size:200%;
    border-radius:50%
}
 .social-share-buttons-row a{
    width:50px;
    height:50px;
    margin:10px 5px;
    display:inline-block;
    flex:1 1 auto;
    border-radius:0
}
 .yt-lazy-load {
     position: relative;
     cursor: pointer;
}
 .yt-lazy-load .yt-img {
     z-index: 1
}
 .yt-lazy-load .play-button-red {
     position: absolute;
     top: 50%;
     left: 50%;
     transform: translateY(-50%) translateX(-50%);
     z-index: 2
}

.nav-network {
    position: fixed;
    top: 0;
    right: 0;
    color: #fff;
    background: #000;
    z-index: 10000;
    transition: .25s all linear !important;
    width: 300px;
}
.nav-network {
    /*display: none;*/
    right: -100%;
}
.nav-network.expanded {
    display: block;
}
.nav-network a.l_toggle_menu_network {
    color: #fff;
    display: block;
    text-align: right;
}
.nav-network ul {
    margin: 0;
    padding: 0;
}
.nav-network ul li {
    display: block !important;
    width: 100%;
    padding: 0px;
    margin: 0;
    text-align: center;
    position: relative;
}
.nav-network ul li a {
    color: #fff;
    display: block;
    padding: 20px;
}
.nav-network ul li a img {
    max-width: 140px;
}
body.expanded-network{
    max-height:100%;
    overflow:hidden;
}
#header.fixed li.l_toggle_menu_network img.light, div.l_toggle_menu_network.fixed img.light {
    display: block;
}
#header.fixed li.l_toggle_menu_network img.dark, div.l_toggle_menu_network.fixed img.dark {
    display: none;
}
.nav-network.expanded {
    height: 100vh;
    right: 0;
    overflow-y: scroll;
    box-shadow: -5px -5px 10px rgba(0,0,0,.5);
}

.cocktail {
    display: flex;
    flex-direction: row-reverse;
    flex-wrap: wrap;
}
.cocktail .image {
    position: relative;
    width: 250px;
    max-width: 100%;
    text-align: center;
    margin-left: 20px;
}
.cocktail .image img {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    max-height: 100%;
}
.cocktail .details {
    width: 400px;
    max-width: 100%;
    text-align: center;
}
.cocktail .details .title h3 {
    font-family: 'proxima-nova',sans-serif;
    text-transform: uppercase;
    color: #d7d7d7;
    font-size: 24px;
    letter-spacing: 2px;
}
.cocktail .details .divider {
    width: 15px;
    height: 2px;
    margin: auto;
    display: block;
    background: #bdbdbd;
    margin: 20px auto;
}
.cocktail .details .ingredients {
/*    font-style: italic;
    font-weight: 600;
    font-size: 16px;*/
    color: #d7d7d7;
    font: italic 16px/24px Vollkorn, Helvetica, serif;
    font-weight: 600;
}

@media screen and (max-width: 768px){
    .cocktail {
        display: block;
    }
    .cocktail .details, .cocktail .image {
        width: 100%;
        text-align: center;
        margin-left: 0;
    }
    .cocktail .image img {
        position: relative;
        top: inherit;
        left: inherit;
        transform: none;

        max-height: 250px;
    }

    body.post-template-single-kraken #story_title {
        margin: auto !important;
        padding-bottom: 15px;
    }
    body.post-template-single-kraken #story_title:before, body.post-template-single-kraken #story_title:after {
        display: none;
    }
}

.social-share {
    width: 100%;
    text-align: center;
    margin: 50px auto;
}
.social-share .txt-share {
    font-family: 'proxima-nova',sans-serif;
    text-transform: uppercase;
    font-size: 14px;
    margin-bottom: 20px;
    color: #fff;
}
.social-share .txt-share:before {
    font-size: 18px;
    margin-right: 6px;
    content: '{';
}
.social-share .txt-share:after {
    font-size: 18px;
    margin-left: 6px;
    content: '}';
}
.social-share a {
    font-size: .9rem;
    padding: 4px;
    margin: auto 4px;
}
.social-share a{
    color: #fff !important;
}

#header.fixed #menu_main li.l_social.subscribe a,#menu_main li.l_social.subscribe a{padding: 0}
#header.fixed{top:0;height:auto;position:fixed}
#header.fixed .container .head_wrap{background:#0064ca}
#header.fixed #logo{width:70px;margin-right:10px;margin-top:0;margin-left:10px}
#header.fixed #logo #icon-white{display:inline}
#header.fixed #logo #logo-blue{display:none}
#header.fixed #logo.logo-gaming{margin-top:-5px}
#header.fixed #head_right{float:right;width:calc(100% - 100px);margin-right:10px}
#header.fixed #head_right .menu_wrap{float:right;margin-top:-5px}
#header.fixed .head_menu{margin-top:-7px;float:left}
#header.fixed #menu_main li a{padding:10px 7px}
#header.fixed .search-top{margin-right:5px}
#header.fixed .search-top .search-field{width:80px}
#header.fixed .menu li a{color:#fff}
#header.fixed .menu li:hover a,.current-menu-item a,#header.fixed .menu li.current-menu-item a{color:#333}
#header.fixed .menu li ul{padding:5px}
#header.fixed #menu_main li ul li{padding:0}
#header.fixed .l_social a i{color:#fff}
#header.fixed #menu_social li:hover{background:0}
#header.fixed li.l_toggle_menu_network img.light, div.l_toggle_menu_network.fixed img.light {
    display: block;
}
#header.fixed li.l_toggle_menu_network img.dark, div.l_toggle_menu_network.fixed img.dark {
    display: none;
}
#header #logo #logo-white,#header #logo #icon-white{display:none}
@media (max-width:767px) {
    li.l_toggle_menu_network {
        display: none;
    }
    .nav-network {
        width: 100%;
    }
    div.l_toggle_menu_network {
        padding: 13px 10px 1px 10px;
    }
    div.l_toggle_menu_network.fixed {
        padding: 13px 10px 12px 10px;
    }
}
@media screen and (max-width:768px){
#menu_main li.l_social.subscribe a,#header.fixed #menu_main li.l_social.subscribe a{padding:10px}
}
@media(min-width:992px){#header.fixed{top:0;height:auto}
#header.fixed .container .head_wrap{background:#0064ca;/*box-shadow:0 1px 3px 0 rgba(0,0,0,0.2)*/}
#header.fixed #logo{width:30px;margin-right:5px;margin-top:-2px;margin-left:5px}
#header.fixed #logo.logo-gaming{margin-top:-5px}
#header.fixed #head_right{float:right;width:calc(100% - 50px);margin-right:5px}
#header.fixed #head_right .menu_wrap{float:right;margin-top:-5px}
#header.fixed .head_menu{margin-top:-7px;float:left}
#header.fixed #menu_main li{font-size:11px}
#header.fixed #menu_main li a{padding:10px 5px}
#header.fixed .menu li a{color:#fff}
#header.fixed .menu li:hover a,.current-menu-item a,#header.fixed .menu li.current-menu-item a{color:#333}
#header.fixed #menu_social.menu li:hover a{color:#fff}
#header.fixed .menu li ul{padding:5px}
#header.fixed #menu_main li ul li{padding:0}
#header.fixed .l_social a i{color:#fff}
#header.fixed #menu_social li:hover{background:0}
}
        </style>

        <?php wp_head(); ?>

        <!-- <script async src="//cdn.publift.com/fuse/tag/2/1088/fuse.js" defer></script> -->

        <script>
    jQuery(document).ready(function($) {
        $(window).scroll(function() {
    //        if ( $(window).scrollTop() > $('#header').outerHeight() ) {
            if ( $(window).scrollTop() > 0 ) {
                $('#header').slideDown().addClass('fixed');
                $('div.l_toggle_menu_network').addClass('fixed');
                if ( window_width > 991 ) {
                    $('#header').slideDown().addClass('fixed');
                } else if ( window_width < 992 && window_width > 420 ) {
                    $('#header').slideDown().addClass('fixed-tablet');
                }
            } else {
                $('#header').removeClass('fixed fixed-tablet');
                $('div.l_toggle_menu_network').removeClass('fixed');
            }
        });

        window.onresize = function(event){
            window_width = $(window).width();
            window_height = $(window).width();
            if ( window_width > 991 ) {
    //            $("body, #td_wrap").removeClass('expanded');
                $('.menu li ul').hide();
            }
        }

        $(".toggle_head_nav").on("click", function(o) {
            $("body, #header, .head_menu, .menu_wrap, #main").toggleClass('expanded');

            $('.toggle_head_nav i').removeClass().addClass(
                $('.head_menu.expanded').length ? 'fa fa-times' : 'fa fa-bars'
            );
        });
    });
        </script>

    </head>

    <body <?php body_class(); ?>>

        <div id="td_wrap" class="content-wrap container">

        <!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-522F5WH"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = 'https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.12&appId=812299355633906&autoLogAppEvents=1';
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>

    <div id="header">
        <div class="container">
            <div class="head_wrap">
            <div id="logo">
                <a href="<?php echo site_url(); ?>">
                    <img src="<?php echo get_template_directory_uri(); ?>/images/kraken/Tonedeaf-logowhite2.png" alt="Tone Deaf" id="logo-blue" />
                    <img src="<?php echo get_template_directory_uri(); ?>/images/icon-tonedeaf-white.png" alt="Tone Deaf" id="icon-white" />
                    <img src="<?php echo get_template_directory_uri(); ?>/images/kraken/Tonedeaf-logowhite2.png" alt="Tone Deaf" id="logo-white" />
                </a>
            </div>
            <div id="head_right">
                <div class="toggle_head_nav">
                    <i class="fa fa-bars" aria-hidden="true"></i>
                </div>
                <div class="menu_wrap">
                    <nav id="social-navigation" class="social_menu" aria-label="Menu Social">
                        <?php
//                        wp_nav_menu( array(
//                            'theme_location' => 'get_in_touch',
//                            'menu_id'        => 'menu_social',
//                            ) );
                        ?>
                        <div class="menu-secondary-menu-container">
                            <ul id="menu_social" class="menu">
                                <li class="l_social facebook menu-item"><a target="_blank" href="https://www.facebook.com/tonedeafmusic"><i class="fa fa-facebook fa-lg" aria-hidden="true"></i></a></li>
                                <li class="l_social twitter menu-item"><a target="_blank" href="https://twitter.com/tonedeaf_music"><i class="fa fa-twitter fa-lg" aria-hidden="true"></i></a></li>
                                <li class="l_social instagram menu-item"><a target="_blank" href="https://www.instagram.com/tonedeaf/"><i class="fa fa-instagram fa-lg" aria-hidden="true"></i></a></li>
                                <li class="l_social youtube menu-item"><a target="_blank" href="https://www.youtube.com/c/tonedeaf"><i class="fa fa-youtube fa-lg" aria-hidden="true"></i></a></li>
                            </ul>
                        </div>
                    </nav>

                    <div class="search-top">
                        <form role="search" method="get" id="searchform" class="searchform" action="<?php echo site_url(); ?>">
                            <input type="text" name="s" class="search-field" placeholder="Search..." autocomplete="off">
                            <button type="submit" class="button"><i class="fa fa-search" aria-hidden="true"></i></button>
                        </form>
                    </div>
                    <button class="button" id="toggle-search-top"><i class="fa fa-search" aria-hidden="true"></i></button>

                    <div class="clear"></div>
                </div>
                <nav id="site-navigation" class="head_menu" aria-label="Main Menu">
                    <?php
                    wp_nav_menu( array(
                        'theme_location' => 'top',
                        'menu_id'        => 'menu_main',
                        ) );
                    ?>
                </nav>
            </div>

            <div id="nav-network" class="nav-network">
                <a href="#" class="l_toggle_menu_network"><i class="fa fa-lg fa-times" aria-hidden="true"></i></a>
                <div class="menu-network" id="menu-network">
                    <ul class="">
                        <li><a href="https://thebrag.com/" target="_blank"><img src="<?php echo get_template_directory_uri(); ?>/images/pubs-white/the-brag.png" alt="The BRAG"></a></li>
                        <li><a href="https://dad.thebrag.com/" target="_blank"><img src="<?php echo get_template_directory_uri(); ?>/images/pubs-white/brag-dad.png" alt="Brag Dad"></a></li>
                        <li><a href="https://thebrag.com/gaming/" target="_blank"><img src="<?php echo get_template_directory_uri(); ?>/images/pubs-white/brag-gaming.png" alt="Brag Gaming"></a></li>
                        <li><a href="https://thebrag.com/issue/" target="_blank"><img src="<?php echo get_template_directory_uri(); ?>/images/pubs-white/brag-mag.png" alt="Brag Magazine"></a></li>
                        <li><a href="https://markets.thebrag.com/" target="_blank"><img src="<?php echo get_template_directory_uri(); ?>/images/pubs-white/brag-markets.png" alt="The Brag Markets"></a></li>
                        <li><a href="https://dontboreus.thebrag.com/" target="_blank"><img src="<?php echo get_template_directory_uri(); ?>/images/pubs-white/dbu.png" alt="Don't Bore Us"></a></li>
                        <li><a href="https://theindustryobserver.thebrag.com/" target="_blank"><img src="<?php echo get_template_directory_uri(); ?>/images/pubs-white/tio.png" alt="The Industry Observer"></a></li>
                        <li><a href="https://tonedeaf.thebrag.com/" target="_blank"><img src="<?php echo get_template_directory_uri(); ?>/images/pubs-white/tone-deaf.png" alt="Tone Deaf"></a></li>
                    </ul>
                </div>
            </div>

            <div class="clear"></div>
            </div>
        </div>
    </div>

<div id="main">

    <div class="container" style="width: 700px; max-width: 100%;">

        <div class="logo-kraken" style="text-align: center">

        </div>
