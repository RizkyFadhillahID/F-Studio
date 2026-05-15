@extends('layouts.app')
@section('title', 'Ruangan')
@section('page-title', 'Manajemen Ruangan')
@section('content')
    <div class="page-card p-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-semibold mb-0">Daftar Ruangan</h6>
            <a href="{{ route('rooms.create') }}" class="btn btn-sm text-white" style="background:#e94560">
                <i class="bi bi-plus-lg me-1"></i>Tambah
            </a>
        </div>
        <div class="row g-3">
            @forelse($rooms as $room)
                <div class="col-md-6 col-xl-4">
                    <div class="border rounded overflow-hidden h-100">
                        @if ($room->image)
                            <img src="{{ Storage::url($room->image) }}" class="w-100" style="height:140px;object-fit:cover"
                                alt="{{ $room->name }}">
                        @else
                            <div class="bg-light d-flex align-items-center justify-content-center" style="height:140px"><i
                                    class="bi bi-building fs-1 text-muted"></i></div>
                        @endif
                        <div class="p-3">
                            <div class="d-flex justify-content-between">
                                <h6 class="fw-bold mb-1">{{ $room->name }}</h6>
                                <code class="small">{{ $room->code }}</code>
                            </div>
                            <div class="small text-muted mb-2"><i class="bi bi-people me-1"></i>Kapasitas
                                {{ $room->capacity }}</div>
                            @if ($room->facilities)
                                <div class="d-flex flex-wrap gap-1 mb-2">
                                    @foreach (array_slice($room->facilities, 0, 3) as $f)
                                        <span class="badge bg-light text-dark border small">{{ $f }}</span>
                                    @endforeach
                                    @if (count($room->facilities) > 3)
                                        <span
                                            class="badge bg-light text-dark border small">+{{ count($room->facilities) - 3 }}</span>
                                    @endif
                                </div>
                            @endif
                            <div class="d-flex gap-1 mt-2">
                                <a href="{{ route('rooms.show', $room) }}"
                                    class="btn btn-sm btn-outline-info flex-grow-1"><i class="bi bi-eye me-1"></i>Detail</a>
                                <a href="{{ route('rooms.edit', $room) }}" class="btn btn-sm btn-outline-secondary"><i
                                        class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('rooms.destroy', $room) }}"
                                    onsubmit="return confirm('Hapus ruangan ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col">
                    <p class="text-muted">Belum ada ruangan.</p>
                </div>
            @endforelse
        </div>
        <div class="mt-3">{{ $rooms->links() }}</div>
    </div>
@endsection
