@extends('layouts.app')
@section('title', $room->name)
@section('page-title', 'Detail Ruangan')
@section('content')
    <div class="page-card p-4" style="max-width:600px">
        @if ($room->image)
            <img src="{{ Storage::url($room->image) }}" class="img-fluid rounded mb-3 w-100"
                style="max-height:260px;object-fit:cover">
        @endif
        <h5 class="fw-bold">{{ $room->name }} <code class="fs-6">{{ $room->code }}</code></h5>
        <table class="table table-borderless small">
            <tr>
                <th>Kapasitas</th>
                <td>{{ $room->capacity }} orang</td>
            </tr>
            <tr>
                <th>Status</th>
                <td>{!! $room->is_active
                    ? '<span class="badge bg-success">Aktif</span>'
                    : '<span class="badge bg-danger">Nonaktif</span>' !!}</td>
            </tr>
            <tr>
                <th>Deskripsi</th>
                <td>{{ $room->description ?? '-' }}</td>
            </tr>
            <tr>
                <th>Fasilitas</th>
                <td>
                    @if ($room->facilities)
                        <div class="d-flex flex-wrap gap-1">
                            @foreach ($room->facilities as $f)
                                <span class="badge bg-light text-dark border">{{ $f }}</span>
                            @endforeach
                        </div>
                    @else
                        -
                    @endif
                </td>
            </tr>
        </table>
        <div class="d-flex gap-2">
            <a href="{{ route('rooms.edit', $room) }}" class="btn btn-outline-secondary btn-sm"><i
                    class="bi bi-pencil me-1"></i>Edit</a>
            <a href="{{ route('rooms.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
        </div>
    </div>
@endsection
