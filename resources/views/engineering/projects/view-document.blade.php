@extends('layouts.app-projects')
@section('title', 'ON-GOING PROJECT')
@section('customer', $projectDocument->project->customer->name)

@section('styles')
    <style>
        /* 1. Label Box */
        .label-box {
            min-width: 100px;
            font-weight: bold;
            background-color: var(--bs-secondary-bg);
        }

        /* 2. File Viewer Container */
        .viewer-container {
            background-color: #e9ecef;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Tinggi Viewer Responsif */
        .viewer-container {
            height: 50vh;
            /* Default HP */
            min-height: 400px;
        }

        @media (min-width: 992px) {
            .viewer-container {
                height: 62vh;
                /* Desktop lebih tinggi */
            }
        }

        .viewer-content {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border: none;
        }

        /* 3. Info Blocks */
        .info-block {
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 5px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .info-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #6c757d;
            font-weight: bold;
            margin-bottom: 2px;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid mt-3 pb-3 pb-lg-0">

        <div class="card shadow-sm mb-3">
            <div class="card-body py-3">
                <div class="row g-2">
                    <div class="col-12 col-md-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text label-box border-dark border-2">Model</span>
                            <input type="text" class="form-control border-dark border-2 bg-light fw-bold"
                                value="{{ $projectDocument->project->model }}" readonly>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text label-box border-dark border-2">Part No</span>
                            <input type="text" class="form-control border-dark border-2 bg-light fw-bold"
                                value="{{ $projectDocument->project->part_number }}" readonly>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text label-box border-dark border-2">Part Name</span>
                            <input type="text" class="form-control border-dark border-2 bg-light fw-bold"
                                value="{{ $projectDocument->project->part_name }}" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-2 px-1">
            <div class="d-flex align-items-center bg-secondary-subtle px-3 py-1 rounded border shadow-sm">
                <i class="bi bi-file-earmark-text fs-4 me-2 text-primary"></i>
                <span class="fs-5 fw-bold">{{ $projectDocument->documentType->name }}</span>
            </div>
            <div>
                <button class="btn btn-primary btn-sm shadow-sm px-3 fw-bold" id="btn-download">
                    <i class="bi bi-download me-1"></i> Download
                </button>
            </div>
        </div>

        <div class="row g-3">

            <div class="col-lg-9 order-2 order-lg-1">
                @php
                    // Generate Secure URL di Server Side
                    $customerCode = $projectDocument->project->customer->code;
                    $model = $projectDocument->project->model;
                    $partNumber = $projectDocument->project->part_number;
                    $fileName = $projectDocument->file_name;

                    // Path manual (sesuai folder structure lo)
                    $path = "{$customerCode}/{$model}/{$partNumber}/{$fileName}";
                    $fileUrl = $fileName ? Storage::url($path) : '';
                @endphp

                <div id="fileViewerContainer" class="viewer-container shadow-sm" data-url="{{ $fileUrl }}"
                    data-filename="{{ $fileName }}">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 order-1 order-lg-2">
                <div class="d-flex flex-column h-100">

                    <div class="card shadow-sm border-0 bg-light mb-1 flex-fill">
                        <div class="card-header bg-dark text-white fw-bold py-2">
                            <i class="bi bi-info-circle me-2"></i>Document Info
                        </div>
                        <div class="card-body p-2 d-flex flex-column gap-1 overflow-auto" style="max-height: 300px;">
                            <div class="info-block">
                                <div class="info-label">Uploaded By</div>
                                <div class="fw-bold text-dark">{{ $projectDocument->created_by_name ?? '-' }}</div>
                                <div class="small text-muted">
                                    {{ $projectDocument->created_date ? $projectDocument->created_date->locale('id')->isoFormat('D MMMM Y') : '-' }}
                                </div>
                            </div>

                            <div
                                class="info-block {{ $projectDocument->checked_by_name ? 'border-primary border-opacity-25 bg-primary-subtle' : '' }}">
                                <div class="info-label text-primary">Checked By</div>
                                <div class="fw-bold text-dark">{{ $projectDocument->checked_by_name ?? '-' }}</div>
                                <div class="small text-muted">
                                    {{ $projectDocument->checked_date ? $projectDocument->checked_date->locale('id')->isoFormat('D MMMM Y') : '-' }}
                                </div>
                            </div>

                            <div
                                class="info-block {{ $projectDocument->approved_by_name ? 'border-success border-opacity-25 bg-success-subtle' : '' }}">
                                <div class="info-label text-success">Approved By</div>
                                <div class="fw-bold text-dark">{{ $projectDocument->approved_by_name ?? '-' }}</div>
                                <div class="small text-muted">
                                    {{ $projectDocument->approved_date ? $projectDocument->approved_date->locale('id')->isoFormat('D MMMM Y') : '-' }}
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="d-flex flex-column gap-1">
                        @php
                            $user = auth()->user();
                            $dept = $user->department->type();

                            // Logic Permission (Pindah dari JS ke Blade biar aman)
                            $showCheck = $user->checked && !$projectDocument->checked_date && $dept === 'engineering';
                            $showApprove =
                                $user->approved &&
                                $projectDocument->checked_date &&
                                !$projectDocument->approved_date &&
                                ($dept === 'engineering' || $dept === 'management');
                        @endphp

                        @if ($showCheck)
                            <button class="btn btn-primary w-100 shadow-sm fw-bold py-2 border-3 border-light-subtle"
                                id="btn-check">
                                <i class="bi bi-check-circle me-2"></i>Check
                            </button>
                        @endif

                        @if ($showApprove)
                            <button class="btn btn-success w-100 shadow-sm fw-bold py-2 border-3 border-light-subtle"
                                id="btn-approve">
                                <i class="bi bi-check-all me-2"></i>Approve
                            </button>
                        @endif

                        <a href="{{ route('engineering.projects.onGoing', ['project' => $projectDocument->project->id]) }}"
                            class="btn btn-secondary w-100 border-3 border-secondary-subtle">
                            <i class="bi bi-arrow-left me-2"></i>Back
                        </a>
                    </div>

                </div>
            </div>
        </div>

    </div>
    <x-toast />
@endsection

@section('scripts')
    <script type="module">
        $(function() {
            // --- 1. VIEWER LOGIC ---
            const $container = $('#fileViewerContainer');
            const fileUrl = $container.data('url');
            const fileName = $container.data('filename');

            if (!fileName) {
                $container.html(`
                    <div class="text-center text-muted">
                        <i class="bi bi-file-earmark-x display-1"></i>
                        <p class="mt-2 fw-bold">No file uploaded yet.</p>
                    </div>
                `);
            } else {
                const ext = fileName.split('.').pop().toLowerCase();
                let viewerHtml = '';

                // PDF Viewer
                if (ext === 'pdf') {
                    // Tambahin cache buster & params biar bersih
                    viewerHtml =
                        `<iframe src="${fileUrl}?v={{ time() }}#toolbar=0&navpanes=0&scrollbar=0" class="viewer-content"></iframe>`;
                }
                // Image Viewer
                else if (['jpg', 'jpeg', 'png', 'webp', 'gif'].includes(ext)) {
                    viewerHtml =
                        `<img src="${fileUrl}?v={{ time() }}" class="viewer-content" alt="Document Preview">`;
                }
                // Fallback
                else {
                    viewerHtml = `
                        <div class="text-center">
                            <i class="bi bi-file-earmark-binary display-4 text-warning"></i>
                            <p class="mt-3">Preview not available for .${ext} files.</p>
                            <a href="${fileUrl}" class="btn btn-primary" download="${fileName}">Download File</a>
                        </div>
                    `;
                }

                // Delay render dikit biar spinner keliatan (UX)
                setTimeout(() => {
                    $container.html(viewerHtml);
                }, 500);
            }

            // --- 2. ACTION BUTTONS LOGIC ---
            const csrf = '{{ csrf_token() }}';
            const docId = '{{ $projectDocument->id }}';
            const redirectUrl =
                '{{ route('engineering.projects.onGoing', ['project' => $projectDocument->project->id]) }}';

            // Fungsi helper Loading Button
            function setLoading($btn) {
                const originalText = $btn.html();
                $btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-2"></span>Processing...');
                return function reset() {
                    $btn.prop('disabled', false).html(originalText);
                };
            }

            // Button Check
            $('#btn-check').on('click', function() {
                const resetBtn = setLoading($(this));
                $.post(`/engineering/project-documents/${docId}/checked`, {
                        _token: csrf
                    })
                    .done(() => window.location.href = redirectUrl)
                    .fail((res) => {
                        resetBtn();
                        alert('Error: ' + (res.responseJSON?.message || 'Failed to check document.'));
                    });
            });

            // Button Approve
            $('#btn-approve').on('click', function() {
                const resetBtn = setLoading($(this));
                $.post(`/engineering/project-documents/${docId}/approved`, {
                        _token: csrf
                    })
                    .done(() => window.location.href = redirectUrl)
                    .fail((res) => {
                        resetBtn();
                        alert('Error: ' + (res.responseJSON?.message || 'Failed to approve document.'));
                    });
            });

            // Button Download
            $('#btn-download').on('click', function() {
                if (!fileName || !fileUrl) {
                    alert('No file available to download.');
                    return;
                }
                // Invisible Link Trick
                const link = document.createElement('a');
                link.href = fileUrl;
                link.download = fileName;
                link.target = '_blank';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
        });
    </script>
@endsection
