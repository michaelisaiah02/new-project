@extends('layouts.app-projects')
@section('title', 'NEW PROJECT')
@section('customer', $project->customer->name)

@section('styles')
    <style>
        /* 1. Label Box */
        .label-box {
            min-width: 100px;
            font-weight: bold;
            background-color: var(--bs-secondary-bg);
        }

        /* 2. TABLE LEPAS KANDANG (Full Page Scroll) */
        /* Kita hapus max-height dan overflow biar dia pake scroll browser bawaan */
        .table-container {
            max-height: none !important;
            overflow: visible !important;
            border: none;
            background: transparent;
        }

        /* 3. STICKY TABLE HEADER */
        /* Ini kuncinya: Header tabel nempel di atas layar (top: 0) */
        /* z-index harus lebih kecil dari Navbar (kalau navbar sticky) tapi lebih gede dari konten */
        thead.sticky-top-window th {
            position: sticky;
            top: 0;
            z-index: 1020;
            color: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            /* Bayangan biar berasa 'melayang' */
            border-bottom: 0;
        }

        /* 4. Stage Cell */
        .stage-cell {
            background-color: #f8f9fa;
            vertical-align: middle;
            border-right: 2px solid #dee2e6;
        }

        /* 5. Sticky Footer (Status + Tombol) */
        .action-bar {
            position: sticky;
            bottom: 0;
            background: white;
            border-top: 1px solid #dee2e6;
            padding: 0.75rem 0;
            z-index: 1030;
            /* Paling atas */
            box-shadow: 0 -4px 10px rgba(0, 0, 0, 0.05);
        }

        /* 6. Status Steps Compact */
        /* Dikecilin dikit paddingnya biar footer ga kegedean */
        .status-step {
            flex: 1;
            text-align: center;
            padding: 0.25rem;
            border-radius: 4px;
            background-color: #fff;
            border: 1px solid #dee2e6;
            font-size: 0.7rem;
        }

        .status-step.active {
            background-color: #e8f5e9;
            border-color: #a5d6a7;
            color: #1b5e20;
        }

        .step-label {
            font-weight: bold;
            display: block;
            margin-bottom: 0;
            font-size: 0.75rem;
        }

        .step-info {
            font-size: 0.65rem;
            color: #6c757d;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid mt-3">

        <div class="card shadow-sm mb-4 border-0 bg-secondary-subtle">
            <div class="card-body py-3">
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text label-box border-dark border-2">Model</span>
                            <input type="text" class="form-control border-dark border-2 bg-white fw-bold"
                                value="{{ $project->model }}" readonly>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text label-box border-dark border-2">Part No</span>
                            <input type="text" class="form-control border-dark border-2 bg-white fw-bold"
                                value="{{ $project->part_number }}" readonly>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text label-box border-dark border-2">Part Name</span>
                            <input type="text" class="form-control border-dark border-2 bg-white fw-bold"
                                value="{{ $project->part_name }}" readonly>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top border-dark-subtle">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-calendar-week me-2"></i>Assign Due Dates</h5>
                        <small class="text-muted">Scroll ke bawah untuk mengisi tanggal.</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-dark fw-bold shadow-sm" data-bs-toggle="modal"
                        data-bs-target="#showProjectModal">
                        <i class="bi bi-eye"></i> Full Details
                    </button>
                </div>
            </div>
        </div>

        <form action="{{ route('engineering.projects.updateDueDates', ['project' => $project->id]) }}" method="post"
            id="due-date-form">
            @csrf

            <div class="table-container mb-0">
                <table class="table table-hover table-bordered mb-0 align-middle shadow-sm bg-white">
                    <thead class="sticky-top-window text-center table-primary">
                        <tr>
                            <th style="width: 100px;" class="py-3">Stage</th>
                            <th class="py-3">Document Requirement</th>
                            <th style="width: 200px;" class="py-3">Target Due Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($projectDocuments as $stageId => $docs)
                            @foreach ($docs as $index => $pd)
                                <tr>
                                    @if ($index === 0)
                                        <td rowspan="{{ $docs->count() }}" class="text-center stage-cell">
                                            <div class="small text-muted text-uppercase fw-bold"
                                                style="font-size: 0.65rem;">Stage</div>
                                            <div class="fs-3 text-primary fw-bold lh-1">{{ $pd->stage->stage_number }}</div>
                                            <div class="small fw-bold text-dark mt-1 text-uppercase"
                                                style="font-size: 0.7rem;">
                                                {{ $pd->stage->stage_name }}
                                            </div>
                                        </td>
                                    @endif

                                    <td class="fw-500 py-3">
                                        {{ $pd->documentType->name }}
                                        @if ($pd->documentType->code == 'DM')
                                            <span class="badge bg-warning text-dark ms-2 shadow-sm"
                                                style="font-size: 0.6rem;">REQUIRED</span>
                                        @endif
                                    </td>

                                    <td class="text-center p-2 bg-light">
                                        <input type="date" name="due_dates[{{ $pd->id }}]"
                                            value="{{ $pd->due_date?->toDateString() }}"
                                            class="form-control text-center due-date-input border-secondary fw-bold shadow-sm"
                                            min="{{ now()->toDateString() }}"
                                            {{ auth()->user()->approved || auth()->user()->checked ? 'readonly' : '' }}>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="action-bar">
                <div class="container-fluid">

                    <div class="row g-1 mb-2 px-1">
                        <div class="col-3">
                            <div class="status-step active">
                                <span class="step-label"><i class="bi bi-pencil"></i> Created</span>
                                <div class="step-info">
                                    <div class="text-truncate fw-bold">
                                        {{ Str::limit($project->approvalStatus->created_by_name ?? '-', 10) }}
                                    </div>
                                    <div class="text-muted">
                                        {{ $project->approvalStatus->created_date ? \Carbon\Carbon::parse($project->approvalStatus->created_date)->locale('id')->translatedFormat('d M Y') : '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-3">
                            <div class="status-step {{ $project->approvalStatus->checked_date ? 'active' : '' }}">
                                <span class="step-label"><i class="bi bi-check-circle"></i> Checked</span>
                                <div class="step-info">
                                    <div class="text-truncate fw-bold">
                                        {{ $project->approvalStatus->checked_by_name ? Str::limit($project->approvalStatus->checked_by_name, 10) : '-' }}
                                    </div>
                                    <div class="text-muted">
                                        {{ $project->approvalStatus->checked_date ? \Carbon\Carbon::parse($project->approvalStatus->checked_date)->locale('id')->format('d M Y') : '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-3">
                            <div class="status-step {{ $project->approvalStatus->approved_date ? 'active' : '' }}">
                                <span class="step-label"><i class="bi bi-check-all"></i> Appr.</span>
                                <div class="step-info">
                                    <div class="text-truncate fw-bold">
                                        {{ $project->approvalStatus->approved_by_name ? Str::limit($project->approvalStatus->approved_by_name, 10) : '-' }}
                                    </div>
                                    <div class="text-muted">
                                        {{ $project->approvalStatus->approved_date ? \Carbon\Carbon::parse($project->approvalStatus->approved_date)->locale('id')->format('d M Y') : '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-3">
                            <div
                                class="status-step {{ $project->approvalStatus->management_approved_date ? 'active' : '' }}">
                                <span class="step-label"><i class="bi bi-building"></i> Mgt.</span>
                                <div class="step-info">
                                    <div class="text-truncate fw-bold">
                                        {{ $project->approvalStatus->management_approved_by_name ? Str::limit($project->approvalStatus->management_approved_by_name, 10) : '-' }}
                                    </div>
                                    <div class="text-muted">
                                        {{ $project->approvalStatus->management_approved_date ? \Carbon\Carbon::parse($project->approvalStatus->management_approved_date)->locale('id')->format('d M Y') : '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <a href="{{ route('engineering') }}"
                            class="btn btn-secondary px-4 fw-bold border-3 border-secondary-subtle">
                            <i class="bi bi-arrow-left"></i> Back
                        </a>

                        <div class="d-flex gap-2">
                            @if ($canCheck)
                                <button type="button" id="btnChecked"
                                    class="btn btn-info text-white px-4 fw-bold border-3 border-info-subtle shadow-sm">
                                    <i class="bi bi-check-square me-1"></i> Checked
                                </button>
                            @endif

                            @if ($canApprove)
                                <button type="button" id="btnApproved"
                                    class="btn btn-success px-4 fw-bold border-3 border-success-subtle shadow-sm">
                                    <i class="bi bi-check2-all me-1"></i> Approved
                                </button>
                            @endif

                            @if ($canApproveManagement)
                                <button type="button" id="btnApprovedManagement"
                                    class="btn btn-dark px-4 fw-bold border-3 border-dark-subtle shadow-sm">
                                    <i class="bi bi-building me-1"></i> Mgt. Approve
                                </button>
                            @endif

                            @if (!$canCheck && !$canApprove && !$canApproveManagement)
                                <button id="btnSave"
                                    class="btn btn-primary px-5 fw-bold border-3 border-primary-subtle shadow-lg"
                                    type="submit" disabled>
                                    <i class="bi bi-save me-2"></i> Save Dates
                                </button>
                            @endif
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
        $(document).ready(function() {
            // 1. Helper: Loading State
            function setLoading($btn) {
                const originalText = $btn.html();
                $btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-2"></span>Processing...');
                return function reset() {
                    $btn.prop('disabled', false).html(originalText);
                };
            }

            // 2. Helper: Validate Dates
            function getFirstEmptyDueDate() {
                let emptyInput = null;
                $('.due-date-input').each(function() {
                    if (!$(this).val() && !emptyInput) emptyInput = $(this);
                });
                return emptyInput;
            }

            function validateAndScroll() {
                const emptyInput = getFirstEmptyDueDate();
                if (emptyInput) {
                    alert('Please fill in all Due Dates before proceeding.');
                    $('html, body').animate({
                        scrollTop: emptyInput.offset().top - 200
                    }, 400);
                    emptyInput.focus().addClass('is-invalid');

                    // Remove invalid class on change
                    emptyInput.one('change', function() {
                        $(this).removeClass('is-invalid');
                    });
                    return false;
                }
                return true;
            }

            // 3. Helper: AJAX Action
            function approvalAction(type, $btn) {
                const resetBtn = setLoading($btn);

                $.ajax({
                    url: "{{ route('engineering.projects.approval') }}",
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        project_id: @json($project->id),
                        action: type
                    },
                    success: function() {
                        window.location.reload();
                    },
                    error: function(xhr) {
                        resetBtn();
                        alert('Error: ' + (xhr.responseJSON?.message || 'Action failed'));
                    }
                });
            }

            // 4. Button Handlers
            $('#btnChecked').on('click', function() {
                if (validateAndScroll()) {
                    approvalAction('checked', $(this));
                }
            });

            $('#btnApproved').on('click', function() {
                if (validateAndScroll()) {
                    approvalAction('approved', $(this));
                }
            });

            $('#btnApprovedManagement').on('click', function() {
                if (validateAndScroll()) {
                    approvalAction('approved_management', $(this));
                }
            });

            // 5. Save Button Logic
            $('.due-date-input').on('change', function() {
                $('#btnSave').prop('disabled', false).html('<i class="bi bi-save me-1"></i> Save Changes');
            });

            // Form Submit Loading
            $('#due-date-form').on('submit', function() {
                setLoading($('#btnSave'));
            });
        });
    </script>
@endsection
