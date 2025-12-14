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
              <tr>
                <th scope="col">NO</th>
                <th scope="col">Costumer</th>
                <th scope="col">Model</th>
                <th scope="col">Part</th>
                <th scope="col">Date</th>
                <th scope="col">Remark</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($newProjects as $newProject)
                <tr class="">
                  <td scope="row">{{ $loop->iteration }}</td>
                  <td>{{ $newProject->customer_code }}</td>
                  <td>{{ $newProject->model }}</td>
                  <td>
                    <a href="{{ route('engineering.projects.new', ['project' => $newProject->part_number]) }}">
                      {{ $newProject->part_number }} - {{ $newProject->part_name }} -
                      {{ $newProject->part_type }}
                    </a>
                  </td>
                  <td>{{ $newProject->created_at->format('d-m-Y') }}</td>
                  <td>
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
                <th scope="col">NO</th>
                <th scope="col">Costumer</th>
                <th scope="col">Model</th>
                <th scope="col">Part Number</th>
                <th scope="col">Date</th>
                <th scope="col">Remark</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($ongoingProjects as $ongoingProject)
                <tr class="">
                  <td scope="row">{{ $loop->iteration }}</td>
                  <td>{{ $ongoingProject->customer_code }}</td>
                  <td>{{ $ongoingProject->model }}</td>
                  <td>
                    <a href="{{ route('engineering.projects.onGoing', ['project' => $ongoingProject->part_number]) }}">
                      {{ $ongoingProject->part_number }} - {{ $ongoingProject->part_name }} -
                      {{ $ongoingProject->part_type }}
                    </a>
                  </td>
                  <td>{{ $ongoingProject->created_at->format('d-m-Y') }}</td>
                  <td>On Going</td> {{-- Harusnya Persentase dari document yg udah di upload --}}
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
      <div class="col-auto px-0">
        <a href="" class="btn btn-primary border-3 border-light-subtle ms-auto">List Mass Production Part</a>
      </div>
    </div>
  </div>
@endsection
