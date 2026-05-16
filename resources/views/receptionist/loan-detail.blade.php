@extends('receptionist.layout')

@section('title', 'Detail Peminjaman — ' . $equipmentLoan->loan_code)

@section('content')
    <div class="mb-3">
        <a href="{{ route('receptionist.loans') }}" class="text-decoration-none text-muted small">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar Peminjaman
        </a>
    </div>

    {{-- Header Card --}}
    <div class="rc-card mb-3">
        <div class="rc-card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-box-seam-fill me-2"></i>{{ $equipmentLoan->loan_code }}</span>
            @php
                $badge = match ($equipmentLoan->status) {
                    'pending' => 'warning text-dark',
                    'approved' => 'success',
                    'active' => 'primary',
                    'returned' => 'info text-dark',
                    'rejected' => 'danger',
                    'overdue' => 'danger',
                    default => 'secondary',
                };
                $label = match ($equipmentLoan->status) {
                    'pending' => 'Menunggu Persetujuan',
                    'approved' => 'Disetujui',
                    'active' => 'Sedang Dipinjam',
                    'returned' => 'Sudah Dikembalikan',
                    'rejected' => 'Ditolak',
                    'overdue' => 'Terlambat',
                    default => Str::ucfirst($equipmentLoan->status),
                };
            @endphp
            <span class="badge bg-{{ $badge }} rounded-pill">{{ $label }}</span>
        </div>
        <div class="rc-card-body">
            <div class="row g-2">
                <div class="col-6">
                    <small class="text-muted d-block">Tamu</small>
                    <strong>{{ $equipmentLoan->customer_name ?? auth()->user()->name }}</strong>
                    @if ($equipmentLoan->customer_phone)
                        <div class="text-muted small">{{ $equipmentLoan->customer_phone }}</div>
                    @endif
                </div>
                <div class="col-6 text-end">
                    <small class="text-muted d-block">Diajukan</small>
                    <strong>{{ \App\Helpers\DateHelper::formatDateTimeID($equipmentLoan->created_at) }}</strong>
                </div>
                <div class="col-6">
                    <small class="text-muted d-block">Tanggal Pinjam</small>
                    <strong>{{ optional($equipmentLoan->loan_date) ? \App\Helpers\DateHelper::formatDateID($equipmentLoan->loan_date) : '-' }}</strong>
                </div>
                <div class="col-6 text-end">
                    <small class="text-muted d-block">Batas Kembali</small>
                    <strong>{{ \App\Helpers\DateHelper::formatDateID($equipmentLoan->due_date) }}</strong>
                </div>
            </div>

            @if ($equipmentLoan->booking)
                <div class="mt-3 p-2 rounded" style="background:#f0f2f5">
                    <small class="text-muted"><i class="bi bi-link-45deg"></i> Terhubung dengan Booking</small>
                    <div class="fw-semibold">{{ $equipmentLoan->booking->booking_code }} —
                        {{ $equipmentLoan->booking->title }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Keperluan --}}
    <div class="rc-card mb-3">
        <div class="rc-card-header"><i class="bi bi-chat-left-text-fill me-2"></i>Keperluan</div>
        <div class="rc-card-body">
            <p class="mb-0">{{ $equipmentLoan->purpose }}</p>
            @if ($equipmentLoan->notes)
                <small class="text-muted mt-2 d-block"><i class="bi bi-sticky"></i> {{ $equipmentLoan->notes }}</small>
            @endif
        </div>
    </div>

    {{-- Daftar Peralatan --}}
    <div class="rc-card mb-3">
        <div class="rc-card-header"><i class="bi bi-list-check me-2"></i>Peralatan ({{ $equipmentLoan->items->count() }}
            item)</div>
        <div class="rc-card-body p-0">
            @foreach ($equipmentLoan->items as $item)
                <div class="d-flex justify-content-between align-items-center px-3 py-2
                        {{ !$loop->last ? 'border-bottom' : '' }}">
                    <div>
                        <span class="fw-semibold">{{ $item->equipment->name ?? 'Peralatan dihapus' }}</span>
                    </div>
                    <span class="badge text-bg-light border fw-semibold">×{{ $item->quantity }}</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Respons Admin --}}
    @if ($equipmentLoan->admin_notes || $equipmentLoan->approvedBy)
        <div class="rc-card mb-3">
            <div class="rc-card-header">
                <i class="bi bi-shield-check-fill me-2"></i>
                Respons Admin
            </div>
            <div class="rc-card-body">
                @if ($equipmentLoan->approvedBy)
                    <small class="text-muted">Oleh: <strong>{{ $equipmentLoan->approvedBy->name }}</strong>
                        @if ($equipmentLoan->approved_at)
                            pada {{ \App\Helpers\DateHelper::formatDateTimeID($equipmentLoan->approved_at) }}
                        @endif
                    </small>
                @endif
                @if ($equipmentLoan->admin_notes)
                    <p class="mb-0 mt-2">{{ $equipmentLoan->admin_notes }}</p>
                @endif
            </div>
        </div>
    @endif

    @if ($equipmentLoan->status === 'pending')
        <div class="rc-alert-danger">
            <i class="bi bi-hourglass-split me-2"></i>
            Peminjaman ini sedang menunggu persetujuan admin.
        </div>
    @elseif ($equipmentLoan->status === 'approved')
        <div class="rc-alert-success">
            <i class="bi bi-check-circle-fill me-2"></i>
            Peminjaman disetujui. Peralatan bisa diambil sesuai tanggal.
        </div>
    @endif

    <a href="{{ route('receptionist.loans') }}" class="btn btn-outline-secondary w-100 mt-1">
        <i class="bi bi-arrow-left me-2"></i>Kembali ke Daftar
    </a>
@endsection