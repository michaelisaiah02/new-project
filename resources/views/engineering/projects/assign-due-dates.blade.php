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
                <table class="table table-sm table-bordered m-0 text-start align-middle">
                    <thead class="table-primary">
                        <tr>
                            <th class="text-center">Stage</th>
                            <th>Document</th>
                            <th class="text-center">Due Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($projectDocuments as $stageId => $docs)
                            @foreach ($docs as $index => $pd)
                                <tr>
                                    @if ($index === 0)
                                        <td rowspan="{{ $docs->count() }}" class="text-center">
                                            Stage {{ $pd->stage->stage_number }}
                                        </td>
                                    @endif

                                    <td class="w-75">
                                        {{ $pd->documentType->name }}
                                    </td>

                                    <td>
                                        <input type="date" name="due_dates[{{ $pd->id }}]"
                                            value="{{ $pd->due_date }}"
                                            class="form-control form-control-sm
                                              bg-secondary-subtle border-3 border-dark">
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="row align-items-center position-absolute bottom-0 start-0 end-0 mx-0 px-0 mb-2">
                <div class="col-md">
                    <div class="form-floating">
                        <textarea class="form-control form-control-sm" id="floatingPassword" placeholder="Password"></textarea>
                        <label for="floatingPassword">Approval History</label>
                    </div>
                </div>
                <div class="col-auto">
                    <a href="{{ route('engineering') }}" class="btn btn-primary">Back</a>
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary" type="submit">
                        Approved/ Checked
                    </button>
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary" type="submit">
                        Save
                    </button>
                </div>
            </div>
        </form>
    </div>
    @include('engineering.projects.partials.data-project-modal', ['project' => $project])
    <x-toast />
@endsection

@section('scripts')
    <script type="module"></script>
@endsection
