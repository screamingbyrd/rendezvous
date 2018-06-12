// assets/js/app.js

require('../css/main.css');
require('../css/employer.css');
require('../css/offer.css');

// require jQuery normally
const $ = require('jquery');

// create global $ and jQuery variables
global.$ = global.jQuery = $;

$(document).ready(function() {

    console.log('noob');
    $('.select2').select2();

});