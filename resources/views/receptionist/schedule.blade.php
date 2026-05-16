@extends('receptionist.layout')
@section('title', 'Jadwal Hari Ini')

@section('content')
    <div class="section-title">
        <i class="bi bi-calendar3"></i>
        Jadwal Hari Ini — {{ \Carbon\Carbon::parse($today)->locale('id')->isoFormat('D MMMM Y') }}
    </div>

    {{-- Filter Ruangan --}}
    <div class="d-flex gap-2 mb-3 overflow-auto pb-1" style="scrollbar-width:none;">
        <a href="{{ route('receptionist.schedule') }}"
            class="btn btn-sm {{ !request('room') ? 'btn-accent' : 'btn-outline-secondary' }}"
            style="border-radius:20px; white-space:nowrap; font-size:.78rem; padding:4px 14px;">
            Semua Ruangan
        </a>
        @foreach ($rooms as $room)
            <a href="{{ route('receptionist.schedule', ['room' => $room->id]) }}"
                class="btn btn-sm {{ request('room') == $room->id ? 'btn-accent' : 'btn-outline-secondary' }}"
                style="border-radius:20px; white-space:nowrap; font-size:.78rem; padding:4px 14px;">
                {{ $room->name }}
            </a>
        @endforeach
    </div>

    @php
        $filtered = request('room') ? $bookings->where('room_id', request('room')) : $bookings;
    @endphp

    @if ($filtered->isEmpty())
        <div class="rc-card">
            <div class="rc-card-body text-center py-5" style="color:#6c757d;">
                <i class="bi bi-calendar-check" style="font-size:2.5rem; display:block; margin-bottom:10px;"></i>
                <strong>Tidak ada jadwal hari ini</strong><br>
                <span style="font-size:.85rem;">Semua ruangan bebas!</span>
            </div>
        </div>
    @else
        @foreach ($filtered->sortBy('start_datetime') as $b)
            <div class="rc-card mb-2">
                <div class="rc-card-body py-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="fw-bold" style="font-size:.95rem; color:#1a1a2e;">
                                {{ \App\Helpers\DateHelper::formatTimeID($b->start_datetime) }} –
                                {{ \App\Helpers\DateHelper::formatTimeID($b->end_datetime) }} WIB
                            </div>
                            <div style="font-size:.8rem; color:#6c757d;">
                                <i class="bi bi-building me-1"></i>{{ $b->room->name }}
                            </div>
                        </div>
                        <span class="badge badge-{{ $b->status }}" style="font-size:.72rem; border-radius:6px; padding:4px 10px;">
                            {{ ucfirst($b->status) }}
                        </span>
                    </div>
                    <div style="font-size:.85rem; font-weight:600; margin-bottom:2px;">
                        <i class="bi bi-person-fill me-1" style="color:#e94560;"></i>
                        {{ $b->customer_name ?? '—' }}
                    </div>
                    <div style="font-size:.78rem; color:#6c757d;">{{ $b->title }}</div>
                </div>
            </div>
        @endforeach
    @endif

    <a href="{{ route('receptionist.rooms') }}" class="btn btn-accent w-100 py-3 mt-2"
        style="border-radius:14px; font-size:.95rem;">
        <i class="bi bi-plus-circle-fill me-2"></i>Buat Booking Baru
    </a>

@endsection