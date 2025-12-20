<div class="modal fade modal-xl" id="showProjectModal{{ $project->id }}" tabindex="-1"
    aria-labelledby="showProjectModalLabel" aria-hidden="true">
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
