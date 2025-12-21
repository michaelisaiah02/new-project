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
                            @forelse ($newProjects as $newProject)
                                <tr class="">
                                    <td style="width: 4%" class="text-center" scope="row">{{ $loop->iteration }}</td>
                                    <td style="width: 11%">{{ $newProject->customer_code }}</td>
                                    <td style="width: 10%">{{ $newProject->model }}</td>
                                    <td class="d-flex align-items-center justify-content-between">
                                        <a href="{{ route('engineering.projects.new', ['project' => $newProject->id]) }}">
                                            {{ $newProject->part_number }} - {{ $newProject->part_name }} -
                                            {{ $newProject->part_type }} - {{ $newProject->suffix }} -
                                            {{ $newProject->minor_change }}
                                        </a>
                                        <button class="btn btn-sm btn-primary show-project me-3" data-bs-toggle="modal"
                                            data-bs-target="#showProjectModal{{ $newProject->id }}">
                                            Show Detail
                                        </button>
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
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No New projects found.</td>
                                    </tr>
                                @endforelse
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
                                @forelse ($ongoingProjects as $ongoingProject)
                                    <tr>
                                        <td style="width: 4%" class="text-center align-middle" scope="row">
                                            {{ $loop->iteration }}</td>
                                        <td style="width: 11%" class="align-middle">{{ $ongoingProject->customer_code }}</td>
                                        <td style="width: 10%" class="align-middle">{{ $ongoingProject->model }}</td>
                                        <td class="d-flex align-items-center justify-content-between">
                                            <a class="align-middle"
                                                href="{{ route('engineering.projects.onGoing', ['project' => $ongoingProject->id]) }}">
                                                {{ $ongoingProject->part_number }} - {{ $ongoingProject->part_name }} -
                                                {{ $ongoingProject->part_type }} - {{ $ongoingProject->suffix }} -
                                                {{ $ongoingProject->minor_change }}
                                            </a>
                                            <button class="btn btn-sm btn-primary show-project me-3" data-bs-toggle="modal"
                                                data-bs-target="#showProjectModal{{ $ongoingProject->id }}">
                                                Show Detail
                                            </button>
                                        </td>
                                        <td style="width: 10%" class="align-middle">
                                            {{ $ongoingProject->created_at->format('d-m-Y') }}</td>
                                        <td style="width: 15%" class="align-middle">
                                            {{ $ongoingProject->statusOngoing() }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No Ongoing projects found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="row justify-content-between align-items-center position-absolute bottom-0 start-0 end-0 mx-0 px-0 mb-1">
                @if (auth()->user()->department->name === 'Management')
                    <div class="col-auto px-0">
                        <a href="{{ route('management') }}" class="btn btn-primary border-3 border-light-subtle ms-auto">
                            Back
                        </a>
                    </div>
                @else
                    <div class="col-auto px-0">
                        <form action="{{ route('logout') }}" method="post">
                            @csrf
                            <button type="submit" class="btn btn-primary border-3 border-light-subtle">LOGOUT</button>
                        </form>
                    </div>
                @endif
                <div class="col-auto px-0">
                    <a href="{{ route('masspro.index') }}" class="btn btn-primary border-3 border-light-subtle ms-auto">List
                        Mass Production Part</a>
                </div>
            </div>
        </div>
        @foreach ($newProjects as $newProject)
            @include('engineering.projects.partials.data-projects-modal', ['project' => $newProject])
        @endforeach
        @foreach ($ongoingProjects as $ongoingProject)
            @include('engineering.projects.partials.data-projects-modal', ['project' => $ongoingProject])
        @endforeach
        <div class="modal fade" id="fileViewerModal" tabindex="-1">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title" id="fileViewerTitle">File Viewer</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body p-0" style="height: 80vh;">
                        <div id="fileViewerContainer"
                            class="w-100 h-100 d-flex justify-content-center align-items-center bg-light">
                            <!-- injected by JS -->
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <x-toast />
    @endsection
    @section('scripts')
        <script type="module">
            const viewerModal = new bootstrap.Modal('#fileViewerModal')
            const container = document.getElementById('fileViewerContainer')
            const titleEl = document.getElementById('fileViewerTitle')

            document.querySelectorAll('.view-file').forEach(btn => {
                btn.addEventListener('click', () => {
                    const file = btn.dataset.file
                    const title = btn.dataset.title
                    console.log('title', title)
                    const ext = file.split('.').pop().toLowerCase()

                    titleEl.innerText = title
                    container.innerHTML = ''

                    // IMAGE
                    if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
                        container.innerHTML = `
                <img src="${file}" class="img-fluid" style="max-height:100%;" />
            `
                    }

                    // PDF
                    else if (ext === 'pdf') {
                        container.innerHTML = `
                <iframe src="${file}" style="width:100%; height:100%; border:none;"></iframe>
            `
                    }

                    // 3D FILE
                    else if (['stp', 'step', 'iges', 'igs', 'stl'].includes(ext)) {
                        container.innerHTML = `
                <div class="text-center">
                    <i class="bi bi-cube fs-1 mb-3"></i>
                    <p class="fw-bold">3D File Detected</p>
                    <a href="${file}" class="btn btn-primary" target="_blank">
                        Download & Open in 3D Viewer
                    </a>
                </div>
            `
                    }

                    // UNKNOWN
                    else {
                        container.innerHTML = `
                <div class="text-center text-danger">
                    <p>File format not supported for preview</p>
                    <a href="${file}" class="btn btn-secondary">Download</a>
                </div>
            `
                    }

                    viewerModal.show()
                })
            })
        </script>
    @endsection
