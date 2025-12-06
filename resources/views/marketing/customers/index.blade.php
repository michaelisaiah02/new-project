@extends('layouts.app')

@section('title', 'MANAGEMENT CUSTOMERS')

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
                <a href="{{ route('marketing.customers.create') }}"
                    class="btn btn-primary btn-lg text-white rounded-pill m-0 py-2">
                    <i class="bi bi-plus-lg fs-5"></i>
                </a>
            </div>
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
            // Delegasi tombol Delete
            $(document).on('click', '.btn-delete-customer', function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                $('#deleteCustomerForm').attr('action',
                    `{{ url('marketing/customers/delete-customer') }}/${id}`);
                $('#deleteCustomerName').text(name);
            });

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
