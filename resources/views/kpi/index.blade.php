@extends('layouts.app')
@section('title', 'KPI NEW PROJECT')

@section('styles')
    <style>
        /* 1. Label Box Konsisten */
        .label-box {
            min-width: 100px;
            text-align: start;
            white-space: nowrap;
        }

        /* 2. Selectize Custom Theme (Yellow/Black) */
        .selectize-input {
            background-color: var(--bs-warning-bg-subtle) !important;
            border: 1px solid var(--bs-warning-border-subtle) !important;
            border-radius: 0 0.375rem 0.375rem 0 !important;
            /* Rounded kanan doang */
            padding-top: 0.5rem !important;
            padding-bottom: 0.5rem !important;
            box-shadow: none !important;
        }

        /* Hilangkan border radius kiri karena nempel sama label */
        .selectize-control.single .selectize-input {
            border-top-left-radius: 0 !important;
            border-bottom-left-radius: 0 !important;
        }

        .selectize-input.focus {
            border-color: #86b7fe !important;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
        }

        .selectize-dropdown {
            border-color: var(--bs-warning-border-subtle) !important;
        }

        .selectize-dropdown-content {
            background-color: #fff !important;
        }

        .selectize-dropdown-content .option.active {
            background-color: #fff3cc !important;
            /* Kuning muda pas hover */
            color: #000 !important;
        }

        /* 3. Chart Container Responsive */
        .chart-container {
            position: relative;
            height: 250px;
            width: 100%;
        }

        @media (max-width: 768px) {
            .chart-container {
                height: 250px;
                /* Di HP jangan terlalu tinggi */
            }
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid mt-3">

        <form action="{{ route('kpi.index') }}" method="get" id="form-kpi">
            <div class="row g-2 justify-content-center mb-2">

                {{-- 1. PILIH CUSTOMER --}}
                <div class="col-12 col-md-6 col-lg-5">
                    <div class="input-group">
                        <span
                            class="input-group-text label-box border-dark border-3 bg-warning-subtle fw-bold">Customer</span>
                        <select id="customer" name="customer" class="form-select">
                            <option value="">Pilih Customer</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->code }}"
                                    {{ request('customer') == $customer->code ? 'selected' : '' }}>
                                    {{ $customer->code }} - {{ $customer->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- 2. PILIH MODEL --}}
                <div class="col-12 col-md-6 col-lg-5">
                    <div class="input-group">
                        <span class="input-group-text label-box border-dark border-3 bg-warning-subtle fw-bold">Model</span>
                        <select id="model" name="model" disabled class="form-select">
                            <option value="">Pilih Model</option>
                            {{-- AJAX --}}
                        </select>
                    </div>
                </div>

                {{-- 3. PILIH PART NUMBER --}}
                <div class="col-12 col-md-6 col-lg-5">
                    <div class="input-group">
                        <span class="input-group-text label-box border-dark border-3 bg-warning-subtle fw-bold">Part
                            No.</span>
                        <select id="part_number" name="part_number" disabled class="form-select">
                            <option value="">Pilih Part Number</option>
                            {{-- AJAX --}}
                        </select>
                    </div>
                </div>

                {{-- 4. PILIH VARIANT --}}
                <div class="col-12 col-md-6 col-lg-5">
                    <div class="input-group">
                        <span
                            class="input-group-text label-box border-dark border-3 bg-warning-subtle fw-bold">Suffix/MC</span>
                        <select id="variant_combo" name="variant_combo" disabled class="form-select">
                            <option value="">Pilih Suffix - MC</option>
                            {{-- AJAX --}}
                        </select>
                    </div>
                    <input type="hidden" name="suffix" id="hidden_suffix">
                    <input type="hidden" name="minor_change" id="hidden_minor_change">
                </div>
            </div>
        </form>

        @if ($selectedProject)
            @php
                $hasDelay = $delayDocuments->count() > 0;
                $chartCol = $hasDelay ? 'col-lg-8' : 'col-12';
            @endphp

            <div class="row g-3 align-items-stretch">
                {{-- KOLOM KIRI: CHART --}}
                <div class="{{ $chartCol }}">
                    <div class="card shadow-sm h-100 border-secondary-subtle">
                        <div class="card-header bg-primary text-white py-2">
                            <h5 class="mb-0 fs-6 fw-bold">
                                <i class="bi bi-bar-chart-fill me-2"></i>
                                Performance: {{ $selectedProject->part_number }} - {{ $selectedProject->suffix }} -
                                {{ $selectedProject->minor_change }}
                            </h5>
                        </div>
                        <div class="card-body d-flex align-items-center justify-content-center">
                            <div class="chart-container">
                                <canvas id="kpiChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- KOLOM KANAN: LIST DELAY (Optional) --}}
                @if ($hasDelay)
                    <div class="col-lg-4">
                        <div class="card border-danger shadow-sm h-100">
                            <div
                                class="card-header bg-danger text-white py-2 d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>Late List</h6>
                                <span class="badge bg-white text-danger fw-bold rounded-pill">{{ $delayDocuments->count() }}
                                    Docs</span>
                            </div>

                            <div class="card-body p-0" style="max-height: 250px; overflow-y: auto;">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover table-sm mb-0" style="font-size: 0.8rem;">
                                        <thead class="table-light sticky-top shadow-sm">
                                            <tr>
                                                <th class="ps-3">Doc Type</th>
                                                <th class="text-center">Late (Days)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($delayDocuments as $doc)
                                                <tr>
                                                    <td class="ps-3 align-middle">
                                                        <div class="fw-bold text-truncate" style="max-width: 180px;">
                                                            {{ $doc->documentType->name ?? $doc->document_type_code }}
                                                        </div>
                                                        <div class="d-flex gap-2 mt-1">
                                                            <span
                                                                class="badge bg-secondary-subtle text-dark border border-secondary-subtle">
                                                                Due:
                                                                {{ \Carbon\Carbon::parse($doc->due_date)->format('d/m/y') }}
                                                            </span>
                                                            <span
                                                                class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                                                Act:
                                                                {{ \Carbon\Carbon::parse($doc->actual_date)->format('d/m/y') }}
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td class="text-center align-middle">
                                                        <span class="badge bg-danger fs-6 rounded-pill">
                                                            {{ \Carbon\Carbon::parse($doc->actual_date)->diffInDays(\Carbon\Carbon::parse($doc->due_date)) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            @if ($delayDocuments->count() > 5)
                                <div class="card-footer bg-light text-center py-1">
                                    <small class="text-muted fst-italic" style="font-size: 0.7rem;">Scroll for more</small>
                                </div>
                            @endif
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
        <div class="row sticky-bottom mt-2 mb-3">
            <div class="col-auto">
                <a href="{{ $backUrl }}" class="btn btn-primary px-4 border-3 border-light-subtle shadow-sm">Back</a>
            </div>
        </div>

    </div>
    <x-toast />
@endsection

@section('scripts')
    <script type="module">
        $(document).ready(function() {
            // --- 0. DATA OLD VALUES ---
            var oldCustomer = "{{ request('customer') }}";
            var oldModel = "{{ request('model') }}";
            var oldPart = "{{ request('part_number') }}";
            var oldSuffix = "{{ request('suffix') }}";
            var oldMc = "{{ request('minor_change') }}";
            var oldVariant = (oldSuffix || oldMc) ? (oldSuffix || '') + '|' + (oldMc || '') : null;

            // --- 1. INISIALISASI SELECTIZE ---
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
                        return '<div class="px-2 py-1"><div class="fw-bold">' + escape(item
                                .part_number) +
                            '</div><small class="text-muted">' + escape(item.part_name || '-') +
                            '</small></div>';
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

            // --- 2. LOGIKA RE-POPULATE (OLD DATA) ---
            if (oldCustomer) {
                // A. Load Model
                $.ajax({
                    url: '{{ route('kpi.api.models') }}',
                    type: 'GET',
                    data: {
                        customer_code: oldCustomer
                    },
                    success: function(res) {
                        controlModel.enable();
                        controlModel.addOption(res);
                        if (oldModel) {
                            controlModel.setValue(oldModel, true);

                            // B. Load Part
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
                                        controlPart.setValue(oldPart, true);

                                        // C. Load Variant
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

            // --- 3. EVENT LISTENERS (CASCADE) ---
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

            // --- 4. AUTO SUBMIT ---
            controlVariant.on('change', function(value) {
                if (value) {
                    var parts = value.split('|');
                    var suffixVal = parts[0] === 'null' ? '' : parts[0];
                    var mcVal = parts[1] === 'null' ? '' : parts[1];
                    $('#hidden_suffix').val(suffixVal);
                    $('#hidden_minor_change').val(mcVal);

                    // Show Loading or Toast here if needed
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
                const labels = @json($chartLabels);
                const dataValues = @json($chartValues);

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'On-Time Percentage (%)',
                            data: dataValues,
                            backgroundColor: '#0d6efd',
                            /* Bootstrap Primary Blue */
                            borderColor: '#0a58ca',
                            borderWidth: 1,
                            borderRadius: 4,
                            barPercentage: 0.6,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        /* Biar ngikutin tinggi container */
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                grid: {
                                    color: '#e9ecef'
                                },
                                ticks: {
                                    callback: function(value) {
                                        return value + "%"
                                    }
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
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
