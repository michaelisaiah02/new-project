<style>
    .label-box {
        min-width: 140px;
        white-space: wrap;
        text-align: start;
    }

    @media (max-width: 768px) {
        .label-box {
            min-width: 110px;
            font-size: 0.85rem;
            padding: 0.5rem;
        }
    }
</style>

<div class="modal fade" id="showProjectModal" tabindex="-1" aria-labelledby="showProjectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-light">
                <h5 class="modal-title fw-bold" id="showProjectModalLabel">
                    <i class="bi bi-info-circle me-2"></i>Project Detail
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>

            <div class="modal-body bg-light">
                <div class="row g-1">

                    <div class="col-12 col-lg-6">
                        <div class="input-group">
                            <span
                                class="input-group-text label-box border-dark border-3 bg-secondary-subtle">Customer</span>
                            <input type="text" class="form-control bg-secondary-subtle border-secondary border"
                                value="{{ $project->customer->code }} - {{ $project->customer->name }}" readonly>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="input-group">
                            <span
                                class="input-group-text label-box border-dark border-3 bg-secondary-subtle">Department</span>
                            <input type="text" class="form-control bg-secondary-subtle border-secondary border"
                                value="{{ $project->customer->department->name }}" readonly>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="input-group">
                            <span
                                class="input-group-text label-box border-dark border-3 bg-secondary-subtle">Model</span>
                            <input type="text" class="form-control bg-warning-subtle border-warning border"
                                value="{{ $project->model }}" readonly>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="input-group">
                            <span
                                class="input-group-text label-box border-dark border-3 bg-secondary-subtle">Drawing</span>

                            @php
                                $basePath =
                                    $project->customer->code .
                                    '/' .
                                    $project->model .
                                    '/' .
                                    $project->part_number .
                                    '/';
                            @endphp

                            <button type="button" class="btn btn-primary border-3 border-light-subtle grow view-file"
                                {{ $project->drawing_2d ? '' : 'disabled' }}
                                data-file="{{ $project->drawing_2d ? Storage::url($basePath . $project->drawing_2d) : '#' }}"
                                data-title="View 2D - {{ $project->drawing_2d }}">
                                <i class="bi bi-file-earmark-image me-md-1"></i> View 2D
                            </button>

                            <button type="button" class="btn btn-dark border-3 border-light-subtle grow view-file"
                                {{ $project->drawing_3d ? '' : 'disabled' }}
                                data-file="{{ $project->drawing_3d ? Storage::url($basePath . $project->drawing_3d) : '#' }}"
                                data-title="View 3D - {{ $project->drawing_3d }}">
                                <i class="bi bi-box me-md-1"></i> View 3D
                            </button>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="input-group">
                            <span class="input-group-text label-box border-dark border-3 bg-secondary-subtle">No.
                                Part</span>
                            <input type="text" class="form-control bg-warning-subtle border-warning border"
                                value="{{ $project->part_number }}" readonly>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="d-flex flex-column flex-md-row gap-2">
                            <input type="text" class="form-control bg-secondary-subtle border-secondary border"
                                value="{{ $project->drawing_2d }}" readonly placeholder="No 2D File">
                            <input type="text" class="form-control bg-secondary-subtle border-secondary border"
                                value="{{ $project->drawing_3d ?? '-' }}" readonly placeholder="No 3D File">
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="input-group">
                            <span class="input-group-text label-box border-dark border-3 bg-secondary-subtle">Part
                                Name</span>
                            <input class="form-control bg-warning-subtle border-warning border"
                                value="{{ $project->part_name }}" readonly>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="input-group">
                            <span class="input-group-text label-box border-dark border-3 bg-secondary-subtle">No.
                                Drawing</span>
                            <input class="form-control bg-warning-subtle border-warning border"
                                value="{{ $project->drawing_number }}" readonly>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="input-group">
                            <span class="input-group-text label-box border-dark border-3 bg-secondary-subtle">Part
                                Type</span>
                            <input class="form-control bg-warning-subtle border-warning border"
                                value="{{ $project->part_type }}" readonly>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="input-group">
                            <span class="input-group-text label-box border-dark border-3 bg-secondary-subtle">No.
                                ECI/EO</span>
                            <input class="form-control bg-warning-subtle border-warning border"
                                value="{{ $project->eee_number }}" readonly>

                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="input-group">
                            <span
                                class="input-group-text label-box border-dark border-3 bg-secondary-subtle">Suffix</span>
                            <input class="form-control bg-warning-subtle border-warning border"
                                value="{{ $project->suffix }}" readonly>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="input-group">
                            <span
                                class="input-group-text label-box border-dark border-3 bg-secondary-subtle">QTY/Year</span>
                            <input class="form-control bg-warning-subtle border-warning border"
                                value="{{ $project->qty }}" readonly>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="input-group">
                            <span class="input-group-text label-box border-dark border-3 bg-secondary-subtle">Rev
                                Date</span>
                            <input class="form-control bg-warning-subtle border-warning border"
                                value="{{ \Carbon\Carbon::parse($project->drawing_revision_date)->locale('id')->translatedFormat('d F Y') }}"
                                readonly>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="input-group">
                            <span class="input-group-text label-box border-dark border-3 bg-secondary-subtle">Target
                                MP</span>
                            <input class="form-control bg-warning-subtle border-warning border"
                                value="{{ \Carbon\Carbon::parse($project->masspro_target)->locale('id')->translatedFormat('d F Y') }}"
                                readonly>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="d-flex flex-column gap-2">
                            <div class="input-group">
                                <span class="input-group-text label-box border-dark border-3 bg-secondary-subtle">No.
                                    SPK/LOI</span>
                                <input class="form-control bg-warning-subtle border-warning border"
                                    value="{{ $project->sldg_number }}" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <div class="input-group">
                            <span class="input-group-text label-box border-dark border-3 bg-secondary-subtle">Tgl
                                Terima</span>
                            <input class="form-control bg-warning-subtle border-warning border"
                                value="{{ \Carbon\Carbon::parse($project->receive_date_sldg)->locale('id')->translatedFormat('d F Y') }}"
                                readonly>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="input-group">
                            <span
                                class="input-group-text label-box border-dark border-3 bg-secondary-subtle">Material</span>
                            <input class="form-control bg-warning-subtle border-warning border"
                                value="{{ $project->material_on_drawing }}" readonly>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="input-group">
                            <span class="input-group-text label-box border-dark border-3 bg-secondary-subtle">Minor
                                Change</span>
                            <input class="form-control bg-warning-subtle border-warning border"
                                value="{{ $project->minor_change }}" readonly>
                        </div>
                    </div>

                </div>
            </div>

            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary px-4 shadow-sm"
                    data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="fileViewerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content h-100">
            <div class="modal-header bg-dark text-white py-2">
                <h6 class="modal-title text-truncate" id="fileViewerTitle" style="max-width: 90%;">File Viewer</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0 bg-secondary-subtle position-relative"
                style="height: 80vh; min-height: 400px;">
                <div id="fileViewerContainer" class="w-100 h-100 d-flex justify-content-center align-items-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="module">
    // Inisialisasi Modal
    const viewerModalEl = document.getElementById('fileViewerModal');
    const viewerModal = new bootstrap.Modal(viewerModalEl);
    const container = document.getElementById('fileViewerContainer');
    const titleEl = document.getElementById('fileViewerTitle');

    document.querySelectorAll('.view-file').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();

            const file = btn.dataset.file;
            const title = btn.dataset.title;

            // Validasi file
            if (!file || file === '#' || file === '') {
                alert('File not available');
                return;
            }

            // Reset container content
            container.innerHTML =
                '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
            titleEl.innerText = title;

            const ext = file.split('.').pop().toLowerCase();

            // Delay dikit biar transisi modal mulus baru load content
            setTimeout(() => {
                let content = '';

                // IMAGE
                if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
                    content =
                        `<img src="${file}" class="img-fluid shadow-sm rounded" style="max-height:95%; max-width:95%; object-fit:contain;" />`;
                }
                // PDF
                else if (ext === 'pdf') {
                    content =
                        `<iframe src="${file}" style="width:100%; height:100%; border:none;"></iframe>`;
                }
                // 3D FILE
                else if (['stp', 'step', 'iges', 'igs', 'stl'].includes(ext)) {
                    content = `
                        <div class="text-center p-5 bg-white rounded shadow">
                            <i class="bi bi-box-seam fs-1 text-primary mb-3 d-block"></i>
                            <h5 class="fw-bold mb-2">3D File Detected (${ext.toUpperCase()})</h5>
                            <p class="text-muted mb-4">Browser cannot preview 3D files natively.</p>
                            <a href="${file}" class="btn btn-primary px-4 rounded-pill" target="_blank">
                                <i class="bi bi-download me-2"></i> Download & Open externally
                            </a>
                        </div>
                    `;
                }
                // UNKNOWN
                else {
                    content = `
                        <div class="text-center p-5 bg-white rounded shadow">
                            <i class="bi bi-file-earmark-x fs-1 text-danger mb-3 d-block"></i>
                            <h5 class="fw-bold mb-2">Format Not Supported</h5>
                            <p class="text-muted mb-4">Cannot preview .${ext} files.</p>
                            <a href="${file}" class="btn btn-secondary px-4 rounded-pill">Download File</a>
                        </div>
                    `;
                }

                container.innerHTML = content;
            }, 300); // 300ms delay

            viewerModal.show();
        });
    });

    // Bersihin container pas modal ditutup biar ga makan memori/bocorin audio/video
    viewerModalEl.addEventListener('hidden.bs.modal', () => {
        container.innerHTML = '';
    });
</script>
