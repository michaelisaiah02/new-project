@extends('layouts.app')
@section('title', 'EDIT CUSTOMER')
@section('content')
    <div class="container-fluid px-4">
        <form action="{{ route('marketing.customers.update', ['customer' => $customer->code]) }}" method="post">
            @csrf
            @method('PUT')
            <div class="row mb-3">
                <label for="code" class="form-label">Customer Code</label>
                <input type="text" class="form-control" id="code" name="code" value="{{ $customer->code }}" required>
                <div class="invalid-feedback">Customer Code must be 5 characters.</div>
            </div>
            <div class="row mb-3">
                <label for="name" class="form-label">Customer Name</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ $customer->name }}"
                    required>
                <div class="invalid-feedback">Name is required.</div>
            </div>
            <div class="row mb-3">
                <label for="department" class="form-label">Department</label>
                <select class="form-select" id="department" name="department_id" required>
                    <option value="" disabled>Choose Department</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}" @selected(old('department_id', data_get($customer, 'department_id', data_get($customer, 'department.id'))) == $department->id)>
                            {{ $department->name }}
                        </option>
                    @endforeach
                </select>
                <div class="invalid-feedback">Department must be selected.</div>
            </div>
            <div class="row justify-content-between">
                <div class="col-auto">
                    <a href="{{ route('marketing.customers.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </div>
        </form>
    </div>
    <x-toast />
@endsection
