@extends('layouts.app')

@section('title', 'NEW PROJECT')

@section('content')
    <div class="container py-4">

        <div class="row g-3 justify-content-center">

            <div class="col-12 col-md-5"> <a href="{{ route('marketing.projects.index') }}"
                    class="btn btn-primary w-100 py-3 rounded-4 border-4 border-light-subtle d-flex align-items-center justify-content-center shadow-sm h-100">
                    <span class="h2 fw-bold mb-0">INPUT NEW PROJECT</span>
                </a>
            </div>

            <div class="col-12 col-md-5">
                <a href="{{ route('masspro.index') }}"
                    class="btn btn-primary w-100 py-3 rounded-4 border-4 border-light-subtle d-flex align-items-center justify-content-center shadow-sm h-100">
                    <span class="h2 fw-bold mb-0">LIST MASS PRODUCTION PART</span>
                </a>
            </div>

            <div class="col-12 col-md-5">
                <a href="{{ route('marketing.users.index') }}"
                    class="btn btn-primary w-100 py-3 rounded-4 border-4 border-light-subtle d-flex align-items-center justify-content-center shadow-sm h-100">
                    <span class="h2 fw-bold mb-0">MANAGEMENT USER</span>
                </a>
            </div>

            <div class="col-12 col-md-5">
                <a href="{{ route('marketing.customers.index') }}"
                    class="btn btn-primary w-100 py-3 rounded-4 border-4 border-light-subtle d-flex align-items-center justify-content-center shadow-sm h-100">
                    <span class="h2 fw-bold mb-0">MANAGEMENT CUSTOMER</span>
                </a>
            </div>

            <div class="col-12 col-md-5">
                <a href="{{ route('engineering') }}"
                    class="btn btn-primary w-100 py-3 rounded-4 border-4 border-light-subtle d-flex align-items-center justify-content-center shadow-sm h-100">
                    <span class="h2 fw-bold mb-0">ENGINEERINGS</span>
                </a>
            </div>

            <div class="col-12 col-md-5">
                <a href="{{ route('kpi.index') }}"
                    class="btn btn-primary w-100 py-3 rounded-4 border-4 border-light-subtle d-flex align-items-center justify-content-center shadow-sm h-100">
                    <span class="h2 fw-bold mb-0">KPI</span>
                </a>
            </div>

        </div>

        <div class="row justify-content-center mt-5 mb-3">
            <div class="col-auto">
                <form action="{{ route('logout') }}" method="post">
                    @csrf
                    <button type="submit" class="btn btn-primary px-5 py-2 fw-bold border-3 border-light-subtle shadow">
                        LOGOUT
                    </button>
                </form>
            </div>
        </div>

    </div>
@endsection
