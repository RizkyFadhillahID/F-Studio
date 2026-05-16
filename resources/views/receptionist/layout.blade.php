<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>@yield('title', 'Resepsionis') — F-Studio</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --brand: #e94560;
            --brand-dark: #c73652;
            --brand-soft: rgba(233,69,96,.1);
            --dark: #0f172a;
            --dark-2: #1e293b;
            --surface: #ffffff;
            --bg: #f1f5f9;
            --border: #e2e8f0;
            --text: #0f172a;
            --text-muted: #64748b;
            --topbar-h: 60px;
            --bottombar-h: 68px;
            --radius: 14px;
            --shadow: 0 4px 16px rgba(0,0,0,.08);
            --shadow-sm: 0 1px 4px rgba(0,0,0,.06);
        }

        *, *::before, *::after { box-sizing: border-box; }

        html, body {
            background: var(--bg);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            font-size: 14px;
            color: var(--text);
            -webkit-font-smoothing: antialiased;
            padding-top: var(--topbar-h);
            padding-bottom: var(--bottombar-h);
            min-height: 100vh;
        }

        /* ── TOP NAV ── */
        .top-nav {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: var(--topbar-h);
            background: var(--dark);
            display: flex;
            align-items: center;
            padding: 0 16px;
            gap: 12px;
            z-index: 1000;
            border-bottom: 1px solid rgba(255,255,255,.06);
        }

        .top-nav::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 1px;
            background: linear-gradient(to right, transparent, rgba(233,69,96,.5), transparent);
        }

        .top-nav .brand {
            flex: 1;
            font-weight: 800;
            font-size: 1.05rem;
            color: #fff;
            text-decoration: none;
            letter-spacing: -.2px;
        }

        .top-nav .brand span { color: var(--brand); }

        .top-nav .badge-role {
            background: rgba(233,69,96,.18);
            color: var(--brand);
            font-size: .62rem;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: .06em;
            border: 1px solid rgba(233,69,96,.25);
        }

        .top-nav .nav-avatar {
            width: 32px; height: 32px;
            background: var(--brand);
            border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: .82rem; color: #fff; flex-shrink: 0;
        }

        .top-nav .nav-user-name {
            font-size: .8rem;
            color: rgba(255,255,255,.7);
            max-width: 110px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .btn-logout-top {
            background: none; border: none;
            color: rgba(255,255,255,.45);
            font-size: 1.05rem; cursor: pointer;
            padding: 5px 8px; border-radius: 7px;
            transition: background .15s, color .15s;
        }
        .btn-logout-top:hover { background: rgba(233,69,96,.15); color: var(--brand); }

        /* ── MAIN CONTENT ── */
        .rc-main {
            padding: 16px;
            max-width: 720px;
            margin: 0 auto;
        }

        /* ── BOTTOM TAB ── */
        .bottom-nav {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            height: var(--bottombar-h);
            background: var(--dark);
            display: flex;
            border-top: 1px solid rgba(255,255,255,.07);
            z-index: 1000;
            padding-bottom: env(safe-area-inset-bottom);
        }

        .bottom-nav a {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: rgba(255,255,255,.4);
            text-decoration: none;
            font-size: .62rem;
            font-weight: 700;
            gap: 4px;
            transition: color .15s;
            letter-spacing: .04em;
            text-transform: uppercase;
            padding: 8px 0;
        }

        .bottom-nav a i { font-size: 1.2rem; }

        .bottom-nav a.active {
            color: var(--brand);
            position: relative;
        }

        .bottom-nav a.active::before {
            content: '';
            position: absolute;
            top: 0; left: 20%; right: 20%;
            height: 2px;
            background: var(--brand);
            border-radius: 0 0 4px 4px;
        }

        /* ── CARDS ── */
        .rc-card {
            background: var(--surface);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border);
            overflow: hidden;
            margin-bottom: 14px;
        }

        .rc-card-header {
            background: var(--dark-2);
            color: #fff;
            padding: 12px 16px;
            font-weight: 600;
            font-size: .9rem;
            display: flex; align-items: center; gap: 8px;
        }

        .rc-card-header i { color: var(--brand); }

        .rc-card-body { padding: 16px; }

        /* ── STAT CARDS ── */
        .stat-card {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 16px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border);
            display: flex; align-items: center; gap: 14px;
        }

        .stat-icon {
            width: 44px; height: 44px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem; flex-shrink: 0;
        }

        .stat-number { font-size: 1.6rem; font-weight: 800; line-height: 1; }

        .stat-label {
            font-size: .72rem; font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase; letter-spacing: .04em;
        }

        /* ── STATUS BADGES ── */
        .badge-pending   { background: #fef9c3; color: #854d0e; }
        .badge-approved  { background: #dcfce7; color: #14532d; }
        .badge-rejected  { background: #fee2e2; color: #7f1d1d; }
        .badge-cancelled { background: #f1f5f9; color: #475569; }
        .badge-completed { background: #dbeafe; color: #1e3a8a; }
        .badge-active    { background: #dbeafe; color: #1e3a8a; }
        .badge-overdue   { background: #fee2e2; color: #7f1d1d; }
        .badge-returned  { background: #dcfce7; color: #14532d; }

        /* ── BOOKING CARD ── */
        .booking-card {
            background: var(--surface);
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border);
            overflow: hidden;
            margin-bottom: 10px;
            cursor: pointer;
            transition: transform .15s, box-shadow .15s;
            display: flex;
        }

        .booking-card:hover { transform: translateY(-1px); box-shadow: var(--shadow); }

        .booking-card .bc-strip { width: 4px; flex-shrink: 0; }
        .bc-strip.pending    { background: #f59e0b; }
        .bc-strip.approved   { background: #10b981; }
        .bc-strip.rejected   { background: #ef4444; }
        .bc-strip.cancelled  { background: #94a3b8; }
        .bc-strip.completed  { background: #3b82f6; }

        .booking-card .bc-body { padding: 12px 14px; flex: 1; }

        /* ── ROOM CARD ── */
        .room-card {
            background: var(--surface);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border);
            display: flex; flex-direction: column;
        }

        .room-card-header {
            background: var(--dark-2);
            color: #fff;
            padding: 13px 14px 10px;
        }

        .room-card-body { padding: 14px; flex: 1; }
        .room-card-footer { padding: 10px 14px 14px; }

        /* ── BUTTONS ── */
        .btn-accent {
            background: var(--brand); border-color: var(--brand);
            color: #fff; font-weight: 600;
        }
        .btn-accent:hover { background: var(--brand-dark); border-color: var(--brand-dark); color: #fff; }

        .btn { border-radius: 9px; font-weight: 500; }

        /* ── FORMS ── */
        .form-label { font-weight: 600; font-size: .85rem; }

        .form-control, .form-select {
            border-radius: 9px; border-color: var(--border); font-size: .875rem;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--brand);
            box-shadow: 0 0 0 3px rgba(233,69,96,.12);
        }

        /* ── ALERTS ── */
        .rc-alert {
            border-radius: 12px; padding: 12px 16px;
            margin-bottom: 14px; font-size: .875rem; font-weight: 500;
            display: flex; align-items: flex-start; gap: 10px;
        }

        .rc-alert-success {
            background: #dcfce7; color: #14532d;
            border-left: 4px solid #22c55e;
        }

        .rc-alert-danger {
            background: #fee2e2; color: #7f1d1d;
            border-left: 4px solid #ef4444;
        }

        /* ── SECTION TITLE ── */
        .section-title {
            font-weight: 700; font-size: .95rem;
            color: var(--text); margin-bottom: 12px;
            display: flex; align-items: center; gap: 8px;
        }

        .section-title i { color: var(--brand); }

        /* ── SCHEDULE SLOT ── */
        .schedule-slot {
            background: var(--surface);
            border-radius: 10px;
            padding: 10px 14px;
            margin-bottom: 8px;
            border-left: 4px solid var(--brand);
            box-shadow: var(--shadow-sm);
            display: flex; justify-content: space-between; align-items: center;
        }
    </style>
    @stack('styles')
</head>

<body>

    {{-- TOP NAV --}}
    <nav class="top-nav">
        <a href="{{ route('receptionist.dashboard') }}" class="brand"><span>F</span>-Studio</a>
        <span class="badge-role">Resepsionis</span>
        <div class="d-flex align-items-center gap-2">
            <div class="nav-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            <span class="nav-user-name d-none d-sm-block">{{ auth()->user()->name }}</span>
            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn-logout-top" title="Keluar">
                    <i class="bi bi-box-arrow-right"></i>
                </button>
            </form>
        </div>
    </nav>

    {{-- MAIN CONTENT --}}
    <div class="rc-main">
        @if (session('success'))
            <div class="rc-alert rc-alert-success">
                <i class="bi bi-check-circle-fill flex-shrink-0"></i>
                {{ session('success') }}
            </div>
        @endif
        @if (session('error') || $errors->has('error'))
            <div class="rc-alert rc-alert-danger">
                <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
                {{ session('error') ?? $errors->first('error') }}
            </div>
        @endif
        @yield('content')
    </div>

    {{-- BOTTOM TAB --}}
    <nav class="bottom-nav">
        <a href="{{ route('receptionist.dashboard') }}"
            class="{{ request()->routeIs('receptionist.dashboard') ? 'active' : '' }}">
            <i class="bi bi-house-fill"></i>Beranda
        </a>
        <a href="{{ route('receptionist.schedule') }}"
            class="{{ request()->routeIs('receptionist.schedule') ? 'active' : '' }}">
            <i class="bi bi-calendar3"></i>Jadwal
        </a>
        <a href="{{ route('receptionist.rooms') }}"
            class="{{ request()->routeIs('receptionist.rooms*') || request()->routeIs('receptionist.book') || request()->routeIs('receptionist.availability') ? 'active' : '' }}">
            <i class="bi bi-building-fill"></i>Ruangan
        </a>
        <a href="{{ route('receptionist.bookings') }}"
            class="{{ request()->routeIs('receptionist.bookings*') ? 'active' : '' }}">
            <i class="bi bi-clipboard2-check-fill"></i>Booking
        </a>
        <a href="{{ route('receptionist.loans') }}"
            class="{{ request()->routeIs('receptionist.loans*') ? 'active' : '' }}">
            <i class="bi bi-box-seam-fill"></i>Peralatan
        </a>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>

</html>
