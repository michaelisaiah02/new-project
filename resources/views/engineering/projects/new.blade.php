@extends('layouts.app-customer')
@section('title', 'New Project')
@section('customer', $project->customer->name)
@section('content')
    <div class="container-fluid mt-2">
        <form action="{{ route('engineering.projects.updateNew', ['project' => $project->part_number]) }}">
            <div class="row justify-content-center mb-2">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width">Model</span>
                        <input type="text" class="form-control bg-secondary-subtle border-secondary border"
                            value="{{ $project->model }}" id="model" placeholder="Model Part" aria-label="Model"
                            aria-describedby="model" readonly>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width">No. Part</span>
                        <input type="text" class="form-control bg-secondary-subtle border-secondary border"
                            id="part-number" placeholder="Nomor Part" aria-label="No. Part" aria-describedby="part-number"
                            value="{{ $project->part_number }}" readonly>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width">Part
                            Name</span>
                        <input type="text" class="form-control bg-secondary-subtle border-secondary border"
                            id="part-name" placeholder="Nama Part" aria-label="Part Name" aria-describedby="part-name"
                            value="{{ $project->part_name }}" readonly>
                    </div>
                </div>
            </div>
            <div class="row mb-2 ms-0 justify-content-between">
                <div class="col-auto border-0 shadow-sm bg-secondary-subtle bg-gradient rounded-2">
                    <p class="fs-4 p-0 m-0">Documents Requirement</p>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-primary btn-show-project" data-bs-toggle="modal"
                        data-bs-target="#showProjectModal" data-customer="{{ $project->customer->name }}"
                        data-model="{{ $project->model }}" data-part-number="{{ $project->part_number }}"
                        data-part-name="{{ $project->part_name }}" data-part-type="{{ $project->part_type }}"
                        data-qty="{{ $project->qty }}" data-eee-number="{{ $project->eee_number }}"
                        data-suffix="{{ $project->suffix }}" data-drawing-number="{{ $project->drawing_number }}"
                        data-drawing-revision-date="{{ $project->drawing_revision_date }}"
                        data-material-on-drawing="{{ $project->material_on_drawing }}"
                        data-receive-date-sldg="{{ $project->receive_date_sldg }}"
                        data-sldg-number="{{ $project->sldg_number }}"
                        data-masspro-target="{{ $project->masspro_target }}"
                        data-minor-change="{{ $project->minor_change }}" data-remark="{{ $project->remark }}">
                        Show Details
                    </button>
                </div>
            </div>
            <div class="container-fluid mb-5 pb-3 pt-1" style="max-height: 350px; overflow-y: auto;">
                @foreach ($stages as $stage)
                    <div class="row mb-2 mx-0">
                        <div class="col-auto my-auto">
                            <span class="card border-dark border-3 bg-secondary-subtle adjust-width p-1">Stage
                                {{ $stage->stage_number }}</span>
                        </div>
                        <div class="col-md-10">
                            <div class="row gap-2">
                                @foreach ($stage->documents as $doc)
                                    <div class="card col-auto">
                                        <div class="card-body my-0 py-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value=""
                                                    id="checkChecked" checked>
                                                <label class="form-check-label" for="checkChecked">
                                                    {{ $doc->name }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <hr class="border border-primary border-2 opacity-75">
                @endforeach
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
    <div class="modal fade modal" id="showProjectModal" tabindex="-1" aria-labelledby="showProjectModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-header bg-primary text-light">
                <h5 class="modal-title" id="showProjectModalLabel">Project Data</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body bg-light">
                <div class="input-group mb-1">
                    <span class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width">No. Part</span>
                    <input type="text" class="form-control bg-secondary-subtle border-secondary border" id="part-number"
                        placeholder="Nomor Part" aria-label="No. Part" aria-describedby="part-number"
                        value="{{ $project->part_number }}" readonly>
                </div>
                <div class="input-group">
                    <span class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width">Nama
                        Part</span>
                    <input type="text" class="form-control bg-secondary-subtle border-secondary border"
                        id="part-number" placeholder="Nomor Part" aria-label="No. Part" aria-describedby="part-number"
                        value="{{ $project->part_name }}" readonly>
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
