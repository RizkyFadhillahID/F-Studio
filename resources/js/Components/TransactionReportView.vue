<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { formatRupiah } from '../lib/payment';
import { bookingStatusLabel, loanStatusLabel } from '../lib/status';

const props = defineProps({
    rows: { type: Array, default: () => [] },
    summary: { type: Object, default: () => ({}) },
    filters: { type: Object, default: () => ({}) },
    baseUrl: { type: String, required: true },
    exportUrl: { type: String, required: true },
});

const today = new Date().toISOString().slice(0, 10);
const dateFrom = ref(props.filters.date_from ?? new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().slice(0, 10));
const dateTo = ref(props.filters.date_to ?? today);
const jenis = ref(props.filters.jenis ?? '');
const paymentStatus = ref(props.filters.payment_status ?? '');

function applyFilter() {
    router.get(props.baseUrl, {
        date_from: dateFrom.value || undefined,
        date_to: dateTo.value || undefined,
        jenis: jenis.value || undefined,
        payment_status: paymentStatus.value || undefined,
    }, { preserveState: true, replace: true });
}

const exportHref = computed(() => {
    const params = new URLSearchParams({
        date_from: dateFrom.value || '',
        date_to: dateTo.value || '',
        jenis: jenis.value || '',
        payment_status: paymentStatus.value || '',
    });
    return `${props.exportUrl}?${params.toString()}`;
});

function fmt(dt) {
    return dt ? new Date(dt).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }) : '—';
}

function statusLabel(row) {
    const map = row.type === 'booking' ? bookingStatusLabel : loanStatusLabel;
    return map[row.status] ?? row.status;
}

const typeLabel = { booking: 'Pemesanan Ruangan', loan: 'Peminjaman Alat' };
</script>

<template>
    <div>
        <!-- Filter -->
        <div class="aurora-card mb-4 flex flex-wrap items-end gap-3 p-4">
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-400">Dari Tanggal</label>
                <input v-model="dateFrom" type="date" class="aurora-input" />
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-400">Sampai Tanggal</label>
                <input v-model="dateTo" type="date" class="aurora-input" />
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-400">Jenis</label>
                <select v-model="jenis" class="aurora-input">
                    <option value="">Semua Jenis</option>
                    <option value="booking">Pemesanan Ruangan</option>
                    <option value="loan">Peminjaman Alat</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-400">Pembayaran</label>
                <select v-model="paymentStatus" class="aurora-input">
                    <option value="">Semua Status</option>
                    <option value="paid">Lunas</option>
                    <option value="unpaid">Belum Bayar</option>
                </select>
            </div>
            <button class="aurora-btn-primary rounded-lg px-4 py-2 text-sm font-semibold" @click="applyFilter">Terapkan</button>
            <a :href="exportHref" class="aurora-btn-ghost ml-auto rounded-lg px-4 py-2 text-sm font-semibold">⬇ Unduh CSV</a>
        </div>

        <!-- Ringkasan -->
        <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="aurora-card p-4">
                <p class="text-2xl font-bold text-white">{{ summary.total_transaksi ?? 0 }}</p>
                <p class="mt-0.5 text-xs text-slate-500">Total Transaksi</p>
            </div>
            <div class="aurora-card p-4">
                <p class="text-2xl font-bold text-emerald-400">{{ formatRupiah(summary.total_pendapatan) }}</p>
                <p class="mt-0.5 text-xs text-slate-500">Total Pendapatan (Lunas)</p>
            </div>
            <div class="aurora-card p-4">
                <p class="text-2xl font-bold text-white">{{ summary.total_booking ?? 0 }} / {{ summary.total_loan ?? 0 }}</p>
                <p class="mt-0.5 text-xs text-slate-500">Pemesanan / Peminjaman</p>
            </div>
            <div class="aurora-card p-4">
                <p class="text-2xl font-bold text-white">{{ summary.lunas ?? 0 }} / {{ summary.belum_bayar ?? 0 }}</p>
                <p class="mt-0.5 text-xs text-slate-500">Lunas / Belum Bayar</p>
            </div>
        </div>

        <!-- Tabel -->
        <div class="aurora-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-white/[0.06] text-sm">
                    <thead class="bg-white/[0.03]">
                        <tr>
                            <th class="aurora-th">Kode</th>
                            <th class="aurora-th">Jenis</th>
                            <th class="aurora-th">Pelanggan</th>
                            <th class="aurora-th">Item</th>
                            <th class="aurora-th">Tanggal Transaksi</th>
                            <th class="aurora-th text-center">Status</th>
                            <th class="aurora-th text-center">Pembayaran</th>
                            <th class="aurora-th text-right">Nominal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.06]">
                        <tr v-for="row in rows" :key="`${row.type}-${row.id}`" class="aurora-row">
                            <td class="aurora-td font-mono text-xs text-slate-500">{{ row.code }}</td>
                            <td class="aurora-td text-slate-400">{{ typeLabel[row.type] }}</td>
                            <td class="aurora-td text-slate-300">{{ row.customer }}</td>
                            <td class="aurora-td text-slate-400">{{ row.item }}</td>
                            <td class="aurora-td text-slate-400">{{ fmt(row.created_at) }}</td>
                            <td class="aurora-td text-center">
                                <span class="aurora-pill aurora-pill-muted">{{ statusLabel(row) }}</span>
                            </td>
                            <td class="aurora-td text-center">
                                <span v-if="row.payment_status === 'paid'" class="aurora-pill aurora-pill-ok">Lunas</span>
                                <span v-else class="aurora-pill aurora-pill-warn">Belum bayar</span>
                            </td>
                            <td class="aurora-td text-right text-slate-300">{{ row.payment_status === 'paid' ? formatRupiah(row.amount) : '—' }}</td>
                        </tr>
                        <tr v-if="!rows.length">
                            <td colspan="8" class="aurora-td py-8 text-center text-slate-500">Tidak ada transaksi pada rentang tanggal ini.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
