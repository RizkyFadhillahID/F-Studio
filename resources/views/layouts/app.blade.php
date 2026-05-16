<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'F-Studio SmartHub')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        html,
        body {
            height: 100%;
            overflow: hidden;
            background: #f0f2f5;
        }

        .sidebar {
            width: 250px;
            height: 100vh;
            background: #1a1a2e;
            position: sticky;
            top: 0;
            flex-shrink: 0;
        }

        .sidebar .brand {
            color: #e94560;
            font-size: 1.3rem;
            font-weight: 700;
            padding: 1.5rem 1rem;
            border-bottom: 1px solid #2a2a45;
        }

        .sidebar .nav-link {
            color: #a0aec0;
            padding: .6rem 1rem;
            border-radius: 6px;
            margin: 2px 8px;
            transition: all .2s;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: #e94560;
            color: #fff;
        }

        .sidebar .nav-link i {
            width: 20px;
        }

        .sidebar .nav-section {
            color: #4a5568;
            font-size: .7rem;
            text-transform: uppercase;
            padding: .75rem 1rem .25rem;
            letter-spacing: .1em;
        }

        .main-content {
            flex: 1;
            min-width: 0;
            height: 100vh;
            overflow-y: auto;
        }

        .topbar {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: .75rem 1.5rem;
        }

        .page-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .08);
        }

        .stat-card {
            border-radius: 10px;
            padding: 1.25rem;
            color: #fff;
        }

        .badge-pending {
            background: #fff3cd;
            color: #856404;
        }

        .badge-approved {
            background: #d1e7dd;
            color: #0a3622;
        }

        .badge-rejected {
            background: #f8d7da;
            color: #58151c;
        }

        .badge-active {
            background: #cfe2ff;
            color: #084298;
        }

        .badge-returned {
            background: #d1e7dd;
            color: #0a3622;
        }

        .badge-overdue {
            background: #f8d7da;
            color: #58151c;
        }

        .badge-completed {
            background: #e2e3e5;
            color: #41464b;
        }

        .badge-cancelled {
            background: #e2e3e5;
            color: #41464b;
        }
    </style>
    @stack('styles')
</head>

<body>
    <div class="d-flex" style="height:100vh;overflow:hidden;">
        {{-- Sidebar --}}
        <nav class="sidebar d-flex flex-column flex-shrink-0">
            <div class="brand"><i class="bi bi-camera-reels me-2"></i>F-Studio</div>
            <div class="overflow-auto flex-grow-1 py-2">
                <div class="nav-section">Utama</div>
                <a href="{{ route('dashboard') }}"
                    class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>

                @auth
                    @if (auth()->user()->role === 'admin')
                        <div class="nav-section">Manajemen</div>
                        <a href="{{ route('users.index') }}"
                            class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('users.*') ? 'active' : '' }}">
                            <i class="bi bi-people"></i> Pengguna
                        </a>
                        <a href="{{ route('categories.index') }}"
                            class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                            <i class="bi bi-tags"></i> Kategori
                        </a>
                        <a href="{{ route('equipment.index') }}"
                            class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('equipment.*') ? 'active' : '' }}">
                            <i class="bi bi-camera"></i> Peralatan
                        </a>
                        <a href="{{ route('rooms.index') }}"
                            class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('rooms.*') ? 'active' : '' }}">
                            <i class="bi bi-building"></i> Ruangan
                        </a>

                        <div class="nav-section">Transaksi</div>
                        <a href="{{ route('bookings.index') }}"
                            class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('bookings.*') || request()->routeIs('loans.*') ? 'active' : '' }}">
                            <i class="bi bi-receipt"></i> Transaksi
                        </a>
                    @endif
                @endauth
            </div>
            <div class="p-2 border-top border-secondary">
                <div class="d-flex align-items-center gap-2 p-2 text-white-50 small mb-1">
                    <i class="bi bi-person-circle fs-5"></i>
                    <div>
                        <div class="fw-semibold text-white">{{ auth()->user()->name }}</div>
                        <div class="text-capitalize">{{ auth()->user()->role }}</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="nav-link d-flex align-items-center gap-2 w-100 text-start border-0 bg-transparent text-danger">
                        <i class="bi bi-box-arrow-right"></i> Keluar
                    </button>
                </form>
            </div>
        </nav>

        {{-- Main --}}
        <div class="main-content d-flex flex-column">
            <div class="topbar d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-semibold text-dark">@yield('page-title', 'Dashboard')</h6>
                <span class="text-muted small">{{ now()->isoFormat('dddd, D MMMM YYYY') }}</span>
            </div>
            <div class="p-4 flex-grow-1">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @yield('content')
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>

</html>
