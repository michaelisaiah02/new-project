@extends('layouts.app')
@section('title', 'MASS PRODUCTION')

@section('styles')
    <style>
        /* CSS Khusus Halaman Ini */

        /* 1. Label Box: Di PC lebarnya fix biar rapi, di HP auto biar muat */
        .label-box {
            min-width: 120px;
            /* Default PC */
            white-space: wrap;
            /* Biar teks panjang kayak 'Tanggal Terima' bisa turun ke bawah */
            text-align: start;
        }
    </style>
@endsection


@section('content')
    <div class="container-fluid mt-2">
        <form action="{{ route('masspro.index') }}" method="get" id="form-masspro">
            <div class="row justify-content-center mb-2">

                {{-- CUSTOMER --}}
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="input-group mb-1">
                        <span class="input-group-text border-dark border-3 bg-warning-subtle label-box w-25">Customer</span>
                        {{-- Prioritas Value: 1. Request User, 2. AutoFilled dari Controller (Reverse Logic) --}}
                        @php $valCust = request('customer') ?? ($autoFilled['customer'] ?? ''); @endphp
                        <select class="form-select border-warning border" id="customer" name="customer">
                            <option value="">All Customer</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->code }}" {{ $valCust == $customer->code ? 'selected' : '' }}>
                                    {{ $customer->code }} - {{ $customer->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- MODEL --}}
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="input-group mb-1">
                        <span class="input-group-text border-dark border-3 bg-warning-subtle label-box w-25">Model</span>
                        <select class="form-select border-warning border" id="model" name="model">
                            <option value="">All Model</option>
                        </select>
                    </div>
                </div>

                {{-- PART NUMBER --}}
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="input-group mb-1">
                        <span class="input-group-text border-dark border-3 bg-warning-subtle label-box w-25">No. Part</span>
                        <select class="form-select border-warning border" id="part_number" name="part_number">
                            <option value="">All Part Number</option>
                        </select>
                    </div>
                </div>

                {{-- REMARK --}}
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="input-group mb-1">
                        <span class="input-group-text border-dark border-3 bg-warning-subtle label-box w-25">Remark</span>
                        <select class="form-select border-warning border" id="remark" name="remark">
                            <option value="all" {{ request('remark') == 'all' ? 'selected' : '' }}>All
                            </option>
                            <option value="completed" {{ request('remark') == 'completed' ? 'selected' : '' }}>Completed
                            </option>
                            <option value="canceled" {{ request('remark') == 'canceled' ? 'selected' : '' }}>Canceled
                            </option>
                        </select>
                    </div>
                </div>

                {{-- SUFFIX (DIPISAH) --}}
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="input-group mb-1">
                        <span class="input-group-text border-dark border-3 bg-warning-subtle label-box w-25">Suffix</span>
                        <select class="form-select border-warning border" id="suffix" name="suffix">
                            <option value="">All</option>
                        </select>
                    </div>
                </div>

                {{-- MINOR CHANGE --}}
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="input-group mb-1">
                        <span class="input-group-text border-dark border-3 bg-warning-subtle label-box w-25">MC</span>
                        <select class="form-select border-warning border" id="minor_change" name="minor_change">
                            <option value="">All</option>
                        </select>
                    </div>
                </div>

            </div>
        </form>

        {{-- TABEL DATA --}}
        <div class="table-responsive mb-3 pt-1" style="max-height: 500px; overflow-y: auto;">
            <table class="table table-bordered table-hover align-middle text-center text-nowrap">
                <thead class="table-secondary position-sticky top-0">
                    <tr>
                        <th>Model</th>
                        <th>No. Part</th>
                        <th>Part Name</th>
                        <th>Suffix</th>
                        <th>MC</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($massproRecords as $record)
                        <tr>
                            <td>{{ $record->model }}</td>
                            <td class="fw-bold">{{ $record->part_number }}</td>
                            <td class="text-start">{{ $record->part_name }}</td>
                            <td>{{ $record->suffix }}</td>
                            <td>{{ $record->minor_change }}</td>
                            <td>
                                <span class="badge {{ $record->remark == 'completed' ? 'bg-success' : 'bg-danger' }}">
                                    {{ ucfirst($record->remark) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('masspro.view', array_merge(['project' => $record->id], request()->query())) }}"
                                    class="btn btn-sm btn-primary">View Stage</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                @if (request()->filled('customer') || request()->filled('model') || request()->filled('part_number'))
                                    <i class="bi bi-x-circle display-6 d-block mb-2"></i>
                                    Data tidak ditemukan.
                                @else
                                    <i class="bi bi-search display-6 d-block mb-2"></i>
                                    Silakan pilih salah satu filter untuk menampilkan data.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
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
    <div class="row justify-content-between align-items-center mx-0 sticky-bottom">
        <div class="col-auto">
            <a href="{{ $backUrl }}" class="btn btn-primary px-4 border-3 border-light-subtle shadow-sm">Back</a>
        </div>
    </div>
    <x-toast />
@endsection

@section('scripts')
    <script type="module">
        $(document).ready(function() {
            // --- 0. FLAG PEREDAM (PENTING BIAR GA LOOPING) ---
            // Variabel ini berfungsi untuk memblokir auto-submit saat kita sedang mengutak-atik dropdown via coding (AJAX)
            var isUpdating = false;

            // --- 1. AMBIL VALUE AWAL ---
            var valCust = "{{ request('customer') ?? ($autoFilled['customer'] ?? '') }}";
            var valModel = "{{ request('model') ?? ($autoFilled['model'] ?? '') }}";
            var valPart = "{{ request('part_number') }}";
            var valSuffix = "{{ request('suffix') }}";
            var valMc = "{{ request('minor_change') }}";
            var valRemark = "{{ request('remark') ?? 'all' }}";

            // --- 2. SETUP SELECTIZE ---
            var $sCust = $('#customer').selectize({
                create: false,
                sortField: 'text'
            });
            var $sModel = $('#model').selectize({
                valueField: 'text',
                labelField: 'text',
                searchField: 'text',
                create: false
            });
            var $sPart = $('#part_number').selectize({
                valueField: 'part_number',
                labelField: 'part_number',
                searchField: ['part_number', 'part_name'],
                create: false,
                render: {
                    option: function(item, escape) {
                        return '<div><span class="fw-bold">' + escape(item.part_number) +
                            '</span><span class="text-muted small ms-2">(' + escape(item.part_name ||
                                '-') + ')</span></div>';
                    }
                }
            });
            var $sSuffix = $('#suffix').selectize({
                valueField: 'text',
                labelField: 'text',
                searchField: 'text',
                create: false
            });
            var $sMc = $('#minor_change').selectize({
                valueField: 'text',
                labelField: 'text',
                searchField: 'text',
                create: false
            });
            var $sRemark = $('#remark').selectize({
                valueField: 'id',
                labelField: 'text',
                searchField: 'text',
                create: false
            });

            var cCust = $sCust[0].selectize;
            var cModel = $sModel[0].selectize;
            var cPart = $sPart[0].selectize;
            var cSuffix = $sSuffix[0].selectize;
            var cMc = $sMc[0].selectize;
            var cRemark = $sRemark[0].selectize;

            // Set Value Awal Customer
            if (valCust) cCust.setValue(valCust, true);

            // --- 3. HELPER: AMBIL SEMUA FILTER ---
            function getAllFilters() {
                return {
                    customer: cCust.getValue(),
                    model: cModel.getValue() || valModel,
                    part_number: cPart.getValue() || valPart,
                    suffix: cSuffix.getValue() || valSuffix,
                    minor_change: cMc.getValue() || valMc,
                    remark: cRemark.getValue() || valRemark,
                };
            }

            // --- 4. LOAD OPSI SECARA PARALEL ---
            var params = getAllFilters();

            // A. Load Model
            $.ajax({
                url: '{{ route('masspro.api.models') }}',
                data: params,
                success: function(res) {
                    isUpdating = true; // <--- NYALAKAN PEREDAM
                    cModel.clearOptions(); // Ini memicu change, tapi ditahan oleh isUpdating
                    cModel.addOption(res.map(function(x) {
                        return {
                            text: x
                        }
                    }));
                    if (valModel) cModel.setValue(valModel, true);
                    isUpdating = false; // <--- MATIKAN PEREDAM
                }
            });

            // B. Load Part
            $.ajax({
                url: '{{ route('masspro.api.parts') }}',
                data: params,
                success: function(res) {
                    isUpdating = true;
                    cPart.clearOptions();
                    cPart.addOption(res);
                    if (valPart) cPart.setValue(valPart, true);
                    isUpdating = false;
                }
            });

            // C. Load Suffix
            $.ajax({
                url: '{{ route('masspro.api.suffixes') }}',
                data: params,
                success: function(res) {
                    isUpdating = true;
                    cSuffix.clearOptions();
                    cSuffix.addOption(res.map(function(x) {
                        return {
                            text: x
                        }
                    }));
                    if (valSuffix) cSuffix.setValue(valSuffix, true);
                    isUpdating = false;
                }
            });

            // D. Load MC
            $.ajax({
                url: '{{ route('masspro.api.minorChanges') }}',
                data: params,
                success: function(res) {
                    isUpdating = true;
                    cMc.clearOptions();
                    cMc.addOption(res.map(function(x) {
                        return {
                            text: x
                        }
                    }));
                    if (valMc) cMc.setValue(valMc, true);
                    isUpdating = false;
                }
            });

            // E. Load Remark
            $.ajax({
                url: '{{ route('masspro.api.remarks') }}',
                data: params,
                success: function(res) {
                    isUpdating = true;
                    cRemark.clearOptions();
                    cRemark.addOption({
                        id: 'all',
                        text: 'All'
                    });
                    var options = res.map(function(x) {
                        var label = x.charAt(0).toUpperCase() + x.slice(1);
                        return {
                            id: x,
                            text: label
                        };
                    });
                    cRemark.addOption(options);
                    if (valRemark) cRemark.setValue(valRemark, true);
                    isUpdating = false;
                }
            });

            // --- 5. EVENT LISTENER (AUTO SUBMIT) ---
            function submitForm() {
                // CEK PEREDAM DISINI
                if (isUpdating) return; // Kalau lagi updating via AJAX, JANGAN submit!

                // Debounce sedikit biar ga double submit
                setTimeout(function() {
                    $('#form-masspro').submit();
                }, 100);
            }

            // Logic Trigger: Cek apakah nilai berubah dari nilai awal
            // Gunakan 'String(val)' untuk memastikan tipe data sama (kadang "1" != 1)

            cCust.on('change', function(val) {
                if (val !== valCust && !isUpdating) submitForm();
            });

            cModel.on('change', function(val) {
                if (String(val) !== String(valModel) && !isUpdating) submitForm();
            });

            cPart.on('change', function(val) {
                if (String(val) !== String(valPart) && !isUpdating) submitForm();
            });

            cSuffix.on('change', function(val) {
                if (String(val) !== String(valSuffix) && !isUpdating) submitForm();
            });

            cMc.on('change', function(val) {
                if (String(val) !== String(valMc) && !isUpdating) submitForm();
            });

            cRemark.on('change', function(val) {
                if (String(val) !== String(valRemark) && !isUpdating) submitForm();
            });
        });
    </script>
@endsection
