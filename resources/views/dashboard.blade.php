@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    {{-- Stats row --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card" style="background:linear-gradient(135deg,#e94560,#c73652)">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small opacity-75">Total Pengguna</div>
                        <div class="fs-3 fw-bold">{{ $stats['total_users'] }}</div>
                    </div>
                    <i class="bi bi-people fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card" style="background:linear-gradient(135deg,#0ea5e9,#0284c7)">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small opacity-75">Total Peralatan</div>
                        <div class="fs-3 fw-bold">{{ $stats['total_equipment'] }}</div>
                    </div>
                    <i class="bi bi-camera fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card" style="background:linear-gradient(135deg,#10b981,#059669)">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small opacity-75">Total Ruangan</div>
                        <div class="fs-3 fw-bold">{{ $stats['total_rooms'] }}</div>
                    </div>
                    <i class="bi bi-building fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card" style="background:linear-gradient(135deg,#f59e0b,#d97706)">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small opacity-75">Booking Pending</div>
                        <div class="fs-3 fw-bold" id="stat-pending-bookings">{{ $stats['pending_bookings'] }}</div>
                    </div>
                    <i class="bi bi-calendar-check fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- Pending bookings --}}
        <div class="col-lg-6">
            <div class="page-card p-3">
                <h6 class="fw-semibold mb-3"><i class="bi bi-calendar-check me-2 text-warning"></i>Booking Menunggu
                    Persetujuan</h6>
                @forelse($pendingBookings as $b)
                    <div class="d-flex align-items-center gap-3 py-2 border-bottom">
                        <div class="flex-grow-1">
                            <div class="fw-semibold small">{{ $b->title }}</div>
                            <div class="text-muted x-small" style="font-size:.75rem">
                                {{ $b->room->name }} &bull;
                                {{ \App\Helpers\DateHelper::formatDateTimeID($b->start_datetime) }}
                            </div>
                            <div class="text-muted x-small" style="font-size:.75rem">oleh {{ $b->user->name }}</div>
                        </div>
                        <a href="{{ route('bookings.show', $b) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                    </div>
                @empty
                    <p class="text-muted small">Tidak ada booking pending.</p>
                @endforelse
            </div>
        </div>

        {{-- Overdue loans --}}
        <div class="col-lg-6">
            <div class="page-card p-3">
                <h6 class="fw-semibold mb-3"><i class="bi bi-exclamation-triangle me-2 text-danger"></i>Peminjaman Jatuh
                    Tempo</h6>
                @forelse($overdueLoans as $l)
                    <div class="d-flex align-items-center gap-3 py-2 border-bottom">
                        <div class="flex-grow-1">
                            <div class="fw-semibold small">{{ $l->loan_code }}</div>
                            <div class="text-muted" style="font-size:.75rem">{{ $l->user->name }}</div>
                            <div class="text-danger" style="font-size:.75rem">Jatuh tempo:
                                {{ \App\Helpers\DateHelper::formatDateID($l->due_date) }}</div>
                        </div>
                        <a href="{{ route('loans.show', $l) }}" class="btn btn-sm btn-outline-danger">Detail</a>
                    </div>
                @empty
                    <p class="text-muted small">Tidak ada peminjaman jatuh tempo.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Auto-polling setiap 30 detik untuk update angka pendingbooking & loan
        function pollAdminStats() {
            fetch('{{ route('dashboard.stats') }}', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(r => r.ok ? r.json() : null)
                .then(data => {
                    if (!data) return;
                    const map = {
                        'stat-pending-bookings': data.pending_bookings
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
                .catch(() => {});
        }
        setInterval(pollAdminStats, 30000);
    </script>
@endpush
