@extends('receptionist.layout')
@section('title', 'Ketersediaan ' . $room->name)

@section('content')
    <a href="{{ route('receptionist.rooms') }}" class="d-inline-flex align-items-center gap-2 mb-3"
        style="color:#6c757d; text-decoration:none; font-size:.85rem; font-weight:600;">
        <i class="bi bi-arrow-left"></i> Kembali ke daftar ruangan
    </a>

    <div class="rc-card mb-3">
        <div class="rc-card-header">
            <i class="bi bi-building me-2"></i>{{ $room->name }}
            @if ($room->room_code)
                <code style="opacity:.6; font-size:.75rem; margin-left:8px;">{{ $room->room_code }}</code>
            @endif
        </div>
        <div class="rc-card-body">
            <div class="row g-2" style="font-size:.82rem; color:#6c757d;">
                @if ($room->capacity)
                    <div class="col-6"><i class="bi bi-people-fill me-1"></i> {{ $room->capacity }} orang</div>
                @endif
                @if ($room->hourly_rate)
                    <div class="col-6"><i class="bi bi-tag-fill me-1"></i> Rp
                        {{ number_format($room->hourly_rate, 0, ',', '.') }}/jam
                    </div>
                @endif
            </div>
            <a href="{{ route('receptionist.book', $room) }}" class="btn btn-accent w-100 mt-3" style="border-radius:10px;">
                <i class="bi bi-calendar-plus me-2"></i>Buat Booking Sekarang
            </a>
        </div>
    </div>

    <div class="section-title">
        <i class="bi bi-calendar3"></i>
        Jadwal 14 Hari ke Depan
    </div>

    @if ($bookings->isEmpty())
        <div class="rc-card">
            <div class="rc-card-body text-center py-4" style="color:#198754;">
                <i class="bi bi-check-circle-fill" style="font-size:2rem; display:block; margin-bottom:8px;"></i>
                <strong>Ruangan bebas!</strong><br>
                <span style="font-size:.82rem;">Tidak ada booking dalam 14 hari ke depan.</span>
            </div>
        </div>
    @else
        @php
            $grouped = $bookings->groupBy(fn($b) => \Carbon\Carbon::parse($b->start_datetime)->format('Y-m-d'));
        @endphp
        @foreach ($grouped as $date => $items)
            <div
                style="font-size:.78rem; font-weight:700; color:#6c757d; text-transform:uppercase; letter-spacing:.5px; margin:12px 0 6px;">
                {{ \Carbon\Carbon::parse($date)->locale('id')->isoFormat('dddd, D MMMM Y') }}
            </div>
            @foreach ($items as $b)
                <div class="rc-card mb-2">
                    <div class="rc-card-body py-2 px-3 d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-semibold" style="font-size:.85rem;">
                                {{ \App\Helpers\DateHelper::formatTimeID($b->start_datetime) }} –
                                {{ \App\Helpers\DateHelper::formatTimeID($b->end_datetime) }} WIB
                            </div>
                            <div style="font-size:.78rem; color:#6c757d;">
                                @if ($b->customer_name)
                                    {{ $b->customer_name }} &bull;
                                @endif
                                {{ $b->title }}
                            </div>
                        </div>
                        <span class="badge badge-{{ $b->status }}" style="font-size:.7rem; border-radius:6px; padding:4px 8px;">
                            {{ ucfirst($b->status) }}
                        </span>
                    </div>
                </div>
            @endforeach
        @endforeach
    @endif
@endsection