@extends('layouts.app-projects')
@section('title', 'MASS PRODUCTION')
@section('customer', $project->customer->name)
@section('styles')
    <style>
        .label-box {
            min-width: 120px;
            /* Default PC */
            white-space: wrap;
            /* Biar teks panjang kayak 'Tanggal Terima' bisa turun ke bawah */
            text-align: start;
        }
    </style>
@section('content')
    <div class="container-fluid mt-2">
        <div class="row justify-content-center mb-2">
            <div class="col-md-4">
                <div class="input-group mb-1">
                    <span class="input-group-text border-dark border-3 bg-secondary-subtle label-box">Customer</span>
                    <input type="text" class="form-control bg-secondary-subtle border-secondary border"
                        value="{{ $project->customer_code . '-' . $project->customer->name }}" readonly>
                </div>
            </div>
            <div class="col-md-4">
                <div class="input-group mb-1">
                    <span class="input-group-text border-dark border-3 bg-secondary-subtle label-box">No. Part</span>
                    <input type="text" class="form-control bg-secondary-subtle border-secondary border"
                        value="{{ $project->part_number }}" readonly>
                </div>
            </div>
            <div class="col-md-4">
                <div class="input-group mb-1">
                    <span class="input-group-text border-dark border-3 bg-secondary-subtle label-box">Suffix</span>
                    <input type="text" class="form-control bg-secondary-subtle border-secondary border"
                        value="{{ $project->suffix }}" readonly>
                </div>
            </div>
            <div class="col-md-4">
                <div class="input-group mb-1">
                    <span class="input-group-text border-dark border-3 bg-secondary-subtle label-box">Model</span>
                    <input type="text" class="form-control bg-secondary-subtle border-secondary border"
                        value="{{ $project->model }}" readonly>
                </div>
            </div>
            <div class="col-md-4">
                <div class="input-group mb-1">
                    <span class="input-group-text border-dark border-3 bg-secondary-subtle label-box">Part
                        Name</span>
                    <input type="text" class="form-control bg-secondary-subtle border-secondary border"
                        value="{{ $project->part_name }}" readonly>
                </div>
            </div>
            <div class="col-md-4">
                <div class="input-group mb-1">
                    <span class="input-group-text border-dark border-3 bg-secondary-subtle label-box">No.
                        ECI/EO/ECN</span>
                    <input type="text" class="form-control bg-secondary-subtle border-secondary border"
                        value="{{ $project->part_name }}" readonly>
                </div>
            </div>
        </div>
        <div class="table-responsive mb-5 pb-3 pt-1" style="max-height: 350px; overflow-y: auto;">
            <table class="table table-sm table-bordered m-0 text-start align-middle text-nowrap">
                <thead class="table-primary sticky-top">
                    <tr>
                        <th class="text-center">Stage</th>
                        <th>Document</th>
                        <th class="text-center">File Name Upload</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($projectDocuments as $stageId => $docs)
                        @foreach ($docs as $index => $pd)
                            <tr>
                                @if ($index === 0)
                                    <td rowspan="{{ $docs->count() }}" class="text-center">
                                        <p class="p-0 m-0">Stage
                                            {{ $pd->stage->stage_number }}</p>
                                        <p class="p-0 m-0">
                                            {{ $pd->stage->stage_name }}</p>
                                    </td>
                                @endif

                                <td class="text-wrap w-25">
                                    {{ $pd->documentType->name }}
                                </td>
                                <td class="text-center">
                                    @if ($pd->file_name)
                                        {{ $pd->file_name }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('masspro.document', array_merge(['projectDocument' => $pd->id], request()->query())) }}"
                                        class="btn btn-sm btn-primary btn-view" data-filename="{{ $pd->file_name }}">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="row justify-content-between align-items-center sticky-bottom mb-2">
            <div class="col-auto">
                <a href="{{ route('masspro.index', request()->query()) }}" class="btn btn-primary">Back</a>
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-primary btn-show-project" data-bs-toggle="modal"
                    data-bs-target="#showProjectModal">
                    Show Details
                </button>
            </div>
        </div>
    </div>
    @include('engineering.projects.partials.data-project-modal', ['project' => $project])
    <x-toast />
@endsection
