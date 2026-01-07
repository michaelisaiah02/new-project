@extends('layouts.app')
@section('title', 'MASS PRODUCTION')
@section('content')
    <div class="container-fluid mt-2">
        <form action="{{ route('masspro.index') }}" method="get">
            <div class="row justify-content-center mb-2">
                <div class="col-md-4">
                    <div class="input-group mb-1">
                        <span
                            class="input-group-text border-dark border-3 bg-warning-subtle adjust-width w-25">Customer</span>
                        <select class="form-select bg-warning-subtle border-warning border" id="customer" name="customer"
                            aria-label="Model" aria-describedby="customer">
                            <option value="">All Customer</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->code }}"
                                    {{ request('customer') === $customer->code ? 'selected' : '' }}>{{ $customer->code }} -
                                    {{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group mb-1">
                        <span class="input-group-text border-dark border-3 bg-warning-subtle adjust-width"
                            style="width: 30%">Model</span>
                        <input type="text" class="form-control bg-warning-subtle border-warning border"
                            placeholder="Model Part" aria-label="Model Part" aria-describedby="model" id="model"
                            name="model" value="{{ request('model') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group mb-1">
                        <span class="input-group-text border-dark border-3 bg-warning-subtle adjust-width w-25">No.
                            Part</span>
                        <input type="text" class="form-control bg-warning-subtle border-warning border"
                            placeholder="Nomor Part" aria-label="Nomor Part" aria-describedby="part-num-label"
                            id="part-num" name="part_number" value="{{ request('part_number') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group mb-1">
                        <span
                            class="input-group-text border-dark border-3 bg-warning-subtle adjust-width w-25">Remark</span>
                        <select class="form-select bg-warning-subtle border-warning border" id="remark" name="remark"
                            aria-label="Remark" aria-describedby="remark">
                            <option value="all" {{ request('remark') == 'all' ? 'selected' : '' }}>All</option>
                            <option value="completed" {{ request('remark') == 'completed' ? 'selected' : '' }}>Completed
                            </option>
                            <option value="canceled" {{ request('remark') == 'canceled' ? 'selected' : '' }}>Canceled
                            </option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group mb-1">
                        <span class="input-group-text border-dark border-3 bg-warning-subtle adjust-width"
                            style="width: 30%">Minor
                            Change</span>
                        <input class="form-control bg-warning-subtle border-warning border" type="text"
                            aria-label="Minor Change" aria-describedby="minor_change" id="minor_change" name="minor_change"
                            placeholder="..." value="{{ request('minor_change') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group mb-1">
                        <span
                            class="input-group-text border-dark border-3 bg-warning-subtle adjust-width w-25">Suffix</span>
                        <input class="form-control bg-warning-subtle border-warning border" type="text"
                            aria-label="Suffix" aria-describedby="suffix" id="suffix" name="suffix" placeholder="..."
                            value="{{ request('suffix') }}">
                    </div>
                </div>
                <div class="col-md-1 my-auto">
                    <button class="btn btn-primary" type="submit">Search</button>
                </div>
            </div>
        </form>
        <div class="table-responsive mb-5 pb-3 pt-1" style="max-height: 300px; overflow-y: auto;">
            <table class="table table-bordered table-hover align-middle text-center">
                <thead class="table-secondary position-sticky top-0">
                    <tr>
                        <th>Model</th>
                        <th>No. Part</th>
                        <th>Part Name</th>
                        <th>Suffix</th>
                        <th>No. ECI/EO/ECN</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($massproRecords as $index => $record)
                        <tr>
                            <td>{{ $record->model }}</td>
                            <td>{{ $record->part_number }}</td>
                            <td>{{ $record->part_name }}</td>
                            <td>{{ $record->suffix }}</td>
                            <td>{{ $record->eee_number }}</td>
                            <td>
                                <a href="{{ route('masspro.view', array_merge(['project' => $record->id], request()->query())) }}"
                                    class="btn btn-sm btn-primary">
                                    View Stage
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                @if (request()->anyFilled(['customer', 'model', 'part_number', 'suffix']))
                                    No Mass Production records found.
                                @else
                                    Silakan masukkan kriteria pencarian untuk menampilkan data.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
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
        </div>
    </div>
    <x-toast />
@endsection
