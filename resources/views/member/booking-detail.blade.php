@extends('member.layout')
@section('title', 'Detail Pemesanan')

@section('content')
    <a href="{{ route('member.bookings') }}"
        class="d-flex align-items-center gap-1 text-muted small mb-3 text-decoration-none">
        <i class="bi bi-arrow-left"></i> Kembali ke pemesanan saya
    </a>

    {{-- Status Header --}}
    <div class="fcard mb-3 text-center py-4"
        style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); color:#fff">
        <div class="mb-2">
            <span class="badge badge-{{ $booking->status }} fs-6 px-3 py-2">
                @if ($booking->status === 'pending')
                    <i class="bi bi-hourglass-split me-1"></i>Menunggu Persetujuan
                @elseif($booking->status === 'approved')
                    <i class="bi bi-check-circle-fill me-1"></i>Disetujui
                @elseif($booking->status === 'rejected')
                    <i class="bi bi-x-circle-fill me-1"></i>Ditolak
                @else
                    <i class="bi bi-slash-circle me-1"></i>Dibatalkan
                @endif
            </span>
        </div>
        <h5 class="fw-bold mb-1">{{ $booking->title }}</h5>
        <code style="color:#aaa">{{ $booking->booking_code }}</code>
    </div>

    {{-- Details --}}
    <div class="fcard mb-3">
        <h6 class="fw-semibold mb-3"><i class="bi bi-info-circle me-2 text-danger"></i>Detail Pemesanan</h6>
        <table class="table table-borderless small mb-0">
            <tr>
                <th class="text-muted ps-0" style="width:40%">Ruangan</th>
                <td class="fw-semibold">{{ $booking->room->name }}</td>
            </tr>
            <tr>
                <th class="text-muted ps-0">Kode Ruangan</th>
                <td><code>{{ $booking->room->room_code }}</code></td>
            </tr>
            <tr>
                <th class="text-muted ps-0">Tanggal</th>
                <td>{{ \App\Helpers\DateHelper::formatDateID($booking->start_datetime) }}</td>
            </tr>
            <tr>
                <th class="text-muted ps-0">Waktu</th>
                <td>{{ \App\Helpers\DateHelper::formatTimeID($booking->start_datetime) }} –
                    {{ \App\Helpers\DateHelper::formatTimeID($booking->end_datetime) }} WIB</td>
            </tr>
            <tr>
                <th class="text-muted ps-0">Durasi</th>
                <td>{{ \Carbon\Carbon::parse($booking->start_datetime)->diffInHours($booking->end_datetime) }} jam</td>
            </tr>
            @if ($booking->notes)
                <tr>
                    <th class="text-muted ps-0">Catatan</th>
                    <td>{{ $booking->notes }}</td>
                </tr>
            @endif
            <tr>
                <th class="text-muted ps-0">Diajukan</th>
                <td>{{ \App\Helpers\DateHelper::formatDateTimeID($booking->created_at) }}</td>
            </tr>
        </table>
    </div>

    {{-- Admin Response --}}
    @if ($booking->admin_notes || $booking->approvedBy)
        <div class="fcard mb-3">
            <h6 class="fw-semibold mb-3">
                <i class="bi bi-person-check me-2 text-danger"></i>Respons Admin
            </h6>
            @if ($booking->admin_notes)
                <div class="alert
                @if ($booking->status === 'approved') alert-success
                @elseif($booking->status === 'rejected') alert-danger
                @else alert-secondary @endif
                d-flex gap-2 align-items-start mb-2"
                    style="font-size:0.85rem">
                    <i class="bi bi-chat-left-text-fill mt-1 flex-shrink-0"></i>
                    <span>{{ $booking->admin_notes }}</span>
                </div>
            @endif
            @if ($booking->approvedBy)
                <div class="text-muted small">
                    <i class="bi bi-person me-1"></i>
                    {{ $booking->approvedBy->name }}
                    pada {{ \App\Helpers\DateHelper::formatDateTimeID($booking->approved_at) }}
                </div>
            @endif
        </div>
    @endif

    {{-- Pending info box --}}
    @if ($booking->status === 'pending')
        <div class="alert alert-warning d-flex gap-2 align-items-start" style="font-size:0.82rem">
            <i class="bi bi-hourglass-split mt-1 flex-shrink-0"></i>
            <div>Pemesanan Anda sedang menunggu persetujuan admin. Biasanya diproses dalam 1×24 jam.</div>
        </div>
    @endif
@endsection
