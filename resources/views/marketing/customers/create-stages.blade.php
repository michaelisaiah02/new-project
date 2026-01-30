@extends('layouts.app')
@section('title', 'ADD NEW CUSTOMER STAGES')

@section('styles')
    <style>
        /* 1. Label Style Konsisten */
        .label-box {
            min-width: 120px;
            font-weight: bold;
            background-color: var(--bs-secondary-bg);
        }

        /* 2. Table Container Responsive */
        .table-container {
            max-height: 60vh;
            /* Tinggi tabel fleksibel mengikuti layar */
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
        }

        /* 3. Sticky Header */
        thead.sticky-top th {
            z-index: 10;
            box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.2);
        }

        /* 4. Highlight Animation untuk Row Masspro */
        @keyframes highlightFade {
            0% {
                background-color: #ffe69c;
            }

            /* Kuning Terang */
            50% {
                background-color: #fff3cd;
            }

            100% {
                background-color: transparent;
            }
        }

        .highlight-row {
            animation: highlightFade 2s ease-out;
        }

        /* 5. Sticky Footer Action Bar */
        .action-bar {
            position: sticky;
            bottom: 0;
            background: white;
            border-top: 1px solid #dee2e6;
            padding: 1rem 0;
            z-index: 20;
            box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.1);
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid mt-3 pb-5">

        <form id="form-stage" action="{{ route('marketing.customers.storeStage', ['customer' => $customer->code]) }}"
            method="post">
            @csrf
            <input type="hidden" name="customer_code" value="{{ $customer->code }}">
            <input type="hidden" name="stage_number" value="{{ $stageNumber }}">

            <input type="hidden" name="decision" id="input-decision" value="">

            <div class="card shadow-sm mb-3">
                <div class="card-body py-3">
                    <div class="row g-3 align-items-center">
                        <div class="col-12 col-md-6">
                            <div class="input-group">
                                <span class="input-group-text label-box border-dark border-3">Stage
                                    {{ $stageNumber }}</span>
                                <input type="text" class="form-control border-dark border-3" id="stage_name"
                                    name="stage_name" placeholder="Stage Name (Optional)">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="input-group">
                                <span class="input-group-text label-box border-dark border-3">Customer</span>
                                <input type="text" class="form-control bg-secondary-subtle border-dark border-3"
                                    value="{{ $customer->name }}" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white py-2">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <h6 class="mb-0 fw-bold text-primary"><i class="bi bi-file-earmark-text me-2"></i>Document
                                Requirements</h6>
                        </div>
                        <div class="col"></div>
                        <div class="col-12 col-md-4">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                                <input type="text" id="doc-search" class="form-control" placeholder="Cari dokumen...">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-container">
                        <table class="table table-sm table-hover table-bordered table-striped align-middle mb-0">
                            <thead class="table-primary sticky-top text-center">
                                <tr>
                                    <th style="width: 40px;">#</th>
                                    <th>Jenis Dokumen</th>
                                    <th colspan="4">Posisi QR Code</th>
                                </tr>
                                <tr class="table-light">
                                    <th></th>
                                    <th></th>
                                    <th class="small text-muted">Atas Kiri</th>
                                    <th class="small text-muted">Atas Kanan</th>
                                    <th class="small text-muted">Bawah Kiri</th>
                                    <th class="small text-muted">Bawah Kanan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($availableDocuments as $doc)
                                    <tr class="doc-row" data-code="{{ $doc->code }}">
                                        <td class="text-center">
                                            <input type="checkbox" class="form-check-input doc-check cursor-pointer"
                                                name="document_type_codes[]" value="{{ $doc->code }}">
                                        </td>
                                        <td class="fw-bold text-start">
                                            {{ $doc->name }}
                                            @if ($doc->code == 'DM')
                                                <span class="badge bg-warning text-dark ms-1">Required</span>
                                            @endif
                                        </td>

                                        @foreach (['top_left', 'top_right', 'bottom_left', 'bottom_right'] as $pos)
                                            <td class="text-center qr-cell cursor-pointer position-relative">
                                                <input type="radio" class="form-check-input qr-option"
                                                    name="qr_position[{{ $doc->code }}]" value="{{ $pos }}"
                                                    disabled>
                                                <div class="position-absolute top-0 start-0 w-100 h-100"
                                                    style="z-index: 1;"></div>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="action-bar">
                <div class="container-fluid">
                    <div class="row justify-content-between align-items-center">
                        <div class="col-auto">
                            <a href="{{ route('marketing.customers.index') }}"
                                class="btn btn-secondary px-4 border-3 border-secondary-subtle">
                                <i class="bi bi-arrow-left me-2"></i>Back
                            </a>
                        </div>
                        <div class="col-auto d-flex gap-2">
                            <button type="button" id="btn-next" class="btn btn-outline-primary px-4 border-3 fw-bold">
                                Next Stage <i class="bi bi-chevron-right ms-1"></i>
                            </button>
                            <button type="button" id="btn-finish"
                                class="btn btn-primary px-4 border-3 border-primary-subtle fw-bold shadow-sm">
                                <i class="bi bi-check-lg me-1"></i> Finish
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </form>
    </div>

    <div class="modal fade" id="massproModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-warning text-dark border-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>Requirement Missing
                    </h5>
                </div>
                <div class="modal-body p-4 text-center">
                    <p class="fs-5 mb-1">Declaration Masspro (DM) belum dipilih.</p>
                    <p class="text-muted">Dokumen ini <strong>WAJIB</strong> ada jika Anda ingin menyelesaikan (Finish)
                        proses ini.</p>
                </div>
                <div class="modal-footer justify-content-center border-0 bg-light">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary px-4 fw-bold" id="forceMasspro">
                        Tambahkan & Lanjutkan
                    </button>
                </div>
            </div>
        </div>
    </div>
    <x-toast />
@endsection

@section('scripts')
    <script type="module">
        $(function() {
            // --- 1. SEARCH & UI LOGIC (Sama kaya sebelumnya) ---
            $('#doc-search').on('keyup', function() {
                const value = $(this).val().toLowerCase();
                $('.doc-row').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });

            $('.doc-row').on('click', function(e) {
                if ($(e.target).closest('.qr-option').length > 0) return;
                if ($(e.target).closest('.qr-cell').length > 0) return;
                const checkbox = $(this).find('.doc-check');
                checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
            });

            $('.qr-cell').on('click', function(e) {
                e.stopPropagation();
                const row = $(this).closest('tr');
                const checkbox = row.find('.doc-check');
                const radio = $(this).find('.qr-option');
                if (!checkbox.is(':checked')) checkbox.prop('checked', true).trigger('change');
                radio.prop('checked', true);
            });

            $('.doc-check').on('change', function() {
                const row = $(this).closest('tr');
                const radios = row.find('.qr-option');
                if ($(this).is(':checked')) {
                    radios.prop('disabled', false);
                    if (!radios.is(':checked')) radios.first().prop('checked', true);
                    row.addClass('table-primary');
                } else {
                    radios.prop('disabled', true).prop('checked', false);
                    row.removeClass('table-primary');
                }
            });

            // --- 2. SUBMIT LOGIC (PERBAIKAN UTAMA DISINI) ---

            // Function Submit yang Bersih
            function processSubmit(actionValue) {
                // A. Validasi Posisi QR (Harus ada radio yg kepilih utk setiap dokumen yg dicentang)
                let isValid = true;
                $('.doc-check:checked').each(function() {
                    if ($(this).closest('tr').find('.qr-option:checked').length === 0) {
                        isValid = false;
                        alert(
                            `Dokumen "${$(this).closest('tr').find('td:eq(1)').text().trim()}" belum dipilih posisi QR-nya.`);
                        return false;
                    }
                });

                if (!isValid) return;

                // B. Set Value Hidden Input
                $('#input-decision').val(actionValue);

                // C. Submit Form
                // Kita pake DOM native .submit() biar aman dari loop event listener jQuery kalo ada
                document.getElementById('form-stage').submit();
            }

            // Button Next
            $('#btn-next').on('click', function() {
                processSubmit('next');
            });

            // Button Finish
            const $massproModal = new bootstrap.Modal(document.getElementById('massproModal'));
            let pendingFinish = false;

            $('#btn-finish').on('click', function() {
                // Cek Masspro (DM)
                const $dmCheckbox = $('input.doc-check[value="DM"]');

                if ($dmCheckbox.length > 0 && !$dmCheckbox.is(':checked')) {
                    pendingFinish = true;
                    $massproModal.show();

                    // Highlight Row Logic
                    const row = $dmCheckbox.closest('tr')[0];
                    row.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    $(row).addClass('highlight-row');
                    setTimeout(() => $(row).removeClass('highlight-row'), 2000);

                    return;
                }

                processSubmit('finish');
            });

            // Handle Modal Force Lanjutkan
            $('#forceMasspro').on('click', function() {
                const $dmCheckbox = $('input.doc-check[value="DM"]');
                $dmCheckbox.prop('checked', true).trigger('change'); // Centang otomatis
                $massproModal.hide();

                if (pendingFinish) {
                    processSubmit('finish');
                }
            });
        });
    </script>
@endsection
