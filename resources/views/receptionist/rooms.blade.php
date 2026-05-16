@extends('receptionist.layout')
@section('title', 'Pilih Ruangan')

@section('content')
    <div class="section-title">
        <i class="bi bi-grid-3x3-gap-fill"></i>
        Pilih Ruangan untuk Booking
    </div>

    {{-- Search --}}
    <form method="GET" class="mb-3">
        <div class="input-group" style="border-radius:12px; overflow:hidden; box-shadow:0 2px 6px rgba(0,0,0,.08);">
            <span class="input-group-text" style="background:#fff; border-right:none; border:1px solid #dee2e6;">
                <i class="bi bi-search" style="color:#6c757d;"></i>
            </span>
            <input type="text" name="search" class="form-control" placeholder="Cari nama ruangan..."
                value="{{ request('search') }}" style="border-left:none; border:1px solid #dee2e6;">
            @if (request('search'))
                <a href="{{ route('receptionist.rooms') }}" class="btn btn-outline-secondary"
                    style="border-radius:0 12px 12px 0;">
                    <i class="bi bi-x"></i>
                </a>
            @endif
        </div>
    </form>

    @if ($rooms->isEmpty())
        <div class="rc-card">
            <div class="rc-card-body text-center py-5" style="color:#6c757d;">
                <i class="bi bi-building-x" style="font-size:2.5rem; display:block; margin-bottom:10px;"></i>
                Tidak ada ruangan tersedia.
            </div>
        </div>
    @else
        <div class="row g-3">
            @foreach ($rooms as $room)
                <div class="col-6">
                    <div class="room-card h-100">
                        <div class="room-card-header">
                            <div class="fw-bold" style="font-size:.92rem; line-height:1.3;">{{ $room->name }}</div>
                            @if ($room->room_code)
                                <code style="font-size:.72rem; opacity:.7; display:block; margin-top:2px;">{{ $room->room_code }}</code>
                            @endif
                        </div>
                        <div class="room-card-body">
                            @if ($room->capacity)
                                <div style="font-size:.78rem; color:#6c757d; margin-bottom:4px;">
                                    <i class="bi bi-people-fill me-1"></i>{{ $room->capacity }} orang
                                </div>
                            @endif
                            @if ($room->hourly_rate)
                                <div style="font-size:.78rem; color:#0f5132; font-weight:600; margin-bottom:4px;">
                                    <i class="bi bi-tag-fill me-1"></i>
                                    Rp {{ number_format($room->hourly_rate, 0, ',', '.') }}/jam
                                </div>
                            @endif
                            @if ($room->description)
                                <p style="font-size:.75rem; color:#6c757d; margin-bottom:0; line-height:1.4;">
                                    {{ Str::limit($room->description, 60) }}
                                </p>
                            @endif
                            @if ($room->facilities)
                                <div style="font-size:.72rem; color:#6c757d; margin-top:6px;">
                                    <i class="bi bi-check2-circle me-1" style="color:#198754;"></i>
                                    {{ Str::limit(is_array($room->facilities) ? implode(', ', $room->facilities) : $room->facilities, 50) }}
                                </div>
                            @endif
                        </div>
                        <div class="room-card-footer">
                            <a href="{{ route('receptionist.book', $room) }}" class="btn btn-accent btn-sm w-100"
                                style="border-radius:8px; font-size:.82rem; padding:7px 0;">
                                <i class="bi bi-calendar-plus me-1"></i>Buat Booking
                            </a>
                            <a href="{{ route('receptionist.availability', $room) }}"
                                class="btn btn-outline-secondary btn-sm w-100 mt-1"
                                style="border-radius:8px; font-size:.78rem; padding:5px 0;">
                                <i class="bi bi-eye me-1"></i>Cek Jadwal
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

@endsection