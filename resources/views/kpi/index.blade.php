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
            {{-- Tentukan lebar kolom dinamis --}}
            @php
                $hasDelay = $delayDocuments->count() > 0;
                // Kalau ada delay, chart pakai 8 kolom, kalau tidak ada delay, chart full 12 kolom
                $chartCol = $hasDelay ? 'col-lg-8' : 'col-12';
            @endphp

            <div class="row">
                {{-- KOLOM KIRI: CHART --}}
                <div class="{{ $chartCol }} mb-4">
                    <div class="card shadow-sm h-100"> {{-- h-100 biar tingginya sama dengan sebelahnya --}}
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                Performance: {{ $selectedProject->part_number }} - {{ $selectedProject->suffix }} -
                                {{ $selectedProject->minor_change }}
                            </h5>
                        </div>
                        <div class="card-body">
                            {{-- Container Chart --}}
                            <div style="position: relative; height: 280px; width: 100%;">
                                <canvas id="kpiChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- KOLOM KANAN: LIST DELAY (Hanya muncul jika ada delay) --}}
                @if ($hasDelay)
                    <div class="col-lg-4 mb-4">
                        <div class="card border-danger shadow-sm h-100">
                            <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                                <h6 class="mb-0"><i class="bi bi-exclamation-triangle-fill me-2"></i>Late List</h6>
                                <span class="badge bg-white text-danger">{{ $delayDocuments->count() }} Docs</span>
                            </div>

                            {{--
                        Kita kasih max-height dan overflow-auto
                        supaya kalau listnya panjang, dia bisa discroll
                        dan tidak bikin chart di sebelahnya jadi gepeng/kosong bawahnya
                    --}}
                            <div class="card-body p-0" style="max-height: 280px; overflow-y: auto;">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover table-sm mb-0"
                                        style="font-size: 0.85rem;">
                                        <thead class="table-light sticky-top">
                                            <tr>
                                                <th>Doc Type</th>
                                                <th class="text-center">Late</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($delayDocuments as $doc)
                                                <tr>
                                                    <td>
                                                        <div class="fw-bold text-truncate" style="max-width: 150px;"
                                                            title="{{ $doc->documentType->name }}">
                                                            {{ $doc->documentType->name ?? $doc->document_type_code }}
                                                        </div>
                                                        <small class="text-muted">
                                                            Due:
                                                            {{ \Carbon\Carbon::parse($doc->due_date)->format('d/m/y') }}
                                                        </small>
                                                        <small class="text-muted ms-3">
                                                            Actual:
                                                            {{ \Carbon\Carbon::parse($doc->actual_date)->format('d/m/y') }}
                                                        </small>
                                                    </td>
                                                    <td class="text-center align-middle">
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
                            <div class="card-footer bg-light text-center">
                                @if ($delayDocuments->count() > 5)
                                    <small class="text-muted">Scroll untuk melihat lebih banyak</small>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
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
