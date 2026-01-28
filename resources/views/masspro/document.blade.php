@extends('layouts.app-projects')
@section('title', 'ON-GOING PROJECT')
@section('customer', $projectDocument->project->customer->name)

@section('styles')
    <style>
        /* CSS Khusus Halaman Viewer */
        .label-box {
            min-width: 120px;
            /* Sedikit lebih kecil dari form input biasa biar muat banyak */
            white-space: wrap;
            text-align: start;
        }

        /* Container Viewer */
        .viewer-wrapper {
            background-color: #e9ecef;
            /* Abu-abu dikit biar kontras sama kertas PDF/Img */
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Responsif Height */
        /* Di HP: Tinggi fix secukupnya biar ga makan layar */
        .viewer-wrapper {
            height: 50vh;
            min-height: 400px;
        }

        /* Di Laptop/PC: Tinggi maksimal biar puas liatnya */
        @media (min-width: 992px) {
            .viewer-wrapper {
                height: 75vh;
            }
        }

        .viewer-content {
            width: 100%;
            height: 100%;
            object-fit: contain;
            /* Biar gambar ga gepeng */
            border: none;
        }

        /* Side Panel Info */
        .info-card {
            height: 100%;
            background-color: #f8f9fa;
        }

        @media (max-width: 768px) {
            .label-box {
                min-width: 100px;
                font-size: 0.8rem;
                padding: 0.5rem;
            }
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid mt-3 pb-4">

        <div class="row g-2 mb-3">
            <div class="col-12 col-md-4">
                <div class="input-group">
                    <span class="input-group-text label-box border-dark border-3 bg-secondary-subtle">Model</span>
                    <input type="text" class="form-control bg-secondary-subtle border-secondary border"
                        value="{{ $projectDocument->project->model }}" readonly>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="input-group">
                    <span class="input-group-text label-box border-dark border-3 bg-secondary-subtle">No. Part</span>
                    <input type="text" class="form-control bg-secondary-subtle border-secondary border"
                        value="{{ $projectDocument->project->part_number }}" readonly>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="input-group">
                    <span class="input-group-text label-box border-dark border-3 bg-secondary-subtle">Part Name</span>
                    <input type="text" class="form-control bg-secondary-subtle border-secondary border"
                        value="{{ $projectDocument->project->part_name }}" readonly>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-3 bg-secondary-subtle">
            <div class="card-body py-2 px-3">
                <div class="row align-items-center justify-content-between g-2">
                    <div class="col-12 col-md-auto">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-file-earmark-text fs-4 me-2 text-primary"></i>
                            <h5 class="mb-0 fw-bold text-uppercase">
                                {{ $projectDocument->documentType->name }}
                            </h5>
                        </div>
                    </div>
                    <div class="col-12 col-md-auto">
                        <button class="btn btn-primary w-100 shadow-sm border-3 border-light-subtle px-4" id="btn-download">
                            <i class="bi bi-download me-2"></i>Download File
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">

            <div class="col-lg-9 order-2 order-lg-1">
                @php
                    // Kita generate full URL di PHP biar aman
                    $basePath =
                        $projectDocument->project->customer->code .
                        '/' .
                        $projectDocument->project->model .
                        '/' .
                        $projectDocument->project->part_number .
                        '/';
                    $fullUrl = $projectDocument->file_name ? Storage::url($basePath . $projectDocument->file_name) : '';
                @endphp

                <div id="fileViewerContainer" class="viewer-wrapper shadow-sm" data-url="{{ $fullUrl }}"
                    data-filename="{{ $projectDocument->file_name }}">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 order-1 order-lg-2">
                <div class="card info-card border-secondary-subtle shadow-sm h-100">
                    <div class="card-header bg-primary text-white fw-bold">
                        <i class="bi bi-info-circle me-2"></i>Document Info
                    </div>
                    <div class="card-body d-flex flex-column gap-3">
                        <div class="p-2 bg-white rounded border border-light-subtle">
                            <small class="text-muted d-block text-uppercase" style="font-size: 0.7rem;">Uploaded By</small>
                            <div class="fw-bold text-dark">{{ $projectDocument->created_by_name ?? '-' }}</div>
                            <div class="text-secondary small">
                                {{ $projectDocument->created_date ? $projectDocument->created_date->locale('id')->isoFormat('D MMMM Y') : '-' }}
                            </div>
                        </div>

                        <div class="p-2 bg-white rounded border border-light-subtle">
                            <small class="text-muted d-block text-uppercase" style="font-size: 0.7rem;">Checked By</small>
                            <div
                                class="fw-bold {{ $projectDocument->checked_by_name ? 'text-dark' : 'text-muted fst-italic' }}">
                                {{ $projectDocument->checked_by_name ?? 'Not Checked' }}
                            </div>
                            <div class="text-secondary small">
                                {{ $projectDocument->checked_date ? $projectDocument->checked_date->locale('id')->isoFormat('D MMMM Y') : '-' }}
                            </div>
                        </div>

                        <div class="p-2 bg-white rounded border border-light-subtle">
                            <small class="text-muted d-block text-uppercase" style="font-size: 0.7rem;">Approved By</small>
                            <div
                                class="fw-bold {{ $projectDocument->approved_by_name ? 'text-success' : 'text-muted fst-italic' }}">
                                {{ $projectDocument->approved_by_name ?? 'Not Approved' }}
                            </div>
                            <div class="text-secondary small">
                                {{ $projectDocument->approved_date ? $projectDocument->approved_date->locale('id')->isoFormat('D MMMM Y') : '-' }}
                            </div>
                        </div>

                        <div class="mt-auto">
                            <a href="{{ route('masspro.view', array_merge(['project' => $projectDocument->project->id], request()->query())) }}"
                                class="btn btn-outline-secondary w-100 border-2 fw-bold">
                                <i class="bi bi-arrow-left me-2"></i>Back to List
                            </a>
                        </div>
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
            const $container = $('#fileViewerContainer');
            const fileUrl = $container.data('url');
            const fileName = $container.data('filename');

            // 1. Logic Render Viewer
            if (!fileName || !fileUrl) {
                $container.html(`
                    <div class="text-center text-muted">
                        <i class="bi bi-file-earmark-x fs-1"></i>
                        <p class="mt-2">No file uploaded.</p>
                    </div>
                `);
            } else {
                const ext = fileName.split('.').pop().toLowerCase();
                let viewerHtml = '';

                if (ext === 'pdf') {
                    // Tambahin parameter toolbar=0 biar bersih
                    viewerHtml =
                        `<iframe src="${fileUrl}#toolbar=0&navpanes=0&scrollbar=0" class="viewer-content"></iframe>`;
                } else if (['jpg', 'jpeg', 'png', 'webp', 'gif'].includes(ext)) {
                    viewerHtml = `<img src="${fileUrl}" class="viewer-content" alt="Document Preview">`;
                } else {
                    viewerHtml = `
                        <div class="text-center">
                            <i class="bi bi-file-earmark-zip fs-1 text-warning"></i>
                            <p class="mt-2 fw-bold">Preview not available for .${ext} files</p>
                            <a href="${fileUrl}" class="btn btn-primary btn-sm" download="${fileName}">
                                Download to view
                            </a>
                        </div>
                    `;
                }

                // Delay dikit biar transisi loading kerasa (UX)
                setTimeout(() => {
                    $container.html(viewerHtml);
                }, 300);
            }

            // 2. Logic Tombol Download
            $('#btn-download').on('click', function(e) {
                e.preventDefault();

                if (!fileName || !fileUrl) {
                    // Pake Toast bawaan template lo kalo ada, atau alert biasa
                    alert('No file to download.');
                    return;
                }

                // Trik download file pake invisible link
                const link = document.createElement('a');
                link.href = fileUrl;
                link.download = fileName; // Hint browser buat download, bukan open
                link.target = '_blank'; // Jaga-jaga kalau browser maksa open
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
        });
    </script>
@endsection
