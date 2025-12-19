@extends('layouts.app-projects')
@section('title', 'ON-GOING PROJECT')
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
        <div class="table-responsive mb-5 pb-3 pt-1" style="max-height: 400px; overflow-y: auto;">
            <table class="table table-sm table-bordered m-0 text-start align-middle">
                <thead class="table-primary sticky-top">
                    <tr>
                        <th class="text-center">Stage</th>
                        <th>Document</th>
                        <th class="text-center">Due Date</th>
                        <th class="text-center">Actual Date</th>
                        <th class="text-center">File Name Upload</th>
                        <th class="text-center">Action</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Remark</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($projectDocuments as $stageId => $docs)
                        @foreach ($docs as $index => $pd)
                            <tr>
                                @if ($index === 0)
                                    <td rowspan="{{ $docs->count() }}" class="text-center">
                                        <p>Stage
                                            {{ $pd->stage->stage_number }}</p>
                                        <p>
                                            {{ $pd->stage->stage_name }}</p>
                                    </td>
                                @endif

                                <td class="text-wrap w-25">
                                    {{ $pd->documentType->name }}
                                </td>

                                <td class="text-center">
                                    {{ \Carbon\Carbon::parse($pd->due_date)->locale('id')->translatedFormat('d/m/Y') }}
                                </td>
                                <td class="text-center">
                                    @if ($pd->actual_date)
                                        {{ \Carbon\Carbon::parse($pd->actual_date)->locale('id')->translatedFormat('d/m/Y') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($pd->file_name)
                                        {{ $pd->file_name }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('engineering.project-documents.view', ['projectDocument' => $pd->id]) }}"
                                        class="btn btn-sm btn-primary btn-view" data-filename="{{ $pd->file_name }}">
                                        View
                                    </a>
                                    @if (!(auth()->user()->approved || auth()->user()->checked))
                                        <button type="button" class="btn btn-sm btn-primary border-3 border-light-subtle"
                                            id="btn-upload-{{ $pd->id }}">Upload</button>
                                        <input type="file"
                                            class="form-control bg-secondary-subtle border-secondary border"
                                            id="upload-{{ $pd->id }}" accept="application/pdf" hidden>
                                    @endif
                                </td>
                                <td class="text-center" id="status-{{ $pd->id }}">
                                    @php
                                        $now = now();

                                        if (!$pd->file_name) {
                                            // belum submit
                                            if ($pd->due_date && $now->gt($pd->due_date)) {
                                                $status = 'Delay';
                                            } else {
                                                $status = 'Not Yet Submitted';
                                            }
                                        } else {
                                            // sudah submit
                                            if ($pd->approved_date !== null) {
                                                $status = 'Finish';
                                            } elseif (!$pd->checked_date) {
                                                $status = 'Not Yet Checked';
                                            } else {
                                                $status = 'Not Yet Approved';
                                            }
                                        }
                                    @endphp
                                    {{ $status }}
                                </td>
                                <td>
                                    <input type="text" id="remark-{{ $pd->id }}"
                                        class="form-control form-control-sm border-0" value="{{ $pd->remark }}">
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="row align-items-center position-absolute bottom-0 start-0 end-0 mx-0 px-0 mb-2">
            <div class="col-auto">
                <a href="{{ route('engineering') }}" class="btn btn-primary">Back</a>
            </div>
            <div class="col-auto">
                <button href="{{ route('engineering') }}" class="btn btn-danger" data-bs-toggle="modal"
                    data-bs-target="#cancelModal">Cancel Project</button>
            </div>
            <div class="col-auto mx-auto">
                <button type="button" class="btn btn-primary btn-show-project" data-bs-toggle="modal"
                    data-bs-target="#showProjectModal">
                    Show Details
                </button>
            </div>
            @if ($project->canShowCheckedButton(auth()->user()))
                <div class="col-auto">
                    <button class="btn btn-primary" id="btn-check">Checked</button>
                </div>
            @endif
            @if ($project->canShowApprovedButton(auth()->user()))
                <div class="col-auto">
                    <button class="btn btn-primary"
                        {{ auth()->user()->department->type() === 'management' ? 'id=btn-approve-management' : 'id=btn-approve' }}>Approved</button>
                </div>
            @endif
        </div>
    </div>
    <div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Are you sure?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Cancel Project -
                        {{ $project->part_number }}-{{ $project->suffix }}-{{ $project->minor_change }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <form action="{{ route('engineering.projects.cancel', ['project' => $project->id]) }}"
                        method="post">
                        @csrf
                        <button type="submit" class="btn btn-primary">Confirm</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    @include('engineering.projects.partials.data-project-modal', ['project' => $project])
    <x-toast />
@endsection

@section('scripts')
    <script type="module">
        function showToast(type, message) {
            const icons = {
                success: 'bi-check-square-fill text-success',
                error: 'bi-x-square-fill text-danger',
                warning: 'bi-exclamation-triangle-fill text-warning'
            };

            const bg = {
                success: 'text-bg-success',
                error: 'text-bg-danger',
                warning: 'text-bg-warning'
            };

            const toastId = `toast-${Date.now()}`;

            const toastHtml = `
                <div class="toast-container position-absolute top-50 end-0 translate-middle-y p-3">
                    <div id="${toastId}" class="toast align-items-center ${bg[type]} border-0" role="alert">
                    <div class="toast-header">
                        <i class="bi ${icons[type]} me-1"></i>
                        <strong class="me-auto">{{ config('app.name') }} - ${type.charAt(0).toUpperCase() + type.slice(1)}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                    </div>
                    <div class="toast-body">
                        ${message}
                    </div>
                    </div>
                </div>
            `;

            document.body.insertAdjacentHTML('beforeend', toastHtml);

            const toastEl = document.getElementById(toastId);
            const toast = new bootstrap.Toast(toastEl, {
                delay: 4000
            });
            toast.show();

            toastEl.addEventListener('hidden.bs.toast', () => {
                toastEl.closest('.toast-container').remove();
            });
        }

        $(document).ready(function() {
            // Kalau belum ada file yang diupload, tombol view disable
            $('a.btn-view').each(function() {
                const row = $(this).closest('tr');
                const fileName = $(this).data('filename');
                if (!fileName) {
                    $(this).addClass('disabled');
                } else {
                    $(this).removeClass('disabled');
                }
            });
            // Trigger file input
            $('button[id^="btn-upload-"]').on('click', function() {
                const id = $(this).attr('id').replace('btn-upload-', '');
                $('#upload-' + id).click();
            });

            // Upload file AJAX
            $('input[type="file"]').on('change', function() {
                const id = $(this).attr('id').replace('upload-', '');
                const file = this.files[0];

                if (!file) return;

                let formData = new FormData();
                formData.append('file', file);

                $.ajax({
                    url: `/engineering/project-documents/${id}/upload`,
                    method: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(res) {
                        location.reload();
                    },
                    error: function(xhr) {
                        let message = 'File upload failed. Please try again.';

                        if (xhr.responseJSON?.message) {
                            message = xhr.responseJSON.message;
                        }

                        showToast('error', message);
                    }
                });
            });

            // Auto save remark
            $('input[id^="remark-"]').on('blur', function() {
                const id = $(this).attr('id').replace('remark-', '');
                const remark = $(this).val();

                $.ajax({
                    url: `/engineering/project-documents/${id}/remark`,
                    method: 'POST',
                    data: {
                        remark
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
            });

            // Checked button
            $('#btn-check').on('click', function() {
                $.post(
                    `/engineering/projects/{{ $project->id }}/checked/ongoing`, {
                        _token: '{{ csrf_token() }}'
                    },
                    () => {
                        location.reload();
                    }
                ).fail(res => alert(res.responseJSON.message));
            });

            // Approved button
            $('#btn-approve, #btn-approve-management').on('click', function() {
                $.post(
                    `/engineering/projects/{{ $project->id }}/approved/ongoing`, {
                        _token: '{{ csrf_token() }}'
                    },
                    () => {
                        location.reload();
                    }
                ).fail(res => alert(res.responseJSON.message));
            });
        });
    </script>
@endsection
