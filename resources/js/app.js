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

        // 1. Cek Validasi HTML5 Native
        if (!this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();

            // INI YANG KURANG TADI:
            // Paksa form buat nampilin style validasi (border merah & pesan error)
            $form.addClass('was-validated');

            return; // Stop di sini, jangan lanjut ke logic "Processing"
        }

        // 2. Cek Double Submit
        if ($form.data('is-submitting')) {
            e.preventDefault();
            return;
        }

        // 3. Kalau Valid, Lanjut Processing
        $form.data('is-submitting', true);
        $btn.prop('disabled', true);
        $btn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');

        // Biarkan form submit
        return true;
    });
});
