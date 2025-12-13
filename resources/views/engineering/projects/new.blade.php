@extends('layouts.app-projects')
@section('title', 'New Project')
@section('customer', $project->customer->name)
@section('content')
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
                <p class="fs-4 p-0 m-0">Documents Requirement</p>
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-primary btn-show-project" data-bs-toggle="modal"
                    data-bs-target="#showProjectModal">
                    Show Details
                </button>
            </div>
        </div>
        <form action="{{ route('engineering.projects.saveNew', ['project' => $project->part_number]) }}" method="post">
            @csrf
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
                                    @php
                                        $isChecked =
                                            isset($selectedDocs[$stage->id]) &&
                                            in_array($doc->code, $selectedDocs[$stage->id]);
                                    @endphp

                                    <div class="card col-auto">
                                        <div class="card-body my-0 py-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="{{ $doc->code }}"
                                                    id="checkChecked{{ $stage->id }}{{ $doc->code }}"
                                                    name="documents_codes[{{ $stage->stage_number }}][]"
                                                    {{ $isChecked ? 'checked' : '' }}>
                                                <label class="form-check-label"
                                                    for="checkChecked{{ $stage->id }}{{ $doc->code }}">
                                                    {{ $doc->name }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @if ($errors->has("documents_codes.$stage->stage_number"))
                                <div class="text-danger small mt-1">
                                    {{ $errors->first("documents_codes.$stage->stage_number") }}
                                </div>
                            @endif
                        </div>
                    </div>
                    <hr class="border border-2 border-primary opacity-75">
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
    @include('engineering.projects.partials.data-project-modal', ['project' => $project])
    <x-toast />
@endsection
