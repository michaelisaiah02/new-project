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
    @vite(['resources/css/app.css', 'resources/sass/app.scss', 'resources/js/app.js'])
    @yield('styles')
    @if (!request()->is('login'))
        <style>
            #navbar-new-project {
                height: 6.5rem;
            }
        </style>
    @endif
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light text-light py-0 my-0 {{ request()->is('login') ? 'bg-transparent px-3' : 'bg-primary' }}"
        id="navbar-new-project">
        <div class="container-fluid justify-content-between my-0 py-0">
            <a class="navbar-brand mx-0 mx-md-4" href="/">
                <img src="{{ asset('image/logo-pt.png') }}" alt="Logo" class="mt-0 logo">
            </a>
            <div class="row text-center justify-content-end align-items-center w-75 {{ request()->is('login') ? 'text-light' : 'bg-none' }}"
                id="title-section">
                <div class="col-md-9">
                    <p class="align-self-center company-name py-0 my-0 lh-lg">PT. CATURINDO AGUNGJAYA RUBBER</p>
                    <p id="main-title" class="align-self-center main-title py-0 my-0 text-uppercase lh-1 shadow-sm">
                        @yield('title')</p>
                </div>
                <div class="col-md-auto">
                    <div class="card my-0 py-0">
                        <div class="card-body my-0 py-0 bg-secondary-subtle border-3 rounded-3 text-center">
                            <p class="py-0 my-0">{{ auth()->id() }}</p>
                            <p class="py-0 my-0">{{ auth()->user()->name }}</p>
                        </div>
                    </div>
                </div>
                @stack('subtitle')
            </div>
            <a class="navbar-brand mx-0 mx-md-4" href="/">
                <img src="{{ asset('image/logo-rice.png') }}" alt="Logo" class="mt-0 logo">
            </a>
        </div>
    </nav>
    <!-- Modal Auto Logout -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logoutModalLabel">Auto Logout</h5>
                </div>
                <div class="modal-body">
                    No activity detected. You will be logged out automatically in a few seconds...
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary"
                        onclick="localStorage.removeItem('forceLogout'); document.getElementById('auto-logout-form').submit();">
                        OK
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Logout -->
    <form id="auto-logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>
    @yield('content')
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
    @endauth
    <div id="connection-indicator" style="display: none; position: fixed; bottom: 1rem; right: 1rem; z-index: 9999;">
        <div class="alert alert-danger mb-0 py-2 px-3" role="alert">
            ⚠️ Connection was lost...
        </div>
    </div>
    @auth
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
