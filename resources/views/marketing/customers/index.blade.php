@extends('layouts.app')

@section('title', 'MANAGEMENT USER')

@section('content')
    <div class="container mt-3">
        <div class="row justify-content-md-end justify-content-center align-items-center mb-3">
            <div class="col-auto my-2 my-md-0 d-flex align-items-center">
                <div id="loading-spinner" style="display: none;" class="text-center me-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <input type="search" class="form-control" placeholder="Search..." id="search-customer" autocomplete="off">
            </div>
        </div>
        <div class="table-responsive text-nowrap mb-3" style="max-height: 350px; overflow-y: auto;">
            <table class="table table-sm table-bordered m-0" id="customer-table">
                <thead class="table-primary sticky-top">
                    <tr class="text-center">
                        <th>No</th>
                        <th>Customer Code</th>
                        <th>Customer Name</th>
                        <th>Departement</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="customer-table-body">
                    {{-- Data will generate by AJAX --}}
                </tbody>
            </table>
        </div>
        <div
            class="text-center row justify-content-between align-items-start position-absolute bottom-0 start-0 end-0 mb-3 mx-3">
            <div class="col-auto">
                <a href="{{ route('marketing') }}" class="btn btn-primary fs-5">Back</a>
            </div>
            <div class="col-auto">
                <button class="btn btn-primary btn-lg text-white rounded-pill m-0 py-2" data-bs-toggle="modal"
                    data-bs-target="#customerModal" id="btn-add-customer">
                    <i class="bi bi-plus-lg fs-5"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Tambah/Edit Customer -->
    <div class="modal fade modal-sm" id="customerModal" tabindex="-1" aria-labelledby="customerModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content needs-validation" method="POST" id="customerForm" novalidate>
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="customerModalLabel">Add Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <label for="code" class="form-label">Customer Code</label>
                        <input type="text" class="form-control" id="code" name="code" minlength="5"
                            maxlength="5" required>
                        <div class="invalid-feedback">Customer Code must be 5 characters.</div>
                    </div>
                    <div class="row">
                        <label for="name" class="form-label">Customer Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                        <div class="invalid-feedback">Name is required.</div>
                    </div>
                    <div class="row">
                        <label for="department" class="form-label">Department</label>
                        <select class="form-select" id="department" name="department_id">
                            <option value="" disabled selected>Choose Department</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">Department must be selected.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
    <!-- Modal Delete Customer -->
    <div class="modal fade" id="deleteCustomerModal" tabindex="-1" aria-labelledby="deleteCustomerModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" id="deleteCustomerForm" class="modal-content">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteCustomerModalLabel">Delete Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the customer named <strong id="deleteCustomerName"></strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
    <x-toast />
@endsection

@section('scripts')
    <script type="module">
        function fetchCustomers(keyword = '', page = 1, role = '') {
            $('#loading').show();
            $.ajax({
                url: `{{ route('marketing.customers.search') }}`,
                type: 'GET',
                data: {
                    keyword: keyword,
                    page: page,
                    role: role
                },
                success: function(response) {
                    $('#customer-table-body').html(response.html);
                    $('#pagination-links').html(response.pagination);
                    $('html, body').animate({
                        scrollTop: $('#customer-table').offset().top - 100
                    }, 300);
                    $('.pagination nav').addClass('w-100');
                },
                complete: function() {
                    $('#loading').hide();
                },
                error: function() {
                    alert('Gagal memuat data.');
                }
            });
        }

        $(document).ready(function() {
            // Add Customer
            $('#btn-add-customer').click(function() {
                $('#customerForm').trigger('reset');
                $('#customerModalLabel').text('Add Customer');
                $('#customerForm').attr('action', "{{ route('marketing.customers.store') }}");
            });

            // Delegasi tombol Edit
            $(document).on('click', '.btn-edit-customer', function() {
                const id = $(this).data('id');
                $('#code').val(id);
                $('#name').val($(this).data('name'));
                $('#department').val($(this).data('department'));
                $('#customerModalLabel').text('Edit Customer');
                $('#customerForm').attr('action',
                    `{{ url('marketing/customers/update-customer') }}/${id}`);
                new bootstrap.Modal(document.getElementById('customerModal')).show();
            });

            // Delegasi tombol Delete
            $(document).on('click', '.btn-delete-customer', function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                $('#deleteCustomerForm').attr('action',
                    `{{ url('marketing/customers/delete-customer') }}/${id}`);
                $('#deleteCustomerName').text(name);
            });

            const $superiorGroup = $('#superiorForm');
            $superiorGroup.hide();

            function toggleSuperior() {
                const roleFilled = $('#role').val();
                const departmentFilled = $('#department').val();

                if (roleFilled && departmentFilled) {
                    $superiorGroup.show();
                    fetchSuperiors();
                } else {
                    $superiorGroup.hide();
                    $('#superior').val('');
                }
            }

            $('#role, #department').on('change', toggleSuperior);

            // Form Validation
            $('.needs-validation').on('submit', function(e) {
                if (!this.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                $(this).addClass('was-validated');
            });

            let debounceTimer;
            $('#search-customer').on('keyup', function() {
                clearTimeout(debounceTimer);
                const keyword = $(this).val();
                debounceTimer = setTimeout(() => {
                    fetchCustomers(keyword);
                }, 400);
            });

            // Initial fetch
            fetchCustomers();
        });
    </script>
@endsection
