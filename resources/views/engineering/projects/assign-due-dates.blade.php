@extends('layouts.app-projects')
@section('title', 'New Project')
@section('customer', $project->customer->name)
@section('content')
    @php
        $stages = [];
    @endphp
    <div class="container-fluid mt-2">
        <div class="row justify-content-center mb-2">
            <div class="col-md-4">
                <div class="input-group mb-1">
                    <span class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width">Model</span>
                    <input type="text" class="form-control bg-secondary-subtle border-secondary border"
                        value="{{ $project->model }}" id="model" placeholder="Model Part" aria-label="Model"
                        aria-describedby="model" readonly>
                </div>
            </div>
            <div class="col-md-4">
                <div class="input-group mb-1">
                    <span class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width">No. Part</span>
                    <input type="text" class="form-control bg-secondary-subtle border-secondary border" id="part-number"
                        placeholder="Nomor Part" aria-label="No. Part" aria-describedby="part-number"
                        value="{{ $project->part_number }}" readonly>
                </div>
            </div>
            <div class="col-md-4">
                <div class="input-group mb-1">
                    <span class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width">Part
                        Name</span>
                    <input type="text" class="form-control bg-secondary-subtle border-secondary border" id="part-name"
                        placeholder="Nama Part" aria-label="Part Name" aria-describedby="part-name"
                        value="{{ $project->part_name }}" readonly>
                </div>
            </div>
        </div>
        <div class="row mb-2 ms-0 justify-content-between">
            <div class="col-auto border-0 shadow-sm bg-secondary-subtle bg-gradient rounded-2">
                <p class="fs-4 p-0 m-0 fw-bold">Assign Due Dates</p>
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-primary btn-show-project" data-bs-toggle="modal"
                    data-bs-target="#showProjectModal">
                    Show Details
                </button>
            </div>
        </div>
        <form action="{{ route('engineering.projects.saveAssignDueDates', ['project' => $project->part_number]) }}"
            method="post">
            @csrf
            <div class="table-responsive mb-5 pb-3 pt-1" style="max-height: 350px; overflow-y: auto;">
                <table class="table table-sm table-bordered m-0 text-center">
                    <thead class="table-primary">
                        <tr>
                            <th scope="col">Stage</th>
                            <th scope="col">Document</th>
                            <th scope="col">Due Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="">
                            <td scope="row" rowspan="3">Stage 1</td>
                            <td class="align-middle w-75">Document A</td>
                            <td>
                                <input type="date" name="due_date" id="due-date"
                                    class="form-control form-control-sm
                                    bg-secondary-subtle border-3 border-dark">
                            </td>
                        </tr>
                        <tr class="">
                            <td scope="row">Document B</td>
                            <td>
                                <input type="date" name="due_date" id="due-date"
                                    class="form-control form-control-sm
                                    bg-secondary-subtle border-3 border-dark">
                            </td>
                        </tr>
                        <tr class="">
                            <td scope="row">Document C</td>
                            <td>
                                <input type="date" name="due_date" id="due-date"
                                    class="form-control form-control-sm
                                    bg-secondary-subtle border-3 border-dark">
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div
                class="row justify-content-between align-items-center position-absolute bottom-0 start-0 end-0 mx-0 px-0 mb-2">
                <div class="col-auto">
                    <a href="{{ route('engineering') }}" class="btn btn-primary">Back</a>
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary" type="submit">
                        Assign Due Dates
                    </button>
                </div>
            </div>
        </form>
    </div>
    <div class="modal fade modal-lg" id="showProjectModal" tabindex="-1" aria-labelledby="showProjectModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-header bg-primary text-light">
                <h5 class="modal-title" id="showProjectModalLabel">Project Data</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body bg-light">
                <div class="input-group mb-1">
                    <span class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width">Customer</span>
                    <input type="text" class="form-control bg-secondary-subtle border-secondary border text-center"
                        value="{{ $project->customer->code }}" readonly>
                    <input type="text"
                        class="form-control bg-secondary-subtle border-secondary border w-auto text-center"
                        value="{{ $project->customer->name }}" readonly>
                </div>
                <div class="input-group mb-1">
                    <span class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width">No. Part</span>
                    <input type="text" class="form-control bg-secondary-subtle border-secondary border"
                        value="{{ $project->part_number }}" readonly>
                    <span class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width">Nama
                        Part</span>
                    <input type="text" class="form-control bg-secondary-subtle border-secondary border"
                        value="{{ $project->part_name }}" readonly>
                    <span class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width">Part Type</span>
                    <input type="text" class="form-control bg-secondary-subtle border-secondary border"
                        value="{{ $project->part_type }}" readonly>
                </div>
                <div class="input-group mb-1">
                    <span class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width">Model</span>
                    <input type="text" class="form-control bg-secondary-subtle border-secondary border"
                        value="{{ $project->model }}" readonly>
                    <span class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width">No.
                        ECI/EO/ECN</span>
                    <input type="text" class="form-control bg-secondary-subtle border-secondary border"
                        value="{{ $project->eee_number }}" readonly>
                </div>
                <div class="input-group mb-1">
                    <span class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width">Suffix</span>
                    <input type="text" class="form-control bg-secondary-subtle border-secondary border"
                        value="{{ $project->suffix }}" readonly>
                </div>
                <div class="input-group mb-1">
                    <span class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width">No. Drawing</span>
                    <input type="text" class="form-control bg-secondary-subtle border-secondary border"
                        value="{{ $project->drawing_number }}" readonly>
                    <span class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width">Drawing
                        Revision
                        Date</span>
                    <input type="text" class="form-control bg-secondary-subtle border-secondary border"
                        value="{{ $project->drawing_revision_date }}" readonly>
                </div>
                <div class="input-group mb-1">
                    <span class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width">No. SPK
                        /LOI/DIE
                        GO</span>
                    <input type="text" class="form-control bg-secondary-subtle border-secondary border"
                        value="{{ $project->sldg_number }}" readonly>
                    <span class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width">Tanggal
                        Terima SPK/LOI/DIE
                        GO</span>
                    <input type="text" class="form-control bg-secondary-subtle border-secondary border"
                        value="{{ $project->receive_date_sldg }}" readonly>
                </div>
                <div class="input-group mb-1">
                    <span class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width">QTY/Year</span>
                    <input type="text" class="form-control bg-secondary-subtle border-secondary border"
                        value="{{ $project->qty }}" readonly>
                    <span class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width">Target
                        Masspro</span>
                    <input type="text" class="form-control bg-secondary-subtle border-secondary border"
                        value="{{ $project->masspro_target }}" readonly>
                </div>
                <div class="input-group mb-1">
                    <span class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width">Material
                        on
                        Drawing</span>
                    <input type="text" class="form-control bg-secondary-subtle border-secondary border"
                        value="{{ $project->material_on_drawing }}" readonly>
                </div>
                <div class="input-group mb-1">
                    <span class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width">Minor
                        Change</span>
                    <input type="text" class="form-control bg-secondary-subtle border-secondary border"
                        value="{{ $project->minor_change }}" readonly>
                </div>
                <div class="input-group">
                    <span class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width">Remark</span>
                    <input type="text" class="form-control bg-secondary-subtle border-secondary border"
                        value="{{ $project->remark }}" readonly>
                </div>
            </div>
            <div class="modal-footer bg-primary">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
            </form>
        </div>
    </div>
    <x-toast />
@endsection

@section('scripts')
    <script type="module"></script>
@endsection
