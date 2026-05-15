@extends('layouts.app')
@section('title', 'Detail Booking')
@section('page-title', 'Detail Booking')
@section('content')
    <div class="page-card p-4" style="max-width:700px">
        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <h5 class="fw-bold mb-1">{{ $booking->title }}</h5>
                <code>{{ $booking->booking_code }}</code>
            </div>
            <span class="badge badge-{{ $booking->status }} fs-6">{{ ucfirst($booking->status) }}</span>
        </div>

        {{-- Info Ruangan --}}
        <table class="table table-borderless small">
            <tr>
                <th width="140">Ruangan</th>
                <td>{{ $booking->room->name }}</td>
            </tr>
            @if ($booking->customer_name)
                <tr>
                    <th>Nama Pelanggan</th>
                    <td class="fw-semibold">{{ $booking->customer_name }}</td>
                </tr>
                @if ($booking->customer_phone)
                    <tr>
                        <th>Telepon</th>
                        <td>{{ $booking->customer_phone }}</td>
                    </tr>
                @endif
                <tr>
                    <th>Resepsionis</th>
                    <td>{{ $booking->user->name }} <span class="badge bg-secondary"
                            style="font-size:.65rem;">Resepsionis</span></td>
                </tr>
            @else
                <tr>
                    <th>Pemohon</th>
                    <td>{{ $booking->user->name }}</td>
                </tr>
            @endif
            <tr>
                <th>Mulai</th>
                <td>{{ \App\Helpers\DateHelper::formatDateTimeID($booking->start_datetime) }}</td>
            </tr>
            <tr>
                <th>Selesai</th>
                <td>{{ \App\Helpers\DateHelper::formatDateTimeID($booking->end_datetime) }}</td>
            </tr>
            <tr>
                <th>Catatan</th>
                <td>{{ $booking->notes ?? '-' }}</td>
            </tr>
            @if ($booking->admin_notes)
                <tr>
                    <th>Catatan Admin</th>
                    <td>{{ $booking->admin_notes }}</td>
                </tr>
            @endif
            @if ($booking->approvedBy)
                <tr>
                    <th>Disetujui oleh</th>
                    <td>{{ $booking->approvedBy->name }} pada
                        {{ \App\Helpers\DateHelper::formatDateTimeID($booking->approved_at) }}</td>
                </tr>
            @endif
        </table>

        {{-- Linked Equipment Loans --}}
        @if ($booking->equipmentLoans->isNotEmpty())
            <hr>
            <h6 class="fw-bold mb-2"><i class="bi bi-box-seam me-1"></i>Peralatan yang Dipinjam</h6>
            @foreach ($booking->equipmentLoans as $loan)
                <div class="border rounded p-2 mb-2 small">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <code>{{ $loan->loan_code }}</code>
                        <span class="badge badge-{{ $loan->status }}">{{ ucfirst($loan->status) }}</span>
                    </div>
                    <ul class="mb-0 ps-3">
                        @foreach ($loan->items as $item)
                            <li>{{ $item->equipment->name }} × {{ $item->quantity }}</li>
                        @endforeach
                    </ul>
                    @if ($loan->approvedBy)
                        <div class="text-muted mt-1">
                            Disetujui: {{ $loan->approvedBy->name }}
                            {{ \App\Helpers\DateHelper::formatDateTimeID($loan->approved_at) }}
                        </div>
                    @endif
                </div>
            @endforeach
            @if ($booking->status === 'pending')
                <p class="text-muted small mb-0">
                    <i class="bi bi-info-circle me-1"></i>Peralatan di atas akan <strong>otomatis disetujui</strong>
                    saat booking ini disetujui.
                </p>
            @endif
        @endif

        {{-- Ubah Status (untuk pending & approved) --}}
        @if (in_array($booking->status, ['pending', 'approved', 'rejected', 'cancelled']))
            <hr>
            <h6 class="fw-semibold mb-2"><i class="bi bi-pencil-square me-1"></i>Ubah Status Pemesanan</h6>
            @if ($errors->any())
                <div class="alert alert-danger alert-sm py-2 small">{{ $errors->first('error') }}</div>
            @endif
            <form method="POST" action="{{ route('bookings.updateStatus', $booking) }}">
                @csrf
                <div class="row g-2 align-items-end">
                    <div class="col-sm-4">
                        <label class="form-label small mb-1">Status Baru</label>
                        <select name="status" class="form-select form-select-sm" required>
                            @foreach (['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'cancelled' => 'Cancelled'] as $val => $label)
                                <option value="{{ $val }}" {{ $booking->status === $val ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label small mb-1">Catatan Admin</label>
                        <input type="text" name="admin_notes" class="form-control form-control-sm"
                            placeholder="Alasan perubahan (opsional)"
                            value="{{ old('admin_notes', $booking->admin_notes) }}">
                    </div>
                    <div class="col-sm-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100">Simpan</button>
                    </div>
                </div>
                <p class="text-muted small mt-2 mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    Mengubah ke <strong>Approved</strong> akan otomatis memotong stok peralatan terkait.
                    Mengubah dari Approved ke status lain akan <strong>mengembalikan stok</strong>.
                </p>
            </form>
        @endif

        <div class="mt-3">
            <a href="{{ route('bookings.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
        </div>
    </div>
@endsection
