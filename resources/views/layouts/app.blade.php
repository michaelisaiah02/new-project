<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'New Project')</title>

    <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="shortcut icon" href="/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
    <link rel="manifest" href="/site.webmanifest" />

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    @yield('styles')

</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light text-light py-0 my-0 {{ request()->is('login') ? 'bg-transparent' : 'bg-primary navbar-custom' }}"
        id="navbar-project">

        <div class="container-fluid justify-content-between px-0 mx-0">
            <a class="navbar-brand mx-2 mx-md-3" href="/">
                <img src="{{ asset('image/logo-pt.png') }}" alt="Logo PT" class="logo">
            </a>

            <div
                class="row text-center justify-content-md-between align-items-center mx-0 px-0 w-75 {{ request()->is('login') ? 'text-light' : '' }}">
                <div class="{{ request()->is('login') ? 'col-12' : 'col-md-8' }}">

                    <p class="company-name py-0 my-0 text-uppercase">
                        PT. CATURINDO AGUNGJAYA RUBBER
                    </p>

                    @if (request()->is('login') || request()->is('/') || request()->is('main-menu'))
                        <p
                            class="main-title py-0 my-0 text-uppercase {{ request()->is('login') ? '' : 'text-shadow-sm' }}">
                            @yield('title')
                        </p>
                    @else
                        {{-- Class 'sub-title-box' menghandle border dotted & styling --}}
                        <div
                            class="d-inline-block px-4 py-1 mt-1 sub-title-box border-light-subtle rounded-2 bg-secondary-subtle text-dark text-uppercase w-100">
                            @yield('title')
                        </div>
                    @endif
                </div>
                @if (!request()->is('login'))
                    <div class="col-md-4 py-1 py-md-0 d-none d-md-block"> {{-- d-none d-md-block biar di HP ga menuhin layar --}}
                        <div class="card bg-secondary-subtle border-light-subtle rounded-3 text-center px-3 py-1">
                            <small class="fw-bold text-dark d-block">{{ auth()->id() }} -
                                {{ auth()->user()->name }}</small>
                            <small class="text-muted d-block">{{ auth()->user()->department->name }}</small>
                        </div>
                    </div>
                @endif
            </div>

            <a class="navbar-brand mx-2 mx-md-3" href="/">
                <img src="{{ asset('image/logo-rice.png') }}" alt="Logo Rice" class="logo">
            </a>
        </div>
    </nav>

    <div class="modal fade" id="logoutModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Session Expired</h5>
                </div>
                <div class="modal-body">
                    No activity detected. You will be logged out automatically.
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary w-100"
                        onclick="localStorage.removeItem('forceLogout'); document.getElementById('auto-logout-form').submit();">
                        OK
                    </button>
                </div>
            </div>
        </div>
    </div>

    <form id="auto-logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
    </form>

    @yield('content')

    <div id="connection-indicator" style="display: none;">
        <div class="alert alert-danger shadow-lg mb-0 py-2 px-3 fw-bold rounded-pill" role="alert">
            <i class="bi bi-wifi-off me-2"></i> Connection lost...
        </div>
    </div>
    @yield('scripts')
    @auth
        <script type="module">
            const maxIdleTime = 5 * 60 * 1000; // 5 menit
            const idleStartDelay = 5 * 1000; // 5 detik
            let idleTimeout, idleInterval;
            let lastActiveTime = Date.now();

            function resetIdleTimer() {
                if (localStorage.getItem('forceLogout') === 'true') return;
                lastActiveTime = Date.now();
                clearTimeout(idleTimeout);
                clearInterval(idleInterval);
                idleTimeout = setTimeout(startIdleCounter, idleStartDelay);
                console.log('[Idle Timer] Reset by user activity');
            }

            function startIdleCounter() {
                console.log('[Idle Timer] Start idle counter');
                idleInterval = setInterval(() => {
                    const now = Date.now();
                    const idleDuration = now - lastActiveTime;
                    console.log(`[Idle Timer] Idle duration: ${idleDuration}ms`);

                    if (idleDuration >= maxIdleTime) {
                        console.log('[Idle Timer] Max idle reached. Triggering logout...');
                        localStorage.setItem('forceLogout', 'true');
                        showLogoutModal();
                    }
                }, 1000);
            }

            function showLogoutModal() {
                clearInterval(idleInterval);
                clearTimeout(idleTimeout);

                const modalEl = document.getElementById('logoutModal');
                if (!modalEl || modalEl.classList.contains('show')) return;

                const logoutModal = new bootstrap.Modal(modalEl);
                logoutModal.show();

                // Auto logout dalam 10 detik
                setTimeout(() => {
                    if (document.getElementById('logoutModal').classList.contains('show')) {
                        localStorage.removeItem('forceLogout');
                        document.getElementById('auto-logout-form').submit();
                    }
                }, 10 * 1000);
            }

            // Saat halaman load
            window.addEventListener('load', () => {
                if (localStorage.getItem('forceLogout') === 'true') {
                    console.log('[Idle Timer] Detected forceLogout on page load');
                    showLogoutModal();
                } else {
                    idleTimeout = setTimeout(startIdleCounter, idleStartDelay);
                }
            });

            // Dengarkan event storage dari tab lain
            window.addEventListener('storage', (event) => {
                if (event.key === 'forceLogout' && event.newValue === 'true') {
                    console.log('[Idle Timer] Detected forceLogout from another tab');
                    showLogoutModal();
                }
            });

            // Deteksi aktivitas user
            ['mousemove', 'keydown', 'click', 'scroll'].forEach(event => {
                document.addEventListener(event, resetIdleTimer);
            });
        </script>

        <script>
            const connectionIndicator = document.getElementById('connection-indicator');
            let isOffline = false;

            async function checkConnection() {
                try {
                    const controller = new AbortController();
                    const timeoutId = setTimeout(() => controller.abort(), 5000); // timeout 5 detik
                    const response = await fetch("{{ route('ping') }}", {
                        method: 'GET',
                        signal: controller.signal,
                        cache: 'no-store',
                    });
                    clearTimeout(timeoutId);

                    if (!response.ok) throw new Error('Server Error');

                    if (isOffline) {
                        // Koneksi kembali normal
                        connectionIndicator.style.display = 'none';
                        isOffline = false;
                    }
                } catch (error) {
                    if (!isOffline) {
                        connectionIndicator.style.display = 'block';
                        isOffline = true;
                    }
                }
            }

            setInterval(checkConnection, 10000); // cek tiap 10 detik
        </script>
    @endauth
</body>

</html>
