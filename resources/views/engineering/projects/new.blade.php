@extends('layouts.app-projects')
@section('title', 'NEW PROJECT')
@section('customer', $project->customer->name)

@section('styles')
    <style>
        /* 1. Label */
        .label-box {
            min-width: 100px;
            font-weight: bold;
            background-color: var(--bs-secondary-bg);
        }

        /* 2. Doc Item Styling */
        .doc-item {
            position: relative;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #dee2e6;
            border-right: 1px solid #dee2e6;
            background-color: #fff;
            transition: background-color 0.1s ease;
            cursor: pointer;
            height: 100%;
            display: flex;
            align-items: flex-start;
        }

        .doc-item:hover {
            background-color: #f8f9fa;
        }

        .doc-item.checked {
            background-color: var(--bs-primary-bg-subtle);
        }

        .form-check-input {
            transform: scale(1.2);
            margin-top: 0.3rem;
            flex-shrink: 0;
        }

        .doc-label {
            margin-left: 0.75rem;
            font-weight: 500;
            color: #495057;
            cursor: pointer;
            user-select: none;
            line-height: 1.4;
        }

        /* 3. Sticky Header Section */
        /* Header "Select Documents" nempel di atas pas scroll */
        .sticky-section-header {
            position: sticky;
            top: 0;
            z-index: 10;
            background-color: #f8f9fa;
            /* Wajib ada background biar ga transparan */
            border-bottom: 1px solid #dee2e6;
            padding-top: 1rem;
            padding-bottom: 1rem;
        }

        /* 4. Scrollable Container */
        /* Area stage bakal punya scroll sendiri */
        .stages-scroll-area {
            height: calc(100vh - 400px);
            /* Kalkulasi biar pas sisa layar */
            min-height: 400px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            background-color: #fff;
            border-radius: 0.375rem;
        }

        /* 5. Footer Action Bar */
        .action-bar {
            background: white;
            border-top: 1px solid #dee2e6;
            padding: .8rem 0;
            z-index: 20;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid mt-3 pb-0">

        <div class="card shadow-sm">
            <div class="card-body py-1">
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text label-box border-dark border-2">Model</span>
                            <input type="text" class="form-control border-dark border-2 bg-light fw-bold"
                                value="{{ $project->model }}" readonly>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text label-box border-dark border-2">Part No</span>
                            <input type="text" class="form-control border-dark border-2 bg-light fw-bold"
                                value="{{ $project->part_number }}" readonly>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text label-box border-dark border-2">Part Name</span>
                            <input type="text" class="form-control border-dark border-2 bg-light fw-bold"
                                value="{{ $project->part_name }}" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <form id="new-project-form" action="{{ route('engineering.projects.saveNew', ['project' => $project->id]) }}"
            method="post">
            @csrf

            <div class="card border-0 shadow-sm">
                <div class="card-header sticky-section-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary text-white rounded p-2 me-2 shadow-sm">
                                <i class="bi bi-list-check fs-4"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0">Select Documents</h5>
                                <small class="text-muted d-none d-md-block">Centang dokumen yang dibutuhkan.</small>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm fw-bold shadow-sm"
                            data-bs-toggle="modal" data-bs-target="#showProjectModal">
                            <i class="bi bi-info-circle me-1"></i> View Details
                        </button>
                    </div>
                </div>

                <div class="card-body p-0 stages-scroll-area">
                    <div class="d-flex flex-column">
                        @foreach ($stages as $stage)
                            <div class="stage-section border-bottom">
                                <div class="bg-light py-2 px-3 d-flex justify-content-between align-items-center sticky-top"
                                    style="top: 0; z-index: 5;">
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-dark me-2 rounded-pill px-3">Stage
                                            {{ $stage->stage_number }}</span>
                                        <h6 class="mb-0 fw-bold text-uppercase text-dark small">{{ $stage->stage_name }}
                                        </h6>
                                    </div>
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input select-all-stage cursor-pointer" type="checkbox"
                                            role="switch" id="selectAll{{ $stage->id }}"
                                            data-stage-id="{{ $stage->id }}">
                                        <label class="form-check-label small fw-bold text-muted cursor-pointer"
                                            for="selectAll{{ $stage->id }}">Select All</label>
                                    </div>
                                </div>

                                <div class="row g-0 row-cols-1 row-cols-md-2 row-cols-xl-3 border-start">
                                    @foreach ($stage->documents as $doc)
                                        @php
                                            $stageSelections = $selectedDocs[$stage->id] ?? [];
                                            // Default: Checked kalau kosong (awal) atau emang ada di array
                                            // TAPI HATI-HATI: Kalau user pernah uncheck semua, array kosong.
                                            // Logic controller lo: empty($stageSelections) ? true : in_array.
                                            // Asumsi: Halaman ini pertama kali load selection kosong -> true.
                                            $isChecked = !empty($stageSelections)
                                                ? in_array($doc->code, $stageSelections)
                                                : true;
                                            $inputId = "check_{$stage->id}_{$doc->code}";
                                        @endphp

                                        <div class="col">
                                            <div class="doc-item {{ $isChecked ? 'checked' : '' }}"
                                                onclick="toggleCheckbox('{{ $inputId }}')">
                                                <input
                                                    class="form-check-input stage-checkbox-{{ $stage->id }} doc-checkbox"
                                                    type="checkbox" value="{{ $doc->code }}" id="{{ $inputId }}"
                                                    name="documents_codes[{{ $stage->stage_number }}][]"
                                                    {{ $isChecked ? 'checked' : '' }}>

                                                <label class="doc-label stretched-link" for="{{ $inputId }}">
                                                    {{ $doc->name }}
                                                    @if ($doc->code == 'DM')
                                                        <span class="badge bg-warning text-dark ms-1"
                                                            style="font-size: 0.6rem;">REQUIRED</span>
                                                    @endif
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="action-bar mt-auto">
                <div class="container-fluid px-0">
                    <div class="row justify-content-between align-items-center">
                        <div class="col-auto">
                            <a href="{{ route('engineering') }}"
                                class="btn btn-secondary px-4 fw-bold border-3 border-secondary-subtle">
                                <i class="bi bi-arrow-left me-1"></i> Back
                            </a>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-primary px-4 fw-bold border-3 border-primary-subtle shadow-sm"
                                type="submit">
                                Assign Due Dates <i class="bi bi-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </form>
    </div>

    @include('engineering.projects.partials.data-project-modal', ['project' => $project])
    <x-toast />
@endsection

@section('scripts')
    <script type="module">
        // Helper global untuk onclick div
        window.toggleCheckbox = function(id) {
            // Default behavior label handles click, script ini cuma placeholder
            // kalau mau logic custom click area selain label
        }

        $(document).ready(function() {
            // 1. Logic DM Disabled
            const $dmCheckbox = $('input[type="checkbox"][value="DM"]');
            if ($dmCheckbox.length) {
                $dmCheckbox.prop('checked', true).prop('disabled', true);
                $dmCheckbox.closest('.doc-item').addClass('bg-secondary-subtle opacity-75');
            }

            // 2. FUNCTION: Sync Select All State
            // Fungsi ini dipanggil pas Load dan pas Change Checkbox
            function syncSelectAllState(stageId) {
                const $stageCheckboxes = $(`.stage-checkbox-${stageId}`).not(
                    ':disabled'); // Abaikan DM yang disabled
                const total = $stageCheckboxes.length;
                const checked = $stageCheckboxes.filter(':checked').length;
                const $selectAllBtn = $(`#selectAll${stageId}`);

                // Kalau total checkbox > 0 dan semuanya checked -> Nyalain Select All
                if (total > 0 && total === checked) {
                    $selectAllBtn.prop('checked', true);
                } else {
                    $selectAllBtn.prop('checked', false);
                }
            }

            // 3. INIT: Loop tiap stage buat set Select All pas awal load
            $('.select-all-stage').each(function() {
                const stageId = $(this).data('stage-id');
                syncSelectAllState(stageId);
            });

            // 4. EVENT: Select All Toggle Click
            $('.select-all-stage').on('change', function() {
                const stageId = $(this).data('stage-id');
                const isChecked = $(this).is(':checked');
                const $checkboxes = $(`.stage-checkbox-${stageId}`).not(':disabled');

                $checkboxes.prop('checked', isChecked).trigger(
                    'change'); // Trigger change biar visual row keupdate
            });

            // 5. EVENT: Individual Checkbox Click
            $(document).on('change', '.doc-checkbox', function() {
                const $item = $(this).closest('.doc-item');
                const stageId = $(this).attr('class').match(/stage-checkbox-(\d+)/)[
                    1]; // Ambil ID stage dari class

                // Visual Update
                if (this.checked) {
                    $item.addClass('checked');
                } else {
                    $item.removeClass('checked');
                }

                // Sync Select All Toggle
                syncSelectAllState(stageId);
            });

            // 6. Submit Handler
            $('#new-project-form').on('submit', function() {
                // Enable DM biar ke-submit
                if ($dmCheckbox.length) {
                    $dmCheckbox.prop('disabled', false);
                }
                const $btn = $(this).find('button[type="submit"]');
                $btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-2"></span>Processing...');
            });
        });
    </script>
@endsection
