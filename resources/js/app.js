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

$(function () {
    $('form').on('submit', function (e) {
        let $form = $(this);
        let $btn = $form.find('button[type="submit"]');

        // Cek kalau udah pernah disubmit biar nggak double post
        if ($form.data('is-submitting')) {
            e.preventDefault();
            return;
        }

        // Tandai form lagi proses
        $form.data('is-submitting', true);

        // Ubah tampilan tombol
        $btn.prop('disabled', true); // Pake .prop lebih aman daripada .attr
        $btn.html('Processing...');

        // Biarkan form submit secara natural
        return true;
    });
});
