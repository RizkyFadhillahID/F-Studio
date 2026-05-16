@extends('receptionist.layout')
@section('title', 'Form Booking — ' . $room->name)

@section('content')
    <a href="{{ route('receptionist.rooms') }}" class="d-inline-flex align-items-center gap-2 mb-3"
        style="color:#6c757d; text-decoration:none; font-size:.85rem; font-weight:600;">
        <i class="bi bi-arrow-left"></i> Kembali ke daftar ruangan
    </a>

    {{-- Room Summary --}}
    <div class="rc-card mb-4">
        <div class="rc-card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-building me-2"></i>{{ $room->name }}</span>
            @if ($room->room_code)
                <code style="font-size:.72rem; opacity:.7;">{{ $room->room_code }}</code>
            @endif
        </div>
        <div class="rc-card-body">
            <div class="row g-2" style="font-size:.82rem; color:#6c757d;">
                @if ($room->capacity)
                    <div class="col-6">
                        <i class="bi bi-people-fill me-1"></i> Kapasitas: <strong>{{ $room->capacity }} orang</strong>
                    </div>
                @endif
                @if ($room->hourly_rate)
                    <div class="col-6">
                        <i class="bi bi-tag-fill me-1"></i> Tarif: <strong>Rp
                            {{ number_format($room->hourly_rate, 0, ',', '.') }}/jam</strong>
                    </div>
                @endif
                @if ($room->facilities)
                    <div class="col-12">
                        <i class="bi bi-check2-circle me-1"
                            style="color:#198754;"></i>{{ is_array($room->facilities) ? implode(', ', $room->facilities) : $room->facilities }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Booking Form --}}
    <div class="rc-card">
        <div class="rc-card-header" style="background:#e94560;">
            <i class="bi bi-calendar-plus-fill me-2"></i>Form Booking untuk Pelanggan
        </div>
        <div class="rc-card-body">
            @if ($errors->any())
                <div class="rc-alert-danger mb-3">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $err)
                            <li style="font-size:.85rem;">{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('receptionist.bookings.store') }}" method="POST">
                @csrf
                <input type="hidden" name="room_id" value="{{ $room->id }}">

                {{-- ── Informasi Pelanggan ── --}}
                <div style="background:#f8f9fa; border-radius:10px; padding:14px; margin-bottom:16px;">
                    <div
                        style="font-size:.8rem; font-weight:700; color:#6c757d; text-transform:uppercase; letter-spacing:.5px; margin-bottom:12px;">
                        <i class="bi bi-person-fill me-1" style="color:#e94560;"></i>Informasi Pelanggan
                    </div>

                    <div class="mb-3">
                        <label for="customer_name" class="form-label">
                            Nama Pelanggan <span style="color:#e94560;">*</span>
                        </label>
                        <input type="text" id="customer_name" name="customer_name"
                            class="form-control @error('customer_name') is-invalid @enderror"
                            placeholder="Contoh: Budi Santoso" value="{{ old('customer_name') }}" required autofocus>
                        @error('customer_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-0">
                        <label for="customer_phone" class="form-label">
                            Nomor Telepon <span style="font-size:.75rem; color:#6c757d;">(opsional)</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-phone"></i></span>
                            <input type="text" id="customer_phone" name="customer_phone"
                                class="form-control @error('customer_phone') is-invalid @enderror"
                                placeholder="0812-3456-7890" value="{{ old('customer_phone') }}">
                            @error('customer_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- ── Detail Booking ── --}}
                <div style="background:#f8f9fa; border-radius:10px; padding:14px; margin-bottom:16px;">
                    <div
                        style="font-size:.8rem; font-weight:700; color:#6c757d; text-transform:uppercase; letter-spacing:.5px; margin-bottom:12px;">
                        <i class="bi bi-info-circle-fill me-1" style="color:#e94560;"></i>Detail Penggunaan
                    </div>

                    <div class="mb-3">
                        <label for="title" class="form-label">
                            Judul / Keperluan <span style="color:#e94560;">*</span>
                        </label>
                        <input type="text" id="title" name="title" class="form-control @error('title') is-invalid @enderror"
                            placeholder="Contoh: Sesi Foto Produk, Rapat Tim, Seminar..." value="{{ old('title') }}"
                            required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="start_datetime" class="form-label">
                                Waktu Mulai <span style="color:#e94560;">*</span>
                            </label>
                            <input type="datetime-local" id="start_datetime" name="start_datetime"
                                class="form-control @error('start_datetime') is-invalid @enderror"
                                value="{{ old('start_datetime') }}" min="{{ now()->addMinutes(30)->format('Y-m-d\TH:i') }}"
                                required>
                            @error('start_datetime')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-6">
                            <label for="end_datetime" class="form-label">
                                Waktu Selesai <span style="color:#e94560;">*</span>
                            </label>
                            <input type="datetime-local" id="end_datetime" name="end_datetime"
                                class="form-control @error('end_datetime') is-invalid @enderror"
                                value="{{ old('end_datetime') }}" required>
                            @error('end_datetime')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div id="duration-info" class="mb-3"
                        style="display:none; background:#e8f5e9; border-radius:8px; padding:8px 12px; font-size:.82rem; color:#1b5e20;">
                        <i class="bi bi-clock-fill me-1"></i>
                        <span id="duration-text"></span>
                        @if ($room->hourly_rate)
                            &bull; Estimasi biaya: <strong id="cost-text"></strong>
                        @endif
                    </div>

                    <div class="mb-0">
                        <label for="notes" class="form-label">
                            Catatan Tambahan <span style="font-size:.75rem; color:#6c757d;">(opsional)</span>
                        </label>
                        <textarea id="notes" name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror"
                            placeholder="Kebutuhan peralatan, jumlah peserta, permintaan khusus...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- ── Peralatan Tambahan (Opsional) ── --}}
                @if (isset($equipment) && $equipment->isNotEmpty())
                    <div style="border-radius:10px; border:1px dashed #ced4da; margin-bottom:16px; overflow:hidden;">
                        <button type="button"
                            class="w-100 text-start d-flex justify-content-between align-items-center px-3 py-2"
                            style="background:#f8f9fa; border:none; font-weight:700; color:var(--dark); font-size:.9rem;"
                            onclick="toggleEquipment(this)">
                            <span><i class="bi bi-box-seam me-2" style="color:var(--accent)"></i>Peralatan Tambahan <span
                                    style="font-weight:400; font-size:.78rem; color:#6c757d">(opsional)</span></span>
                            <i class="bi bi-chevron-down" id="equipToggleIcon"></i>
                        </button>
                        <div id="equipmentSection" style="display:none; padding:14px;">
                            <p class="text-muted mb-3" style="font-size:.82rem">
                                Peralatan akan diajukan bersamaan dengan booking ini dan perlu persetujuan admin.
                            </p>
                            <div id="bookingItemsContainer">
                                <div class="bk-item-row d-flex gap-2 mb-2 align-items-start" data-index="0">
                                    <div class="flex-grow-1">
                                        <select name="equipment_items[0][equipment_id]" class="form-select bk-equip-select">
                                            <option value="">— Pilih Peralatan —</option>
                                            @foreach ($equipment as $eq)
                                                <option value="{{ $eq->id }}" data-max="{{ $eq->quantity_available }}">
                                                    {{ $eq->name }} — Tersedia: {{ $eq->quantity_available }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div style="width:90px">
                                        <input type="number" name="equipment_items[0][quantity]"
                                            class="form-control text-center bk-qty-input" value="1" min="1" max="20"
                                            placeholder="Qty">
                                    </div>
                                    <button type="button" class="btn btn-outline-danger btn-sm" style="height:38px"
                                        onclick="removeBkRow(this)" disabled>
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addBkRow()">
                                <i class="bi bi-plus-lg"></i> Tambah Peralatan
                            </button>
                        </div>
                    </div>
                @endif

                <button type="submit" class="btn btn-accent w-100 py-3"
                    style="border-radius:12px; font-size:1rem; font-weight:700;">
                    <i class="bi bi-send-fill me-2"></i>Ajukan Booking ke Admin
                </button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const hourlyRate = {{ $room->hourly_rate ?? 0 }};
        const startEl = document.getElementById('start_datetime');
        const endEl = document.getElementById('end_datetime');
        const info = document.getElementById('duration-info');
        const durText = document.getElementById('duration-text');
        const costText = document.getElementById('cost-text');

        function updateDuration() {
            if (!startEl.value || !endEl.value) {
                info.style.display = 'none';
                return;
            }
            const start = new Date(startEl.value);
            const end = new Date(endEl.value);
            const diffMs = end - start;
            if (diffMs <= 0) {
                info.style.display = 'none';
                return;
            }
            const hours = diffMs / 3600000;
            const h = Math.floor(hours);
            const m = Math.round((hours - h) * 60);
            durText.textContent = `Durasi: ${h > 0 ? h + ' jam' : ''}${m > 0 ? ' ' + m + ' menit' : ''}`.trim();
            if (costText && hourlyRate > 0) {
                const cost = hours * hourlyRate;
                costText.textContent = 'Rp ' + cost.toLocaleString('id-ID');
            }
            info.style.display = 'block';
        }

        startEl.addEventListener('change', function () {
            // Auto-set end = start + 2 jam
            if (this.value && !endEl.value) {
                const s = new Date(this.value);
                s.setHours(s.getHours() + 2);
                endEl.value = s.toISOString().slice(0, 16);
            }
            updateDuration();
        });
        endEl.addEventListener('change', updateDuration);
        updateDuration();

        // ── Peralatan Tambahan (Equipment section) ──
        let bkRowIndex = 0;
        const bkEquipOptions = {!! isset($equipment)
        ? $equipment->map(fn($e) => ['id' => $e->id, 'name' => $e->name, 'qty' => $e->quantity_available])->toJson()
        : '[]' !!};

        function toggleEquipment(btn) {
            const section = document.getElementById('equipmentSection');
            const icon = document.getElementById('equipToggleIcon');
            const hidden = section.style.display === 'none';
            section.style.display = hidden ? 'block' : 'none';
            icon.className = hidden ? 'bi bi-chevron-up' : 'bi bi-chevron-down';
        }

        function buildBkOptions(selectedId = '') {
            return '<option value="">— Pilih Peralatan —</option>' +
                bkEquipOptions.map(e =>
                    `<option value="${e.id}" data-max="${e.qty}" ${e.id == selectedId ? 'selected' : ''}>` +
                    `${e.name} — Tersedia: ${e.qty}</option>`
                ).join('');
        }

        function addBkRow() {
            bkRowIndex++;
            const cont = document.getElementById('bookingItemsContainer');
            const row = document.createElement('div');
            row.className = 'bk-item-row d-flex gap-2 mb-2 align-items-start';
            row.dataset.index = bkRowIndex;
            row.innerHTML = `
                    <div class="flex-grow-1">
                        <select name="equipment_items[${bkRowIndex}][equipment_id]" class="form-select bk-equip-select">
                            ${buildBkOptions()}
                        </select>
                    </div>
                    <div style="width:90px">
                        <input type="number" name="equipment_items[${bkRowIndex}][quantity]"
                            class="form-control text-center bk-qty-input" value="1" min="1" max="20" placeholder="Qty">
                    </div>
                    <button type="button" class="btn btn-outline-danger btn-sm"
                        style="height:38px" onclick="removeBkRow(this)">
                        <i class="bi bi-trash"></i>
                    </button>`;
            cont.appendChild(row);
            updateBkRemoveButtons();
        }

        function removeBkRow(btn) {
            btn.closest('.bk-item-row').remove();
            updateBkRemoveButtons();
        }

        function updateBkRemoveButtons() {
            const rows = document.querySelectorAll('.bk-item-row');
            rows.forEach(row => {
                const btn = row.querySelector('button');
                btn.disabled = rows.length === 1;
            });
        }
    </script>
@endpush