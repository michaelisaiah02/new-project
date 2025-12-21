@extends('layouts.app')
@section('title', 'INPUT NEW PROJECT')
@section('styles')
    <style>
        .adjust-width {
            width: 10rem;
        }

        button[disabled] {
            pointer-events: auto !important;
        }
    </style>
@endsection
@section('content')
    <div class="container-fluid mt-3">
        <form action="{{ route('marketing.projects.store') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="row justify-content-md-center g-2 column-gap-4">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width">Customer</span>
                        <select class="form-select bg-warning-subtle border-warning border" placeholder="Username"
                            aria-label="Username" aria-describedby="customer" id="customer" name="customer_code">
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
                <div class="col-md-6">
                    <div class="input-group">
                        <span
                            class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width">Department</span>
                        <input type="text" class="form-control bg-secondary-subtle border-secondary border"
                            id="department" placeholder="Nama Department" aria-label="Department"
                            aria-describedby="department" readonly>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width">Model</span>
                        <input type="text" class="form-control bg-warning-subtle border-warning border"
                            placeholder="Model Part" aria-label="Model Part" aria-describedby="model" id="model"
                            name="model" value="{{ old('model') }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-auto">
                            <span
                                class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width rounded-1">Drawing</span>
                        </div>
                        <div class="col">
                            <button type="button"
                                class="btn btn-primary border-3 border-light-subtle w-100 position-relative"
                                id="btn-upload-2d" disabled data-bs-toggle="tooltip" data-bs-placement="top"
                                data-bs-custom-class="custom-tooltip" title="Lengkapi form untuk upload">
                                Upload 2D
                                <span id="badge-upload-2d"
                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark">
                                    !
                                </span>
                            </button>
                            <input type="file" class="form-control bg-secondary-subtle border-secondary border"
                                id="upload-2d" name="drawing_2d" placeholder="Upload 2D" aria-label="Upload 2D"
                                aria-describedby="upload-2d" hidden>
                        </div>
                        <div class="col">
                            <button type="button"
                                class="btn btn-primary border-3 border-light-subtle w-100 position-relative"
                                id="btn-upload-3d" disabled data-bs-toggle="tooltip" data-bs-placement="top"
                                data-bs-custom-class="custom-tooltip" title="Lengkapi form untuk upload">
                                Upload 3D
                                <span id="badge-upload-3d"
                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark">
                                    !
                                </span>
                            </button>
                            <input type="file" class="form-control bg-secondary-subtle border-secondary border"
                                id="upload-3d" name="drawing_3d" placeholder="Upload 3D" aria-label="Upload 3D"
                                aria-describedby="upload-3d" hidden value="{{ old('drawing_3d') }}">
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width"
                            id="part-num-label">No.
                            Part</span>
                        <input type="text" class="form-control bg-warning-subtle border-warning border"
                            placeholder="Nomor Part" aria-label="Nomor Part" aria-describedby="part-num-label"
                            id="part-num" name="part_number" value="{{ old('part_number') }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" class="form-control bg-secondary-subtle border-secondary border"
                            placeholder="Nama File Drawing 2D" aria-label="Nama File Drawing 2D"
                            aria-describedby="drawing-label-2d" id="drawing-label-2d" name="drawing_label_2d" readonly
                            value="{{ old('drawing_label_2d') }}">
                        <input type="text" class="form-control bg-secondary-subtle border-secondary border"
                            placeholder="Nama File Drawing 3D" aria-label="Nama File Drawing 3D"
                            aria-describedby="drawing-label-3d" id="drawing-label-3d" name="drawing_label_3d" readonly
                            value="{{ old('drawing_label_3d') }}">
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width">Part
                            Name</span>
                        <input class="form-control bg-warning-subtle border-warning border" placeholder="Nama Part"
                            aria-label="Nama Part" aria-describedby="part-name" id="part-name" name="part_name"
                            value="{{ old('part_name') }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width text-start">No.
                            Drawing</span>
                        <input class="form-control bg-warning-subtle border-warning border" type="text"
                            aria-label="Nomor Drawing" aria-describedby="drawing-number" id="drawing-number"
                            placeholder="Nomor Drawing" name="drawing_number" value="{{ old('drawing_number') }}">
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width">Part
                            Type</span>
                        <select class="form-select bg-warning-subtle border-warning border"
                            placeholder="Hose/Molding/Weatherstrip/Bonding Metal"
                            aria-label="Hose/Molding/Weatherstrip/Bonding Metal" aria-describedby="part-type"
                            id="part-type" name="part_type">
                            <option value="">Jenis Part</option>
                            <option value="Hose" {{ old('part_type') == 'Hose' ? 'selected' : '' }}>Hose</option>
                            <option value="Molding" {{ old('part_type') == 'Molding' ? 'selected' : '' }}>Molding</option>
                            <option value="Weatherstrip" {{ old('part_type') == 'Weatherstrip' ? 'selected' : '' }}>
                                Weatherstrip
                            </option>
                            <option value="Bonding Metal" {{ old('part_type') == 'Bonding Metal' ? 'selected' : '' }}>
                                Bonding Metal
                            </option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text border-dark border-3 bg-secondary-subtle fs-7">No.
                            ECI/EO/ECN</span>
                        <input class="form-control bg-warning-subtle border-warning border"
                            placeholder="Nomor Revisi Drawing" aria-label="Nomor Revisi Drawing"
                            aria-describedby="eee-number" id="eee-number" name="eee_number"
                            value="{{ old('eee_number') }}">
                        <span class="input-group-text border-dark border-3 bg-secondary-subtle">Suffix</span>
                        <input class="form-control bg-warning-subtle border-warning border" type="text"
                            aria-label="Suffix" aria-describedby="suffix" id="suffix" name="suffix"
                            value="{{ old('suffix', '-') }}" onfocus="if (this.value === '-') this.value='';"
                            onblur="if (this.value === '') this.value='-';">
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="input-group">
                        <span
                            class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width">QTY/Year</span>
                        <input class="form-control bg-warning-subtle border-warning border" placeholder="Qty/Year (pcs)"
                            aria-label="Qty/Year (pcs)" aria-describedby="qty" id="qty" name="qty"
                            type="number" value="{{ old('qty') }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="input-group">
                        <span
                            class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width text-wrap lh-1 pt-0 text-start">Drawing
                            Revision
                            Date</span>
                        <input class="form-control bg-warning-subtle border-warning border" type="date"
                            aria-label="dd/mm/yyyy" aria-describedby="drawing-revision-date" id="drawing-revision-date"
                            name="drawing_revision_date" value="{{ old('drawing_revision_date') }}">
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width">Target
                            Masspro</span>
                        <input class="form-control bg-warning-subtle border-warning border" placeholder="dd/mm/yyyy"
                            type="date" aria-label="dd/mm/yyyy" aria-describedby="masspro-target" id="masspro-target"
                            name="masspro_target" value="{{ old('masspro_target') }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text border-dark border-3 bg-secondary-subtle fs-7 text-wrap pt-0"
                            style="width: 6rem;">No. SPK
                            /LOI/DIE
                            GO</span>
                        <input class="form-control bg-warning-subtle border-warning border"
                            placeholder="No. SPK /LOI/DIE GO" aria-label="No. SPK /LOI/DIE GO"
                            aria-describedby="sldg-number" id="sldg-number" name="sldg_number"
                            value="{{ old('sldg_number') }}">
                        <span
                            class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width text-wrap lh-base pt-0 text-start fs-7">Tanggal
                            Terima SPK/LOI/DIE
                            GO</span>
                        <input class="form-control bg-warning-subtle border-warning border" type="date"
                            aria-label="dd/mm/yyyy" aria-describedby="receive-date-sldg" id="receive-date-sldg"
                            name="receive_date_sldg" value="{{ old('receive_date_sldg') }}">
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="input-group">
                        <span
                            class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width text-wrap lh-1 pt-0 text-start">Material
                            on
                            Drawing</span>
                        <input class="form-control bg-warning-subtle border-warning border" placeholder="Nama Material"
                            aria-label="Nama Material" aria-describedby="material-on-drawing" id="material-on-drawing"
                            name="material_on_drawing" value="{{ old('material_on_drawing') }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width">Minor
                            Change</span>
                        <input class="form-control bg-warning-subtle border-warning border" placeholder="Minor Change"
                            aria-label="Pesan dari management" aria-describedby="minor-change" id="minor-change"
                            name="minor_change" value="{{ old('minor_change') }}">
                    </div>
                </div>
            </div>
            @php
                $backUrl = match (auth()->user()->department->type()) {
                    'management' => route('management'),
                    'engineering' => route('engineering'),
                    'marketing' => route('marketing'),
                    default => route('login'),
                };
            @endphp
            <div
                class="text-center row justify-content-between align-items-start position-absolute bottom-0 start-0 end-0 mb-2 mx-4">
                <div class="col-auto">
                    <a href="{{ $backUrl }}" class="btn btn-primary">Back</a>
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary" type="submit">
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
