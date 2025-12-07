@extends('layouts.app')
@section('title', 'NEW PROJECT')
@section('content')
    <div class="container">
        <div class="row justify-content-center align-items-center my-3">
            <div class="col-4">
                <a href="{{ route('marketing.new-projects.index') }}"
                    class="btn btn-primary py-2 px-5 rounded-4 menu-btn btn1 w-100">INPUT NEW
                    PROJECT</a>
            </div>
        </div>
        <div class="row justify-content-center align-items-center">
            <div class="col-4">
                <a href="{{ route('marketing.users.index') }}"
                    class="btn btn-primary py-2 px-5 rounded-4 menu-btn btn1 w-100">MANAGEMENT USER</a>
            </div>
            <div class="col-4">
                <a href="{{ route('marketing.customers.index') }}"
                    class="btn btn-primary py-2 px-5 rounded-4 menu-btn btn1 w-100">MANAGEMENT CUSTOMER</a>
            </div>
        </div>
        <div
            class="text-center row justify-content-start align-items-center position-absolute bottom-0 start-0 end-0 mb-3 mx-3">
            <div class="col-auto">
                <form action="{{ route('logout') }}" method="post">
                    @csrf
                    <button type="submit" class="btn btn-primary border-3 border-light-subtle">LOGOUT</button>
                </form>
            </div>
        </div>
    </div>
@endsection
