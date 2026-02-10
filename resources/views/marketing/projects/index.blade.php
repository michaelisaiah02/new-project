@extends('layouts.app')
@section('title', 'INPUT NEW PROJECT')
@section('styles')
    <style>
        /* CSS Khusus Halaman Ini */

        /* 1. Label Box: Di PC lebarnya fix biar rapi, di HP auto biar muat */
        .label-box {
            min-width: 140px;
            /* Default PC */
            white-space: wrap;
            /* Biar teks panjang kayak 'Tanggal Terima' bisa turun ke bawah */
            text-align: start;
        }

        /* Responsif untuk HP: Label sedikit mengecil */
        @media (max-width: 768px) {
            .label-box {
                min-width: 110px;
                font-size: 0.85rem;
                padding: 0.5rem;
            }
        }

        /* Fix button disabled pointer events */
        button[disabled] {
            pointer-events: auto !important;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid mt-3">
        <form action="{{ route('marketing.projects.store') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="row g-1">
                <div class="col-12 col-lg-6">
                    <div class="input-group">
                        <span class="input-group-text label-box border-dark border-3 bg-secondary-subtle">Customer</span>
                        <select class="form-select bg-warning-subtle border-warning border" id="customer"
                            name="customer_code">
                            <option value="" selected disabled>Kode Customer</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->code }}" data-department="{{ $customer->department->name }}"
                                    {{ old('customer_code') == $customer->code ? 'selected' : '' }}
                                    {{ $customer->documentTypes()->where('code', 'DM')->exists() ? '' : 'disabled' }}>
                                    {{ $customer->code }} -
                                    {{ $customer->documentTypes()->where('code', 'DM')->exists() ? $customer->name : '(Deklarasi Masspro belum ada)' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="input-group">
                        <span class="input-group-text label-box border-dark border-3 bg-secondary-subtle">Department</span>
                        <input type="text" class="form-control bg-secondary-subtle border-secondary border"
                            id="department" placeholder="Nama Department" readonly>
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="input-group">
                        <span class="input-group-text label-box border-dark border-3 bg-secondary-subtle">Model</span>
                        <input type="text" class="form-control bg-warning-subtle border-warning border"
                            placeholder="Model Part" id="model" name="model" value="{{ old('model') }}">
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="input-group">
                        <span class="input-group-text label-box border-dark border-3 bg-secondary-subtle">Drawing</span>
                        <div class="grow position-relative">
                            <button type="button" class="btn btn-primary border-3 border-light-subtle w-100 rounded-0"
                                id="btn-upload-2d" disabled data-bs-toggle="tooltip" title="Lengkapi form untuk upload">
                                Upload 2D
                                <span id="badge-upload-2d"
                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark">!</span>
                            </button>
                            <input type="file" id="upload-2d" name="drawing_2d" hidden accept=".pdf" size="5120">
                        </div>
                        <div class="grow position-relative">
                            <button type="button" class="btn btn-primary border-3 border-light-subtle w-100 rounded-end"
                                id="btn-upload-3d" disabled data-bs-toggle="tooltip" title="Lengkapi form untuk upload">
                                Upload 3D
                                <span id="badge-upload-3d"
                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark">!</span>
                            </button>
                            <input type="file" id="upload-3d" name="drawing_3d" hidden value="{{ old('drawing_3d') }}">
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="input-group">
                        <span class="input-group-text label-box border-dark border-3 bg-secondary-subtle"
                            id="part-num-label">No. Part</span>
                        <input type="text" class="form-control bg-warning-subtle border-warning border"
                            placeholder="Nomor Part" id="part-num" name="part_number" value="{{ old('part_number') }}">
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="d-flex flex-column flex-md-row gap-2">
                        <input type="text" class="form-control bg-secondary-subtle border-secondary border"
                            placeholder="Nama File Drawing 2D" id="drawing-label-2d" name="drawing_label_2d" readonly
                            value="{{ old('drawing_label_2d') }}">
                        <input type="text" class="form-control bg-secondary-subtle border-secondary border"
                            placeholder="Nama File Drawing 3D" id="drawing-label-3d" name="drawing_label_3d" readonly
                            value="{{ old('drawing_label_3d') }}">
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="input-group">
                        <span class="input-group-text label-box border-dark border-3 bg-secondary-subtle">Part Name</span>
                        <input class="form-control bg-warning-subtle border-warning border" placeholder="Nama Part"
                            id="part-name" name="part_name" value="{{ old('part_name') }}">
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="input-group">
                        <span class="input-group-text label-box border-dark border-3 bg-secondary-subtle text-start">No.
                            Drawing</span>
                        <input class="form-control bg-warning-subtle border-warning border" type="text"
                            id="drawing-number" placeholder="Nomor Drawing" name="drawing_number"
                            value="{{ old('drawing_number') }}">
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="input-group">
                        <span class="input-group-text label-box border-dark border-3 bg-secondary-subtle">Part Type</span>
                        <select class="form-select bg-warning-subtle border-warning border" id="part-type"
                            name="part_type">
                            <option value="">Jenis Part</option>
                            <option value="Hose" {{ old('part_type') == 'Hose' ? 'selected' : '' }}>Hose</option>
                            <option value="Molding" {{ old('part_type') == 'Molding' ? 'selected' : '' }}>Molding</option>
                            <option value="Weatherstrip" {{ old('part_type') == 'Weatherstrip' ? 'selected' : '' }}>
                                Weatherstrip</option>
                            <option value="Bonding Metal" {{ old('part_type') == 'Bonding Metal' ? 'selected' : '' }}>
                                Bonding Metal</option>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="input-group">
                        <span class="input-group-text label-box border-dark border-3 bg-secondary-subtle">No. ECI/EO</span>
                        <input class="form-control bg-warning-subtle border-warning border" placeholder="No. Revisi"
                            id="eee-number" name="eee_number" value="{{ old('eee_number') }}">
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="input-group">
                        <span class="input-group-text label-box border-dark border-3 bg-secondary-subtle">Suffix</span>
                        <input class="form-control bg-warning-subtle border-warning border" type="text" id="suffix"
                            name="suffix" value="{{ old('suffix', '-') }}"
                            onfocus="if (this.value === '-') this.value='';"
                            onblur="if (this.value === '') this.value='-';">
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="input-group">
                        <span class="input-group-text label-box border-dark border-3 bg-secondary-subtle">QTY/Year</span>
                        <input class="form-control bg-warning-subtle border-warning border" placeholder="Qty/Year (pcs)"
                            id="qty" name="qty" type="number" value="{{ old('qty') }}">
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="input-group">
                        <span class="input-group-text label-box border-dark border-3 bg-secondary-subtle">Dwg Rev
                            Date</span>
                        <input class="form-control bg-warning-subtle border-warning border" type="date"
                            id="drawing-revision-date" name="drawing_revision_date"
                            value="{{ old('drawing_revision_date') }}">
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="input-group">
                        <span class="input-group-text label-box border-dark border-3 bg-secondary-subtle">Target
                            Masspro</span>
                        <input class="form-control bg-warning-subtle border-warning border" placeholder="dd/mm/yyyy"
                            type="date" id="masspro-target" name="masspro_target"
                            value="{{ old('masspro_target') }}">
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="input-group">
                        <span class="input-group-text label-box border-dark border-3 bg-secondary-subtle">No.
                            SPK/LOI</span>
                        <input class="form-control bg-warning-subtle border-warning border"
                            placeholder="Nomor SPK/LOI/DIE GO" id="sldg-number" name="sldg_number"
                            value="{{ old('sldg_number') }}">
                    </div>
                </div>
                <div class="col-12 col-lg-6">
                    <div class="input-group">
                        <span class="input-group-text label-box border-dark border-3 bg-secondary-subtle">Tgl
                            Terima</span>
                        <input class="form-control bg-warning-subtle border-warning border" type="date"
                            id="receive-date-sldg" name="receive_date_sldg" value="{{ old('receive_date_sldg') }}">
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="input-group">
                        <span class="input-group-text label-box border-dark border-3 bg-secondary-subtle">Material</span>
                        <input class="form-control bg-warning-subtle border-warning border" placeholder="Nama Material"
                            id="material-on-drawing" name="material_on_drawing"
                            value="{{ old('material_on_drawing') }}">
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="input-group">
                        <span class="input-group-text label-box border-dark border-3 bg-secondary-subtle">Minor
                            Change</span>
                        <input class="form-control bg-warning-subtle border-warning border" placeholder="Minor Change"
                            id="minor-change" name="minor_change" value="{{ old('minor_change') }}">
                    </div>
                </div>

            </div> @php
                $backUrl = match (auth()->user()->department->type()) {
                    'management' => route('management'),
                    'engineering' => route('engineering'),
                    'marketing' => route('marketing'),
                    default => route('login'),
                };
            @endphp

            <div class="row justify-content-between align-items-center mt-2 px-3">
                <div class="col-auto">
                    <a href="{{ $backUrl }}"
                        class="btn btn-primary px-4 border-3 border-light-subtle shadow-sm">Back</a>
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary px-4 border-3 border-light-subtle shadow-sm" type="submit">
                        Save
                    </button>
                </div>
            </div>

        </form>
    </div>
    <x-toast />
@endsection
@section('scripts')
    <script type="module">
        function checkFilledForm() {
            const missing = [];
            let tooltip2D, tooltip3D;

            tooltip2D = new bootstrap.Tooltip(
                document.getElementById('btn-upload-2d')
            );
            tooltip3D = new bootstrap.Tooltip(
                document.getElementById('btn-upload-3d')
            );

            if ($('#customer').val() === null) missing.push('Customer');
            if ($('#part-num').val().trim() === '') missing.push('Part Number');
            if ($('#suffix').val().trim() === '') missing.push('Suffix');
            if ($('#minor-change').val().trim() === '') missing.push('Minor Change');

            const isValid = missing.length === 0;

            $('#btn-upload-2d, #btn-upload-3d').prop('disabled', !isValid);
            $('#badge-upload-2d, #badge-upload-3d').toggle(!isValid);

            const tooltipText = isValid ?
                '' :
                'Lengkapi: ' + missing.join(', ');

            const btn2d = document.getElementById('btn-upload-2d');
            const btn3d = document.getElementById('btn-upload-3d');

            btn2d.setAttribute('data-bs-original-title', tooltipText);
            btn3d.setAttribute('data-bs-original-title', tooltipText);

            tooltip2D.update();
            tooltip3D.update();
        }
        $(document).ready(function() {
            const csrfToken = '{{ csrf_token() }}';

            $('#customer').change(function() {
                const department = $(this).find('option:selected').data('department');
                $('#department').val(department);
            });

            if ($('#customer').val() !== null) {
                const department = $('#customer').find('option:selected').data('department');
                $('#department').val(department);
            }

            // disable upload buttons kalau customer dan nomor part belum diisi
            $('#customer, #part-num, #suffix, #minor-change').on('input change', function() {
                checkFilledForm();
            });

            $('#btn-upload-2d').click(function() {
                $('#upload-2d').click();
            });
            $('#btn-upload-3d').click(function() {
                $('#upload-3d').click();
            });

            $('#upload-2d, #upload-3d').change(function() {
                const customerCode = $('#customer').val();
                const partNum = $('#part-num').val().trim();
                const suffix = $('#suffix').val().trim();
                const minor = $('#minor-change').val().trim();

                const is2D = $(this).attr('id') === 'upload-2d';
                const fileInput = is2D ? $('#upload-2d')[0] : $('#upload-3d')[0];
                const drawingLabelInput = is2D ? $('#drawing-label-2d') : $('#drawing-label-3d');
                const extension = fileInput.files.length > 0 ? fileInput.files[0].name.split('.').pop() :
                    '';

                const label = `Dwg${is2D ? '2D' : '3D'}-${partNum}-${suffix}-${minor}.${extension}`;
                if (fileInput.files.length > 0) {
                    $(drawingLabelInput).val(label);
                }
            });

            // kalau sudah upload file, tapi customer, part number, suffix, minor diubah, generate ulang nama filenya
            $('#customer, #part-num, #suffix, #minor-change').on('input change', function() {
                ['#upload-2d', '#upload-3d'].forEach(function(inputId) {
                    const is2D = inputId === '#upload-2d';
                    const fileInput = $(inputId)[0];
                    const drawingLabelInput = is2D ? $('#drawing-label-2d') : $(
                        '#drawing-label-3d');
                    const extension = fileInput.files.length > 0 ? fileInput.files[0].name.split(
                        '.').pop() : '';
                    const partNum = $('#part-num').val().trim();
                    const suffix = $('#suffix').val().trim();
                    const minor = $('#minor-change').val().trim();
                    if (fileInput.files.length > 0) {
                        const label =
                            `Dwg${is2D ? '2D' : '3D'}-${partNum}-${suffix}-${minor}.${extension}`;
                        $(drawingLabelInput).val(label);
                    }
                });
            });
            checkFilledForm();
        });
    </script>
@endsection
