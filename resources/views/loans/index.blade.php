@extends('layouts.app')
@section('title', 'Peminjaman Peralatan')
@section('page-title', 'Peminjaman Peralatan')
@section('content')
    <div class="page-card p-3">
        <div class="table-responsive">
            <table class="table table-hover small align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Kode</th>
                        <th>Pemohon</th>
                        <th>Tujuan</th>
                        <th>Jatuh Tempo</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($loans as $l)
                        <tr>
                            <td>{{ $loop->iteration + ($loans->currentPage() - 1) * $loans->perPage() }}</td>
                            <td><code>{{ $l->loan_code }}</code></td>
                            <td>{{ $l->user->name }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($l->purpose, 40) }}</td>
                            <td>{{ \App\Helpers\DateHelper::formatDateID($l->due_date) }}</td>
                            <td><span class="badge badge-{{ $l->status }}">{{ ucfirst($l->status) }}</span></td>
                            <td>
                                <a href="{{ route('loans.show', $l) }}" class="btn btn-sm btn-outline-info"><i
                                        class="bi bi-eye"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Tidak ada data peminjaman.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $loans->links() }}
    </div>
@endsection
