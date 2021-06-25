// app.js

// this "modifies" the jquery module: adding behavior to it
// the bootstrap module doesn't export/return anything
import './styles/app.scss';

const jquery = require("jquery");
const $ = require('jquery');
const jQuery = require("jquery");
window.jQuery = $;

require('bootstrap');
require('@fancyapps/fancybox');

// or you can include specific pieces
// require('bootstrap/js/dist/tooltip');
// require('bootstrap/js/dist/popover');
