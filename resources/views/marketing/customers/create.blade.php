@extends('layouts.app')
@section('title', 'ADD NEW CUSTOMER')
@section('content')
    <div class="container-fluid px-4">
        <form action="{{ route('marketing.customers.store') }}" method="post">
            @csrf
            <div class="row mb-3">
                <label for="code" class="form-label">Customer Code</label>
                <input type="text" class="form-control" id="code" name="code" required>
                <div class="invalid-feedback">Customer Code must be 5 characters.</div>
            </div>
            <div class="row mb-3">
                <label for="name" class="form-label">Customer Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
                <div class="invalid-feedback">Name is required.</div>
            </div>
            <div class="row mb-3">
                <label for="department" class="form-label">Department</label>
                <select class="form-select" id="department" name="department_id">
                    <option value="" disabled selected>Choose Department</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                    @endforeach
                </select>
                <div class="invalid-feedback">Department must be selected.</div>
            </div>
            <div class="row justify-content-between">
                <div class="col-auto">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Next</button>
                </div>
            </div>
        </form>
    </div>
@endsection
