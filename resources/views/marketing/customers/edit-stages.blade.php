@extends('layouts.app')
@section('title', 'ADD NEW CUSTOMER STAGES')
@section('content')
    <div class="container-fluid">
        <form id="form-edit-stage"
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
                                <th rowspan="2">Jenis Dokumen
                                    <div class="row mx-1">
                                        <input type="text" id="doc-search" class="form-control form-control-sm"
                                            placeholder="Cari dokumen... (contoh: NPWP, DM, SIUP)">
                                    </div>
                                </th>
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
                                <tr class="text-center align-middle doc-row">
                                    <td>
                                        <input type="checkbox" class="doc-check" name="document_type_codes[]"
                                            value="{{ $doc->code }}"
                                            {{ $currentDocs->contains($doc->code) ? 'checked' : '' }}>
                                    </td>
                                    <td class="text-start">{{ $doc->name }}</td>
                                    @foreach (['top_left', 'top_right', 'bottom_left', 'bottom_right'] as $position)
                                        <td class="qr-cell">
                                            <input type="radio" class="qr-option" name="qr_position[{{ $doc->code }}]"
                                                value="{{ $position }}"
                                                {{ $stage->documents->firstWhere('code', $doc->code)?->pivot->qr_position == $position ? 'checked' : '' }}>
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
                    <a id="btn-back" href="{{ route('marketing.customers.index') }}"
                        class="btn btn-primary border-3 border-light-subtle">Back</a>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-danger border-3 border-light-subtle" id="delete-stage-btn">
                        Delete Stage
                    </button>
                </div>
                <div class="col-auto ms-auto">
                    <input type="hidden" name="target_stage" id="target_stage" value="{{ $stageNumber }}">
                    <input type="hidden" name="form_action" id="form_action">
                    <div class="input-group">
                        <span class="input-group-text bg-primary text-light user-select-none">Stage</span>
                        @for ($i = 1; $i <= $maxStage; $i++)
                            <button type="button" data-target-stage="{{ $i }}"
                                class="btn btn-primary btn-navigate border-light-subtle {{ $i == $stageNumber ? 'active fw-bold' : '' }}">
                                {{ $i }}
                            </button>
                        @endfor
                    </div>
                </div>
                <div class="col-auto ms-auto">
                    @if ($canAddStage)
                        <button id="add-stage" type="button" class="btn btn-success">
                            + Add Stage
                        </button>
                    @else
                        <button class="btn btn-secondary" disabled>
                            All documents already used
                        </button>
                    @endif
                </div>
                <div class="col-auto">
                    <button id="btn-save" type="button" class="btn btn-primary border-3 border-light-subtle">
                        Save
                    </button>
                </div>
                <div class="col-auto">
                    <button id="btn-finish" type="button" class="btn btn-primary border-3 border-light-subtle">
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
    <div class="modal fade" id="massproModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Required Document</h5>
                </div>
                <div class="modal-body">
                    Declaration Masspro belum dipilih.
                    Dokumen ini **wajib** untuk melanjutkan.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="button" class="btn btn-primary" id="forceMasspro">
                        Lanjutkan
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

            // =========================
            // DELETE STAGE (confirm modal)
            // =========================
            const $deleteBtn = $('#delete-stage-btn');
            const $formAction = $('#form_action');
            const $targetStageInput = $('#target_stage');

            // navigate stage buttons
            $('.btn-navigate').on('click', function() {
                const targetStage = $(this).data('target-stage');
                $targetStageInput.val(targetStage);
                $formAction.val('navigate');
                $('form').trigger('submit');
            });

            // add stage button
            $('#add-stage').on('click', function() {
                $formAction.val('add_stage');
                $('form').trigger('submit');
            });

            // save button
            $('#btn-save').on('click', function() {
                $formAction.val('save');
                $('form').trigger('submit');
            });

            // finish button
            $('#btn-finish').on('click', function() {
                if (!hasMasspro() && pendingAction === null) {
                    pendingAction = this;
                    massproModal.show();
                    setTimeout(scrollToMassproRow, 150);
                } else {
                    $formAction.val('finish');
                    $('form').trigger('submit');
                }
            });

            if ($deleteBtn.length) {
                $deleteBtn.on('click', function() {
                    const $trigger = $(this);

                    if (!$trigger.data('confirmed')) {
                        const $modal = $('#confirmDeleteStageModal');
                        const modal = bootstrap.Modal.getOrCreateInstance($modal[0]);

                        // bind sekali tiap mau show
                        $modal.off('click.confirmDelete')
                            .on('click.confirmDelete', '#confirm-delete-stage', function() {
                                $trigger.data('confirmed', true);
                                modal.hide();
                                $trigger.trigger('click');
                            });

                        modal.show();
                        return;
                    }

                    // reset flag
                    $trigger.data('confirmed', false);

                    // submit delete form
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
            }

            // =========================
            // DOC ROW CLICK => toggle checkbox
            // =========================
            $('.doc-row').on('click', function(e) {
                // klik radio -> skip
                if ($(e.target).is('.qr-option')) return;

                // klik QR cell -> skip (biar handler qr-cell jalan)
                if ($(e.target).closest('.qr-cell').length) return;

                // klik checkbox -> skip
                if ($(e.target).is('.doc-check')) return;

                const $cb = $(this).find('.doc-check');
                $cb.prop('checked', !$cb.prop('checked')).trigger('change');
            });

            // =========================
            // QR CELL CLICK => auto check checkbox + check radio
            // =========================
            $('.qr-cell').on('click', function(e) {
                e.stopPropagation();

                const $row = $(this).closest('tr');
                const $cb = $row.find('.doc-check');
                const $radio = $(this).find('.qr-option');

                if (!$cb.prop('checked')) {
                    $cb.prop('checked', true).trigger('change');
                }

                $radio.prop('checked', true).trigger('change');
            });

            // =========================
            // CHECKBOX CHANGE => enable/disable radios + default pick
            // =========================
            function syncRowRadios($row) {
                const $cb = $row.find('.doc-check');
                const $radios = $row.find('.qr-option');

                if ($cb.prop('checked')) {
                    $radios.prop('disabled', false);

                    // kalau belum ada yg dipilih -> pilih pertama
                    if ($radios.filter(':checked').length === 0) {
                        $radios.first().prop('checked', true);
                    }
                } else {
                    $radios.prop('checked', false).prop('disabled', true);
                }
            }

            $('.doc-check').on('change', function() {
                syncRowRadios($(this).closest('tr'));
            });

            // INIT (sekali aja)
            $('.doc-row').each(function() {
                syncRowRadios($(this));
            });

            // =========================
            // SUBMIT VALIDATION => tiap dokumen checked harus punya QR
            // =========================
            $('form').on('submit', function(e) {
                let valid = true;

                $('.doc-check:checked').each(function() {
                    const $row = $(this).closest('tr');
                    const hasQR = $row.find('.qr-option:checked').length > 0;

                    if (!hasQR) {
                        valid = false;
                        return false; // break each
                    }
                });

                if (!valid) {
                    e.preventDefault();
                    alert("Setiap dokumen yang dipilih harus punya posisi QR ya 😅");
                }
            });

            // =========================
            // MASSPRO REQUIRED (DM) => modal + auto-scroll
            // =========================
            function hasMasspro() {
                // check if DM field is appeared and checked
                if ($('input.doc-check[value="DM"]').length == 0) return true;
                return $('input.doc-check[value="DM"]').is(':checked');
            }

            function scrollToMassproRow() {
                const row = document.querySelector('input.doc-check[value="DM"]')?.closest('tr');
                if (!row) return;

                row.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center',
                    inline: 'nearest'
                });

                row.classList.add('table-warning');
                setTimeout(() => row.classList.remove('table-warning'), 2000);
            }

            const massproModalEl = document.getElementById('massproModal');
            const massproModal = massproModalEl ?
                bootstrap.Modal.getOrCreateInstance(massproModalEl) :
                null;

            let pendingAction = null;

            $('#forceMasspro').on('click', function() {
                massproModal?.hide();

                if (pendingAction) {
                    pendingAction.click();
                    pendingAction = null;
                }
            });

            $('#doc-search').on('keyup', function() {
                let keyword = $(this).val().toLowerCase().trim();

                $('.doc-row').each(function() {
                    let docName = $(this)
                        .find('td:nth-child(2)') // kolom "Jenis Dokumen"
                        .text()
                        .toLowerCase();

                    $(this).toggle(docName.includes(keyword));
                });
            });
            $('#doc-search').focus();
        });
    </script>

@endsection
