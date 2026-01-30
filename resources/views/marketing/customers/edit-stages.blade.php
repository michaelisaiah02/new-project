@extends('layouts.app')
@section('title', 'EDIT CUSTOMER STAGE')

@section('styles')
    <style>
        /* 1. Styling Konsisten */
        .label-box {
            min-width: 120px;
            font-weight: bold;
            background-color: var(--bs-secondary-bg);
        }

        .table-container {
            max-height: 60vh;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
        }

        thead.sticky-top th {
            z-index: 10;
            box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.2);
        }

        /* 2. Highlight Row Animation */
        @keyframes highlightFade {
            0% {
                background-color: #ffe69c;
            }

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

        /* 3. Sticky Action Bar */
        .action-bar {
            position: sticky;
            bottom: 0;
            background: white;
            border-top: 1px solid #dee2e6;
            padding: 1rem 0;
            z-index: 20;
            box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        /* 4. Stage Navigation Buttons */
        .btn-navigate.active {
            font-weight: bold;
            border: 2px solid white !important;
            outline: 2px solid var(--bs-primary);
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid mt-3 pb-5">

        <form id="form-edit-stage"
            action="{{ route('marketing.customers.saveStage', ['customer' => $customer->code, 'stageNumber' => $stageNumber]) }}"
            method="post">
            @csrf
            @method('PUT')

            <input type="hidden" name="customer_code" value="{{ $customer->code }}">
            <input type="hidden" name="stage_number" value="{{ $stageNumber }}">

            <input type="hidden" name="form_action" id="input-form-action" value="save">
            <input type="hidden" name="target_stage" id="input-target-stage" value="">

            <div class="card shadow-sm mb-3">
                <div class="card-body py-3">
                    <div class="row g-3 align-items-center">
                        <div class="col-12 col-md-6">
                            <div class="input-group">
                                <span class="input-group-text label-box border-dark border-3">Stage
                                    {{ $stageNumber }}</span>
                                <input type="text" class="form-control border-dark border-3" id="stage_name"
                                    name="stage_name" value="{{ $stage->stage_name }}" placeholder="Stage Name">
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
                            <h6 class="mb-0 fw-bold text-primary"><i class="bi bi-pencil-square me-2"></i>Edit Requirement
                            </h6>
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
                                    @php
                                        // Cek apakah dokumen ini sudah ada di stage ini (Existing Data)
                                        // $currentDocs harusnya Collection code dokumen yg udah kepilih
                                        $isChecked = $currentDocs->contains($doc->code);

                                        // Ambil posisi QR yang tersimpan (kalo ada)
                                        // Asumsi $stage->documents ada pivot qr_position
                                        $savedPosition = null;
                                        $pivotDoc = $stage->documents->firstWhere('code', $doc->code);
                                        if ($pivotDoc) {
                                            $savedPosition = $pivotDoc->pivot->qr_position;
                                        }
                                    @endphp

                                    <tr class="doc-row {{ $isChecked ? 'table-primary' : '' }}"
                                        data-code="{{ $doc->code }}">
                                        <td class="text-center">
                                            <input type="checkbox" class="form-check-input doc-check cursor-pointer"
                                                name="document_type_codes[]" value="{{ $doc->code }}"
                                                {{ $isChecked ? 'checked' : '' }}>
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
                                                    {{ $isChecked ? '' : 'disabled' }}
                                                    {{ $savedPosition == $pos ? 'checked' : '' }}>
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
                    <div class="row align-items-center gy-2">

                        <div class="col-auto d-flex gap-2">
                            <a href="{{ route('marketing.customers.index') }}"
                                class="btn btn-secondary px-3 border-3 border-secondary-subtle">
                                <i class="bi bi-arrow-left"></i>
                            </a>
                            <button type="button" class="btn btn-danger border-3 border-danger-subtle"
                                id="btn-delete-stage">
                                <i class="bi bi-trash"></i> Delete Stage
                            </button>
                        </div>

                        <div class="col d-flex justify-content-center">
                            <div class="btn-group shadow-sm" role="group">
                                @for ($i = 1; $i <= $maxStage; $i++)
                                    <button type="button"
                                        class="btn btn-primary btn-navigate {{ $i == $stageNumber ? 'active' : '' }}"
                                        data-target="{{ $i }}">
                                        {{ $i }}
                                    </button>
                                @endfor

                                @if ($canAddStage)
                                    <button type="button" class="btn btn-success btn-add-stage" title="Add New Stage">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                @endif
                            </div>
                        </div>

                        <div class="col-auto d-flex gap-2">
                            <button type="button" id="btn-save"
                                class="btn btn-primary px-4 border-3 border-primary-subtle fw-bold">
                                Save
                            </button>
                            <button type="button" id="btn-finish"
                                class="btn btn-dark px-4 border-3 border-dark-subtle fw-bold shadow-sm">
                                <i class="bi bi-check2-all me-1"></i> Finish
                            </button>
                        </div>

                    </div>
                </div>
            </div>

        </form>

        <form id="form-delete-stage"
            action="{{ route('marketing.customers.destroyStage', ['customer' => $customer->code, 'stageNumber' => $stageNumber]) }}"
            method="POST" style="display: none;">
            @csrf
            @method('DELETE')
        </form>

    </div>

    <div class="modal fade" id="deleteStageModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white border-0">
                    <h5 class="modal-title fw-bold">Delete Stage {{ $stageNumber }}?</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-4">
                    <p class="mb-0">Are you sure you want to delete this stage? <br> All document requirements in this
                        stage will be removed.</p>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger px-4" id="confirm-delete">Delete Permanently</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="massproModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-warning text-dark border-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>Requirement
                        Missing</h5>
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
            // --- 1. UI INTERACTION (Sama kayak Add Stage) ---

            // Search
            $('#doc-search').on('keyup', function() {
                const value = $(this).val().toLowerCase();
                $('.doc-row').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });

            // Row Click
            $('.doc-row').on('click', function(e) {
                if ($(e.target).closest('.qr-option').length > 0) return;
                if ($(e.target).closest('.qr-cell').length > 0) return;
                const checkbox = $(this).find('.doc-check');
                checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
            });

            // QR Cell Click
            $('.qr-cell').on('click', function(e) {
                e.stopPropagation();
                const row = $(this).closest('tr');
                const checkbox = row.find('.doc-check');
                const radio = $(this).find('.qr-option');
                if (!checkbox.is(':checked')) checkbox.prop('checked', true).trigger('change');
                radio.prop('checked', true);
            });

            // Checkbox Change
            $('.doc-check').on('change', function() {
                const row = $(this).closest('tr');
                const radios = row.find('.qr-option');
                if ($(this).is(':checked')) {
                    radios.prop('disabled', false);
                    // Kalau belum ada radio yg checked, check yg pertama
                    if (!radios.is(':checked')) radios.first().prop('checked', true);
                    row.addClass('table-primary');
                } else {
                    radios.prop('disabled', true).prop('checked', false);
                    row.removeClass('table-primary');
                }
            });

            // --- 2. SUBMIT LOGIC (Refactored for Edit) ---

            // Shared Submit Function
            function processSubmit(actionType, targetStage = null) {
                // A. Validasi Posisi QR (Hanya jika action bukan delete)
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

                // B. Set Hidden Inputs
                $('#input-form-action').val(actionType);
                if (targetStage) {
                    $('#input-target-stage').val(targetStage);
                }

                // C. Submit Form
                document.getElementById('form-edit-stage').submit();
            }

            // Button Save
            $('#btn-save').on('click', function() {
                processSubmit('save');
            });

            // Button Finish (With Masspro Check)
            const $massproModal = new bootstrap.Modal(document.getElementById('massproModal'));
            let pendingFinish = false;

            $('#btn-finish').on('click', function() {
                const $dmCheckbox = $('input.doc-check[value="DM"]');

                // Cek DM wajib checked
                if ($dmCheckbox.length > 0 && !$dmCheckbox.is(':checked')) {
                    pendingFinish = true;
                    $massproModal.show();

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

            // Force Masspro Modal
            $('#forceMasspro').on('click', function() {
                const $dmCheckbox = $('input.doc-check[value="DM"]');
                $dmCheckbox.prop('checked', true).trigger('change');
                $massproModal.hide();
                if (pendingFinish) processSubmit('finish');
            });

            // --- 3. NAVIGATION LOGIC (Pindah Stage) ---
            // Saat pindah stage, kita "Save" dulu stage yg sekarang, baru redirect.
            $('.btn-navigate').on('click', function() {
                const target = $(this).data('target');
                // Kirim action 'navigate' dan target stage ke backend
                processSubmit('navigate', target);
            });

            // Add Stage Button (Save -> Redirect to Add Page)
            $('.btn-add-stage').on('click', function() {
                processSubmit('add_stage');
            });

            // --- 4. DELETE STAGE LOGIC ---
            const $deleteModal = new bootstrap.Modal(document.getElementById('deleteStageModal'));

            $('#btn-delete-stage').on('click', function() {
                $deleteModal.show();
            });

            $('#confirm-delete').on('click', function() {
                // Submit form delete yang terpisah (Method DELETE)
                document.getElementById('form-delete-stage').submit();
            });
        });
    </script>
@endsection
