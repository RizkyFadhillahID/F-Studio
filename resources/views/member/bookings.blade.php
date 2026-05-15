@extends('member.layout')
@section('title', 'Pemesanan Saya')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">Pemesanan Saya</h5>
        <a href="{{ route('member.rooms') }}" class="btn btn-accent btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Baru
        </a>
    </div>

    {{-- Filter Tabs --}}
    <div class="d-flex gap-2 mb-4 overflow-auto pb-1">
        @foreach (['' => 'Semua', 'pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'] as $val => $label)
            <a href="{{ route('member.bookings', $val ? ['status' => $val] : []) }}"
                class="btn btn-sm flex-shrink-0 {{ request('status') == $val ? 'btn-accent' : 'btn-outline-secondary' }}"
                style="border-radius:20px">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- Booking List --}}
    @forelse($bookings as $b)
        <a href="{{ route('member.bookings.show', $b) }}" class="text-decoration-none">
            <div class="fcard mb-2"
                style="border-left: 3px solid
                @if ($b->status === 'approved') #198754
                @elseif($b->status === 'rejected') #dc3545
                @elseif($b->status === 'pending') #ffc107
                @else #6c757d @endif">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <span class="fw-semibold">{{ $b->title }}</span>
                    <span class="badge badge-{{ $b->status }} ms-2 flex-shrink-0">{{ ucfirst($b->status) }}</span>
                </div>
                <div class="text-muted small mb-1">
                    <i class="bi bi-door-open me-1"></i>{{ $b->room->name }}
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted" style="font-size:0.78rem">
                        <i class="bi bi-calendar3 me-1"></i>
                        {{ \App\Helpers\DateHelper::formatDateID($b->start_datetime) }}
                        &nbsp;
                        <i class="bi bi-clock me-1"></i>
                        {{ \App\Helpers\DateHelper::formatTimeID($b->start_datetime) }}–{{ \App\Helpers\DateHelper::formatTimeID($b->end_datetime) }}
                    </div>
                    <code style="font-size:0.7rem">{{ $b->booking_code }}</code>
                </div>
            </div>
        </a>
    @empty
        <div class="fcard text-center py-5 text-muted">
            <i class="bi bi-calendar-x fs-1 mb-3 d-block"></i>
            <p class="mb-2">Belum ada pemesanan{{ request('status') ? ' dengan status ini' : '' }}.</p>
            <a href="{{ route('member.rooms') }}" class="btn btn-accent btn-sm">Pesan Ruangan</a>
        </div>
    @endforelse

    {{-- Pagination --}}
    @if ($bookings->hasPages())
        <div class="d-flex justify-content-center mt-3">
            {{ $bookings->links() }}
        </div>
    @endif
@endsection
