@if (session()->has('error'))
    <div class="toast-container position-absolute top-50 end-0 translate-middle-y p-3">
        <div id="errorNotification" class="toast align-items-center text-bg-danger border-0" role="alert"
            aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i class="bi bi-x-square-fill text-danger me-1"></i>
                <strong class="me-auto">{{ config('app.name') }} - Error</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body bg-danger text-light">
                {{ session()->get('error') }}
            </div>
        </div>
    </div>
@endif
@if (session()->has('warning'))
    <div class="toast-container position-absolute top-50 end-0 translate-middle-y p-3">
        <div id="warningNotification" class="toast align-items-center text-bg-warning border-0" role="alert"
            aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i>
                <strong class="me-auto">{{ config('app.name') }} - Warning</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body bg-warning text-dark">
                {{ session()->get('warning') }}
            </div>
        </div>
    </div>
@endif
@if (session()->has('success'))
    <div class="toast-container position-absolute top-50 end-0 translate-middle-y p-3">
        <div id="successNotification" class="toast align-items-center text-bg-success border-0" role="alert"
            aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i class="bi bi-check-square-fill text-success me-1"></i>
                <strong class="me-auto">{{ config('app.name') }} - Success</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body bg-success text-light">
                {{ session()->get('success') }}
            </div>
        </div>
    </div>
@endif
@if ($errors->any())
    <div class="toast-container position-absolute top-50 end-0 translate-middle-y p-3">
        <div id="errorNotification" class="toast align-items-center border-0" role="alert" aria-live="assertive"
            aria-atomic="true">
            <div class="toast-header">
                <i class="bi bi-x-square-fill text-danger me-1"></i>
                <strong class="me-auto">{{ config('app.name') }} - Error</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body bg-danger text-light">
                @foreach ($errors->all() as $error)
                    - {{ $error }} <br>
                @endforeach
            </div>
        </div>
    </div>
    <script type="module">
        const toastLiveExample = document.getElementById('errorNotification')
        const toastBootstrap = bootstrap.Toast.getOrCreateInstance(toastLiveExample)
        toastBootstrap.show();
    </script>
@endif
@session('error')
    <script type="module">
        const toastLiveExample = document.getElementById('errorNotification')
        const toastBootstrap = bootstrap.Toast.getOrCreateInstance(toastLiveExample)
        toastBootstrap.show();
    </script>
@endsession
@session('success')
    <script type="module">
        const toastLiveExample = document.getElementById('successNotification')
        const toastBootstrap = bootstrap.Toast.getOrCreateInstance(toastLiveExample)
        toastBootstrap.show();
    </script>
@endsession
@session('warning')
    <script type="module">
        const toastLiveExample = document.getElementById('warningNotification')
        const toastBootstrap = bootstrap.Toast.getOrCreateInstance(toastLiveExample)
        toastBootstrap.show();
    </script>
@endsession
