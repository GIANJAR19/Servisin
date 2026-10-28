<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SERVISIN - Sistem Antrean dan Booking Bengkel')</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --servisin-navy: #1b385a;
            --servisin-blue: #23527c;
            --servisin-light: #f4f6f9;
        }
        body {
            background-color: var(--servisin-light);
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            min-height: 100vh;
        }
        .sidebar {
            background-color: var(--servisin-navy);
            color: #ffffff;
            min-height: 100vh;
            width: 250px;
            box-shadow: 2px 0 10px rgba(0,0,0,0.08);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
        }
        .sidebar .brand {
            padding: 1.25rem 1.5rem;
            font-size: 1.35rem;
            font-weight: 800;
            letter-spacing: 1px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar .nav-section-title {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: rgba(255,255,255,0.5);
            padding: 1.2rem 1.5rem 0.4rem;
            font-weight: 700;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.85);
            padding: 0.7rem 1.5rem;
            font-size: 0.92rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.2s ease;
        }
        .sidebar .nav-link:hover {
            color: #ffffff;
            background-color: rgba(255,255,255,0.1);
        }
        .sidebar .nav-link.active {
            color: #ffffff;
            background-color: #0d6efd;
            font-weight: 600;
        }
        .sidebar-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 1rem 1.25rem;
            border-top: 1px solid rgba(255,255,255,0.1);
            background-color: rgba(0,0,0,0.15);
        }
        .content-area {
            margin-left: 250px;
            padding: 1.75rem 2rem;
        }
        @media (max-width: 991.98px) {
            .sidebar {
                position: static;
                width: 100%;
                min-height: auto;
            }
            .content-area {
                margin-left: 0;
                padding: 1rem;
            }
            .sidebar-footer {
                position: static;
            }
        }
        .badge-status-Menunggu {
            background-color: #6c757d;
            color: #ffffff;
        }
        .badge-status-Dikerjakan {
            background-color: #ffc107;
            color: #000000;
        }
        .badge-status-Selesai {
            background-color: #198754;
            color: #ffffff;
        }
        .badge-status-Dibatalkan {
            background-color: #dc3545;
            color: #ffffff;
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="d-flex flex-column flex-lg-row">
        <!-- Sidebar Sesuai Wireframe F.4b -->
        <aside class="sidebar">
            <div class="brand d-flex align-items-center justify-content-between">
                <div>
                    <i class="bi bi-tools text-primary me-2"></i>SERVISIN
                </div>
            </div>

            <nav class="nav flex-column mt-2">
                <div class="nav-section-title">Menu Utama</div>
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> Dashboard Antrean
                </a>
                <a href="{{ route('booking.admin.index') }}" class="nav-link {{ request()->routeIs('booking.*') ? 'active' : '' }}">
                    <i class="bi bi-calendar-check"></i> Booking
                </a>
                <a href="{{ route('pelanggan.index') }}" class="nav-link {{ request()->routeIs('pelanggan.*') || request()->routeIs('kendaraan.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i> Pelanggan & Kendaraan
                </a>
                <a href="{{ route('layanan.index') }}" class="nav-link {{ request()->routeIs('layanan.*') ? 'active' : '' }}">
                    <i class="bi bi-wrench-adjustable"></i> Layanan Servis
                </a>

                <div class="nav-section-title">Sistem</div>
                @if(auth()->user()->isPemilik())
                <a href="{{ route('pengguna.index') }}" class="nav-link {{ request()->routeIs('pengguna.*') ? 'active' : '' }}">
                    <i class="bi bi-shield-lock"></i> Kelola Pengguna 
                    <span class="badge bg-warning text-dark ms-auto" style="font-size: 0.68rem;">Pemilik</span>
                </a>
                @endif
                <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                    <i class="bi bi-person-gear"></i> Pengaturan Akun
                </a>
                <a href="{{ route('booking.index') }}" target="_blank" class="nav-link text-info">
                    <i class="bi bi-box-arrow-up-right"></i> Halaman Booking Publik
                </a>
            </nav>

            <div class="sidebar-footer">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2 overflow-hidden">
                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; font-weight: bold; flex-shrink: 0;">
                            {{ substr(auth()->user()->nama, 0, 1) }}
                        </div>
                        <div class="text-truncate">
                            <div class="small fw-bold text-white text-truncate">{{ auth()->user()->nama }}</div>
                            <div class="text-white-50" style="font-size: 0.72rem;">{{ auth()->user()->role }}</div>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-light" title="Keluar">
                            <i class="bi bi-box-arrow-right"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="content-area flex-grow-1">
            <!-- Flash Message Alerts -->
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if($errors->any())
            <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-circle-fill me-2"></i> Periksa kembali data formulir yang Anda kirimkan:
                <ul class="mb-0 mt-1 small">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
