@extends('receptionist.layout')
@section('title', 'Booking Saya')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="section-title mb-0">
            <i class="bi bi-clipboard2-check-fill"></i>
            Booking Saya
        </div>
        <a href="{{ route('receptionist.rooms') }}" class="btn btn-accent btn-sm" style="border-radius:8px;">
            <i class="bi bi-plus-lg me-1"></i>Baru
        </a>
    </div>

    {{-- Filter Status --}}
    <div class="d-flex gap-2 mb-3 overflow-auto pb-1" style="scrollbar-width:none;">
        @php $statuses = ['' => 'Semua', 'pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'cancelled' => 'Dibatalkan'] @endphp
        @foreach ($statuses as $val => $label)
            <a href="{{ route('receptionist.bookings', array_filter(['status' => $val, 'date' => request('date')])) }}"
                class="btn btn-sm {{ request('status', '') === $val ? 'btn-accent' : 'btn-outline-secondary' }}"
                style="border-radius:20px; white-space:nowrap; font-size:.78rem; padding:4px 14px;">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- Filter Tanggal --}}
    <div class="mb-3">
        <form method="GET" class="d-flex gap-2">
            @if (request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <input type="date" name="date" class="form-control form-control-sm" style="border-radius:8px; max-width:180px;"
                value="{{ request('date') }}">
            <button type="submit" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;">
                <i class="bi bi-funnel"></i> Filter
            </button>
            @if (request('date'))
                <a href="{{ route('receptionist.bookings', array_filter(['status' => request('status')])) }}"
                    class="btn btn-sm btn-outline-danger" style="border-radius:8px;">
                    <i class="bi bi-x"></i>
                </a>
            @endif
        </form>
    </div>

    @forelse($bookings as $b)
        <a href="{{ route('receptionist.bookings.show', $b) }}" style="text-decoration:none;">
            <div class="booking-card">
                <div class="d-flex">
                    <div class="bc-border {{ $b->status }}"></div>
                    <div class="p-3 flex-fill">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <div>
                                <div class="fw-bold" style="font-size:.9rem; color:#1a1a2e;">
                                    {{ $b->customer_name ?? '—' }}
                                </div>
                                <div style="font-size:.78rem; color:#6c757d;">{{ $b->title }}</div>
                            </div>
                            <span class="badge badge-{{ $b->status }}"
                                style="font-size:.68rem; padding:3px 8px; border-radius:6px; white-space:nowrap;">
                                {{ ucfirst($b->status) }}
                            </span>
                        </div>
                        <div style="font-size:.78rem; color:#6c757d;">
                            <i class="bi bi-building me-1"></i>{{ $b->room->name }}
                        </div>
                        <div class="d-flex justify-content-between mt-1" style="font-size:.75rem; color:#6c757d;">
                            <span><i
                                    class="bi bi-calendar3 me-1"></i>{{ \App\Helpers\DateHelper::formatDateID($b->start_datetime) }}</span>
                            <span><i class="bi bi-clock me-1"></i>
                                {{ \App\Helpers\DateHelper::formatTimeID($b->start_datetime) }} –
                                {{ \App\Helpers\DateHelper::formatTimeID($b->end_datetime) }} WIB
                            </span>
                            <code style="font-size:.68rem; color:#e94560;">{{ $b->booking_code }}</code>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    @empty
        <div class="rc-card">
            <div class="rc-card-body text-center py-5" style="color:#6c757d;">
                <i class="bi bi-inbox" style="font-size:2.5rem; display:block; margin-bottom:10px;"></i>
                <strong>Belum ada booking</strong><br>
                <span style="font-size:.85rem;">Buat booking baru untuk pelanggan dari menu Ruangan.</span>
            </div>
        </div>
    @endforelse

    {{ $bookings->appends(request()->query())->links() }}

@endsection