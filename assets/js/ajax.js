/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import '../css/app.scss';

// Need jQuery? Install it with "yarn add jquery", then uncomment to import it.
// import $ from 'jquery';
const jquery = require("jquery");
const $ = require('jquery');
const jQuery = require("jquery");
window.jQuery = $;

// this "modifies" the jquery module: adding behavior to it
// the bootstrap module doesn't export/return anything
require('bootstrap/dist/js/bootstrap.js');
require('@fortawesome/fontawesome-free/js/all.min.js');
require('@fancyapps/fancybox');


$(document).ready(function() {
    let currentPage = 1;
    $.ajax({
        url: '/' + document.documentElement.lang+'/posts/' + currentPage,
        type: 'GET',
        dataType: 'json',
        async: true,
        success: function(data, status) {
            console.log(data);
        },
        error : function ( xhr , textStatus , errorThrown ) {
            alert ( 'Ajax request failed.' );
        }
    });
});

