@extends('layouts.app-projects')
@section('title', 'ON-GOING PROJECT')
@section('customer', $projectDocument->project->customer->name)
@section('styles')
    <style>
        .file-viewer {
            width: 100%;
            height: calc(100vh - 220px);
            /* sisain space header + tombol */
            display: flex;
            justify-content: left;
            align-items: center;
            background: #f8f9fa;
        }

        .file-viewer iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        .file-viewer img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
    </style>
@endsection
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
                        value="{{ $projectDocument->project->model }}" id="model" placeholder="Model Part"
                        aria-label="Model" aria-describedby="model" readonly>
                </div>
            </div>
            <div class="col-md-4">
                <div class="input-group mb-1">
                    <span class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width">No. Part</span>
                    <input type="text" class="form-control bg-secondary-subtle border-secondary border" id="part-number"
                        placeholder="Nomor Part" aria-label="No. Part" aria-describedby="part-number"
                        value="{{ $projectDocument->project->part_number }}" readonly>
                </div>
            </div>
            <div class="col-md-4">
                <div class="input-group mb-1">
                    <span class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width">Part
                        Name</span>
                    <input type="text" class="form-control bg-secondary-subtle border-secondary border" id="part-name"
                        placeholder="Nama Part" aria-label="Part Name" aria-describedby="part-name"
                        value="{{ $projectDocument->project->part_name }}" readonly>
                </div>
            </div>
        </div>
        <div class="row mb-2 ms-0 justify-content-start align-items-center">
            <div class="col-auto border-0 shadow-sm bg-secondary-subtle bg-gradient rounded-2">
                <p class="fs-4 p-0 m-0">Documents : {{ $projectDocument->documentType->name }}</p>
            </div>
            <div class="col-auto">
                <button class="btn btn-primary w-100 my-0" id="btn-download">Download</button>
            </div>
        </div>
        <div id="fileViewerContainer" class="file-viewer bg-light">
        </div>
        <div class="row justify-content-end align-items-center position-absolute bottom-0 start-0 end-0 mx-0 px-0 mb-2">
            <div class="col-2 text-center">
                <button class="btn btn-primary w-100 mb-1" id="btn-check" hidden>Checked</button>
                <button class="btn btn-primary w-100 mb-1" id="btn-approve" hidden>Approved</button>
                <a href="{{ route('engineering.projects.onGoing', ['project' => $projectDocument->project->part_number]) }}"
                    class="btn btn-primary w-100">Back</a>
            </div>
        </div>
    </div>
    <x-toast />
@endsection

@section('scripts')
    <script type="module">
        function checkApproval() {
            const userCanCheck = @json(auth()->user()->checked);
            const userCanApprove = @json(auth()->user()->approved);
            const userDepartment = @json(auth()->user()->department->type());
            const isChecked = @json($projectDocument->checked);
            const isApproved = @json($projectDocument->approved);
            console.log(userCanCheck, userCanApprove, userDepartment, isChecked, isApproved);
            if (userCanCheck && !isChecked && userDepartment === 'engineering') {
                $('#btn-check').removeAttr('hidden');
            }
            if (userCanApprove && isChecked && !isApproved && (userDepartment === 'engineering' || userDepartment ===
                    'management')) {
                $('#btn-approve').removeAttr('hidden');
            }
        }
        $(function() {
            const fileName = "{{ $projectDocument->file_name }}";
            const $viewerContainer = $('#fileViewerContainer');

            if (!fileName) {
                $viewerContainer.text('No file uploaded.');
                return;
            }

            const customerCode = "{{ $projectDocument->project->customer->code }}";
            const model = "{{ $projectDocument->project->model }}";
            const partNumber = "{{ $projectDocument->project->part_number }}";

            const fileUrl = `/storage/${customerCode}/${model}/${partNumber}/${fileName}`;
            const ext = fileName.split('.').pop().toLowerCase();

            let $viewer;

            if (ext === 'pdf') {
                $viewer = $('<iframe>', {
                    src: fileUrl
                });
            } else if (['jpg', 'jpeg', 'png', 'webp', 'gif'].includes(ext)) {
                $viewer = $('<img>', {
                    src: fileUrl
                });
            } else {
                $viewer = $('<p>').text('Unsupported file format');
            }

            $viewerContainer.append($viewer);
        });
        $(document).ready(function() {
            const csrf = '{{ csrf_token() }}';
            const docId = '{{ $projectDocument->id }}';

            $('#btn-check').on('click', function() {
                $.post(
                    `/engineering/project-documents/${docId}/checked`, {
                        _token: csrf
                    },
                    () => {
                        location.reload();
                    }
                ).fail(res => alert(res.responseJSON.message));
            });

            $('#btn-approve').on('click', function() {
                $.post(
                    `/engineering/project-documents/${docId}/approved`, {
                        _token: csrf
                    },
                    () => {
                        location.reload();
                    }
                ).fail(res => alert(res.responseJSON.message));
            });

            $('#btn-download').on('click', function() {
                const customerCode = "{{ $projectDocument->project->customer->code }}";
                const model = "{{ $projectDocument->project->model }}";
                const partNumber = "{{ $projectDocument->project->part_number }}";
                const fileName = "{{ $projectDocument->file_name }}";

                if (!fileName) {
                    alert('No file uploaded.');
                    return;
                }

                const fileUrl = `/storage/${customerCode}/${model}/${partNumber}/${fileName}`;
                const link = document.createElement('a');
                link.href = fileUrl;
                link.download = fileName;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
            checkApproval();
        });
    </script>
@endsection
