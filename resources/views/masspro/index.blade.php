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
                            <option value="all" {{ request('remark') == 'all' ? 'selected' : '' }}>All (Masspro Only)
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

                {{-- MINOR CHANGE (DIPISAH) --}}
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
@endsection

@section('scripts')
    <script type="module">
        $(document).ready(function() {
            // --- 1. AMBIL VALUE AWAL (Termasuk AutoFilled dari Controller) ---
            // Kita pakai ?? untuk fallback ke empty string
            var valCust = "{{ request('customer') ?? ($autoFilled['customer'] ?? '') }}";
            var valModel = "{{ request('model') ?? ($autoFilled['model'] ?? '') }}";
            var valPart = "{{ request('part_number') }}";
            var valSuffix = "{{ request('suffix') }}";
            var valMc = "{{ request('minor_change') }}";

            // --- 2. SETUP SELECTIZE (Init Awal) ---
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
                create: false
            });

            var cCust = $sCust[0].selectize;
            var cModel = $sModel[0].selectize;
            var cPart = $sPart[0].selectize;
            var cSuffix = $sSuffix[0].selectize;
            var cMc = $sMc[0].selectize;

            // Fungsi Auto Submit
            function submitForm() {
                setTimeout(function() {
                    $('#form-masspro').submit();
                }, 50);
            }

            // --- 3. FUNGSI LOAD OPSI (INDEPENDENT & MIXED) ---
            // Kita load opsi dropdown berdasarkan apa yg sedang terpilih.
            // Kita kirim SEMUA parameter saat request API, biar API yang mikir filternya.

            var currentParams = {
                customer_code: valCust,
                model: valModel,
                part_number: valPart
            };

            // A. Load Model (Berdasarkan Cust & Part)
            $.ajax({
                url: '{{ route('masspro.api.models') }}',
                data: currentParams,
                success: function(res) {
                    var options = res.map(function(x) {
                        return {
                            text: x
                        };
                    });
                    cModel.addOption(options);
                    if (valModel) cModel.setValue(valModel, true); // True = Silent (no trigger change)
                }
            });

            // B. Load Part (Berdasarkan Cust & Model)
            $.ajax({
                url: '{{ route('masspro.api.parts') }}',
                data: currentParams,
                success: function(res) {
                    cPart.addOption(res);
                    if (valPart) cPart.setValue(valPart, true);
                }
            });

            // C. Load Suffix (Berdasarkan Part)
            $.ajax({
                url: '{{ route('masspro.api.suffixes') }}', // Pastikan route ini ada
                data: {
                    part_number: valPart
                },
                success: function(res) {
                    var options = res.map(function(x) {
                        return {
                            text: x
                        };
                    });
                    cSuffix.addOption(options);
                    if (valSuffix) cSuffix.setValue(valSuffix, true);
                }
            });

            // D. Load MC (Berdasarkan Part)
            $.ajax({
                url: '{{ route('masspro.api.minorChanges') }}', // Pastikan route ini ada
                data: {
                    part_number: valPart
                },
                success: function(res) {
                    var options = res.map(function(x) {
                        return {
                            text: x
                        };
                    });
                    cMc.addOption(options);
                    if (valMc) cMc.setValue(valMc, true);
                }
            });


            // --- 4. EVENT LISTENER (AUTO SUBMIT SAJA) ---
            // Kita hapus logic "clear options" yg ribet.
            // Biarkan User pilih -> Submit -> Controller filter & autoFill -> Halaman Reload -> Dropdown terisi yg benar.

            cCust.on('change', function(val) {
                if (val !== valCust) submitForm();
            });
            cModel.on('change', function(val) {
                if (val !== valModel) submitForm();
            });
            cPart.on('change', function(val) {
                if (val !== valPart) submitForm();
            });
            cSuffix.on('change', function(val) {
                if (val !== valSuffix) submitForm();
            });
            cMc.on('change', function(val) {
                if (val !== valMc) submitForm();
            });
            $sRemark[0].selectize.on('change', function(val) {
                submitForm();
            });

        });
    </script>
@endsection
