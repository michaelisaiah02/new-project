@extends('layouts.app')

@section('title', 'MANAGEMENT DOCUMENT TYPES')

@section('content')
    <div class="container mt-3">
        <div class="row justify-content-md-end justify-content-center align-items-center mb-3">
            <div class="col-auto my-2 my-md-0 d-flex align-items-center">
                <div id="loading-spinner" style="display: none;" class="text-center me-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <input type="search" class="form-control" placeholder="Search..." id="search-document-type"
                    autocomplete="off">
            </div>
        </div>
        <div class="table-responsive text-nowrap mb-3" style="max-height: 350px; overflow-y: auto;">
            <table class="table table-sm table-bordered m-0" id="document-type-table">
                <thead class="table-primary sticky-top">
                    <tr class="text-center">
                        <th>No</th>
                        <th>Document Type</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="document-type-table-body">
                    {{-- Data will generate by AJAX --}}
                </tbody>
            </table>
        </div>
        <div
            class="text-center row justify-content-between align-items-start position-absolute bottom-0 start-0 end-0 mb-3 mx-3">
            <div class="col-auto">
                <a href="{{ route('index') }}" class="btn btn-primary fs-5">Back</a>
            </div>
            <div class="col-auto">
                <button class="btn btn-primary btn-lg text-white rounded-pill m-0 py-2" data-bs-toggle="modal"
                    data-bs-target="#documentTypeModal" id="btn-add-document-type">
                    <i class="bi bi-plus-lg fs-5"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Tambah/Edit Document Type -->
    <div class="modal fade modal-sm" id="documentTypeModal" tabindex="-1" aria-labelledby="documentTypeModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content needs-validation" method="POST" id="documentTypeForm" novalidate>
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="documentTypeModalLabel">Add Document Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="code" name="code">
                    <div class="row">
                        <label for="name" class="form-label">Document Type Name</label>
                        <input type="text" class="form-control" id="name" name="name" pattern="^[A-Za-z\s]+$"
                            required>
                        <div class="invalid-feedback">Name must contain letters only.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
    <!-- Modal Delete Document Type -->
    <div class="modal fade" id="deleteDocumentTypeModal" tabindex="-1" aria-labelledby="deleteDocumentTypeModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" id="deleteDocumentTypeForm" class="modal-content">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteDocumentTypeModalLabel">Delete Document Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the document type named <strong
                            id="deleteDocumentTypeName">?</strong></p>
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
        function fetchDocumentTypes(keyword = '') {
            $('#loading').show();
            $.ajax({
                url: `{{ route('document-type.search') }}`,
                type: 'GET',
                data: {
                    keyword: keyword,
                },
                success: function(response) {
                    $('#document-type-table-body').html(response.html);
                    $('#pagination-links').html(response.pagination);
                    $('html, body').animate({
                        scrollTop: $('#document-type-table').offset().top - 100
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
            // Add Document Type
            $('#btn-add-document-type').click(function() {
                $('#documentTypeForm').trigger('reset');
                $('#documentTypeModalLabel').text('Add Document Type');
                $('#documentTypeForm').attr('action', "{{ route('document-type.store') }}");
            });

            $('#department').change(function() {
                const departmentId = $(this).val();
                checkDepartment(departmentId);
            });

            // Delegasi tombol Edit
            $(document).on('click', '.btn-edit', function() {
                const code = $(this).data('code');
                $('#code').val(code);
                $('#name').val($(this).data('name'));

                $('#documentTypeModalLabel').text('Edit Document Type');
                $('#documentTypeForm').attr('action',
                    `{{ url('document-type/update') }}/${code}`);
                new bootstrap.Modal(document.getElementById('documentTypeModal')).show();
            });

            // Delegasi tombol Delete
            $(document).on('click', '.btn-delete', function() {
                const code = $(this).data('code');
                const name = $(this).data('name');
                console.log(name);
                $('#deleteDocumentTypeForm').attr('action',
                    `{{ url('document-type/delete') }}/${code}`);
                $('#deleteDocumentTypeName').text(name);
            });

            // Form Validation
            $('.needs-validation').on('submit', function(e) {
                if (!this.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                $(this).addClass('was-validated');
                $('#checked, #approved').prop('disabled', false);
            });

            let debounceTimer;
            $('#search-document-type').on('keyup', function() {
                clearTimeout(debounceTimer);
                const keyword = $(this).val();
                debounceTimer = setTimeout(() => {
                    fetchDocumentTypes(keyword);
                }, 400);
            });

            // Initial fetch
            fetchDocumentTypes();
        });
    </script>
@endsection
