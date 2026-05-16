@extends('layouts.app')
@section('title', 'Kategori')
@section('page-title', 'Kategori Peralatan')
@section('content')
    <div class="page-card p-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-semibold mb-0">Daftar Kategori</h6>
            <a href="{{ route('categories.create') }}" class="btn btn-sm text-white" style="background:#e94560">
                <i class="bi bi-plus-lg me-1"></i>Tambah
            </a>
        </div>
        <table class="table table-hover small">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Nama</th>
                    <th>Deskripsi</th>
                    <th>Peralatan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $cat)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="fw-semibold">{{ $cat->name }}</td>
                        <td>{{ $cat->description ?? '-' }}</td>
                        <td>{{ $cat->equipment_count }}</td>
                        <td class="d-flex gap-1">
                            <a href="{{ route('categories.edit', $cat) }}" class="btn btn-sm btn-outline-secondary"><i
                                    class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('categories.destroy', $cat) }}"
                                onsubmit="return confirm('Hapus kategori ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">Belum ada kategori.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        {{ $categories->withQueryString()->links() }}
    </div>
@endsection