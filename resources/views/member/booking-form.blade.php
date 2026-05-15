@extends('member.layout')
@section('title', 'Pesan Ruangan')

@section('content')
    {{-- Back link --}}
    <a href="{{ route('member.rooms') }}" class="d-flex align-items-center gap-1 text-muted small mb-3 text-decoration-none">
        <i class="bi bi-arrow-left"></i> Kembali ke daftar ruangan
    </a>

    {{-- Room Summary Card --}}
    <div class="fcard mb-4" style="border-left: 4px solid var(--accent)">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h6 class="fw-bold mb-0">{{ $room->name }}</h6>
                <code class="text-muted small">{{ $room->room_code }}</code>
            </div>
            <span class="badge bg-success">Tersedia</span>
        </div>
        <hr class="my-2">
        <div class="row g-2 small text-muted">
            <div class="col-6">
                <i class="bi bi-people me-1"></i>Kapasitas: <strong class="text-dark">{{ $room->capacity }} orang</strong>
            </div>
            <div class="col-6">
                <i class="bi bi-tag me-1"></i>Tarif:
                @if ($room->hourly_rate > 0)
                    <strong class="text-dark">Rp {{ number_format($room->hourly_rate, 0, ',', '.') }}/jam</strong>
                @else
                    <strong class="text-success">Gratis</strong>
                @endif
            </div>
        </div>
    </div>

    {{-- Booking Form --}}
    <div class="fcard">
        <h6 class="fw-bold mb-3"><i class="bi bi-calendar-plus me-2 text-danger"></i>Form Pemesanan</h6>

        <form method="POST" action="{{ route('member.bookings.store') }}">
            @csrf
            <input type="hidden" name="room_id" value="{{ $room->id }}">

            {{-- Title --}}
            <div class="mb-3">
                <label class="form-label">Judul / Keperluan <span class="text-danger">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}"
                    class="form-control @error('title') is-invalid @enderror"
                    placeholder="Contoh: Sesi Foto Produk Kosmetik">
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Date & Time Row --}}
            <div class="row g-3 mb-3">
                <div class="col-12">
                    <label class="form-label">Waktu Mulai <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="start_datetime" value="{{ old('start_datetime') }}"
                        class="form-control @error('start_datetime') is-invalid @enderror"
                        min="{{ now()->addHours(1)->format('Y-m-d\TH:i') }}">
                    @error('start_datetime')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Waktu Selesai <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="end_datetime" value="{{ old('end_datetime') }}"
                        class="form-control @error('end_datetime') is-invalid @enderror">
                    @error('end_datetime')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Notes --}}
            <div class="mb-4">
                <label class="form-label">Catatan Tambahan</label>
                <textarea name="notes" rows="3" class="form-control"
                    placeholder="Kebutuhan khusus, perlengkapan yang diperlukan, dll.">{{ old('notes') }}</textarea>
            </div>

            {{-- Info Box --}}
            <div class="alert alert-info d-flex gap-2 align-items-start mb-4" style="font-size:0.82rem">
                <i class="bi bi-info-circle-fill mt-1 flex-shrink-0"></i>
                <div>Pemesanan akan diproses dan menunggu persetujuan admin. Anda akan mendapat notifikasi setelah status
                    diperbarui.</div>
            </div>

            <button type="submit" class="btn btn-accent w-100 py-3" style="font-size:1rem">
                <i class="bi bi-send-fill me-2"></i>Kirim Permintaan Booking
            </button>
        </form>
    </div>
@endsection

<script>
    // Auto-set end_datetime to +2 hours when start_datetime changes
    document.querySelector('[name="start_datetime"]')?.addEventListener('change', function() {
        const end = document.querySelector('[name="end_datetime"]');
        if (!end.value) {
            const d = new Date(this.value);
            d.setHours(d.getHours() + 2);
            end.value = d.toISOString().slice(0, 16);
        }
    });
</script>
