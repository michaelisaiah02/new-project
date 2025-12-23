import './bootstrap';
import jQuery from 'jquery';
window.$ = jQuery;
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;
// import * as datepicker from 'bootstrap-datepicker';
// window.datepicker = datepicker;
// import flatpickr from "flatpickr";
// window.flatpickr = flatpickr;
import selectize from '@selectize/selectize';
window.selectize = selectize;
import Chart from 'chart.js/auto';
window.Chart = Chart;

$(document).ready(function () {
    $('button[type=submit]').on('click', function () {
        $(this).attr('disabled', 'disabled');
        $(this).text('Processing...');
        $(this).closest('form').trigger('submit');
    });
});
