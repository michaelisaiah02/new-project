@extends('layouts.app')

@section('title', 'New Project')

@section('styles')
    <style>
        body {
            background-image: url("image/bg.jpeg");
            background-size: 100%;
            background-repeat: no-repeat;
        }

        /* Penjelasan: Menambahkan style untuk judul agar lebih terlihat */
        .login-title {
            font-size: 2.5rem;
            font-weight: bold;
            color: white;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            margin-bottom: 1rem;
        }
    </style>
@endsection

@section('content')
    <div class="container">
        <div class="row justify-content-center align-items-center">
            <div class="col-auto">
                <div
                    class="card bg-primary bg-opacity-50 shadow-sm border-0 rounded-4 position-absolute bottom-0 start-50 translate-middle">
                    <div class="card-body p-4">
                        <form action="{{ route('login') }}" method="POST" id="loginForm">
                            @csrf
                            <div class="form-floating mb-3 mx-auto">
                                <input type="text" class="form-control text-center bg-light" placeholder="Employee ID"
                                    id="employeeID" name="employeeID" value="{{ old('employeeID') }}" required autofocus>
                                <label for="employeeID">Employee ID</label>
                            </div>
                            <div class="form-floating mb-3 mx-auto">
                                <input type="password" class="form-control text-center bg-light" placeholder="Password"
                                    id="password" name="password" required autocomplete="current-password">
                                <label for="password">Password</label>
                            </div>
                            <div class="row justify-content-center mx-auto px-2">
                                <button type="submit"
                                    class="btn btn-primary text-light fw-bold text-center mb-2 mx-auto">Login</button>
                                <p class="text-center fst-italic text-light small">Jika login gagal, hubungi (IT)</p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <x-toast />
@endsection

@section('scripts')
    {{-- Script idle-timeout Anda tetap di sini, tidak perlu diubah --}}
    <script>
        const MAX_IDLE_TIME = 15 * 60 * 1000; // 15 menit
        let lastActivity = Date.now();

        function resetActivityTimer() {
            lastActivity = Date.now();
        }

        function checkIdleTime() {
            const now = Date.now();
            if (now - lastActivity > MAX_IDLE_TIME) {
                location.reload(); // reload kalau terlalu lama diam
            }
        }

        // Dengarkan semua aktivitas user
        ['click', 'mousemove', 'keydown', 'scroll', 'touchstart'].forEach(evt => {
            document.addEventListener(evt, resetActivityTimer);
        });

        // Cek setiap 1 menit
        setInterval(checkIdleTime, 60 * 1000);
    </script>
@endsection
