@extends('layouts.app')
@section('title', $equipment->name)
@section('page-title', 'Detail Peralatan')
@section('content')
    <div class="page-card p-4" style="max-width:640px">
        <div class="row g-4">
            <div class="col-md-4">
                @if ($equipment->image)
                    <img src="{{ Storage::url($equipment->image) }}" class="img-fluid rounded" alt="{{ $equipment->name }}">
                @else
                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height:180px">
                        <i class="bi bi-camera fs-1 text-muted"></i>
                    </div>
                @endif
            </div>
            <div class="col-md-8">
                <h5 class="fw-bold">{{ $equipment->name }}</h5>
                <code class="text-muted">{{ $equipment->code }}</code>
                <table class="table table-borderless small mt-3">
                    <tr>
                        <th>Kategori</th>
                        <td>{{ $equipment->category->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Stok Tersedia</th>
                        <td>{{ $equipment->quantity_available }}/{{ $equipment->quantity_total }}</td>
                    </tr>
                    <tr>
                        <th>Lokasi</th>
                        <td>{{ $equipment->location ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>{!! $equipment->is_active
                            ? '<span class="badge bg-success">Aktif</span>'
                            : '<span class="badge bg-danger">Nonaktif</span>' !!}
                        </td>
                    </tr>
                    <tr>
                        <th>Deskripsi</th>
                        <td>{{ $equipment->description ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="mt-3 d-flex gap-2">
            <a href="{{ route('equipment.edit', $equipment) }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
            <a href="{{ route('equipment.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
        </div>
    </div>
@endsection
