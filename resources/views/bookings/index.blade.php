@extends('layouts.app')
@section('title', 'Transaksi')
@section('page-title', 'Transaksi')
@section('content')
    {{-- ── Search bar ── --}}
    <form method="GET" action="{{ route('bookings.index') }}" class="mb-3 d-flex gap-2">
        <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari kode / nama…"
            value="{{ request('search') }}" style="max-width:260px">
        <button class="btn btn-sm btn-secondary">Cari</button>
        @if (request()->hasAny(['search', 'status', 'loan_status', 'room_id']))
            <a href="{{ route('bookings.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
        @endif
    </form>

    {{-- ── Tabs ── --}}
    <ul class="nav nav-tabs mb-0" id="txTabs">
        <li class="nav-item">
            <a class="nav-link {{ !request()->has('tab') || request('tab') === 'bookings' ? 'active' : '' }}"
                href="{{ route('bookings.index', array_merge(request()->except('tab', 'bpage', 'lpage'), ['tab' => 'bookings'])) }}">
                <i class="bi bi-calendar-check me-1"></i>Pemesanan Ruangan
                @if ($bookings->total() > 0)
                    <span class="badge bg-secondary ms-1">{{ $bookings->total() }}</span>
                @endif
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request('tab') === 'loans' ? 'active' : '' }}"
                href="{{ route('bookings.index', array_merge(request()->except('tab', 'bpage', 'lpage'), ['tab' => 'loans'])) }}">
                <i class="bi bi-bag me-1"></i>Peminjaman Mandiri
                @if ($loans->total() > 0)
                    <span class="badge bg-secondary ms-1">{{ $loans->total() }}</span>
                @endif
            </a>
        </li>
    </ul>

    <div class="page-card p-3" style="border-top-left-radius:0">
        {{-- ── Tab: Pemesanan Ruangan ── --}}
        @if (!request()->has('tab') || request('tab') === 'bookings')
            <div class="d-flex gap-2 mb-2 flex-wrap">
                @foreach (['', 'pending', 'approved', 'rejected', 'cancelled'] as $s)
                    <a href="{{ route('bookings.index', array_merge(request()->except('status', 'bpage'), ['tab' => 'bookings', 'status' => $s])) }}"
                        class="btn btn-sm {{ request('status') === $s || ($s === '' && !request()->has('status')) ? 'btn-dark' : 'btn-outline-secondary' }}">
                        {{ $s === '' ? 'Semua' : ucfirst($s) }}
                    </a>
                @endforeach
            </div>
            <div class="table-responsive">
                <table class="table table-hover small align-middle mb-1">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Kode</th>
                            <th>Pelanggan</th>
                            <th>Dibuat oleh</th>
                            <th>Ruangan</th>
                            <th>Mulai</th>
                            <th>Selesai</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $b)
                            <tr>
                                <td>{{ $loop->iteration + ($bookings->currentPage() - 1) * $bookings->perPage() }}</td>
                                <td><code>{{ $b->booking_code }}</code></td>
                                <td>
                                    {{ $b->customer_name ?? $b->user->name }}
                                    @if ($b->customer_name)
                                        <span class="badge bg-info text-dark" style="font-size:.65rem;">Tamu</span>
                                    @endif
                                </td>
                                <td class="text-muted">{{ $b->user->name }}</td>
                                <td>{{ $b->room->name }}</td>
                                <td>{{ \App\Helpers\DateHelper::formatDateTimeID($b->start_datetime) }}</td>
                                <td>{{ \App\Helpers\DateHelper::formatDateTimeID($b->end_datetime) }}</td>
                                <td>
                                    <span class="badge badge-{{ $b->status }}">{{ ucfirst($b->status) }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('bookings.show', $b) }}" class="btn btn-sm btn-outline-info"><i
                                            class="bi bi-eye"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-3">Tidak ada data pemesanan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $bookings->appends(request()->except('bpage'))->links() }}

            {{-- ── Tab: Peminjaman Mandiri ── --}}
        @else
            <div class="d-flex gap-2 mb-2 flex-wrap">
                @foreach (['', 'pending', 'approved', 'active', 'returned', 'rejected'] as $s)
                    <a href="{{ route('bookings.index', array_merge(request()->except('loan_status', 'lpage'), ['tab' => 'loans', 'loan_status' => $s])) }}"
                        class="btn btn-sm {{ request('loan_status') === $s || ($s === '' && !request()->has('loan_status')) ? 'btn-dark' : 'btn-outline-secondary' }}">
                        {{ $s === '' ? 'Semua' : ucfirst($s) }}
                    </a>
                @endforeach
            </div>
            <div class="table-responsive">
                <table class="table table-hover small align-middle mb-1">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Kode</th>
                            <th>Tamu / Pemohon</th>
                            <th>Dibuat oleh</th>
                            <th>Keperluan</th>
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
                                <td>
                                    {{ $l->customer_name ?? $l->user->name }}
                                    @if ($l->customer_name)
                                        <span class="badge bg-info text-dark" style="font-size:.65rem;">Tamu</span>
                                    @endif
                                </td>
                                <td class="text-muted">{{ $l->user->name }}</td>
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
                                <td colspan="8" class="text-center text-muted py-3">Tidak ada data peminjaman mandiri.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $loans->appends(request()->except('lpage'))->links() }}
        @endif
    </div>
@endsection
