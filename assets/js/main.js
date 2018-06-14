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

$(document).ready(function() {

    $('.select2').select2();

    //main page

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


});
