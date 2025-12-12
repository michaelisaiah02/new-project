@extends('layouts.app')
@section('title', 'ADD NEW CUSTOMER STAGES')
@section('content')
    <div class="container-fluid">
        <form action="{{ route('marketing.customers.storeStage', ['customer' => $customer->code]) }}" method="post">
            @csrf
            <input type="hidden" name="customer_code" value="{{ $customer->code }}">
            <input type="hidden" name="stage_number" value="{{ $stageNumber }}">
            <div class="row mb-3 justify-content-between align-items-center">
                <label for="stage_number" class="col-md-auto col-form-label">Select Document Requirement for Stage
                    {{ $stageNumber }}</label>
                <div class="col-md-3 me-auto">
                    <input type="text" class="form-control form-control-sm bg-secondary-subtle border-3 border-dark"
                        id="stage_name" name="stage_name">
                </div>
                <label for="stage_name" class="col-md-auto col-form-label">Customer Name</label>
                <div class="col-md-3">
                    <input type="text" class="form-control form-control-sm bg-secondary-subtle border-3 border-dark"
                        id="stage_name" value="{{ $customer->name }}" readonly>
                    <div class="invalid-feedback">Stage Name is required.</div>
                </div>
            </div>
            <div class="row mb-3">
                <div class="table-responsive overflow-y-auto" style="max-height: 400px;">
                    <table class="table table-sm table-bordered table-striped-columns m-0 text-center">
                        <thead class="table-primary sticky-top align-middle">
                            <tr>
                                <th rowspan="2"></th>
                                <th rowspan="2">Jenis Dokumen</th>
                                <th colspan="4">Posisi QR Code</th>
                            </tr>
                            <tr>
                                <th>Atas Kiri</th>
                                <th>Atas Kanan</th>
                                <th>Bawah Kiri</th>
                                <th>Bawah Kanan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($availableDocuments as $doc)
                                <tr class="text-center align-middle doc-row">
                                    <td>
                                        <input type="checkbox" class="doc-check" name="document_type_codes[]"
                                            value="{{ $doc->code }}">
                                    </td>
                                    <td class="text-start">{{ $doc->name }}</td>
                                    @foreach (['top_left', 'top_right', 'bottom_left', 'bottom_right'] as $pos)
                                        <td class="qr-cell">
                                            <input type="radio" class="qr-option" name="qr_position[{{ $doc->code }}]"
                                                value="{{ $pos }}">
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div
                class="text-center row justify-content-between align-items-start position-absolute bottom-0 start-0 end-0 mb-3 mx-3">
                <div class="col-auto">
                    <a href="{{ route('marketing.customers.index') }}"
                        class="btn btn-primary border-3 border-light-subtle">Back</a>
                </div>
                <div class="col-auto ms-auto">
                    <button type="submit" name="decision" value="next"
                        class="btn btn-primary border-3 border-light-subtle">
                        Next Stage
                    </button>
                </div>
                <div class="col-auto">
                    <button type="submit" name="decision" value="finish"
                        class="btn btn-primary border-3 border-light-subtle">
                        Finish
                    </button>
                </div>
            </div>
        </form>
    </div>
    <x-toast />
@endsection
@section('scripts')
    <script type="module">
        $(function() {
            // CLICK ON ROW (toggle checkbox)
            $('.doc-row').on('click', function(e) {

                // kalau klik radio -> skip
                if ($(e.target).is('.qr-option')) return;

                // kalau klik QR cell -> skip (biar handler QR-cell yang jalan)
                if ($(e.target).closest('.qr-cell').length) return;

                // kalau klik checkbox -> skip (default behavior)
                if ($(e.target).is('.doc-check')) return;

                // toggle checkbox
                const $cb = $(this).find('.doc-check');
                $cb.prop('checked', !$cb.prop('checked')).trigger('change');
            });

            // CLICK QR CELL → AUTO CHECK RADIO
            $('.qr-cell').on('click', function(e) {
                e.stopPropagation(); // biar klik cell gak nyentuh row-click

                const $radio = $(this).find('.qr-option');

                // enable row first (if needed)
                const $row = $(this).closest('tr');
                const $cb = $row.find('.doc-check');

                // kalau checkbox belum checked → checklist dulu
                if (!$cb.prop('checked')) {
                    $cb.prop('checked', true).trigger('change');
                }

                // sekarang check radio-nya
                $radio.prop('checked', true).trigger('change');
            });


            // CHECKBOX CHANGE HANDLER
            $('.doc-check').on('change', function() {
                let $row = $(this).closest('tr');
                let $radios = $row.find('.qr-option');

                if (this.checked) {
                    $radios.prop('disabled', false);

                    // kalau belum ada radio yg dipilih → pilih yg pertama
                    if (!$radios.is(':checked')) {
                        $radios.first().prop('checked', true);
                    }

                } else {
                    $radios.prop('checked', false).prop('disabled', true);
                }
            });

            // INIT LOAD
            $('.doc-check').trigger('change');
        });

        $(document).ready(function() {
            // disable semua qr options saat awal
            if ($('.doc-check').is(':not(:checked)')) {
                $('.qr-option').prop('disabled', true);
                $('.qr-option').prop('checked', false);
            }

            // ketika checkbox dokumen di klik
            $('.doc-check').on('change', function() {
                let row = $(this).closest('tr');

                if ($(this).is(':checked')) {
                    // enable radio positions
                    row.find('.qr-option').prop('disabled', false);
                    row.find('.qr-option').first().prop('checked', true);
                } else {
                    // uncheck radio + disable
                    row.find('.qr-option').prop('checked', false).prop('disabled', true);
                }
            });

            // handle submit
            $('form').on('submit', function(e) {
                let valid = true;
                let message = '';

                $('.doc-check:checked').each(function() {
                    let row = $(this).closest('tr');
                    let hasQR = row.find('.qr-option:checked').length > 0;

                    if (!hasQR) {
                        valid = false;
                        message = "Setiap dokumen yang dipilih harus punya posisi QR ya 😅";
                    }
                });

                if (!valid) {
                    e.preventDefault();
                    alert(message);
                }
            });

        });
    </script>
@endsection
