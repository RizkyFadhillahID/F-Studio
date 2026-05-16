<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>@yield('title', 'Portal Resepsionis') — F-Studio</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --accent: #e94560;
            --accent-dark: #c73652;
            --dark: #1a1a2e;
            --sidebar-bg: #16213e;
            --surface: #ffffff;
            --bg: #f0f2f5;
            --text-muted-custom: #6c757d;
        }

        * {
            box-sizing: border-box;
        }

        body {
            background: var(--bg);
            font-family: 'Segoe UI', system-ui, sans-serif;
            padding-top: 60px;
            padding-bottom: 75px;
            min-height: 100vh;
        }

        /* ── Top Nav ── */
        .top-nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 60px;
            background: var(--dark);
            color: #fff;
            display: flex;
            align-items: center;
            padding: 0 16px;
            z-index: 1000;
            gap: 12px;
        }

        .top-nav .brand {
            font-weight: 700;
            font-size: 1.05rem;
            color: #fff;
            text-decoration: none;
            flex: 1;
        }

        .top-nav .brand span {
            color: var(--accent);
        }

        .top-nav .badge-role {
            background: var(--accent);
            color: #fff;
            font-size: 0.65rem;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 10px;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .top-nav .user-info {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .top-nav .avatar {
            width: 34px;
            height: 34px;
            background: var(--accent);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: .85rem;
        }

        .top-nav .user-name {
            font-size: .82rem;
            opacity: .85;
            max-width: 120px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .top-nav .btn-logout {
            background: none;
            border: none;
            color: rgba(255, 255, 255, .6);
            font-size: 1.1rem;
            cursor: pointer;
            padding: 4px 8px;
            transition: color .2s;
        }

        .top-nav .btn-logout:hover {
            color: var(--accent);
        }

        /* ── Main Content ── */
        .main-content {
            padding: 16px;
            max-width: 768px;
            margin: 0 auto;
        }

        /* ── Bottom Tab Bar ── */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 65px;
            background: var(--dark);
            display: flex;
            border-top: 1px solid rgba(255, 255, 255, .1);
            z-index: 1000;
        }

        .bottom-nav a {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: rgba(255, 255, 255, .45);
            text-decoration: none;
            font-size: .65rem;
            font-weight: 600;
            gap: 3px;
            transition: color .2s;
            letter-spacing: .3px;
            text-transform: uppercase;
        }

        .bottom-nav a i {
            font-size: 1.25rem;
        }

        .bottom-nav a:hover,
        .bottom-nav a.active {
            color: var(--accent);
        }

        .bottom-nav a.active i {
            color: var(--accent);
        }

        /* ── Cards ── */
        .rc-card {
            background: var(--surface);
            border-radius: 14px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .08);
            overflow: hidden;
            margin-bottom: 14px;
        }

        .rc-card-header {
            background: var(--dark);
            color: #fff;
            padding: 12px 16px;
            font-weight: 600;
        }

        .rc-card-body {
            padding: 16px;
        }

        /* ── Stat Cards ── */
        .stat-card {
            background: var(--surface);
            border-radius: 14px;
            padding: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .08);
            text-align: center;
        }

        .stat-card .stat-icon {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            margin: 0 auto 10px;
        }

        .stat-card .stat-number {
            font-size: 1.7rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 4px;
        }

        .stat-card .stat-label {
            font-size: .72rem;
            font-weight: 600;
            color: var(--text-muted-custom);
            text-transform: uppercase;
            letter-spacing: .4px;
        }

        /* ── Status Badges ── */
        .badge-pending {
            background: #fff3cd;
            color: #856404;
        }

        .badge-approved {
            background: #d1e7dd;
            color: #0f5132;
        }

        .badge-rejected {
            background: #f8d7da;
            color: #842029;
        }

        .badge-cancelled {
            background: #e2e3e5;
            color: #41464b;
        }

        .badge-completed {
            background: #cfe2ff;
            color: #084298;
        }

        /* ── Booking Card ── */
        .booking-card {
            background: var(--surface);
            border-radius: 12px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, .07);
            overflow: hidden;
            margin-bottom: 12px;
            cursor: pointer;
            transition: transform .15s, box-shadow .15s;
        }

        .booking-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, .12);
        }

        .booking-card .bc-border {
            width: 5px;
            flex-shrink: 0;
        }

        .bc-border.pending {
            background: #ffc107;
        }

        .bc-border.approved {
            background: #198754;
        }

        .bc-border.rejected {
            background: #dc3545;
        }

        .bc-border.cancelled {
            background: #6c757d;
        }

        .bc-border.completed {
            background: #0d6efd;
        }

        /* ── Room Card ── */
        .room-card {
            background: var(--surface);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .08);
            display: flex;
            flex-direction: column;
        }

        .room-card-header {
            background: var(--dark);
            color: #fff;
            padding: 14px 14px 10px;
        }

        .room-card-body {
            padding: 14px;
            flex: 1;
        }

        .room-card-footer {
            padding: 10px 14px 14px;
        }

        /* ── Buttons ── */
        .btn-accent {
            background: var(--accent);
            border-color: var(--accent);
            color: #fff;
            font-weight: 600;
        }

        .btn-accent:hover {
            background: var(--accent-dark);
            border-color: var(--accent-dark);
            color: #fff;
        }

        /* ── Form ── */
        .form-label {
            font-weight: 600;
            font-size: .85rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 .2rem rgba(233, 69, 96, .18);
        }

        /* ── Alert ── */
        .rc-alert-success {
            background: #d1e7dd;
            border-left: 4px solid #198754;
            color: #0f5132;
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 14px;
            font-weight: 500;
        }

        .rc-alert-danger {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            color: #842029;
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 14px;
            font-weight: 500;
        }

        /* ── Section Title ── */
        .section-title {
            font-weight: 700;
            font-size: 1rem;
            color: var(--dark);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-title i {
            color: var(--accent);
        }

        /* ── Schedule slot ── */
        .schedule-slot {
            background: #fff;
            border-radius: 10px;
            padding: 10px 14px;
            margin-bottom: 8px;
            border-left: 4px solid #e94560;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
    @stack('styles')
</head>

<body>
    <!-- Top Navigation -->
    <nav class="top-nav">
        <a href="{{ route('receptionist.dashboard') }}" class="brand">
            <span>F</span>-Studio
        </a>
        <span class="badge-role">Resepsionis</span>
        <div class="user-info">
            <div class="avatar">{{ substr(auth()->user()->name, 0, 1) }}</div>
            <span class="user-name">{{ auth()->user()->name }}</span>
            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn-logout" title="Keluar">
                    <i class="bi bi-box-arrow-right"></i>
                </button>
            </form>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        @if (session('success'))
            <div class="rc-alert-success">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            </div>
        @endif
        @if (session('error') || $errors->has('error'))
            <div class="rc-alert-danger">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                {{ session('error') ?? $errors->first('error') }}
            </div>
        @endif

        @yield('content')
    </div>

    <!-- Bottom Tab Bar -->
    <nav class="bottom-nav">
        <a href="{{ route('receptionist.dashboard') }}"
            class="{{ request()->routeIs('receptionist.dashboard') ? 'active' : '' }}">
            <i class="bi bi-house-fill"></i>
            Beranda
        </a>
        <a href="{{ route('receptionist.schedule') }}"
            class="{{ request()->routeIs('receptionist.schedule') ? 'active' : '' }}">
            <i class="bi bi-calendar3"></i>
            Jadwal
        </a>
        <a href="{{ route('receptionist.rooms') }}"
            class="{{ request()->routeIs('receptionist.rooms*') || request()->routeIs('receptionist.book') || request()->routeIs('receptionist.availability') ? 'active' : '' }}">
            <i class="bi bi-building"></i>
            Ruangan
        </a>
        <a href="{{ route('receptionist.bookings') }}"
            class="{{ request()->routeIs('receptionist.bookings*') ? 'active' : '' }}">
            <i class="bi bi-clipboard2-check-fill"></i>
            Booking
        </a>
        <a href="{{ route('receptionist.loans') }}"
            class="{{ request()->routeIs('receptionist.loans*') ? 'active' : '' }}">
            <i class="bi bi-box-seam-fill"></i>
            Peralatan
        </a>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>

</html>
