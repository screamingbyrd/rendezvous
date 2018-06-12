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

    if( $('.featured-offer').visible(true)){
        $('.featured-offer').addClass('fadeInRight');
    }
    if( $('.featured-employer').visible(true)){
        $('.featured-employer').addClass('fadeInLeft');
    }

    if( $('.jobnow-description').visible(true)){
        $('.jobnow-description').addClass('fadeInRight');
    }

    if( $('.about-the-offer').visible(true)){
        $('.about-the-offer').addClass('fadeInRight');
    }
    if( $('.job-description').visible(true)){
        $('.job-description').addClass('fadeInLeft');
    }

    $(window).scroll(function() {
        if( $('.featured-offer').visible(true)){
            $('.featured-offer').addClass('fadeInRight');
        }
        if( $('.featured-employer').visible(true)){
            $('.featured-employer').addClass('fadeInLeft');
        }
        if( $('.jobnow-description').visible(true)){
            $('.jobnow-description').addClass('fadeInRight');
        }
        if( $('.about-the-offer').visible(true)){
            $('.about-the-offer').addClass('fadeInRight');
        }
        if( $('.job-description').visible(true)){
            $('.job-description').addClass('fadeInLeft');
        }
    });


});
