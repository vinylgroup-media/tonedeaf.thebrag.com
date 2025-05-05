jQuery(document).ready(function ($) {
    /* $("#toggle-top-search").on("click", function(e) {
      $("#searchform-wrap").toggleClass("expanded");
      if ($("#searchform-wrap").hasClass("expanded")) {
        $(this)
          .find("i")
          .removeClass("fa-search")
          .addClass("fa-times");
        $("#searchform-wrap")
          .find("input.search-field")
          .focus();
      } else {
        $(this)
          .find("i")
          .removeClass("fa-times")
          .addClass("fa-search");
      }
    }); */

    /* $("ul li.menu-item-has-children a[href=\\#]").on("click", function(e) {
      e.preventDefault();
      $(this)
        .parent()
        .find("ul")
        .toggle();
      $(this).toggleClass("expanded");
    });
   */

    $(".btn-toggle-slidedown").on("click", function () {
        if ($(this).data("target")) {
            var target = $("#" + $(this).data("target"));
            if (target.hasClass("slidedown-active")) {
                target.removeClass("slidedown-active");
                $("#overlay").addClass("d-none");
                $("body").removeClass("modal-open");
                target.slideUp();
            } else {
                $(".slidedown-active").removeClass("slidedown-active").hide();
                target.slideDown().addClass("slidedown-active");
                $("#overlay").removeClass("d-none");
                $("body").addClass("modal-open");
            }
        }
    });

    $(".btn-toggle-network-mobile").on("click", function () {
        $(this).toggleClass("active");
        $("#network-mobile").slideToggle();
        $("#search-nav-wrap").slideToggle();
    });

    $("#overlay").on("click", function () {
        $(this).addClass("d-none");
        $("body").removeClass("modal-open");
        $(".slidedown-active").removeClass("slidedown-active").hide(); // .slideUp();
        // $('.nav-wrap').addClass('d-none');
        $(".nav-wrap").removeClass("active");
        $(".modal").hide();
    });

    $(".btn-toggle-menu").on("click", function () {
        $(".nav-wrap").addClass("active");
        $("#overlay").removeClass("d-none");
        $("body").addClass("modal-open");
    });

    $(".btn-close-menu").on("click", function () {
        $(".nav-wrap").removeClass("active");
        $("#overlay").addClass("d-none");
        $("body").removeClass("modal-open");
    });

    $('[data-toggle="modal"]').on("click", function (e) {
        e.preventDefault();
        var target = $($(this).data("target"));
        $("body").addClass("modal-open");
        $("#overlay").removeClass("d-none");
        target.fadeIn();
    });
    $(".modal .close").on("click", function (e) {
        e.preventDefault();
        var target = $(this).closest(".modal");
        target.fadeOut();
        $("#overlay").addClass("d-none");
        $("body").removeClass("modal-open");
    });

    $(".btn-open-top-search").on("click", function () {
        $("#top-search-wrap").addClass("active");
    });
    $(".btn-close-top-search").on("click", function () {
        $("#top-search-wrap").removeClass("active");
    });

    $(".observer-list .topics-active a").on("click", function (e) {
        e.preventDefault();
        var btn = $(this);

        if (!btn.hasClass("subscribed")) {
            var status = "subscribed";
        } else {
            var status = "unsubscribed";
        }

        var list = $(this).data("list");

        var data = {
            action: "subscribe_observer",
            formData: "list=" + list + "&status=" + status,
        };
        $.post(global.ajax_url, data, function (res) {
            if (res.success) {
                $("a[data-list=" + list + "]").toggleClass("subscribed");
            }
        });
    });

    $("body").on("click", ".yt-lazy-load", function () {
        var video_id = $(this).data("id");
        var player_id = $(this).prop("id");
        var player_height = $(this).height();

        var player;
        player = new YT.Player(player_id, {
            height: player_height,
            videoId: video_id,
            events: {
                onReady: onPlayerReady,
            },
        });

        function onPlayerReady(event) {
            event.target.playVideo();
        }
    });

    $(".l_video").on("click", function (e) {
        e.preventDefault();
        var yt = $(this).data("youtube");
        $("#tb-video-modal .modal-body").html("");
        var i =
            '<iframe width="560" height="349" src="http://www.youtube.com/embed/' +
            yt +
            '?rel=0&autoplay=1&color=white" frameborder="0" allowfullscreen ></iframe>';
        $("#tb-video-modal .modal-body").html(i),
            $("#tb-video-modal").modal("show");
    });
    $("#tb-video-modal").on("hidden.bs.modal", function () {
        $("#tb-video-modal .modal-body").html("");
    });
    $(".datepicker").length &&
    $(".datepicker").datepicker({
        format: "dd M yyyy",
    });
    $("body").on("click", ".social-share-link", function (o) {
        return (
            o.preventDefault(),
                window.open(
                    $(this).attr("href"),
                    $(this).data("type"),
                    "height=450, width=550, top=" +
                    ($(window).height() / 2 - 225) +
                    ", left=" +
                    ($(window).width() / 2 - 275) +
                    ", toolbar=0, location=0, menubar=0, directories=0, scrollbars=0"
                ),
                !1
        );
    });

    if ($("#articles-wrap").length) {
        $("#articles-wrap").append('<div class="load-more">Loading...</div>');
        var button = $("#articles-wrap .load-more");
        var loading = false;
        var scrollHandling = {
            allow: true,
            reallow: function () {
                scrollHandling.allow = true;
            },
            delay: 400,
        };
        var count_articles = 2;
    }

    var winTop = $(window).scrollTop();
    var page_title = document.title;
    var page_url =
        document.location.protocol +
        "//" +
        document.location.host +
        document.location.pathname;

    var $news_stories = $(".single-article");
    var visible_news_story = $.grep($news_stories, function (item) {
        return $(item).position().top <= winTop + $(window).height() / 2; // + $('#header').outerHeight() - 30;
    });

    var winHeight = $(window).height();

    if ($(".single-article .post-content").find("h2").length >= 6) {
        $.each(
            $(".single-article .post-content").find("h2"),
            function (index, elem) {
                if ($(this).hasClass("observer-title")) {
                    return;
                }
                var url_slug = $(this)
                    .text()
                    .toLowerCase()
                    .replace(/ /g, "-")
                    .replace(/[^\w-]+/g, "");
                var page_url_scroll =
                    $(visible_news_story).last().find("h1").data("href") +
                    "list/" +
                    url_slug +
                    "/";
                console.log(page_url_scroll);
                $(this).data("href", page_url_scroll);
                $(this).data("id", url_slug);
            }
        );
    } else if ($(".single-article .post-content").find("h3").length >= 6) {
        $.each(
            $(".single-article .post-content").find("h3"),
            function (index, elem) {
                var url_slug = $(this)
                    .text()
                    .toLowerCase()
                    .replace(/ /g, "-")
                    .replace(/[^\w-]+/g, "");
                var page_url_scroll =
                    $(visible_news_story).last().find("h1").data("href") +
                    "list/" +
                    url_slug +
                    "/";
                $(this).data("href", page_url_scroll);
                $(this).data("id", url_slug);
            }
        );
    }
});
