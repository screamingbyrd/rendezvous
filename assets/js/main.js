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

    if( $('.fade-in-right').visible(true)){
        $('.fade-in-right').addClass('fadeInRight');
    }
    if( $('.fade-in-left').visible(true)){
        $('.fade-in-left').addClass('fadeInLeft');
    }

    if( $('.jobnow-description').visible(true)){
        $('.jobnow-description').addClass('fadeInRight');
    }

    $(window).scroll(function() {
        if( $('.fade-in-right').visible(true)){
            $('.fade-in-right').addClass('fadeInRight');
        }
        if( $('.fade-in-left').visible(true)){
            $('.fade-in-left').addClass('fadeInLeft');
        }
        if( $('.jobnow-description').visible(true)){
            $('.jobnow-description').addClass('fadeInRight');
        }
    });


});
