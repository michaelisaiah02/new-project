@extends('layouts.app-projects')
@section('title', 'NEW PROJECT')
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
        <form action="{{ route('engineering.projects.updateDueDates', ['project' => $project->id]) }}" method="post">
            @csrf

            <div class="table-responsive mb-5 pb-3 pt-1" style="max-height: 320px; overflow-y: auto;">
                <table class="table table-sm table-bordered m-0 text-start align-middle">
                    <thead class="table-primary sticky-top">
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
                                            <span
                                                class="card border-dark border-3 bg-secondary-subtle adjust-width p-1 text-center">Stage
                                                {{ $pd->stage->stage_number }}</span>
                                            <span class="card border-dark border adjust-width mt-1 px-1 text-center">
                                                {{ $pd->stage->stage_name }}</span>
                                        </td>
                                    @endif

                                    <td class="w-75">
                                        {{ $pd->documentType->name }}
                                    </td>

                                    <td>
                                        <input type="date" name="due_dates[{{ $pd->id }}]"
                                            value="{{ $pd->due_date?->toDateString() }}" data-id="{{ $pd->id }}"
                                            class="form-control form-control-sm due-date-input bg-secondary-subtle border-3 border-dark text-center"
                                            min="{{ now()->toDateString() }}"
                                            {{ auth()->user()->approved || auth()->user()->checked ? 'readonly' : '' }}>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="row align-items-center position-absolute bottom-0 start-0 end-0 mx-0 px-0 mb-2">
                <div class="col-md">
                    <ul class="list-group list-group-horizontal position-relative text-center">
                        <li class="list-group-item px-1 py-0">
                            <div class="mx-1">
                                <div class="fw-bold">Created By</div>
                                {{ $project->approvalStatus->created_by_name }} -
                                {{ Carbon\Carbon::parse($project->approvalStatus->created_date)->locale('id')->translatedFormat('d/M/Y') }}
                            </div>
                        </li>
                        <li class="list-group-item px-1 py-0">
                            <div class="mx-1">
                                <div class="fw-bold">Checked By</div>
                                {{ $project->approvalStatus->checked_by_name }} -
                                @if ($project->approvalStatus->checked_date)
                                    {{ \Carbon\Carbon::parse($project->approvalStatus->checked_date)->locale('id')->translatedFormat('d/M/Y') }}
                                @endif
                            </div>
                        </li>
                        <li class="list-group-item px-1 py-0">
                            <div class="mx-1">
                                <div class="fw-bold">Approved By</div>
                                {{ $project->approvalStatus->approved_by_name }} -
                                @if ($project->approvalStatus->approved_date)
                                    {{ \Carbon\Carbon::parse($project->approvalStatus->approved_date)->locale('id')->translatedFormat('d/M/Y') }}
                                @endif
                            </div>
                        </li>
                        <li class="list-group-item px-1 py-0">
                            <div class="mx-1">
                                <div class="fw-bold">Management Approved By</div>
                                {{ $project->approvalStatus->management_approved_by_name }} -
                                @if ($project->approvalStatus->management_approved_date)
                                    {{ \Carbon\Carbon::parse($project->approvalStatus->management_approved_date)->locale('id')->translatedFormat('d/M/Y') }}
                                @endif
                            </div>
                        </li>
                        <span class="position-absolute translate-middle badge rounded-3 bg-primary fs-6"
                            style="bottom: 65% !important; left: 8% !important;">
                            Approval History
                            <span class="visually-hidden">unread messages</span>
                        </span>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="{{ route('engineering') }}" class="btn btn-primary">Back</a>
                </div>
                @if ($canCheck)
                    <div class="col-auto">
                        <button type="button" id="btnChecked" class="btn btn-primary">Checked</button>
                    </div>
                @endif

                @if ($canApprove)
                    <div class="col-auto">
                        <button type="button" id="btnApproved" class="btn btn-primary">Approved</button>
                    </div>
                @endif

                @if ($canApproveManagement)
                    <div class="col-auto">
                        <button type="button" id="btnApprovedManagement" class="btn btn-primary">Approved</button>
                    </div>
                @endif

                @if (!$canCheck && !$canApprove && !$canApproveManagement)
                    <div class="col-auto">
                        <button id="btnSave" class="btn btn-primary" type="submit" disabled>
                            Save
                        </button>
                    </div>
                @endif
            </div>
        </form>
    </div>
    @include('engineering.projects.partials.data-project-modal', ['project' => $project])
    <x-toast />
@endsection

@section('scripts')
    <script type="module">
        function setInputDateReadOnlyForCheckedOrApproved() {
            @if (
                $project->approvalStatus->checked ||
                    $project->approvalStatus->approved ||
                    $project->approvalStatus->management_approved)
                $('.due-date-input').attr('readonly', true);
            @endif
        }

        function getFirstEmptyDueDate() {
            let emptyInput = null;

            $('.due-date-input').each(function() {
                if (!$(this).val() && !emptyInput) {
                    emptyInput = $(this);
                }
            });

            return emptyInput;
        }

        function showDueDateWarning() {
            window.dispatchEvent(new CustomEvent('toast', {
                detail: {
                    type: 'warning',
                    message: 'Masih ada Due Date yang belum diisi. Silakan lengkapi terlebih dahulu.'
                }
            }));
        }

        function scrollToInput(input) {
            $('html, body').animate({
                scrollTop: input.offset().top - 150
            }, 400);

            input.focus();
        }

        function approvalAction(type) {
            const PROJECT_ID = @json($project->id);
            $.ajax({
                url: "{{ route('engineering.projects.approval') }}",
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    project_id: PROJECT_ID,
                    action: type
                },
                success: function() {
                    location.reload(); // simple & aman
                }
            });
        }

        function toggleSaveButton() {
            // save boleh selalu aktif ATAU
            // aktif kalau ada perubahan — pilihan UX
            $('#btnSave').prop('disabled', false);
        }

        $(document).ready(function() {
            $('#btnChecked').on('click', function() {
                const emptyInput = getFirstEmptyDueDate();

                if (emptyInput) {
                    showDueDateWarning();
                    scrollToInput(emptyInput);
                    return;
                }
                approvalAction('checked');
            });

            $('#btnApproved').on('click', function() {
                const emptyInput = getFirstEmptyDueDate();

                if (emptyInput) {
                    showDueDateWarning();
                    scrollToInput(emptyInput);
                    return;
                }
                approvalAction('approved');
            });

            $('#btnApprovedManagement').on('click', function() {
                const emptyInput = getFirstEmptyDueDate();

                if (emptyInput) {
                    showDueDateWarning();
                    scrollToInput(emptyInput);
                    return;
                }
                approvalAction('approved_management');
            });

            $('.due-date-input').on('change', function() {
                toggleSaveButton();
            });

            setInputDateReadOnlyForCheckedOrApproved();
        });
    </script>
@endsection
