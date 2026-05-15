@extends('member.layout')
@section('title', 'Beranda')

@section('content')
    {{-- Greeting --}}
    <div class="mb-4">
        <h5 class="fw-bold mb-0">Halo, {{ auth()->user()->name }} 👋</h5>
        <p class="text-muted small mb-0">Selamat datang di Portal Anggota F-Studio</p>
    </div>

    {{-- Stat Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-4">
            <div class="stat-card text-center">
                <div class="stat-value">{{ $stats['total'] }}</div>
                <div class="stat-label">Total Booking</div>
            </div>
        </div>
        <div class="col-4">
            <div class="stat-card text-center" style="border-left-color:#ffc107">
                <div class="stat-value text-warning">{{ $stats['pending'] }}</div>
                <div class="stat-label">Menunggu</div>
            </div>
        </div>
        <div class="col-4">
            <div class="stat-card text-center" style="border-left-color:#198754">
                <div class="stat-value text-success">{{ $stats['approved'] }}</div>
                <div class="stat-label">Disetujui</div>
            </div>
        </div>
    </div>

    {{-- Quick Book CTA --}}
    <a href="{{ route('member.rooms') }}" class="btn btn-accent w-100 mb-4 py-3" style="font-size:1rem">
        <i class="bi bi-plus-circle-fill me-2"></i>Pesan Ruangan Sekarang
    </a>

    {{-- Recent Bookings --}}
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="fw-bold mb-0">Pemesanan Terbaru</h6>
        <a href="{{ route('member.bookings') }}" class="small text-danger">Lihat semua →</a>
    </div>

    @forelse($myBookings as $b)
        <a href="{{ route('member.bookings.show', $b) }}" class="text-decoration-none">
            <div class="fcard d-flex justify-content-between align-items-center py-3">
                <div>
                    <div class="fw-semibold small">{{ $b->title }}</div>
                    <div class="text-muted" style="font-size:0.78rem">
                        <i class="bi bi-door-open me-1"></i>{{ $b->room->name }}
                    </div>
                    <div class="text-muted" style="font-size:0.78rem">
                        <i class="bi bi-calendar3 me-1"></i>
                        {{ \App\Helpers\DateHelper::formatDateTimeID($b->start_datetime) }}
                    </div>
                </div>
                <div class="d-flex flex-column align-items-end gap-1">
                    <span class="badge badge-{{ $b->status }}">{{ ucfirst($b->status) }}</span>
                    <code style="font-size:0.7rem">{{ $b->booking_code }}</code>
                </div>
            </div>
        </a>
    @empty
        <div class="fcard text-center py-4 text-muted">
            <i class="bi bi-calendar-x fs-2 mb-2 d-block"></i>
            Belum ada pemesanan. Yuk, pesan ruangan!
        </div>
    @endforelse
@endsection
