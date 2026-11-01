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
            padding: 1.5rem 2rem;
        }
        .topbar {
            background-color: #ffffff;
            border-radius: 12px;
            padding: 0.75rem 1.25rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
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
    @php
        $unreadNotifCount = \App\Models\Notifikasi::unread()->count();
        $recentNotifs = \App\Models\Notifikasi::recent(5)->get();
    @endphp

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
                <a href="{{ route('notifikasi.index') }}" class="nav-link {{ request()->routeIs('notifikasi.*') ? 'active' : '' }}">
                    <i class="bi bi-bell"></i> Notifikasi
                    @if($unreadNotifCount > 0)
                    <span class="badge bg-danger rounded-pill ms-auto" style="font-size: 0.7rem;">{{ $unreadNotifCount }}</span>
                    @endif
                </a>
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
            <!-- Topbar Header dengan Lonceng Notifikasi Interaktif -->
            <div class="topbar d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted small">Bengkel Mandiri:</span>
                    <strong class="text-dark">SERVISIN</strong>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <!-- Dropdown Lonceng Notifikasi -->
                    <div class="dropdown">
                        <button class="btn btn-light position-relative rounded-pill px-3 py-1 border" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-bell-fill text-secondary"></i>
                            @if($unreadNotifCount > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                                {{ $unreadNotifCount }}
                                <span class="visually-hidden">notifikasi belum dibaca</span>
                            </span>
                            @endif
                        </button>
                        <div class="dropdown-menu dropdown-menu-end shadow-sm p-0" style="width: 340px; border-radius: 12px; overflow: hidden;">
                            <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                                <h6 class="fw-bold mb-0 text-dark">Notifikasi Sistem</h6>
                                @if($unreadNotifCount > 0)
                                <span class="badge bg-danger rounded-pill">{{ $unreadNotifCount }} Baru</span>
                                @endif
                            </div>
                            <div class="list-group list-group-flush" style="max-height: 280px; overflow-y: auto;">
                                @forelse($recentNotifs as $rn)
                                <a href="{{ $rn->booking_id ? route('booking.show', $rn->booking_id) : route('notifikasi.index') }}" class="list-group-item list-group-item-action p-2 {{ !$rn->dibaca ? 'bg-light' : '' }}">
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="bi {{ $rn->tipe === 'booking_baru' ? 'bi-calendar-plus text-primary' : 'bi-arrow-repeat text-warning' }} fs-5 mt-1"></i>
                                        <div class="overflow-hidden">
                                            <div class="small fw-bold text-dark text-truncate">{{ $rn->judul }}</div>
                                            <div class="text-muted" style="font-size: 0.72rem; line-height: 1.2;">{{ Str::limit($rn->pesan, 55) }}</div>
                                            <div class="text-secondary mt-1" style="font-size: 0.65rem;">{{ $rn->created_at->diffForHumans() }}</div>
                                        </div>
                                    </div>
                                </a>
                                @empty
                                <div class="p-3 text-center text-muted small">
                                    Belum ada notifikasi.
                                </div>
                                @endforelse
                            </div>
                            <div class="p-2 text-center bg-light border-top">
                                <a href="{{ route('notifikasi.index') }}" class="small text-decoration-none fw-semibold">
                                    Lihat Semua Notifikasi <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="d-none d-sm-block text-end">
                        <div class="small fw-bold text-dark">{{ auth()->user()->nama }}</div>
                        <div class="text-muted" style="font-size: 0.7rem;">{{ auth()->user()->role }}</div>
                    </div>
                </div>
            </div>

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
