@extends('receptionist.layout')

@section('title', 'Peminjaman Peralatan')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold mb-0" style="color:var(--dark)"><i class="bi bi-box-seam-fill" style="color:var(--accent)"></i>
                Peminjaman Peralatan</h5>
            <small class="text-muted">Ajukan peminjaman peralatan untuk tamu</small>
        </div>
        <a href="{{ route('receptionist.loans.create') }}" class="btn btn-accent btn-sm px-3">
            <i class="bi bi-plus-lg"></i> Sewa
        </a>
    </div>

    {{-- Filter Status --}}
    <div class="d-flex gap-2 mb-3 flex-wrap">
        @foreach (['' => 'Semua', 'pending' => 'Pending', 'approved' => 'Disetujui', 'active' => 'Aktif', 'returned' => 'Dikembalikan', 'rejected' => 'Ditolak'] as $val => $label)
            <a href="{{ request()->fullUrlWithQuery(['status' => $val]) }}"
                class="badge {{ request('status', '') === $val ? 'text-bg-dark' : 'text-bg-light' }} text-decoration-none px-3 py-2">
                {{ $label }}
            </a>
        @endforeach
    </div>

    @forelse ($loans as $loan)
        <a href="{{ route('receptionist.loans.show', $loan) }}" class="text-decoration-none">
            <div class="booking-card mb-2">
                <div class="d-flex">
                    <div
                        class="bc-border {{ match ($loan->status) {'pending' => 'pending','approved', 'active' => 'approved','rejected' => 'rejected',default => 'cancelled'} }}">
                    </div>
                    <div class="p-3 flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="fw-bold text-dark">{{ $loan->loan_code }}</span>
                                @if ($loan->customer_name)
                                    <span class="ms-1 badge bg-secondary text-white"
                                        style="font-size:.65rem">{{ $loan->customer_name }}</span>
                                @endif
                            </div>
                            <span class="badge badge-{{ $loan->status }} rounded-pill">
                                {{ Str::ucfirst($loan->status) }}
                            </span>
                        </div>
                        <div class="mt-1 text-muted" style="font-size:.82rem">
                            <i class="bi bi-bag me-1"></i>{{ $loan->items->count() }} item
                            <span class="mx-2">·</span>
                            <i class="bi bi-calendar me-1"></i>
                            {{ optional($loan->loan_date) ? \App\Helpers\DateHelper::formatDateID($loan->loan_date) : '-' }}
                            @if ($loan->due_date)
                                s/d {{ \App\Helpers\DateHelper::formatDateID($loan->due_date) }}
                            @endif
                        </div>
                        <div class="mt-1 text-muted" style="font-size:.78rem">
                            {{ Str::limit($loan->purpose, 60) }}
                        </div>
                    </div>
                </div>
            </div>
        </a>
    @empty
        <div class="rc-card">
            <div class="rc-card-body text-center py-4 text-muted">
                <i class="bi bi-box-seam" style="font-size:2.5rem;opacity:.3"></i>
                <p class="mt-2 mb-0 fw-semibold">Belum ada peminjaman</p>
                <small>Klik <strong>+ Sewa</strong> untuk mengajukan sewa peralatan baru</small>
            </div>
        </div>
    @endforelse

    {{ $loans->withQueryString()->links('pagination::bootstrap-5') }}
@endsection
