@extends('layouts.app')
@section('title', 'INFORMATION')
@section('content')
    <div class="container-fluid mt-2">
        <div class="card mb-2">
            <div class="card-body p-2">
                <span class="badge bg-none text-dark shadow fs-5 border-2 border-secondary-subtle mb-1">New Project</span>
                <div class="table-responsive overflow-y-auto" style="max-height: 135px;">
                    <table class="table table-sm table-bordered table-hover m-0">
                        <thead class="table-secondary sticky-top">
                            <tr class="text-center">
                                <th scope="col">NO</th>
                                <th scope="col">Costumer</th>
                                <th scope="col">Model</th>
                                <th scope="col" class="text-start">Part</th>
                                <th scope="col">Date</th>
                                <th scope="col">Remark</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($newProjects as $newProject)
                                <tr class="">
                                    <td style="width: 4%" class="text-center" scope="row">{{ $loop->iteration }}</td>
                                    <td style="width: 11%">{{ $newProject->customer_code }}</td>
                                    <td style="width: 10%">{{ $newProject->model }}</td>
                                    <td class="w-50">
                                        <a href="{{ route('engineering.projects.new', ['project' => $newProject->id]) }}">
                                            {{ $newProject->part_number }} - {{ $newProject->part_name }} -
                                            {{ $newProject->part_type }} - {{ $newProject->suffix }} -
                                            {{ $newProject->minor_change }}
                                        </a>
                                    </td>
                                    <td style="width: 10%">{{ $newProject->created_at->format('d-m-Y') }}</td>
                                    <td style="width: 15%">
                                        @switch($newProject->remark)
                                            @case('new')
                                                New
                                            @break

                                            @case('not checked')
                                                Not Yet Checked
                                            @break

                                            @case('not approved')
                                                Not Yet Approved
                                            @break

                                            @case('not approved management')
                                                Not Yet Approved by Management
                                            @break

                                            @default
                                        @endswitch
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
        <div class="card mb-1">
            <div class="card-body p-2">
                <span class="badge bg-none text-dark shadow fs-5 border-2 border-secondary-subtle mb-1">On Going
                    Project</span>
                <div class="table-responsive overflow-y-auto" style="max-height: 200px;">
                    <table class="table table-sm table-bordered table-hover m-0">
                        <thead class="table-secondary sticky-top">
                            <tr>
                                <th scope="col" class="text-center">NO</th>
                                <th scope="col">Costumer</th>
                                <th scope="col">Model</th>
                                <th scope="col">Part</th>
                                <th scope="col">Date</th>
                                <th scope="col">Remark</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($ongoingProjects as $ongoingProject)
                                <tr>
                                    <td style="width: 4%" class="text-center" scope="row">{{ $loop->iteration }}</td>
                                    <td style="width: 11%">{{ $ongoingProject->customer_code }}</td>
                                    <td style="width: 10%">{{ $ongoingProject->model }}</td>
                                    <td class="w-50">
                                        <a
                                            href="{{ route('engineering.projects.onGoing', ['project' => $ongoingProject->id]) }}">
                                            {{ $ongoingProject->part_number }} - {{ $ongoingProject->part_name }} -
                                            {{ $ongoingProject->part_type }} - {{ $ongoingProject->suffix }} -
                                            {{ $ongoingProject->minor_change }}
                                        </a>
                                    </td>
                                    <td style="width: 10%">{{ $ongoingProject->created_at->format('d-m-Y') }}</td>
                                    <td style="width: 15%">On Going</td> {{-- Harusnya Persentase dari document yg udah di upload --}}
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="row justify-content-between align-items-center position-absolute bottom-0 start-0 end-0 mx-0 px-0 mb-1">
            <div class="col-auto px-0">
                <form action="{{ route('logout') }}" method="post">
                    @csrf
                    <button type="submit" class="btn btn-primary border-3 border-light-subtle">LOGOUT</button>
                </form>
            </div>
            @if (auth()->user()->department->name === 'Management')
                <div class="col-auto px-0">
                    <a href="{{ route('management') }}" class="btn btn-primary border-3 border-light-subtle ms-auto">
                        Back
                    </a>
                </div>
            @endif
            <div class="col-auto px-0">
                <a href="{{ route('masspro.index') }}" class="btn btn-primary border-3 border-light-subtle ms-auto">List
                    Mass Production Part</a>
            </div>
        </div>
    </div>
    <x-toast />
@endsection
