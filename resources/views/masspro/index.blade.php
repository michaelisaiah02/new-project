@extends('layouts.app')
@section('title', 'MASS PRODUCTION')
@section('content')
    <div class="container-fluid mt-2">
        <form action="" method="post">
            <div class="row justify-content-center mb-2">
                <div class="col-md-5">
                    <div class="input-group mb-1">
                        <span
                            class="input-group-text border-dark border-3 bg-warning-subtle adjust-width w-25">Customer</span>
                        <select class="form-select bg-warning-subtle border-warning border" id="customer" name="customer"
                            aria-label="Model" aria-describedby="customer">
                            <option value="">Pilih Customer</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->code }}"
                                    {{ old('customer') === $customer->code ? 'selected' : '' }}>
                                    {{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="input-group mb-1">
                        <span class="input-group-text border-dark border-3 bg-warning-subtle adjust-width w-25">Model</span>
                        <select class="form-select bg-warning-subtle border-warning border" id="model" name="model"
                            aria-label="Model" aria-describedby="model">
                            <option value="">Pilih Model</option>
                            @foreach ($models as $model)
                                <option value="{{ $model->model }}" {{ old('model') === $model->model ? 'selected' : '' }}>
                                    {{ $model->model }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="input-group mb-1">
                        <span class="input-group-text border-dark border-3 bg-warning-subtle adjust-width w-25">No.
                            Part</span>
                        <select class="form-select bg-warning-subtle border-warning border" id="part-number"
                            name="part-number" placeholder="Nomor Part" aria-label="No. Part"
                            aria-describedby="part-number">
                            <option value="">Pilih Part Number</option>
                            @foreach ($partNumbers as $partNumber)
                                <option value="{{ $partNumber->part_number }}"
                                    {{ old('part-number') === $partNumber->part_number ? 'selected' : '' }}>
                                    {{ $partNumber->part_number }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="input-group mb-1">
                        <span
                            class="input-group-text border-dark border-3 bg-warning-subtle adjust-width w-25">Suffix</span>
                        <select class="form-select bg-warning-subtle border-warning border" id="suffix" name="suffix"
                            aria-label="Suffix" aria-describedby="suffix">
                            <option value="">Pilih Suffix</option>
                            @foreach ($suffixes as $suffix)
                                <option value="{{ $suffix->suffix }}"
                                    {{ old('suffix') === $suffix->suffix ? 'selected' : '' }}>
                                    {{ $suffix->suffix }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </form>
        <div class="table-responsive mb-5 pb-3 pt-1" style="max-height: 400px; overflow-y: auto;">

        </div>
        @php
            $backUrl = match (auth()->user()->department->type()) {
                'management' => route('management'),
                'engineering' => route('engineering'),
                'marketing' => route('marketing'),
                default => route('login'),
            };
        @endphp
        <div class="row align-items-center position-absolute bottom-0 start-0 end-0 mx-0 px-0 mb-2">
            <div class="col-auto">
                <a href="{{ $backUrl }}" class="btn btn-primary">Back</a>
            </div>
            <div class="col-auto mx-auto">
                <button type="button" class="btn btn-primary btn-show-project" data-bs-toggle="modal"
                    data-bs-target="#showProjectModal">
                    Show Details
                </button>
            </div>
        </div>
    </div>
    <x-toast />
@endsection
