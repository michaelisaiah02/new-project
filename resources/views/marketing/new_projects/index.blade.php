@extends('layouts.app')
@section('title', 'INPUT NEW PROJECT')
@section('styles')
    <style>
        .adjust-width {
            width: 10rem;
        }
    </style>
@endsection
@section('content')
    <div class="container-fluid mt-3">
        <form action="" method="post" enctype="multipart/form-data">
            @csrf
            <div class="row justify-content-md-center g-2 column-gap-4">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width">Customer</span>
                        <select class="form-select bg-warning-subtle border-warning border" placeholder="Username"
                            aria-label="Username" aria-describedby="customer" id="customer" name="customer_code">
                            <option value="" selected disabled>Kode Customer</option>
                            <option value="Customer A">Customer A</option>
                            <option value="Customer B">Customer B</option>
                            <option value="Customer C">Customer C</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="input-group">
                        <span
                            class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width">Department</span>
                        <input type="text" class="form-control bg-secondary-subtle border-secondary border"
                            placeholder="Nama Department" aria-label="Department" aria-describedby="department">
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width">Model</span>
                        <input type="text" class="form-control bg-warning-subtle border-warning border"
                            placeholder="Model Part" aria-label="Model Part" aria-describedby="model" id="model"
                            name="part_model">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-auto">
                            <span
                                class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width rounded-1">Drawing</span>
                        </div>
                        <div class="col">
                            <button type="button" class="btn btn-primary border-3 border-light-subtle w-100"
                                id="btn-upload-2d">Upload 2D</button>
                            <input type="file" class="form-control bg-secondary-subtle border-secondary border"
                                id="upload-2d" name="drawing_2d" placeholder="Upload 2D" aria-label="Upload 2D"
                                aria-describedby="upload-2d" hidden>
                        </div>
                        <div class="col">
                            <button type="button" class="btn btn-primary border-3 border-light-subtle w-100"
                                id="btn-upload-3d">Upload 3D</button>
                            <input type="file" class="form-control bg-secondary-subtle border-secondary border"
                                id="upload-3d" name="drawing_3d" placeholder="Upload 3D" aria-label="Upload 3D"
                                aria-describedby="upload-3d" hidden>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width"
                            id="part-num">No.
                            Part</span>
                        <input type="text" class="form-control bg-warning-subtle border-warning border"
                            placeholder="Nomor Part" aria-label="Nomor Part" aria-describedby="part-num" id="part-num"
                            name="part_num">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" class="form-control bg-secondary-subtle border-secondary border"
                            placeholder="Nama File Drawing 2D" aria-label="Nama File Drawing 2D"
                            aria-describedby="drawing-label-2d" id="drawing-label-2d" name="drawing-label-2d">
                        <input type="text" class="form-control bg-secondary-subtle border-secondary border"
                            placeholder="Nama File Drawing 3D" aria-label="Nama File Drawing 3D"
                            aria-describedby="drawing-label-3d" id="drawing-label-3d" name="drawing-label-3d">
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width">Part
                            Name</span>
                        <input class="form-control bg-warning-subtle border-warning border" placeholder="Nama Part"
                            aria-label="Nama Part" aria-describedby="part-name" id="part-name" name="part_name">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text border-dark border-3 bg-secondary-subtle fs-7">No.
                            ECI/EO/ECN</span>
                        <input class="form-control bg-warning-subtle border-warning border"
                            placeholder="Nomor Revisi Drawing" aria-label="Nomor Revisi Drawing"
                            aria-describedby="eee-no" id="eee-no" name="eee_no">
                        <span class="input-group-text border-dark border-3 bg-secondary-subtle fs-7">No.
                            Drawing</span>
                        <input class="form-control bg-warning-subtle border-warning border"
                            placeholder="Nomor Revisi Drawing" aria-label="Nomor Revisi Drawing"
                            aria-describedby="drawing-num" id="drawing-num" name="drawing_num">
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width">Part
                            Type</span>
                        <input class="form-control bg-warning-subtle border-warning border"
                            placeholder="Hose/Molding/Weatherstrip/Bonding Metal"
                            aria-label="Hose/Molding/Weatherstrip/Bonding Metal" aria-describedby="part-type"
                            id="part-type" name="part_type">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="input-group">
                        <span
                            class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width text-wrap lh-1 pt-0 text-start">Drawing
                            Revision
                            Date</span>
                        <input class="form-control bg-warning-subtle border-warning border" placeholder="dd/mm/yyyy"
                            aria-label="dd/mm/yyyy" aria-describedby="drawing-rev-date" id="drawing-rev-date"
                            name="drawing_rev_date">
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width">Packing
                            Lot</span>
                        <input class="form-control bg-warning-subtle border-warning border" placeholder="Qty/Lot (pcs)"
                            aria-label="Qty/Lot (pcs)" aria-describedby="packing-lot" id="packing-lot"
                            name="packing_lot">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="input-group">
                        <span
                            class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width text-wrap lh-1 pt-0 text-start">Material
                            on
                            Drawing</span>
                        <input class="form-control bg-warning-subtle border-warning border" placeholder="Nama Material"
                            aria-label="Nama Material" aria-describedby="material-on-drawing" id="material-on-drawing"
                            name="material_on_drawing">
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="input-group">
                        <span
                            class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width">QTY/Year</span>
                        <input class="form-control bg-warning-subtle border-warning border" placeholder="Qty/Year (pcs)"
                            aria-label="Qty/Year (pcs)" aria-describedby="qty-year" id="qty-year" name="qty_per_year">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="input-group">
                        <span
                            class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width text-wrap lh-base pt-0 text-start fs-7">Tanggal
                            Terima SPK/LOI/DIE
                            GO</span>
                        <input class="form-control bg-warning-subtle border-warning border" placeholder="dd/mm/yyyy"
                            aria-label="dd/mm/yyyy" aria-describedby="receive-date" id="receive-date"
                            name="receive_date">
                        <span class="input-group-text border-dark border-3 bg-secondary-subtle">No. SPK</span>
                        <input class="form-control bg-warning-subtle border-warning border" placeholder="dd/mm/yyyy"
                            aria-label="dd/mm/yyyy" aria-describedby="spk-num" id="spk-num" name="spk_num">
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width">Target
                            Masspro</span>
                        <input class="form-control bg-warning-subtle border-warning border" placeholder="dd/mm/yyyy"
                            aria-label="dd/mm/yyyy" aria-describedby="masspro-target" id="masspro-target"
                            name="masspro_target">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width">Message</span>
                        <input class="form-control bg-warning-subtle border-warning border"
                            placeholder="Pesan dari management" aria-label="Pesan dari management"
                            aria-describedby="message" id="message" name="message">
                    </div>
                </div>
                <div class="col-md-5">
                </div>
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text border-dark border-3 bg-secondary-subtle adjust-width">Minor</span>
                        <input class="form-control bg-warning-subtle border-warning border"
                            placeholder="Pesan dari management" aria-label="Pesan dari management"
                            aria-describedby="minor" id="minor" name="minor">
                    </div>
                </div>
            </div>
            <div
                class="text-center row justify-content-between align-items-start position-absolute bottom-0 start-0 end-0 mb-2 mx-4">
                <div class="col-auto">
                    <a href="{{ route('marketing') }}" class="btn btn-primary">Back</a>
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary" type="submit">
                        Save
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
@section('scripts')
    <script type="module">
        $(document).ready(function() {
            $('#btn-upload-2d').click(function() {
                $('#upload-2d').click();
            });
            $('#btn-upload-3d').click(function() {
                $('#upload-3d').click();
            });

            $('#upload-2d').change(function() {
                var fileName = $(this).val().split('\\').pop();
                $('#drawing-label-2d').val(fileName);
            });
            $('#upload-3d').change(function() {
                var fileName = $(this).val().split('\\').pop();
                $('#drawing-label-3d').val(fileName);
            });
        });
    </script>
@endsection
