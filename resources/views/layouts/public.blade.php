<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Booking Servis Online - SERVISIN')</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --servisin-navy: #1b385a;
            --servisin-blue: #0d6efd;
            --servisin-light: #f4f6f9;
        }
        body {
            background-color: var(--servisin-light);
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            min-height: 100vh;
        }
        .header-bar {
            background-color: var(--servisin-navy);
            color: #ffffff;
            padding: 1rem 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .slot-btn {
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 0.6rem 0.5rem;
            text-align: center;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            background: #ffffff;
            color: #212529;
            width: 100%;
        }
        .slot-btn:hover:not(.disabled) {
            border-color: #0d6efd;
            color: #0d6efd;
            background-color: #f0f7ff;
        }
        .slot-btn.selected {
            background-color: #0d6efd !important;
            border-color: #0d6efd !important;
            color: #ffffff !important;
        }
        .slot-btn.disabled {
            background-color: #f8f9fa !important;
            border-color: #e9ecef !important;
            color: #adb5bd !important;
            cursor: not-allowed;
            opacity: 0.7;
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Header publik -->
    <header class="header-bar">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="{{ route('booking.index') }}" class="text-white text-decoration-none d-flex align-items-center gap-2 fw-bold fs-4">
                <i class="bi bi-tools text-primary"></i> SERVISIN
            </a>
            <div class="d-flex align-items-center gap-3">
                <span class="text-white-50 d-none d-sm-inline">Booking Servis Online</span>
                <a href="{{ route('login') }}" class="btn btn-sm btn-outline-light">
                    <i class="bi bi-person-fill me-1"></i> Login Staf
                </a>
            </div>
        </div>
    </header>

    <main class="py-4">
        <div class="container">
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @yield('content')
        </div>
    </main>

    <footer class="text-center py-4 text-muted small">
        <div class="container">
            <p class="mb-1">&copy; {{ date('Y') }} <strong>SERVISIN</strong> - Sistem Antrean dan Booking Bengkel Mandiri.</p>
            <p class="mb-0">Dibuat oleh <strong>Gianjar Nugraha (714250007)</strong> | Proyek 2 Laravel</p>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
