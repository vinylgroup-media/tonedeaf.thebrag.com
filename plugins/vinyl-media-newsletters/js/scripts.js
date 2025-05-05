( function() {
    let winTop = $(window).scrollTop();
    const $news_stories = $('.single_story');
    const visible_news_story = $.grep($news_stories, function (item) {
        return $(item).position().top <= winTop + $(window).height() / 2; // + $('#header').outerHeight() - 30;
    });

    $( window ).scroll( function () {
        winTop = $( this ).scrollTop();
        if ( $( '.single' ).length ) {
            if ( $( visible_news_story ).last().find( '.observer-sub-form' ).length ) {
                const elemSubForm = $( visible_news_story )
                    .last()
                    .find( '.observer-sub-form' );

                if ( 0 < elemSubForm.closest( 'blockquote' ).length ) {
                    elemSubForm.detach();
                }
                if (
                    $( window ).scrollTop() <
                    elemSubForm.offset().top +
                    elemSubForm.outerHeight() -
                    $( window ).height() / 2 &&
                    $( window ).scrollTop() + $( window ).innerHeight() >
                    elemSubForm.offset().top + $( window ).height() / 2
                ) {
                    elemSubForm
                        .closest( '.single_story' )
                        .find( '.overlay' )
                        .first()
                        .fadeIn();
                } else {
                    elemSubForm
                        .closest( '.single_story' )
                        .find( '.overlay' )
                        .first()
                        .fadeOut();
                }
            }
            if ( $( '.single_story .overlay' ).length ) {
                $( '.single_story .overlay' ).on( 'click', function () {
                    $( this ).remove();
                } );
            }
        }
    } );

    {
        $( document ).on( 'submit', '.observer-subscribe-form', function ( e ) {
            e.preventDefault();
            const theForm = $( this );
            const elements =  $('.observer-sub-form')

            elements.find('.observer-title').addClass( 'd-none' );
            elements.find('.observer-desc').addClass( 'd-none' );
            elements.find('.img-wrap').addClass( 'd-none' );
            elements.find('.observer-sub-email').addClass( 'd-none' );
            elements.find('.btn-join').addClass( 'd-none' );
            elements.find( '.spinner' ).removeClass( 'd-none' );

            let formData = $( this ).serialize();

            $( '.js-errors-subscribe, .js-msg-subscribe' ).html( '' ).addClass( 'd-none' );

            const data = {
                action: 'subscribe_observer',
                formData: formData
            };

            $.post( tbm_load_next_post.url, data, function ( res ) {
                if ( res.success ) {
                    $('.observer-sub-form').find( '.spinner' ).addClass( 'd-none' );
                    $('.observer-sub-form')
                        .find( '.js-msg-subscribe' )
                        .html( res.data.message )
                        .removeClass( 'd-none' );
                } else {
                    $('.observer-sub-form').find( '.spinner' ).addClass( 'd-none' );
                    $('.observer-sub-form').find('.observer-sub-email').removeClass( 'd-none' );
                    $('.observer-sub-form').find('.btn-join').removeClass( 'd-none' );
                    $('.observer-sub-form')
                        .find( '.js-errors-subscribe' )
                        .html( res.data.error.message )
                        .removeClass( 'd-none' );
                }
                loadingElem.hide();
            } ).error( function () {
                $('.observer-sub-form').find( '.spinner' ).addClass( 'd-none' );
                $('.observer-sub-form').find('.observer-subscribe-form').removeClass( 'd-none' );
                $('.observer-sub-form')
                    .find( '.js-errors-subscribe' )
                    .html( 'Something went wrong, please try again later.' )
                    .removeClass( 'd-none' );
            } );
        } );
    }
}() );
