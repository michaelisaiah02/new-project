@extends('layouts.app')
@section('title', 'KPI NEW PROJECT')
@section('styles')
    <style>
        .selectize-input,
        .selectize-input.full,
        .selectize-control.single .selectize-input.input-active {
            background-color: #fff3cc;
            border: 0;
        }



        .selectize-dropdown-content {
            background-color: #fff3cc;
        }
    </style>
@endsection
@section('content')
    <div class="container-fluid mt-2">
        <form action="{{ route('kpi.index') }}" method="get" id="form-kpi">
            <div class="row justify-content-center mb-2">

                {{-- 1. PILIH CUSTOMER --}}
                <div class="col-md-5">
                    <div class="input-group mb-1">
                        <span class="input-group-text border-dark border-3 bg-warning-subtle fw-bold w-25">Customer</span>
                        <select class="form-select border-warning border selectize-control rounded-end-3" id="customer"
                            name="customer">
                            <option value="">Pilih Customer</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->code }}"
                                    {{ request('customer') == $customer->code ? 'selected' : '' }}> {{-- Ganti old() jadi request() disini juga --}}
                                    {{ $customer->code }} - {{ $customer->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- 2. PILIH MODEL --}}
                <div class="col-md-5">
                    <div class="input-group mb-1">
                        <span class="input-group-text border-dark border-3 bg-warning-subtle fw-bold w-25">Model</span>
                        <select class="form-select border-warning border selectize-control rounded-end-3" id="model"
                            name="model" disabled>
                            <option value="">Pilih Model</option>
                            {{-- Opsi akan diisi via AJAX --}}
                        </select>
                    </div>
                </div>

                {{-- 3. PILIH PART NUMBER --}}
                <div class="col-md-5">
                    <div class="input-group mb-1">
                        <span class="input-group-text border-dark border-3 bg-warning-subtle fw-bold w-25">Part No.</span>
                        <select class="form-select border-warning border selectize-control rounded-end-3" id="part_number"
                            name="part_number" disabled>
                            <option value="">Pilih Part Number</option>
                            {{-- Opsi akan diisi via AJAX --}}
                        </select>
                    </div>
                </div>

                {{-- 4. PILIH VARIANT (Suffix & Minor Change) --}}
                {{-- Kita gunakan satu dropdown untuk memilih kombinasi unik, lalu JS akan memecahnya jika perlu --}}
                <div class="col-md-5">
                    <div class="input-group mb-1">
                        <span class="input-group-text border-dark border-3 bg-warning-subtle fw-bold w-25">Suffix/MC</span>
                        <select class="form-select border-warning border selectize-control rounded-end-3" id="variant_combo"
                            name="variant_combo" disabled>
                            <option value="">Pilih Suffix - MC</option>
                            {{-- Opsi akan diisi via AJAX --}}
                        </select>
                    </div>
                    {{-- Input hidden ini agar logic backend kamu yg lama tetap jalan (menerima suffix dan minor_change terpisah) --}}
                    <input type="hidden" name="suffix" id="hidden_suffix">
                    <input type="hidden" name="minor_change" id="hidden_minor_change">
                </div>

            </div>

            <div class="row justify-content-center d-none">
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Cari Data</button>
                </div>
            </div>
        </form>
        @if ($selectedProject)
            {{-- CARD UTAMA: CHART & TOMBOL --}}
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    {{-- Judul Project --}}
                    <h5 class="mb-0">
                        Performance: {{ $selectedProject->part_name }} ({{ $selectedProject->part_number }})
                    </h5>

                    {{-- Tombol Trigger Modal (Hanya muncul jika ada dokumen delay) --}}
                    @if ($delayDocuments->count() > 0)
                        <button type="button" class="btn btn-warning btn-sm fw-bold text-dark" data-bs-toggle="modal"
                            data-bs-target="#delayModal">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            Lihat List Delay ({{ $delayDocuments->count() }})
                        </button>
                    @else
                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> No Delay</span>
                    @endif
                </div>

                <div class="card-body">
                    {{-- CONTAINER CHART: Atur tinggi disini (height: 300px) agar tidak kegedean --}}
                    <div style="position: relative; height: 275px; width: 100%;">
                        <canvas id="kpiChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- MODAL POPUP: DETAIL DELAY --}}
            @if ($delayDocuments->count() > 0)
                <div class="modal fade" id="delayModal" tabindex="-1" aria-labelledby="delayModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable"> {{-- modal-lg biar lebar, scrollable biar bisa discroll --}}
                        <div class="modal-content">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title" id="delayModalLabel">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Delayed Documents List
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-0">
                                {{-- Tabel dipindah kesini --}}
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover mb-0">
                                        <thead class="table-light sticky-top">
                                            <tr>
                                                <th>Stage</th>
                                                <th>Document Type</th>
                                                <th>Due Date</th>
                                                <th>Actual Date</th>
                                                <th>Late</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($delayDocuments as $doc)
                                                <tr>
                                                    <td><small>{{ $doc->stage->stage_number ?? '-' }}</small></td>
                                                    <td>{{ $doc->documentType->name ?? $doc->document_type_code }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($doc->due_date)->format('d/m/y') }}</td>
                                                    <td class="text-danger fw-bold">
                                                        {{ \Carbon\Carbon::parse($doc->actual_date)->format('d/m/y') }}
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-danger">
                                                            {{ \Carbon\Carbon::parse($doc->actual_date)->diffInDays(\Carbon\Carbon::parse($doc->due_date)) }}
                                                            Hari
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif
        @php
            $backUrl = match (auth()->user()->department->type()) {
                'management' => route('management'),
                'engineering' => route('engineering'),
                'marketing' => route('marketing'),
                default => route('login'),
            };
        @endphp
        <div class="row justify-content-between align-items-center position-absolute bottom-0 start-0 end-0 mx-0 px-0 mb-2">
            <div class="col-auto">
                <a href="{{ $backUrl }}" class="btn btn-primary">Back</a>
            </div>
        </div>
    </div>
    <x-toast />
@endsection
@section('scripts')
    <script type="module">
        $(document).ready(function() {
            // --- 0. AMBIL OLD VALUES DARI LARAVEL ---
            // Kita simpan di variabel JS agar bersih
            var oldCustomer = "{{ request('customer') }}";
            var oldModel = "{{ request('model') }}";
            var oldPart = "{{ request('part_number') }}";

            // Ambil Suffix dan MC
            var oldSuffix = "{{ request('suffix') }}";
            var oldMc = "{{ request('minor_change') }}";
            // Cek apakah ada data old, jika ada gabung pakai '|'
            var oldVariant = (oldSuffix || oldMc) ? (oldSuffix || '') + '|' + (oldMc || '') : null;

            // --- 1. Inisialisasi Selectize ---
            var $selectCustomer = $('#customer').selectize({
                create: false,
                sortField: 'text'
            });
            var $selectModel = $('#model').selectize({
                valueField: 'model',
                labelField: 'model',
                searchField: 'model',
                create: false
            });
            var $selectPart = $('#part_number').selectize({
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
            var $selectVariant = $('#variant_combo').selectize({
                valueField: 'value_string',
                labelField: 'label_string',
                searchField: ['label_string'],
                create: false
            });

            var controlCustomer = $selectCustomer[0].selectize;
            var controlModel = $selectModel[0].selectize;
            var controlPart = $selectPart[0].selectize;
            var controlVariant = $selectVariant[0].selectize;

            // --- 2. LOGIKA RE-POPULATE (MENGEMBALIKAN NILAI LAMA) ---
            // Ini dijalankan SAAT HALAMAN LOAD

            if (oldCustomer) {
                // A. Load Model berdasarkan Old Customer
                $.ajax({
                    url: '{{ route('kpi.api.models') }}',
                    type: 'GET',
                    data: {
                        customer_code: oldCustomer
                    },
                    success: function(res) {
                        controlModel.enable();
                        controlModel.addOption(res); // Masukkan opsi ke dropdown

                        if (oldModel) {
                            controlModel.setValue(oldModel,
                                true); // true = silent (jangan trigger event change)

                            // B. Load Part berdasarkan Old Model
                            $.ajax({
                                url: '{{ route('kpi.api.parts') }}',
                                type: 'GET',
                                data: {
                                    customer_code: oldCustomer,
                                    model: oldModel
                                },
                                success: function(res) {
                                    controlPart.enable();
                                    controlPart.addOption(res);

                                    if (oldPart) {
                                        controlPart.setValue(oldPart,
                                            true); // true = silent

                                        // C. Load Variant berdasarkan Old Part
                                        $.ajax({
                                            url: '{{ route('kpi.api.variants') }}',
                                            type: 'GET',
                                            data: {
                                                customer_code: oldCustomer,
                                                model: oldModel,
                                                part_number: oldPart
                                            },
                                            success: function(res) {
                                                var options = res.map(function(
                                                    item) {
                                                    var s = item
                                                        .suffix ? item
                                                        .suffix : '-';
                                                    var mc = item
                                                        .minor_change ?
                                                        item
                                                        .minor_change :
                                                        '-';
                                                    return {
                                                        value_string: (
                                                                item
                                                                .suffix ||
                                                                '') +
                                                            '|' + (item
                                                                .minor_change ||
                                                                ''),
                                                        label_string: 'Suffix: ' +
                                                            s +
                                                            ' | MC: ' +
                                                            mc,
                                                        suffix: item
                                                            .suffix,
                                                        minor_change: item
                                                            .minor_change
                                                    };
                                                });

                                                controlVariant.enable();
                                                controlVariant.addOption(
                                                    options);

                                                if (oldVariant) {
                                                    // Kita set value variant, TAPI silent (true) agar tidak auto-submit lagi
                                                    controlVariant.setValue(
                                                        oldVariant, true);
                                                }
                                            }
                                        });
                                    }
                                }
                            });
                        }
                    }
                });
            }

            // --- 3. EVENT LISTENER (INTERAKSI USER) ---
            // Kode di bawah ini sama seperti sebelumnya, untuk handle perubahan manual user

            controlCustomer.on('change', function(value) {
                controlModel.clear();
                controlModel.clearOptions();
                controlModel.disable();
                controlPart.clear();
                controlPart.clearOptions();
                controlPart.disable();
                controlVariant.clear();
                controlVariant.clearOptions();
                controlVariant.disable();
                if (!value) return;
                controlModel.load(function(callback) {
                    $.ajax({
                        url: '{{ route('kpi.api.models') }}',
                        type: 'GET',
                        data: {
                            customer_code: value
                        },
                        success: function(res) {
                            controlModel.enable();
                            callback(res);
                        },
                        error: function() {
                            callback();
                        }
                    });
                });
            });

            controlModel.on('change', function(value) {
                controlPart.clear();
                controlPart.clearOptions();
                controlPart.disable();
                controlVariant.clear();
                controlVariant.clearOptions();
                controlVariant.disable();
                var customerCode = controlCustomer.getValue();
                if (!value || !customerCode) return;
                controlPart.load(function(callback) {
                    $.ajax({
                        url: '{{ route('kpi.api.parts') }}',
                        type: 'GET',
                        data: {
                            customer_code: customerCode,
                            model: value
                        },
                        success: function(res) {
                            controlPart.enable();
                            callback(res);
                        },
                        error: function() {
                            callback();
                        }
                    });
                });
            });

            controlPart.on('change', function(value) {
                controlVariant.clear();
                controlVariant.clearOptions();
                controlVariant.disable();
                $('#hidden_suffix').val('');
                $('#hidden_minor_change').val('');

                var customerCode = controlCustomer.getValue();
                var modelVal = controlModel.getValue();
                if (!value || !customerCode || !modelVal) return;

                controlVariant.load(function(callback) {
                    $.ajax({
                        url: '{{ route('kpi.api.variants') }}',
                        type: 'GET',
                        data: {
                            customer_code: customerCode,
                            model: modelVal,
                            part_number: value
                        },
                        success: function(res) {
                            var options = res.map(function(item) {
                                var s = item.suffix ? item.suffix : '-';
                                var mc = item.minor_change ? item.minor_change :
                                    '-';
                                return {
                                    value_string: (item.suffix || '') + '|' + (
                                        item.minor_change || ''),
                                    label_string: 'Suffix: ' + s + ' | MC: ' +
                                        mc,
                                    suffix: item.suffix,
                                    minor_change: item.minor_change
                                };
                            });
                            controlVariant.enable();
                            callback(options);
                        },
                        error: function() {
                            callback();
                        }
                    });
                });
            });

            // AUTO SUBMIT
            controlVariant.on('change', function(value) {
                if (value) {
                    var parts = value.split('|');
                    var suffixVal = parts[0] === 'null' ? '' : parts[0];
                    var mcVal = parts[1] === 'null' ? '' : parts[1];
                    $('#hidden_suffix').val(suffixVal);
                    $('#hidden_minor_change').val(mcVal);

                    // Delay sedikit untuk UX
                    setTimeout(function() {
                        $('#form-kpi').submit();
                    }, 100);
                } else {
                    $('#hidden_suffix').val('');
                    $('#hidden_minor_change').val('');
                }
            });
        });
    </script>
    @if ($selectedProject)
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const ctx = document.getElementById('kpiChart').getContext('2d');

                const labels = @json($chartLabels); // Dari Controller
                const dataValues = @json($chartValues); // Dari Controller

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'On-Time Percentage (%)',
                            data: dataValues,
                            backgroundColor: '#4e73df', // Warna Biru mirip gambar
                            borderColor: '#2e59d9',
                            borderWidth: 1,
                            barPercentage: 0.5, // Mengatur lebar batang
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100, // Mentok di 100%
                                ticks: {
                                    callback: function(value) {
                                        return value + "%"
                                    }
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            }, // Sembunyikan legend karena cuma 1 warna
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.raw + '% On-Time';
                                    }
                                }
                            }
                        }
                    }
                });
            });
        </script>
    @endif
@endsection
