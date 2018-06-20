// assets/js/app.js

require('../css/main.css');
require('../css/employer.css');
require('../css/offer.css');

// require jQuery normally
const $ = require('jquery');

// create global $ and jQuery variables
global.$ = global.jQuery = $;

$.fn.visible = function(partial) {

    let $t            = $(this),
        $w            = $(window),
        viewTop       = $w.scrollTop(),
        viewBottom    = viewTop + $w.height(),
        _top          = $t.offset().top,
        _bottom       = _top + $t.height(),
        compareTop    = partial === true ? _bottom : _top,
        compareBottom = partial === true ? _top : _bottom;

    return ((compareBottom <= viewBottom) && (compareTop >= viewTop));

};

function detectmob() {
    if( navigator.userAgent.match(/Android/i)
        || navigator.userAgent.match(/webOS/i)
        || navigator.userAgent.match(/iPhone/i)
        || navigator.userAgent.match(/iPad/i)
        || navigator.userAgent.match(/iPod/i)
        || navigator.userAgent.match(/BlackBerry/i)
        || navigator.userAgent.match(/Windows Phone/i)
    ){
        return true;
    }
    else {
        return false;
    }
}

$(document).ready(function() {

    $('.select2').select2({
        width: '100%',
        theme: 'bootstrap'

    });

    //main page

    if(detectmob() == false ){
        $( '.fade-in-right' ).each(function(  ) {
            if( $(this).visible(true)){
                $(this).addClass('fadeInRight');
            }
        });
        $( '.fade-in-left' ).each(function(  ) {
            if( $(this).visible(true)){
                $(this).addClass('fadeInLeft');
            }
        });


        $(window).scroll(function() {
            $( '.fade-in-right' ).each(function(  ) {
                if( $(this).visible(true)){
                    $(this).addClass('fadeInRight');
                }
            });
            $( '.fade-in-left' ).each(function(  ) {
                if( $(this).visible(true)){
                    $(this).addClass('fadeInLeft');
                }
            });
        });
    }



    if(detectmob()){
        $('.navbar-fixed-bottom').removeClass('navbar-fixed-bottom').addClass('navbar-static-bottom');
    }


});
