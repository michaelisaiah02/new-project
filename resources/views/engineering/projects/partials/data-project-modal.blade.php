<div class="modal fade modal-xl" id="showProjectModal" tabindex="-1" aria-labelledby="showProjectModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-light">
                <h5 class="modal-title" id="showProjectModalLabel">Project Data</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body bg-light">
                <div class="row justify-content-md-center g-2 column-gap-4">
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text border-dark border-3 bg-secondary-subtle w-25">Customer</span>
                            <input type="text"
                                class="form-control bg-secondary-subtle border-secondary border text-center"
                                value="{{ $project->customer->code }} - {{ $project->customer->name }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <span
                                class="input-group-text border-dark border-3 bg-secondary-subtle w-25">Department</span>
                            <input type="text"
                                class="form-control bg-secondary-subtle border-secondary border text-center"
                                value="{{ $project->customer->department->name }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text border-dark border-3 bg-secondary-subtle w-25">Model</span>
                            <input type="text" class="form-control bg-warning-subtle border-warning border"
                                value="{{ $project->model }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-auto">
                                <span
                                    class="input-group-text border-dark border-3 bg-secondary-subtle rounded-1">Drawing</span>
                            </div>
                            @php
                                $basePath =
                                    $project->customer->code .
                                    '/' .
                                    $project->model .
                                    '/' .
                                    $project->part_number .
                                    '/';
                            @endphp
                            <div class="col">
                                <button type="button"
                                    class="btn btn-primary border-3 border-light-subtle w-100 view-file"
                                    id="btn-upload-2d" {{ $project->drawing_2d ? '' : 'disabled' }}
                                    data-file="{{ Storage::url($basePath . $project->drawing_2d) }}"
                                    data-title="View 2D - {{ $project->drawing_2d }}">View 2D</button>
                            </div>
                            <div class="col">
                                <button type="button"
                                    class="btn btn-primary border-3 border-light-subtle w-100 view-file"
                                    id="btn-upload-3d" {{ $project->drawing_3d ? '' : 'disabled' }}
                                    data-file="{{ Storage::url($basePath . $project->drawing_3d) }}"
                                    data-title="View 3D - {{ $project->drawing_3d }}">View 3D</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text border-dark border-3 bg-secondary-subtle w-25"
                                id="part-num-label">No.
                                Part</span>
                            <input type="text" class="form-control bg-warning-subtle border-warning border"
                                value="{{ $project->part_number }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text"
                                class="form-control bg-secondary-subtle border-secondary border text-center" readonly
                                value="{{ $project->drawing_2d }}">
                            <input type="text"
                                class="form-control bg-secondary-subtle border-secondary border text-center" readonly
                                value="{{ $project->drawing_3d ?? '-' }}">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text border-dark border-3 bg-secondary-subtle w-25">Part
                                Name</span>
                            <input class="form-control bg-warning-subtle border-warning border"
                                value={{ $project->part_name }} readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text border-dark border-3 bg-secondary-subtle w-25 text-start">No.
                                Drawing</span>
                            <input class="form-control bg-warning-subtle border-warning border"
                                value="{{ $project->drawing_number }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text border-dark border-3 bg-secondary-subtle w-25">Part
                                Type</span>
                            <input class="form-control bg-warning-subtle border-warning border"
                                value="{{ $project->part_type }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text border-dark border-3 bg-secondary-subtle fs-7">No.
                                ECI/EO/ECN</span>
                            <input class="form-control bg-warning-subtle border-warning border"
                                value="{{ $project->eee_number }}" readonly>
                            <span class="input-group-text border-dark border-3 bg-secondary-subtle">Suffix</span>
                            <input class="form-control bg-warning-subtle border-warning border"
                                value="{{ $project->suffix }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text border-dark border-3 bg-secondary-subtle w-25">QTY/Year</span>
                            <input class="form-control bg-warning-subtle border-warning border"
                                value="{{ $project->qty }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <span
                                class="input-group-text border-dark border-3 bg-secondary-subtle w-25 text-wrap lh-1 pt-0 text-start">Drawing
                                Revision
                                Date</span>
                            <input class="form-control bg-warning-subtle border-warning border"
                                value="{{ \Carbon\Carbon::parse($project->drawing_revision_date)->locale('id')->translatedFormat('d F Y') }}"
                                readonly>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="input-group">
                            <span
                                class="input-group-text border-dark border-3 bg-secondary-subtle text-wrap lh-1 pt-0 w-25">Target
                                Masspro</span>
                            <input class="form-control bg-warning-subtle border-warning border"
                                value="{{ \Carbon\Carbon::parse($project->masspro_target)->locale('id')->translatedFormat('d F Y') }}"
                                readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text border-dark border-3 bg-secondary-subtle fs-7 text-wrap pt-0"
                                style="width: 6rem;">No. SPK
                                /LOI/DIE
                                GO</span>
                            <input class="form-control bg-warning-subtle border-warning border"
                                value="{{ $project->sldg_number }}" readonly>
                            <span
                                class="input-group-text border-dark border-3 bg-secondary-subtle w-25 text-wrap lh-base pt-0 text-start fs-7">Tanggal
                                Terima SPK/LOI/DIE
                                GO</span>
                            <input class="form-control bg-warning-subtle border-warning border"
                                value="{{ \Carbon\Carbon::parse($project->sldg_date)->locale('id')->translatedFormat('d F Y') }}"
                                readonly>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="input-group">
                            <span
                                class="input-group-text border-dark border-3 bg-secondary-subtle w-25 text-wrap lh-1 pt-0 text-center">Material
                                on
                                Drawing</span>
                            <input class="form-control bg-warning-subtle border-warning border"
                                value="{{ $project->material_on_drawing }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text border-dark border-3 bg-secondary-subtle w-25">Minor
                                Change</span>
                            <input class="form-control bg-warning-subtle border-warning border"
                                value="{{ $project->minor_change }}" readonly>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-primary">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="fileViewerModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="fileViewerTitle">File Viewer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-0" style="height: 80vh;">
                <div id="fileViewerContainer"
                    class="w-100 h-100 d-flex justify-content-center align-items-center bg-light">
                    <!-- injected by JS -->
                </div>
            </div>

        </div>
    </div>
</div>
<script type="module">
    const viewerModal = new bootstrap.Modal('#fileViewerModal')
    const container = document.getElementById('fileViewerContainer')
    const titleEl = document.getElementById('fileViewerTitle')

    document.querySelectorAll('.view-file').forEach(btn => {
        btn.addEventListener('click', () => {
            const file = btn.dataset.file
            const title = btn.dataset.title
            console.log('title', title)
            const ext = file.split('.').pop().toLowerCase()

            titleEl.innerText = title
            container.innerHTML = ''

            // IMAGE
            if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
                container.innerHTML = `
                <img src="${file}" class="img-fluid" style="max-height:100%;" />
            `
            }

            // PDF
            else if (ext === 'pdf') {
                container.innerHTML = `
                <iframe src="${file}" style="width:100%; height:100%; border:none;"></iframe>
            `
            }

            // 3D FILE
            else if (['stp', 'step', 'iges', 'igs', 'stl'].includes(ext)) {
                container.innerHTML = `
                <div class="text-center">
                    <i class="bi bi-cube fs-1 mb-3"></i>
                    <p class="fw-bold">3D File Detected</p>
                    <a href="${file}" class="btn btn-primary" target="_blank">
                        Download & Open in 3D Viewer
                    </a>
                </div>
            `
            }

            // UNKNOWN
            else {
                container.innerHTML = `
                <div class="text-center text-danger">
                    <p>File format not supported for preview</p>
                    <a href="${file}" class="btn btn-secondary">Download</a>
                </div>
            `
            }

            viewerModal.show()
        })
    })
</script>
