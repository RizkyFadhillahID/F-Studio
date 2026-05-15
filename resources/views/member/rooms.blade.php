@extends('member.layout')
@section('title', 'Pilih Ruangan')

@section('content')
    <div class="mb-3">
        <h5 class="fw-bold mb-1">Pilih Ruangan</h5>
        <p class="text-muted small mb-0">Pilih ruangan yang ingin Anda pesan</p>
    </div>

    {{-- Search --}}
    <form method="GET" class="mb-4">
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0" style="border-radius:10px 0 0 10px">
                <i class="bi bi-search text-muted"></i>
            </span>
            <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0 ps-0"
                placeholder="Cari ruangan..." style="border-radius:0 10px 10px 0">
        </div>
    </form>

    {{-- Room Grid --}}
    <div class="row g-3">
        @forelse($rooms as $room)
            <div class="col-12 col-sm-6">
                <div class="room-card h-100">
                    <div class="room-header">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="fw-bold mb-1">{{ $room->name }}</h6>
                                <code style="font-size:0.75rem; color:#aaa">{{ $room->room_code }}</code>
                            </div>
                            <span class="badge bg-success">Tersedia</span>
                        </div>
                    </div>
                    <div class="room-body">
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="text-muted small"><i class="bi bi-people me-1"></i>Kapasitas</div>
                                <div class="fw-semibold">{{ $room->capacity }} orang</div>
                            </div>
                            @if ($room->hourly_rate > 0)
                                <div class="col-6">
                                    <div class="text-muted small"><i class="bi bi-clock me-1"></i>Tarif/jam</div>
                                    <div class="fw-semibold">Rp {{ number_format($room->hourly_rate, 0, ',', '.') }}</div>
                                </div>
                            @else
                                <div class="col-6">
                                    <div class="text-muted small"><i class="bi bi-tag me-1"></i>Tarif</div>
                                    <div class="fw-semibold text-success">Gratis</div>
                                </div>
                            @endif
                        </div>
                        @if ($room->description)
                            <p class="text-muted small mb-3" style="line-height:1.4">
                                {{ Str::limit($room->description, 80) }}</p>
                        @endif
                        @if ($room->facilities)
                            <div class="mb-3">
                                @foreach (array_slice((array) $room->facilities, 0, 4) as $fac)
                                    <span class="badge bg-light text-dark me-1 mb-1">
                                        <i class="bi bi-check2 text-success me-1"></i>{{ $fac }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                        <a href="{{ route('member.book', $room) }}" class="btn btn-accent w-100">
                            <i class="bi bi-calendar-plus me-1"></i>Pesan Sekarang
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="fcard text-center py-4 text-muted">
                    <i class="bi bi-door-closed fs-2 mb-2 d-block"></i>
                    Tidak ada ruangan tersedia.
                </div>
            </div>
        @endforelse
    </div>
@endsection
