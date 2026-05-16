@extends('layouts.app')
@section('title', 'Detail Peminjaman')
@section('page-title', 'Detail Peminjaman')
@section('content')
    <div class="page-card p-4" style="max-width:700px">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <h5 class="fw-bold mb-1">Peminjaman Peralatan</h5>
                <code>{{ $loan->loan_code }}</code>
            </div>
            <span class="badge badge-{{ $loan->status }} fs-6">{{ ucfirst($loan->status) }}</span>
        </div>

        <table class="table table-borderless small mb-3">
            <tr>
                <th>Pemohon</th>
                <td>{{ $loan->user->name }}</td>
            </tr>
            <tr>
                <th>Tujuan</th>
                <td>{{ $loan->purpose }}</td>
            </tr>
            <tr>
                <th>Jatuh Tempo</th>
                <td>{{ \App\Helpers\DateHelper::formatDateID($loan->due_date) }}</td>
            </tr>
            <tr>
                <th>Catatan</th>
                <td>{{ $loan->notes ?? '-' }}</td>
            </tr>
            @if ($loan->admin_notes)
                <tr>
                    <th>Catatan Admin</th>
                    <td>{{ $loan->admin_notes }}</td>
                </tr>
            @endif
        </table>

        <h6 class="fw-semibold mb-2">Item Dipinjam</h6>
        <table class="table table-bordered small mb-3">
            <thead class="table-light">
                <tr>
                    <th>Peralatan</th>
                    <th>Jumlah</th>
                    <th>Catatan</th>
                    <th>Dikembalikan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($loan->items as $item)
                    <tr>
                        <td>{{ $item->equipment->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td class="text-muted">{{ $item->notes ?? '-' }}</td>
                        <td>
                            @if ($item->check_out_at)
                                <span class="badge bg-success"><i class="bi bi-check-lg"></i>
                                    {{ \App\Helpers\DateHelper::formatDateTimeID($item->check_out_at) }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if ($loan->status === 'pending')
            <div class="d-flex gap-2">
                <form method="POST" action="{{ route('loans.approve', $loan) }}" class="flex-grow-1">
                    @csrf
                    <div class="mb-2">
                        <input type="text" name="admin_notes" placeholder="Catatan admin (opsional)"
                            class="form-control form-control-sm">
                    </div>
                    <button type="submit" class="btn btn-success btn-sm w-100"><i
                            class="bi bi-check-lg me-1"></i>Setujui</button>
                </form>
                <form method="POST" action="{{ route('loans.reject', $loan) }}" class="flex-grow-1">
                    @csrf
                    <div class="mb-2">
                        <input type="text" name="admin_notes" placeholder="Alasan penolakan"
                            class="form-control form-control-sm" required>
                    </div>
                    <button type="submit" class="btn btn-danger btn-sm w-100"><i class="bi bi-x-lg me-1"></i>Tolak</button>
                </form>
            </div>
        @endif

        {{-- ── Ubah Status ── --}}
        @if (in_array($loan->status, ['pending', 'approved', 'rejected', 'cancelled']))
            <hr>
            <h6 class="fw-semibold mb-2"><i class="bi bi-pencil-square me-1"></i>Ubah Status Peminjaman</h6>
            @if ($errors->any())
                <div class="alert alert-danger alert-sm py-2 small">{{ $errors->first('error') }}</div>
            @endif
            <form method="POST" action="{{ route('loans.updateStatus', $loan) }}">
                @csrf
                <div class="row g-2 align-items-end">
                    <div class="col-sm-4">
                        <label class="form-label small mb-1">Status Baru</label>
                        <select name="status" class="form-select form-select-sm" required>
                            @foreach (['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'cancelled' => 'Cancelled'] as $val => $label)
                                <option value="{{ $val }}" {{ $loan->status === $val ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label small mb-1">Catatan Admin</label>
                        <input type="text" name="admin_notes" class="form-control form-control-sm"
                            placeholder="Alasan perubahan (opsional)" value="{{ old('admin_notes', $loan->admin_notes) }}">
                    </div>
                    <div class="col-sm-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100">Simpan</button>
                    </div>
                </div>
                <p class="text-muted small mt-2 mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    Mengubah ke <strong>Approved</strong> akan memotong stok. Mengubah dari Approved ke status lain akan
                    <strong>mengembalikan stok</strong>.
                </p>
            </form>
        @endif

        {{-- ── Form Pengembalian Peralatan ── --}}
        @if (in_array($loan->status, ['approved', 'active', 'overdue']))
            <hr>
            <h6 class="fw-semibold mb-2"><i class="bi bi-box-arrow-in-left me-1"></i>Proses Pengembalian Peralatan</h6>
            @if ($errors->has('error'))
                <div class="alert alert-danger py-2 small">{{ $errors->first('error') }}</div>
            @endif
            <form method="POST" action="{{ route('loans.return', $loan) }}">
                @csrf
                <p class="text-muted small mb-3">
                    <i class="bi bi-info-circle me-1"></i>
                    Konfirmasi semua peralatan telah dikembalikan. Stok akan diperbarui otomatis.
                </p>
                @foreach ($loan->items as $i => $item)
                    <input type="hidden" name="items[{{ $i }}][id]" value="{{ $item->id }}">
                    <div class="d-flex align-items-center justify-content-between border rounded px-3 py-2 mb-2 small">
                        <span>
                            <i class="bi bi-box me-1"></i>
                            <strong>{{ $item->equipment->name }}</strong>
                            <span class="badge bg-secondary ms-1">{{ $item->quantity }} unit</span>
                        </span>
                        <input type="text" name="items[{{ $i }}][notes]"
                            class="form-control form-control-sm ms-3" style="max-width:200px"
                            placeholder="Catatan (opsional)">
                    </div>
                @endforeach
                <div class="mb-3 mt-2">
                    <input type="text" name="notes" class="form-control form-control-sm"
                        placeholder="Catatan umum pengembalian (opsional)">
                </div>
                <button type="submit" class="btn btn-warning btn-sm"
                    onclick="return confirm('Konfirmasi semua peralatan sudah dikembalikan?')">
                    <i class="bi bi-box-arrow-in-left me-1"></i>Konfirmasi Pengembalian & Restock
                </button>
            </form>
        @endif

        <div class="mt-3">
            <a href="{{ route('loans.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
        </div>
    </div>
@endsection
