@extends('layouts.app')
@section('title', 'ADD NEW CUSTOMER STAGES')
@section('content')
    <div class="container-fluid">
        <form
            action="{{ route('marketing.customers.saveStage', ['customer' => $customer->code, 'stageNumber' => $stageNumber]) }}"
            method="post">
            @csrf
            @method('PUT')
            <input type="hidden" name="customer_code" value="{{ $customer->code }}">
            <input type="hidden" name="stage_number" value="{{ $stageNumber }}">
            <div class="row mb-3 justify-content-between align-items-center">
                <label for="stage_number" class="col-md-auto col-form-label">Select Document Requirement for Stage
                    {{ $stageNumber }}</label>
                <div class="col-md-3 me-auto">
                    <input type="text" class="form-control form-control-sm bg-secondary-subtle border-3 border-dark"
                        id="stage_name" name="stage_name" value="{{ $stage->stage_name }}">
                </div>
                <label for="stage_name" class="col-md-auto col-form-label">Customer Name</label>
                <div class="col-md-3">
                    <input type="text" class="form-control form-control-sm bg-secondary-subtle border-3 border-dark"
                        id="stage_name" value="{{ $customer->name }}" readonly>
                    <div class="invalid-feedback">Stage Name is required.</div>
                </div>
            </div>
            <div class="row mb-3">
                <div class="table-responsive overflow-y-auto" style="max-height: 400px;">
                    <table class="table table-sm table-bordered table-striped-columns m-0 text-center">
                        <thead class="table-primary sticky-top align-middle">
                            <tr>
                                <th rowspan="2"></th>
                                <th rowspan="2">Jenis Dokumen</th>
                                <th colspan="4">Posisi QR Code</th>
                            </tr>
                            <tr>
                                <th>Atas Kiri</th>
                                <th>Atas Kanan</th>
                                <th>Bawah Kiri</th>
                                <th>Bawah Kanan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($availableDocuments as $doc)
                                <tr class="text-center align-middle">
                                    <td>
                                        <input type="checkbox" class="doc-check" name="document_type_ids[]"
                                            value="{{ $doc->id }}"
                                            {{ $currentDocs->contains($doc->id) ? 'checked' : '' }}>
                                    </td>
                                    <td>{{ $doc->name }}</td>
                                    @foreach (['above_left', 'above_right', 'below_left', 'below_right'] as $position)
                                        <td>
                                            <input type="radio" class="qr-option" name="qr_position[{{ $doc->id }}]"
                                                value="{{ $position }}"
                                                {{ $stage->documents->firstWhere('id', $doc->id)?->pivot->qr_position == $position ? 'checked' : '' }}>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div
                class="text-center row justify-content-between align-items-start position-absolute bottom-0 start-0 end-0 mb-3 mx-3">
                <div class="col-auto">
                    <a href="{{ route('marketing.customers.index') }}"
                        class="btn btn-primary border-3 border-light-subtle">Back</a>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-danger border-3 border-light-subtle" id="delete-stage-btn">
                        Delete Stage
                    </button>
                </div>
                <div class="col-auto ms-auto">
                    <input type="hidden" name="target_stage" id="target_stage" value="{{ $stageNumber }}">
                    <div class="input-group">
                        <span class="input-group-text bg-primary text-light user-select-none">Stage</span>
                        @for ($i = 1; $i <= $maxStage; $i++)
                            <button type="submit" name="action" value="navigate"
                                class="btn btn-primary border-light-subtle {{ $i == $stageNumber ? 'active fw-bold' : '' }}"
                                onclick="document.getElementById('target_stage').value={{ $i }}">
                                {{ $i }}
                            </button>
                        @endfor
                    </div>
                </div>
                <div class="col-auto ms-auto">
                    @if ($canAddStage)
                        <button type="submit" name="action" value="add_stage" class="btn btn-success">
                            + Add Stage
                        </button>
                    @else
                        <button class="btn btn-secondary" disabled>
                            All documents already used
                        </button>
                    @endif
                </div>
                <div class="col-auto">
                    <button type="submit" name="action" value="save"
                        class="btn btn-primary border-3 border-light-subtle">
                        Save
                    </button>
                </div>
                <div class="col-auto">
                    <button type="submit" name="action" value="finish"
                        class="btn btn-primary border-3 border-light-subtle">
                        Finish
                    </button>
                </div>
            </div>
        </form>
    </div>
    <!-- Modal Confirm Delete Stage -->
    <div class="modal fade" id="confirmDeleteStageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-6 mb-0">Delete Stage</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this stage?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="button" class="btn btn-danger" id="confirm-delete-stage">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
    <x-toast />
@endsection
@section('scripts')
    <script type="module">
        $(function() {
            const $btn = $('#delete-stage-btn');
            if (!$btn.length) return;

            $btn.on('click', function() {
                const $trigger = $(this);

                if (!$trigger.data('confirmed')) {
                    let $modal = $('#confirmDeleteStageModal');

                    const modal = bootstrap.Modal.getOrCreateInstance($modal[0]);

                    $modal.off('click.confirmDelete').on('click.confirmDelete', '#confirm-delete-stage',
                        function() {
                            $trigger.data('confirmed', true);
                            modal.hide();
                            $trigger.trigger('click');
                        });

                    modal.show();
                    return;
                }

                $trigger.data('confirmed', false);

                $('<form>', {
                        method: 'POST',
                        action: @json(route('marketing.customers.destroyStage', ['customer' => $customer->code, 'stageNumber' => $stageNumber]))
                    })
                    .css('display', 'none')
                    .append($('<input>', {
                        type: 'hidden',
                        name: '_token',
                        value: @json(csrf_token())
                    }))
                    .append($('<input>', {
                        type: 'hidden',
                        name: '_method',
                        value: 'DELETE'
                    }))
                    .appendTo('body')
                    .trigger('submit');
            });
        });
        $(document).ready(function() {

            $('.doc-check').each(function() {
                let row = $(this).closest('tr');
                if ($(this).is(':checked')) {
                    row.find('.qr-option').prop('disabled', false);
                } else {
                    row.find('.qr-option').prop('disabled', true);
                }
            });

            // ketika checkbox dokumen di klik
            $('.doc-check').on('change', function() {
                let row = $(this).closest('tr');

                if ($(this).is(':checked')) {
                    // enable radio positions
                    row.find('.qr-option').prop('disabled', false);
                    row.find('.qr-option').first().prop('checked', true);
                } else {
                    // uncheck radio + disable
                    row.find('.qr-option').prop('checked', false).prop('disabled', true);
                }
            });

            // handle submit
            $('form').on('submit', function(e) {
                let valid = true;
                let message = '';

                $('.doc-check:checked').each(function() {
                    let row = $(this).closest('tr');
                    let hasQR = row.find('.qr-option:checked').length > 0;

                    if (!hasQR) {
                        valid = false;
                        message = "Setiap dokumen yang dipilih harus punya posisi QR ya 😅";
                    }
                });

                if (!valid) {
                    e.preventDefault();
                    alert(message);
                }
            });

        });
    </script>
@endsection
