<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'F-Studio SmartHub')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --brand: #e94560;
            --brand-dark: #c73652;
            --sidebar-bg: #0f172a;
            --sidebar-hover: rgba(255,255,255,.06);
            --sidebar-text: rgba(255,255,255,.55);
            --sidebar-width: 260px;
            --topbar-h: 64px;
            --bg: #f1f5f9;
            --surface: #ffffff;
            --border: #e2e8f0;
            --text: #0f172a;
            --text-muted: #64748b;
            --radius: 12px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04);
        }

        *, *::before, *::after { box-sizing: border-box; }

        html, body {
            height: 100%;
            overflow: hidden;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            font-size: 14px;
            color: var(--text);
            background: var(--bg);
            -webkit-font-smoothing: antialiased;
        }

        /* === LAYOUT SHELL === */
        .app-shell {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* === SIDEBAR === */
        .sidebar {
            width: var(--sidebar-width);
            flex-shrink: 0;
            background: var(--sidebar-bg);
            display: flex;
            flex-direction: column;
            height: 100vh;
            position: relative;
            z-index: 100;
        }

        .sidebar::after {
            content: '';
            position: absolute;
            right: 0; top: 0; bottom: 0;
            width: 1px;
            background: linear-gradient(to bottom, transparent, rgba(233,69,96,.3) 40%, rgba(233,69,96,.3) 60%, transparent);
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0 20px;
            height: var(--topbar-h);
            flex-shrink: 0;
            border-bottom: 1px solid rgba(255,255,255,.06);
        }

        .sidebar-brand .brand-icon {
            width: 36px; height: 36px;
            background: var(--brand);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.05rem; color: #fff; flex-shrink: 0;
        }

        .sidebar-brand .brand-name {
            font-size: 1.1rem; font-weight: 800;
            color: #fff; letter-spacing: -.3px;
        }

        .sidebar-brand .brand-name span { color: var(--brand); }

        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            padding: 10px 0;
            scrollbar-width: none;
        }
        .sidebar-nav::-webkit-scrollbar { display: none; }

        .nav-section-label {
            font-size: .65rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: rgba(255,255,255,.25);
            padding: 14px 20px 5px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px 9px 18px;
            margin: 2px 10px;
            border-radius: 8px;
            color: var(--sidebar-text);
            text-decoration: none;
            font-weight: 500;
            font-size: .875rem;
            transition: background .15s, color .15s;
        }

        .sidebar-link i { font-size: 1rem; width: 20px; text-align: center; flex-shrink: 0; }

        .sidebar-link:hover { background: var(--sidebar-hover); color: #fff; }

        .sidebar-link.active {
            background: var(--brand);
            color: #fff;
            box-shadow: 0 4px 12px rgba(233,69,96,.3);
        }

        .sidebar-footer {
            flex-shrink: 0;
            padding: 10px;
            border-top: 1px solid rgba(255,255,255,.06);
        }

        .sidebar-user {
            display: flex; align-items: center; gap: 10px;
            padding: 8px 10px; border-radius: 10px; margin-bottom: 4px;
        }

        .sidebar-avatar {
            width: 34px; height: 34px;
            border-radius: 10px;
            background: var(--brand);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: .82rem; color: #fff; flex-shrink: 0;
        }

        .sidebar-user-info .user-name {
            font-weight: 600; color: #fff; font-size: .82rem;
            line-height: 1.2; max-width: 150px;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }

        .sidebar-user-info .user-role {
            font-size: .7rem; color: var(--sidebar-text); text-transform: capitalize;
        }

        .btn-logout {
            display: flex; align-items: center; gap: 8px;
            width: 100%; padding: 8px 12px;
            background: transparent; border: none; border-radius: 8px;
            color: rgba(255,255,255,.4); font-size: .82rem; font-weight: 500;
            cursor: pointer; transition: background .15s, color .15s; text-align: left;
        }
        .btn-logout:hover { background: rgba(233,69,96,.15); color: var(--brand); }

        /* === MAIN AREA === */
        .main-area {
            flex: 1; min-width: 0;
            display: flex; flex-direction: column;
            height: 100vh; overflow: hidden;
        }

        .topbar {
            height: var(--topbar-h);
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center;
            padding: 0 28px; gap: 16px; flex-shrink: 0;
            box-shadow: var(--shadow-sm);
        }

        .topbar-title {
            font-size: 1.05rem; font-weight: 700;
            color: var(--text); letter-spacing: -.2px; flex: 1;
        }

        .topbar-date {
            font-size: .8rem; color: var(--text-muted);
            background: var(--bg); padding: 5px 14px;
            border-radius: 20px; border: 1px solid var(--border);
        }

        .topbar-sep { width: 1px; height: 24px; background: var(--border); }

        .topbar-avatar {
            width: 32px; height: 32px; border-radius: 8px;
            background: var(--brand);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: .8rem; color: #fff;
        }

        .main-content {
            flex: 1; overflow-y: auto; padding: 28px;
            scrollbar-width: thin; scrollbar-color: var(--border) transparent;
        }
        .main-content::-webkit-scrollbar { width: 5px; }
        .main-content::-webkit-scrollbar-track { background: transparent; }
        .main-content::-webkit-scrollbar-thumb { background: var(--border); border-radius: 10px; }

        /* === SHARED COMPONENTS === */
        .page-card {
            background: var(--surface);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border);
        }

        .stat-card { border-radius: var(--radius); padding: 1.25rem; }

        .badge-pending   { background: #fef9c3; color: #854d0e; }
        .badge-approved  { background: #dcfce7; color: #14532d; }
        .badge-rejected  { background: #fee2e2; color: #7f1d1d; }
        .badge-active    { background: #dbeafe; color: #1e3a8a; }
        .badge-returned  { background: #dcfce7; color: #14532d; }
        .badge-overdue   { background: #fee2e2; color: #7f1d1d; }
        .badge-completed { background: #f1f5f9; color: #475569; }
        .badge-cancelled { background: #f1f5f9; color: #475569; }

        .alert { border-radius: 10px; border: none; font-size: .875rem; }

        .table thead th {
            font-size: .72rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .05em;
            color: var(--text-muted); background: #f8fafc;
            border-bottom: 1px solid var(--border);
        }
        .table tbody tr:hover td { background: #f8fafc; }

        .form-control, .form-select {
            border-radius: 8px; border-color: var(--border); font-size: .875rem;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--brand);
            box-shadow: 0 0 0 3px rgba(233,69,96,.12);
        }

        .btn { border-radius: 8px; font-weight: 500; font-size: .875rem; }
        .btn-primary { background: var(--brand); border-color: var(--brand); }
        .btn-primary:hover { background: var(--brand-dark); border-color: var(--brand-dark); }
    </style>
    @stack('styles')
</head>

<body>
<div class="app-shell">

    {{-- SIDEBAR --}}
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon"><i class="bi bi-camera-reels"></i></div>
            <div class="brand-name"><span>F</span>-Studio</div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section-label">Utama</div>
            <a href="{{ route('dashboard') }}"
                class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2-fill"></i> Dashboard
            </a>

            @auth
                @if (auth()->user()->role === 'admin')
                    <div class="nav-section-label">Manajemen</div>
                    <a href="{{ route('users.index') }}"
                        class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <i class="bi bi-people-fill"></i> Pengguna
                    </a>
                    <a href="{{ route('categories.index') }}"
                        class="sidebar-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                        <i class="bi bi-tags-fill"></i> Kategori
                    </a>
                    <a href="{{ route('equipment.index') }}"
                        class="sidebar-link {{ request()->routeIs('equipment.*') ? 'active' : '' }}">
                        <i class="bi bi-camera-fill"></i> Peralatan
                    </a>
                    <a href="{{ route('rooms.index') }}"
                        class="sidebar-link {{ request()->routeIs('rooms.*') ? 'active' : '' }}">
                        <i class="bi bi-building-fill"></i> Ruangan
                    </a>

                    <div class="nav-section-label">Transaksi</div>
                    <a href="{{ route('bookings.index') }}"
                        class="sidebar-link {{ request()->routeIs('bookings.*') || request()->routeIs('loans.*') ? 'active' : '' }}">
                        <i class="bi bi-receipt-cutoff"></i> Transaksi
                    </a>
                @endif
            @endauth
        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="sidebar-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                <div class="sidebar-user-info">
                    <div class="user-name">{{ auth()->user()->name }}</div>
                    <div class="user-role">{{ auth()->user()->role }}</div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-logout">
                    <i class="bi bi-box-arrow-right"></i> Keluar
                </button>
            </form>
        </div>
    </aside>

    {{-- MAIN AREA --}}
    <div class="main-area">
        <header class="topbar">
            <div class="topbar-title">@yield('page-title', 'Dashboard')</div>
            <div class="topbar-date">
                <i class="bi bi-calendar3 me-1"></i>{{ now()->isoFormat('dddd, D MMMM YYYY') }}
            </div>
            <div class="topbar-sep"></div>
            <div class="topbar-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
        </header>

        <main class="main-content">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
