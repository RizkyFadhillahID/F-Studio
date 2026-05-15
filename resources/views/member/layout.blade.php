<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Portal Anggota') — F-Studio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --accent: #e94560;
            --dark: #1a1a2e;
            --card-bg: #ffffff;
        }

        body {
            background: #f0f2f5;
            font-family: 'Segoe UI', sans-serif;
            min-height: 100vh;
        }

        /* ── Top navbar ── */
        .top-nav {
            background: var(--dark);
            padding: 0.75rem 1.5rem;
            position: sticky;
            top: 0;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand {
            color: var(--accent);
            font-size: 1.3rem;
            font-weight: 800;
            text-decoration: none;
        }

        .brand span {
            color: #ffffff;
            font-weight: 400;
        }

        .user-chip {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #fff;
            font-size: 0.85rem;
        }

        .user-chip .avatar {
            width: 34px;
            height: 34px;
            background: var(--accent);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.8rem;
        }

        /* ── Bottom Tab Bar (tablet friendly) ── */
        .tab-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--dark);
            display: flex;
            z-index: 1000;
            border-top: 2px solid var(--accent);
        }

        .tab-item {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 0.6rem 0.25rem;
            color: #aaa;
            text-decoration: none;
            font-size: 0.7rem;
            gap: 0.2rem;
            transition: color 0.2s;
        }

        .tab-item i {
            font-size: 1.3rem;
        }

        .tab-item.active,
        .tab-item:hover {
            color: var(--accent);
        }

        /* ── Content ── */
        .page-content {
            padding: 1.25rem;
            padding-bottom: 5rem;
            max-width: 900px;
            margin: 0 auto;
        }

        /* ── Cards ── */
        .fcard {
            background: var(--card-bg);
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .08);
            padding: 1.25rem;
            margin-bottom: 1rem;
        }

        /* ── Badges ── */
        .badge-pending {
            background: #ffc107;
            color: #000;
        }

        .badge-approved {
            background: #198754;
            color: #fff;
        }

        .badge-rejected {
            background: #dc3545;
            color: #fff;
        }

        .badge-cancelled {
            background: #6c757d;
            color: #fff;
        }

        /* ── Alert ── */
        .alert {
            border-radius: 10px;
        }

        /* ── Buttons ── */
        .btn-accent {
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 0.65rem 1.5rem;
            font-weight: 600;
        }

        .btn-accent:hover {
            background: #c73a52;
            color: #fff;
        }

        /* ── Room card ── */
        .room-card {
            background: #fff;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .08);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .room-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, .12);
        }

        .room-card .room-header {
            background: linear-gradient(135deg, var(--dark) 0%, #16213e 100%);
            padding: 1.25rem;
            color: #fff;
        }

        .room-card .room-body {
            padding: 1rem 1.25rem;
        }

        /* ── Form styling ── */
        .form-control,
        .form-select {
            border-radius: 10px;
            border: 1.5px solid #dee2e6;
            padding: 0.65rem 0.9rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 0.2rem rgba(233, 69, 96, 0.15);
        }

        .form-label {
            font-weight: 600;
            color: #444;
            margin-bottom: 0.3rem;
        }

        /* ── Stat card ── */
        .stat-card {
            background: #fff;
            border-radius: 14px;
            padding: 1.1rem 1.25rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .07);
            border-left: 4px solid var(--accent);
        }

        .stat-card .stat-value {
            font-size: 2rem;
            font-weight: 800;
            color: var(--dark);
            line-height: 1;
        }

        .stat-card .stat-label {
            font-size: 0.8rem;
            color: #888;
            margin-top: 0.2rem;
        }
    </style>
</head>

<body>
    <!-- Top Navbar -->
    <nav class="top-nav">
        <a class="brand" href="{{ route('member.dashboard') }}">
            <i class="bi bi-camera-reels"></i> F-Studio <span>Portal</span>
        </a>
        <div class="user-chip">
            <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            <span class="d-none d-sm-inline">{{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}" class="d-inline ms-1">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size:0.75rem">
                    <i class="bi bi-box-arrow-right"></i>
                </button>
            </form>
        </div>
    </nav>

    <!-- Page Content -->
    <div class="page-content">
        @if (session('success'))
            <div class="alert alert-success d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-check-circle-fill"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if ($errors->has('error'))
            <div class="alert alert-danger d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span>{{ $errors->first('error') }}</span>
            </div>
        @endif

        @yield('content')
    </div>

    <!-- Bottom Tab Bar -->
    <nav class="tab-bar">
        <a href="{{ route('member.dashboard') }}"
            class="tab-item {{ request()->routeIs('member.dashboard') ? 'active' : '' }}">
            <i class="bi bi-house-fill"></i>
            <span>Beranda</span>
        </a>
        <a href="{{ route('member.rooms') }}"
            class="tab-item {{ request()->routeIs('member.rooms') || request()->routeIs('member.book') ? 'active' : '' }}">
            <i class="bi bi-door-open-fill"></i>
            <span>Ruangan</span>
        </a>
        <a href="{{ route('member.bookings') }}"
            class="tab-item {{ request()->routeIs('member.bookings*') ? 'active' : '' }}">
            <i class="bi bi-calendar2-check-fill"></i>
            <span>Pemesanan</span>
        </a>
        <a href="{{ route('member.dashboard') }}" class="tab-item">
            <i class="bi bi-person-fill"></i>
            <span>Profil</span>
        </a>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
