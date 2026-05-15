@extends('layouts.app')
@section('title', 'Peralatan')
@section('page-title', 'Manajemen Peralatan')
@section('content')
    <div class="page-card p-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-semibold mb-0">Daftar Peralatan</h6>
            <a href="{{ route('equipment.create') }}" class="btn btn-sm text-white" style="background:#e94560">
                <i class="bi bi-plus-lg me-1"></i>Tambah
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover small align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Foto</th>
                        <th>Nama</th>
                        <th>Kode</th>
                        <th>Kategori</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($equipment as $item)
                        <tr>
                            <td>{{ $loop->iteration + ($equipment->currentPage() - 1) * $equipment->perPage() }}</td>
                            <td>
                                @if ($item->image)
                                    <img src="{{ Storage::url($item->image) }}" width="48" height="36"
                                        class="rounded object-fit-cover">
                                @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                        style="width:48px;height:36px"><i class="bi bi-camera text-muted"></i></div>
                                @endif
                            </td>
                            <td class="fw-semibold">{{ $item->name }}</td>
                            <td><code>{{ $item->code }}</code></td>
                            <td>{{ $item->category->name ?? '-' }}</td>
                            <td>{{ $item->quantity_available }}/{{ $item->quantity_total }}</td>
                            <td class="d-flex gap-1">
                                <a href="{{ route('equipment.show', $item) }}" class="btn btn-sm btn-outline-info"><i
                                        class="bi bi-eye"></i></a>
                                <a href="{{ route('equipment.edit', $item) }}" class="btn btn-sm btn-outline-secondary"><i
                                        class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('equipment.destroy', $item) }}"
                                    onsubmit="return confirm('Hapus?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">Belum ada peralatan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $equipment->withQueryString()->links() }}
    </div>
@endsection
