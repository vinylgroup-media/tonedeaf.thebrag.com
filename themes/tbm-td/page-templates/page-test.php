<?php
/*
 * Template Name: Test
 */
get_header();
?>

<div class="my-2 text-center ad-mrec" id="ad-incontent-1">
    <script>
        (function(w, d) {
            try {
                d = w.top.document || d;
                w = w.top.document ? w.top : w;
            } catch (e) {}
            var ttag = function() {
                w.teads.page(94115).placement(117427, {
                    slider: {
                        allow_corner_position: false,
                        allow_top_position: false
                    },
                    "css": "margin: 0auto; max-width: 550px;",
                    "format": "inread",
                    "slot": {
                        "selector": "#ad-incontent-1",
                        "minimum": 1
                    }
                }).passback(function passback() {
                    (function(d) {
                        var js = d.createElement("script"),
                            s = _tt_slot[minSlot - 1];
                        js.innerHTML = "var unruly = window.unruly || {}; unruly.native = unruly.native || {}; unruly.native.siteId = 1082790; unruly.native.onFallback = function adFallback() {        return '';    };";
                        s.parentNode.insertBefore(js, s);
                        js = d.createElement("script");
                        js.src = "//video.unrulymedia.com/native/native-loader.js";
                        s.parentNode.insertBefore(js, s);
                    })(window.top.document);
                }).serve();
            };
            if (w.teads && w.teads.page) {
                ttag();
            } else if (!w.teadsscript) {
                var s = document.createElement('script');
                s.src = 'https://s8t.teads.tv/media/format/v3/teads-format.min.js';
                s.async = true;
                s.onload = ttag;
                w.teadsscript = d.getElementsByTagName('head')[0].appendChild(s);
            } else {
                w.teadsscript.addEventListener('load', ttag);
            }
        })(window, document);
    </script>
</div>
<?php
get_footer();
