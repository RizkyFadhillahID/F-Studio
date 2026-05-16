@extends('receptionist.layout')
@section('title', 'Detail Booking — ' . $booking->booking_code)

@section('content')
    <a href="{{ route('receptionist.bookings') }}" class="d-inline-flex align-items-center gap-2 mb-3"
        style="color:#6c757d; text-decoration:none; font-size:.85rem; font-weight:600;">
        <i class="bi bi-arrow-left"></i> Kembali ke daftar booking
    </a>

    {{-- Status Header --}}
    <div class="rc-card mb-3" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); color:#fff;">
        <div class="rc-card-body" style="background:none;">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="badge badge-{{ $booking->status }}"
                        style="font-size:.8rem; padding:5px 12px; border-radius:8px; margin-bottom:8px; display:inline-block;">
                        @php $statusLabels = ['pending'=>'Menunggu Persetujuan','approved'=>'Disetujui','rejected'=>'Ditolak','cancelled'=>'Dibatalkan','completed'=>'Selesai'] @endphp
                        <i
                            class="bi bi-{{ $booking->status === 'approved' ? 'check-circle-fill' : ($booking->status === 'rejected' ? 'x-circle-fill' : ($booking->status === 'pending' ? 'clock-fill' : 'dash-circle-fill')) }} me-1"></i>
                        {{ $statusLabels[$booking->status] ?? ucfirst($booking->status) }}
                    </span>
                    <h5 class="fw-bold mb-1">{{ $booking->title }}</h5>
                    <code style="font-size:.78rem; opacity:.6;">{{ $booking->booking_code }}</code>
                </div>
            </div>
        </div>
    </div>

    {{-- Data Pelanggan --}}
    <div class="rc-card mb-3">
        <div class="rc-card-header" style="background:#0d6efd;">
            <i class="bi bi-person-fill me-2"></i>Data Pelanggan
        </div>
        <div class="rc-card-body">
            <table class="table table-borderless small mb-0">
                <tr>
                    <th style="width:40%; color:#6c757d; font-weight:600;">Nama</th>
                    <td class="fw-semibold">{{ $booking->customer_name ?? '—' }}</td>
                </tr>
                <tr>
                    <th style="color:#6c757d; font-weight:600;">Telepon</th>
                    <td>
                        @if ($booking->customer_phone)
                            <a href="tel:{{ $booking->customer_phone }}" style="color:#0d6efd; text-decoration:none;">
                                <i class="bi bi-telephone-fill me-1"></i>{{ $booking->customer_phone }}
                            </a>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th style="color:#6c757d; font-weight:600;">Dicatat oleh</th>
                    <td style="color:#6c757d;">Resepsionis &bull; {{ auth()->user()->name }}</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Detail Booking --}}
    <div class="rc-card mb-3">
        <div class="rc-card-header">
            <i class="bi bi-info-circle-fill me-2"></i>Detail Pemesanan
        </div>
        <div class="rc-card-body">
            <table class="table table-borderless small mb-0">
                <tr>
                    <th style="width:40%; color:#6c757d; font-weight:600;">Ruangan</th>
                    <td class="fw-semibold">{{ $booking->room->name }}</td>
                </tr>
                @if ($booking->room->room_code)
                    <tr>
                        <th style="color:#6c757d; font-weight:600;">Kode Ruangan</th>
                        <td><code>{{ $booking->room->room_code }}</code></td>
                    </tr>
                @endif
                <tr>
                    <th style="color:#6c757d; font-weight:600;">Tanggal</th>
                    <td>{{ \Carbon\Carbon::parse($booking->start_datetime)->locale('id')->isoFormat('dddd, D MMMM Y') }}
                    </td>
                </tr>
                <tr>
                    <th style="color:#6c757d; font-weight:600;">Waktu</th>
                    <td>
                        {{ \App\Helpers\DateHelper::formatTimeID($booking->start_datetime) }} –
                        {{ \App\Helpers\DateHelper::formatTimeID($booking->end_datetime) }} WIB
                    </td>
                </tr>
                <tr>
                    <th style="color:#6c757d; font-weight:600;">Durasi</th>
                    <td>
                        @php
                            $diff = $booking->start_datetime->diffInMinutes($booking->end_datetime);
                            $h = intdiv($diff, 60);
                            $m = $diff % 60;
                        @endphp
                        {{ $h > 0 ? $h . ' jam' : '' }}{{ $m > 0 ? ' ' . $m . ' menit' : '' }}
                    </td>
                </tr>
                @if ($booking->room->hourly_rate && $diff > 0)
                    <tr>
                        <th style="color:#6c757d; font-weight:600;">Est. Biaya</th>
                        <td class="fw-semibold" style="color:#0f5132;">
                            Rp {{ number_format(($diff / 60) * $booking->room->hourly_rate, 0, ',', '.') }}
                        </td>
                    </tr>
                @endif
                <tr>
                    <th style="color:#6c757d; font-weight:600;">Catatan</th>
                    <td>{{ $booking->notes ?? '—' }}</td>
                </tr>
                <tr>
                    <th style="color:#6c757d; font-weight:600;">Diajukan</th>
                    <td>{{ $booking->created_at->locale('id')->isoFormat('D MMM Y, HH:mm') }}</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Respons Admin --}}
    @if ($booking->approvedBy || $booking->admin_notes)
        <div class="rc-card mb-3"
            style="border-left: 4px solid {{ $booking->status === 'approved' ? '#198754' : '#dc3545' }}">
            <div class="rc-card-header"
                style="background: {{ $booking->status === 'approved' ? '#d1e7dd; color:#0f5132' : '#f8d7da; color:#842029' }}">
                <i class="bi bi-{{ $booking->status === 'approved' ? 'check-circle-fill' : 'x-circle-fill' }} me-2"></i>
                Keputusan Admin
            </div>
            <div class="rc-card-body">
                @if ($booking->approvedBy)
                    <div style="font-size:.82rem; color:#6c757d; margin-bottom:8px;">
                        <i class="bi bi-person-badge-fill me-1"></i>
                        {{ $booking->approvedBy->name }} &bull;
                        {{ $booking->approved_at->locale('id')->isoFormat('D MMM Y, HH:mm') }}
                    </div>
                @endif
                @if ($booking->admin_notes)
                    <div style="background:#f8f9fa; border-radius:8px; padding:10px; font-size:.85rem;">
                        "{{ $booking->admin_notes }}"
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Info box for pending --}}
    @if ($booking->status === 'pending')
        <div style="background:#fff3cd; border-radius:12px; padding:14px 16px; border-left:4px solid #ffc107;">
            <div style="font-size:.85rem; color:#856404;">
                <i class="bi bi-clock-fill me-2"></i>
                <strong>Menunggu persetujuan admin.</strong><br>
                <span style="font-size:.78rem; margin-top:4px; display:block;">
                    Booking ini akan diproses oleh admin. Pantau status di halaman Booking Saya.
                </span>
            </div>
        </div>
    @endif

@endsection
