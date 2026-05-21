@extends('layouts.app')
@section('title', 'INFORMATION')

@section('content')
    <div class="container-fluid mt-2">
        <div class="card mb-2">
            <div class="card-body p-2">
                <span class="badge bg-none text-dark shadow fs-5 border-2 border-secondary-subtle mb-1">New Project</span>
                <div class="table-responsive overflow-y-auto" style="max-height: 135px;">
                    <table class="table table-sm table-bordered table-hover m-0 text-nowrap">
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
                        <table class="table table-sm table-bordered table-hover m-0 text-nowrap">
                            <thead class="table-secondary sticky-top">
                                <tr>
                                    <th scope="col" class="text-center">NO</th>
                                    <th scope="col">Costumer</th>
                                    <th scope="col">Model</th>
                                    <th scope="col">Part</th>
                                    <th scope="col">Date</th>
                                    <th scope="col">Progress</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($ongoingProjects as $ongoingProject)
                                    <tr>
                                        <td style="width: 4%" class="text-center align-middle" scope="row">
                                            {{ $loop->iteration }}</td>
                                        <td style="width: 11%" class="align-middle">{{ $ongoingProject->customer_code }}</td>
                                        <td style="width: 10%" class="align-middle">{{ $ongoingProject->model }}</td>
                                        <td class="d-flex align-items-center justify-content-between align-self-center align-middle"
                                            style="padding: 0.6rem 0.5rem;">
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
                                        <td class="align-middle m-0 py-0" style="width: 25%;">
                                            @php
                                                $prog = $ongoingProject->progress();
                                                $percent =
                                                    $prog['total'] > 0
                                                        ? round(($prog['finish'] / $prog['total']) * 100)
                                                        : 0;
                                            @endphp

                                            <div class="w-100 pe-2">
                                                <div class="d-flex justify-content-between align-items-end">
                                                    <span class="text-secondary fw-bold" style="font-size: 0.75rem;">
                                                        <i class="bi bi-file-earmark-text me-1"></i>{{ $prog['total'] }}
                                                        Documents
                                                    </span>
                                                    <span class="text-primary fw-bolder fs-6 lh-1">{{ $percent }}%</span>
                                                </div>

                                                <div class="progress rounded-pill my-1 shadow-sm"
                                                    style="height: 6px; background-color: #e9ecef;">
                                                    <div class="progress-bar bg-primary progress-bar-striped progress-bar-animated"
                                                        role="progressbar" style="width: {{ $percent }}%"></div>
                                                </div>

                                                <div class="d-flex gap-2 flex-wrap" style="font-size: 0.7rem;">

                                                    @if ($prog['finish'] > 0)
                                                        <span
                                                            class="badge bg-success-subtle text-success border border-success-subtle shadow-sm px-2"
                                                            title="Selesai">
                                                            <i class="bi bi-check2-all me-1"></i>{{ $prog['finish'] }} Finish
                                                        </span>
                                                    @endif

                                                    @if ($prog['delay'] > 0)
                                                        <span
                                                            class="badge bg-danger text-white border border-danger shadow-sm px-2"
                                                            title="Terlambat">
                                                            <i
                                                                class="bi bi-exclamation-triangle-fill me-1"></i>{{ $prog['delay'] }}
                                                            Delay
                                                        </span>
                                                    @endif

                                                    @if ($prog['unchecked'] > 0)
                                                        <span
                                                            class="badge bg-warning-subtle text-dark border border-warning-subtle shadow-sm px-2"
                                                            title="Menunggu Pengecekan">
                                                            <i class="bi bi-search me-1"></i>{{ $prog['unchecked'] }} Unchecked
                                                        </span>
                                                    @endif

                                                    @if ($prog['unapproved'] > 0)
                                                        <span
                                                            class="badge bg-info-subtle text-info-emphasis border border-info-subtle shadow-sm px-2"
                                                            title="Menunggu Approval">
                                                            <i class="bi bi-person-dash me-1"></i>{{ $prog['unapproved'] }}
                                                            Unapproved
                                                        </span>
                                                    @endif

                                                </div>
                                            </div>
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
                @if (in_array(auth()->user()->department->name, ['Management', 'Marketing']))
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
            <div class="modal-dialog modal-fullscreen modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title" id="fileViewerTitle">File Viewer</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body p-0">
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
            $(document).ready(function() {
                // --- 1. GLOBAL LOGIC: UPDATE 3D FILE NAME & SUBMIT ---
                $(document).on('change', '.file-upload-3d', function() {
                    const fileInput = this;
                    const $modal = $(this).closest('.modal');
                    const $labelInput = $modal.find('.target-label-3d');

                    if (!fileInput.files.length) {
                        $labelInput.val('');
                        return;
                    }

                    const extension = fileInput.files[0].name.split('.').pop().toLowerCase();
                    const partNum = $(this).data('part');
                    const suffix = $(this).data('suffix') || '';
                    const mc = $(this).data('mc') || '';

                    $labelInput.val(`Dwg3D-${partNum}-${suffix}-${mc}.${extension}`);
                });

                $(document).on('submit', '.form-update-3d', function(e) {
                    const $btn = $(this).find('.btn-submit-3d');
                    $btn.prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm me-2"></span>Uploading...');
                });

                // --- 2. GLOBAL LOGIC: FILE VIEWER ---

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
                });
            })
        </script>
    @endsection
