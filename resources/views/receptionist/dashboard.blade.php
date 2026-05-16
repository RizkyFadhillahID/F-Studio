@extends('receptionist.layout')
@section('title', 'Dashboard Resepsionis')

@section('content')
    {{-- Header Greeting --}}
    <div class="rc-card"
        style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); color:#fff; border-radius:16px; margin-bottom:14px;">
        <div class="rc-card-body" style="background:none;">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="mb-1 opacity-75" style="font-size:.8rem;">Selamat datang,</p>
                    <h5 class="fw-bold mb-0">{{ auth()->user()->name }}</h5>
                    <p class="mb-0 opacity-75" style="font-size:.8rem; margin-top:4px;">
                        <i class="bi bi-calendar3 me-1"></i>
                        {{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}
                    </p>
                </div>
                <div style="background:rgba(233,69,96,.25); border-radius:12px; padding:10px 14px; text-align:center;">
                    <div style="font-size:1.6rem; font-weight:800; color:#e94560;">
                        {{ \App\Helpers\DateHelper::formatTimeID(now()) }}
                    </div>
                    <div style="font-size:.65rem; opacity:.7; text-transform:uppercase; letter-spacing:.5px;">WIB</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fff3cd; color:#856404;">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div class="stat-number" id="stat-pending" style="color:#856404;">{{ $stats['pending'] }}</div>
                <div class="stat-label">Menunggu Approval</div>
            </div>
        </div>
        <div class="col-6">
            <div class="stat-card">
                <div class="stat-icon" style="background:#d1e7dd; color:#0f5132;">
                    <i class="bi bi-calendar-check-fill"></i>
                </div>
                <div class="stat-number" id="stat-approved" style="color:#0f5132;">{{ $stats['approved'] }}</div>
                <div class="stat-label">Disetujui Hari Ini</div>
            </div>
        </div>
        <div class="col-6">
            <div class="stat-card">
                <div class="stat-icon" style="background:#cfe2ff; color:#084298;">
                    <i class="bi bi-calendar2-day"></i>
                </div>
                <div class="stat-number" id="stat-today" style="color:#084298;">{{ $stats['today'] }}</div>
                <div class="stat-label">Booking Hari Ini</div>
            </div>
        </div>
        <div class="col-6">
            <div class="stat-card">
                <div class="stat-icon" style="background:#f8d7da; color:#842029;">
                    <i class="bi bi-bar-chart-fill"></i>
                </div>
                <div class="stat-number" id="stat-month" style="color:#842029;">{{ $stats['total_month'] }}</div>
                <div class="stat-label">Bulan Ini</div>
            </div>
        </div>
    </div>

    {{-- CTA Buat Booking --}}
    <a href="{{ route('receptionist.rooms') }}" class="btn btn-accent w-100 py-3 mb-4"
        style="border-radius:14px; font-size:1rem; display:flex; align-items:center; justify-content:center; gap:8px;">
        <i class="bi bi-plus-circle-fill" style="font-size:1.2rem;"></i>
        Buat Booking Baru untuk Pelanggan
    </a>

    {{-- Jadwal Hari Ini --}}
    <div class="section-title">
        <i class="bi bi-calendar3"></i>
        Jadwal Hari Ini
    </div>

    @if ($todayBookings->isEmpty())
        <div class="rc-card">
            <div class="rc-card-body text-center py-4" style="color:#6c757d;">
                <i class="bi bi-calendar-x" style="font-size:2rem; display:block; margin-bottom:8px;"></i>
                Tidak ada jadwal hari ini
            </div>
        </div>
    @else
        @foreach ($todayBookings as $b)
            <div class="schedule-slot" style="border-left-color: {{ $b->status === 'approved' ? '#198754' : '#ffc107' }}">
                <div>
                    <div class="fw-semibold" style="font-size:.9rem;">{{ $b->customer_name ?? $b->title }}</div>
                    <div style="font-size:.78rem; color:#6c757d;">
                        {{ $b->room->name }} &bull;
                        {{ \App\Helpers\DateHelper::formatTimeID($b->start_datetime) }} –
                        {{ \App\Helpers\DateHelper::formatTimeID($b->end_datetime) }} WIB
                    </div>
                </div>
                <span class="badge badge-{{ $b->status }}" style="font-size:.7rem; padding:4px 8px; border-radius:6px;">
                    {{ ucfirst($b->status) }}
                </span>
            </div>
        @endforeach
    @endif

    {{-- Booking Saya Terbaru --}}
    @if ($recentBookings->isNotEmpty())
        <div class="section-title mt-3">
            <i class="bi bi-clock"></i>
            Booking Saya Terbaru
        </div>
        @foreach ($recentBookings as $b)
            <a href="{{ route('receptionist.bookings.show', $b) }}" style="text-decoration:none;">
                <div class="booking-card">
                    <div class="d-flex">
                        <div class="bc-border {{ $b->status }}"></div>
                        <div class="p-3 flex-fill">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="fw-semibold" style="font-size:.88rem;">{{ $b->customer_name ?? '-' }}</div>
                                    <div style="font-size:.78rem; color:#6c757d;">{{ $b->title }}</div>
                                    <div style="font-size:.75rem; color:#6c757d; margin-top:2px;">
                                        <i class="bi bi-building me-1"></i>{{ $b->room->name }}
                                    </div>
                                </div>
                                <span class="badge badge-{{ $b->status }}"
                                    style="font-size:.68rem; padding:3px 7px; border-radius:5px; white-space:nowrap;">
                                    {{ ucfirst($b->status) }}
                                </span>
                            </div>
                            <div style="font-size:.75rem; color:#6c757d; margin-top:6px;">
                                <i class="bi bi-calendar3 me-1"></i>
                                {{ \App\Helpers\DateHelper::formatDateTimeID($b->start_datetime) }} WIB
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        @endforeach
    @endif

@endsection

@push('scripts')
    <script>
        // Auto-polling setiap 30 detik untuk update angka statistik tanpa reload
        function pollStats() {
            fetch('{{ route('receptionist.stats') }}', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(r => r.ok ? r.json() : null)
                .then(data => {
                    if (!data) return;
                    const map = {
                        'stat-pending': data.pending,
                        'stat-approved': data.approved,
                        'stat-today': data.today,
                        'stat-month': data.total_month,
                    };
                    Object.entries(map).forEach(([id, val]) => {
                        const el = document.getElementById(id);
                        if (el && el.textContent != val) {
                            el.textContent = val;
                            el.style.transition = 'opacity .3s';
                            el.style.opacity = '0.4';
                            setTimeout(() => el.style.opacity = '1', 300);
                        }
                    });
                })
                .catch(() => { }); // silent fail
        }
        setInterval(pollStats, 30000);
    </script>
@endpush