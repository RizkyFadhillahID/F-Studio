@extends('receptionist.layout')

@section('title', 'Ajukan Peminjaman Peralatan')

@section('content')
    <div class="mb-3">
        <a href="{{ route('receptionist.loans') }}" class="text-decoration-none text-muted small">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar Peminjaman
        </a>
    </div>

    <div class="rc-card">
        <div class="rc-card-header">
            <i class="bi bi-box-seam-fill me-2"></i>Form Peminjaman Peralatan
        </div>
        <div class="rc-card-body">
            <form method="POST" action="{{ route('receptionist.loans.store') }}" id="loanForm">
                @csrf

                {{-- ── Informasi Tamu ── --}}
                <h6 class="fw-bold mb-3 mt-1" style="color:var(--dark)">
                    <i class="bi bi-person-circle" style="color:var(--accent)"></i> Informasi Tamu
                </h6>

                <div class="mb-3">
                    <label class="form-label">Nama Tamu <span class="text-danger">*</span></label>
                    <input type="text" name="customer_name"
                        class="form-control @error('customer_name') is-invalid @enderror" value="{{ old('customer_name') }}"
                        placeholder="Nama lengkap tamu" required>
                    @error('customer_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">No. Telepon <span class="text-muted fw-normal">(opsional)</span></label>
                    <input type="tel" name="customer_phone"
                        class="form-control @error('customer_phone') is-invalid @enderror"
                        value="{{ old('customer_phone') }}" placeholder="08xx-xxxx-xxxx">
                    @error('customer_phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- ── Detail Peminjaman ── --}}
                <hr>
                <h6 class="fw-bold mb-3" style="color:var(--dark)">
                    <i class="bi bi-calendar2-week" style="color:var(--accent)"></i> Detail Peminjaman
                </h6>

                <div class="mb-3">
                    <label class="form-label">Keperluan <span class="text-danger">*</span></label>
                    <textarea name="purpose" rows="2" class="form-control @error('purpose') is-invalid @enderror"
                        placeholder="Jelaskan keperluan peminjaman" required>{{ old('purpose') }}</textarea>
                    @error('purpose')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label">Tanggal Pinjam <span class="text-danger">*</span></label>
                        <input type="date" name="loan_date" class="form-control @error('loan_date') is-invalid @enderror"
                            value="{{ old('loan_date', now()->toDateString()) }}" min="{{ now()->toDateString() }}" required
                            id="loanDate">
                        @error('loan_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-6">
                        <label class="form-label">Tanggal Kembali <span class="text-danger">*</span></label>
                        <input type="date" name="due_date" class="form-control @error('due_date') is-invalid @enderror"
                            value="{{ old('due_date', now()->toDateString()) }}" min="{{ now()->toDateString() }}"
                            required id="dueDate">
                        @error('due_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" rows="2" class="form-control" placeholder="Catatan tambahan (opsional)">{{ old('notes') }}</textarea>
                </div>

                {{-- ── Daftar Peralatan ── --}}
                <hr>
                <h6 class="fw-bold mb-2" style="color:var(--dark)">
                    <i class="bi bi-list-check" style="color:var(--accent)"></i> Peralatan yang Dipinjam
                    <span class="text-danger">*</span>
                </h6>
                @error('items')
                    <div class="rc-alert-danger py-2 mb-2">{{ $message }}</div>
                @enderror
                @error('error')
                    <div class="rc-alert-danger py-2 mb-2">{{ $message }}</div>
                @enderror

                <div id="itemsContainer">
                    {{-- Item baris pertama --}}
                    <div class="item-row d-flex gap-2 mb-2 align-items-start" data-index="0">
                        <div class="flex-grow-1">
                            <select name="items[0][equipment_id]" class="form-select equipment-select" required>
                                <option value="">— Pilih Peralatan —</option>
                                @foreach ($equipment as $eq)
                                    <option value="{{ $eq->id }}" data-max="{{ $eq->quantity_available }}"
                                        {{ old('items.0.equipment_id') == $eq->id ? 'selected' : '' }}>
                                        {{ $eq->name }}
                                        @if ($eq->category)
                                            [{{ $eq->category->name }}]
                                        @endif
                                        — Tersedia: {{ $eq->quantity_available }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div style="width:90px">
                            <input type="number" name="items[0][quantity]" class="form-control text-center qty-input"
                                value="{{ old('items.0.quantity', 1) }}" min="1" max="20" required
                                placeholder="Qty">
                        </div>
                        <button type="button" class="btn btn-outline-danger btn-sm remove-row" style="height:38px"
                            onclick="removeRow(this)" disabled>
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>

                <button type="button" class="btn btn-outline-secondary btn-sm mb-3" onclick="addRow()">
                    <i class="bi bi-plus-lg"></i> Tambah Peralatan
                </button>

                <div class="d-grid mt-3">
                    <button type="submit" class="btn btn-accent py-2 fw-semibold">
                        <i class="bi bi-send-fill me-2"></i>Ajukan Peminjaman
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let rowIndex = 0;
        const equipmentOptions = {!! json_encode($equipmentList) !!};

        function buildOptions(selectedId = '') {
            return '<option value="">— Pilih Peralatan —</option>' +
                equipmentOptions.map(e =>
                    `<option value="${e.id}" data-max="${e.qty_available}" ${e.id == selectedId ? 'selected' : ''}>` +
                    `${e.name}${e.category ? ' [' + e.category + ']' : ''} — Tersedia: ${e.qty_available}</option>`
                ).join('');
        }

        function addRow() {
            rowIndex++;
            const row = document.createElement('div');
            row.className = 'item-row d-flex gap-2 mb-2 align-items-start';
            row.dataset.index = rowIndex;
            row.innerHTML = `
            <div class="flex-grow-1">
                <select name="items[${rowIndex}][equipment_id]" class="form-select equipment-select" required>
                    ${buildOptions()}
                </select>
            </div>
            <div style="width:90px">
                <input type="number" name="items[${rowIndex}][quantity]" class="form-control text-center qty-input"
                    value="1" min="1" max="20" required placeholder="Qty">
            </div>
            <button type="button" class="btn btn-outline-danger btn-sm remove-row" style="height:38px"
                onclick="removeRow(this)">
                <i class="bi bi-trash"></i>
            </button>`;
            document.getElementById('itemsContainer').appendChild(row);
            updateRemoveButtons();
        }

        function removeRow(btn) {
            btn.closest('.item-row').remove();
            updateRemoveButtons();
        }

        function updateRemoveButtons() {
            const rows = document.querySelectorAll('.item-row');
            rows.forEach((row, i) => {
                const btn = row.querySelector('.remove-row');
                btn.disabled = rows.length === 1;
            });
        }

        // Validasi qty tidak melebihi stok tersedia
        document.getElementById('itemsContainer').addEventListener('change', function(e) {
            if (e.target.classList.contains('equipment-select')) {
                const max = parseInt(e.target.selectedOptions[0]?.dataset.max || 20);
                const row = e.target.closest('.item-row');
                const qtyInput = row.querySelector('.qty-input');
                qtyInput.max = max;
                if (parseInt(qtyInput.value) > max) qtyInput.value = max;
            }
        });

        // loan_date tidak boleh setelah due_date
        document.getElementById('loanDate').addEventListener('change', function() {
            const dd = document.getElementById('dueDate');
            if (dd.value && dd.value < this.value) dd.value = this.value;
            dd.min = this.value;
        });
    </script>
@endpush
