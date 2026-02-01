@extends('layouts.app-projects')
@section('title', 'ON-GOING PROJECT')
@section('customer', $project->customer->name)

@section('styles')
    <style>
        /* 1. Header Info Styling */
        .label-box {
            min-width: 100px;
            font-weight: bold;
            background-color: var(--bs-secondary-bg);
        }

        /* 2. Table Container Responsive */
        .table-container {
            max-height: 65vh;
            /* Lebih tinggi & responsif */
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            background: white;
        }

        /* 3. Sticky Header */
        thead.sticky-top th {
            z-index: 10;
            box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.1);
        }

        /* 4. Stage Column Styling */
        .stage-cell {
            background-color: #f8f9fa;
            font-weight: bold;
            vertical-align: middle;
            border-right: 2px solid #dee2e6;
        }

        /* 5. Sticky Action Bar */
        .action-bar {
            position: sticky;
            bottom: 0;
            background: white;
            border-top: 1px solid #dee2e6;
            padding: 1rem 0;
            z-index: 20;
            box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        /* 6. File Input Hidden (untuk trigger JS) */
        .file-input-hidden {
            display: none;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid mt-3 pb-5">

        <div class="card shadow-sm mb-3">
            <div class="card-body py-3">
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-3">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text label-box border-dark border-2">Model</span>
                            <input type="text" class="form-control border-dark border-2 bg-light fw-bold"
                                value="{{ $project->model }}" readonly>
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
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
                    <div class="col-12 col-md-2 text-end">
                        <button type="button" class="btn btn-outline-primary btn-sm w-100 fw-bold" data-bs-toggle="modal"
                            data-bs-target="#showProjectModal">
                            <i class="bi bi-info-circle me-1"></i> Details
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-container shadow-sm mb-3">
            <table class="table table-sm table-hover table-bordered mb-0 align-middle">
                <thead class="table-primary sticky-top text-center text-uppercase small text-nowrap">
                    <tr>
                        <th style="width: 100px;">Stage</th>
                        <th>Document</th>
                        <th style="width: 100px;">Due Date</th>
                        <th style="width: 100px;">Actual</th>
                        <th>File Name</th>
                        <th style="width: 100px;">Action</th>
                        <th style="width: 120px;">Status</th>
                        <th>Remark</th>
                    </tr>
                </thead>
                <tbody class="small">
                    @foreach ($projectDocuments as $stageId => $docs)
                        @foreach ($docs as $index => $pd)
                            <tr>
                                @if ($index === 0)
                                    <td rowspan="{{ $docs->count() }}" class="text-center stage-cell">
                                        <div class="small text-muted text-uppercase">Stage</div>
                                        <div class="fs-5 text-primary">{{ $pd->stage->stage_number }}</div>
                                        <div class="small fw-normal text-truncate" style="max-width: 90px;"
                                            title="{{ $pd->stage->stage_name }}">
                                            {{ $pd->stage->stage_name }}
                                        </div>
                                    </td>
                                @endif

                                <td class="fw-bold text-dark">
                                    {{ $pd->documentType->name }}
                                </td>

                                <td class="text-center">
                                    {{ $pd->due_date ? \Carbon\Carbon::parse($pd->due_date)->format('d/m/y') : '-' }}
                                </td>
                                <td class="text-center">
                                    {{ $pd->actual_date ? \Carbon\Carbon::parse($pd->actual_date)->format('d/m/y') : '-' }}
                                </td>

                                <td class="text-center text-truncate" style="max-width: 150px;">
                                    @if ($pd->file_name)
                                        <span class="badge bg-light text-dark border fw-normal"
                                            title="{{ $pd->file_name }}">
                                            <i class="bi bi-paperclip me-1"></i>{{ Str::limit($pd->file_name, 15) }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        @if ($pd->file_name)
                                            <a href="{{ route('engineering.project-documents.view', ['projectDocument' => $pd->id]) }}"
                                                class="btn btn-sm btn-outline-info btn-view" title="View File"
                                                data-filename="{{ $pd->file_name }}">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        @endif

                                        @if (!(auth()->user()->approved || auth()->user()->checked))
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                id="btn-upload-{{ $pd->id }}" title="Upload File">
                                                <i class="bi bi-upload"></i>
                                            </button>
                                            <input type="file" id="upload-{{ $pd->id }}" class="file-input-hidden"
                                                accept="application/pdf">
                                        @endif
                                    </div>
                                </td>

                                <td class="text-center" id="status-{{ $pd->id }}">
                                    @php
                                        $statusClass = 'bg-secondary';
                                        $statusText = 'Unknown';

                                        if (!$pd->file_name) {
                                            if ($pd->due_date && today()->gt($pd->due_date)) {
                                                $statusText = 'Delay';
                                                $statusClass = 'bg-danger';
                                            } else {
                                                $statusText = 'Pending';
                                                $statusClass = 'bg-warning text-dark';
                                            }
                                        } else {
                                            if ($pd->approved_date !== null) {
                                                $statusText = 'Finish';
                                                $statusClass = 'bg-success';
                                            } elseif (!$pd->checked_date) {
                                                $statusText = 'Unchecked';
                                                $statusClass = 'bg-info text-dark';
                                            } else {
                                                $statusText = 'Unapproved';
                                                $statusClass = 'bg-primary';
                                            }
                                        }
                                    @endphp
                                    <span class="badge {{ $statusClass }} rounded-pill w-100">
                                        {{ $statusText }}
                                    </span>
                                </td>

                                <td>
                                    <input type="text" id="remark-{{ $pd->id }}"
                                        class="form-control form-control-sm border-0 bg-warning-subtle"
                                        placeholder="Add remark..." value="{{ $pd->remark }}">
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="action-bar">
            <div class="container-fluid">
                <div class="row align-items-center">

                    <div class="col-auto d-flex gap-2">
                        <a href="{{ route('engineering') }}"
                            class="btn btn-secondary px-3 fw-bold border-3 border-secondary-subtle">
                            <i class="bi bi-arrow-left me-1"></i> Back
                        </a>
                        <button class="btn btn-outline-danger fw-bold border-2" data-bs-toggle="modal"
                            data-bs-target="#cancelModal">
                            <i class="bi bi-x-circle me-1"></i> Cancel Project
                        </button>
                    </div>

                    <div class="col"></div>

                    <div class="col-auto d-flex gap-2">
                        @if ($project->canShowCheckedButton(auth()->user()))
                            <button class="btn btn-info px-4 fw-bold text-white border-3 border-info-subtle shadow-sm"
                                id="btn-check">
                                <i class="bi bi-check2-square me-1"></i> Check
                            </button>
                        @endif

                        @if ($project->canShowApprovedButton(auth()->user()))
                            <button class="btn btn-success px-4 fw-bold border-3 border-success-subtle shadow-sm"
                                {{ auth()->user()->department->type() === 'management' ? 'id=btn-approve-management' : 'id=btn-approve' }}>
                                <i class="bi bi-check2-all me-1"></i> Approve
                            </button>
                        @endif
                    </div>

                </div>
            </div>
        </div>

    </div>

    <div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>Cancel Project?
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <p class="mb-1">Are you sure you want to cancel this project?</p>
                    <h6 class="fw-bold text-danger">{{ $project->part_number }} - {{ $project->suffix }} -
                        {{ $project->minor_change }}</h6>
                    <p class="text-muted small mb-0">This action cannot be undone.</p>
                </div>
                <div class="modal-footer justify-content-center bg-light border-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Close</button>
                    <form action="{{ route('engineering.projects.cancel', ['project' => $project->id]) }}"
                        method="post">
                        @csrf
                        <button type="submit" class="btn btn-danger px-4 fw-bold">Yes, Cancel Project</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @include('engineering.projects.partials.data-project-modal', ['project' => $project])

    <x-toast />
@endsection

@section('scripts')
    <script type="module">
        // Helper Toast (Kalau belum ada di app.js global)
        function showToast(type, message) {
            // ... (Kode Toast lo yang lama udah oke, pertahankan)
            // Gue skip tulis ulang biar hemat baris, pake yg lama aja.
        }

        $(document).ready(function() {
            // 1. Initial State Check (View Button Disable)
            // Gak perlu loop manual di JS, gue udah handle di Blade pake class 'disabled'

            // 2. Trigger Upload Input
            $('button[id^="btn-upload-"]').on('click', function() {
                const id = $(this).attr('id').replace('btn-upload-', '');
                $('#upload-' + id).click();
            });

            // 3. Upload File Logic (AJAX)
            $('input[type="file"]').on('change', function() {
                const id = $(this).attr('id').replace('upload-', '');
                const file = this.files[0];
                if (!file) return;

                let formData = new FormData();
                formData.append('file', file);

                // Kasih loading state di button upload yg diklik
                const $btn = $('#btn-upload-' + id);
                const originalHtml = $btn.html();
                $btn.html('<span class="spinner-border spinner-border-sm"></span>').prop('disabled', true);

                $.ajax({
                    url: `/engineering/project-documents/${id}/upload`,
                    method: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(res) {
                        location.reload(); // Reload halaman biar status berubah
                    },
                    error: function(xhr) {
                        $btn.html(originalHtml).prop('disabled', false); // Balikin tombol
                        let message = xhr.responseJSON?.message || 'Upload failed.';
                        alert(message); // Pake alert atau showToast('error', message)
                    }
                });
            });

            // 4. Auto Save Remark (Debounce dikit biar ga spam request)
            let remarkTimeout;
            $('input[id^="remark-"]').on('input', function() {
                const $input = $(this);
                const id = $input.attr('id').replace('remark-', '');
                const remark = $input.val();

                clearTimeout(remarkTimeout);
                remarkTimeout = setTimeout(() => {
                    $.ajax({
                        url: `/engineering/project-documents/${id}/remark`,
                        method: 'POST',
                        data: {
                            remark
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function() {
                            $input.addClass('is-valid'); // Visual feedback
                            setTimeout(() => $input.removeClass('is-valid'), 2000);
                        }
                    });
                }, 800); // 800ms delay
            });

            // 5. Button Check
            $('#btn-check').on('click', function() {
                $(this).prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm"></span> Checking...');

                $.post(`/engineering/projects/{{ $project->id }}/checked/ongoing`, {
                        _token: '{{ csrf_token() }}'
                    })
                    .done(() => location.reload())
                    .fail(res => {
                        alert(res.responseJSON.message);
                        $(this).prop('disabled', false).html(
                            '<i class="bi bi-check2-square me-1"></i> Check All');
                    });
            });

            // 6. Button Approve
            $('#btn-approve, #btn-approve-management').on('click', function() {
                $(this).prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm"></span> Approving...');

                $.post(`/engineering/projects/{{ $project->id }}/approved/ongoing`, {
                        _token: '{{ csrf_token() }}'
                    })
                    .done(() => location.reload())
                    .fail(res => {
                        alert(res.responseJSON.message);
                        $(this).prop('disabled', false).html(
                            '<i class="bi bi-check2-all me-1"></i> Approve All');
                    });
            });
        });
    </script>
@endsection
