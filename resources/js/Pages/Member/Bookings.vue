<script setup>
import { ref, computed } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import MemberLayout from '../../Layouts/MemberLayout.vue';
import Pagination from '../../Components/Pagination.vue';
import { bookingAmount, formatRupiah, paymentMethods } from '../../lib/payment';
import { bookingStatusLabel } from '../../lib/status';

const props = defineProps({
    bookings: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const statusBadge = {
    pending: 'aurora-pill-warn',
    approved: 'aurora-pill-ok',
    completed: 'aurora-pill-info',
    rejected: 'aurora-pill-danger',
    cancelled: 'aurora-pill-muted',
};

const status = ref(props.filters.status ?? '');

function applyFilter() {
    router.get('/member/bookings', { status: status.value || undefined }, { preserveState: true, replace: true });
}

const sortColumn = ref('created_at');
const sortDirection = ref('desc');

function toggleSort(col) {
    if (sortColumn.value === col) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortColumn.value = col;
        sortDirection.value = 'asc';
    }
}

const sortedBookings = computed(() => {
    const list = [...(props.bookings.data || [])];
    if (sortColumn.value) {
        list.sort((a, b) => {
            let valA = a[sortColumn.value];
            let valB = b[sortColumn.value];
            
            // Nested relationship sorting
            if (sortColumn.value === 'room') {
                valA = a.room?.name ?? '';
                valB = b.room?.name ?? '';
            }
            
            if (valA === null || valA === undefined) valA = '';
            if (valB === null || valB === undefined) valB = '';
            
            if (typeof valA === 'string') {
                return sortDirection.value === 'asc'
                    ? valA.localeCompare(valB)
                    : valB.localeCompare(valA);
            } else {
                return sortDirection.value === 'asc'
                    ? valA - valB
                    : valB - valA;
            }
        });
    }
    return list;
});

function fmt(dt) {
    return dt ? new Date(dt).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }) : '—';
}

// ── Pembayaran simulasi ──
const viewState = ref('index'); // 'index' | 'payment'
const selected = ref(null);
const payForm = useForm({ method: 'cash' });

const canPay = (b) => ['approved', 'completed'].includes(b.status) && b.payment_status !== 'paid';
const canCancel = (b) => ['pending', 'approved'].includes(b.status) && b.payment_status !== 'paid';

function openPay(b) {
    selected.value = b;
    payForm.method = 'cash';
    viewState.value = 'payment';
}

function cancelBooking(b) {
    if (confirm(`Batalkan pemesanan ruang "${b.room?.name ?? ''}" dengan kode ${b.booking_code}?`)) {
        router.post(`/member/bookings/${b.id}/cancel`, {}, {
            preserveScroll: true,
        });
    }
}

function pay(methodName) {
    payForm.method = methodName;
    payForm.post(`/member/bookings/${selected.value.id}/pay`, {
        preserveScroll: true,
        onSuccess: () => { viewState.value = 'index'; },
    });
}
</script>

<template>
    <Head title="Pemesanan Saya" />
    <MemberLayout>
        <template #header><h1 class="text-base font-semibold text-white">Pemesanan Saya</h1></template>

        <div v-if="viewState === 'index'">
            <div class="mb-4">
                <select v-model="status" class="aurora-input max-w-xs" @change="applyFilter">
                    <option value="">Semua Status</option>
                    <option value="pending">Menunggu</option>
                    <option value="approved">Disetujui</option>
                    <option value="completed">Selesai</option>
                    <option value="rejected">Ditolak</option>
                    <option value="cancelled">Batal</option>
                </select>
            </div>

            <div class="aurora-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-white/[0.06] text-sm">
                        <thead class="bg-white/[0.03]">
                            <tr>
                                <th class="aurora-th cursor-pointer select-none" @click="toggleSort('booking_code')">
                                    Kode <span v-if="sortColumn === 'booking_code'">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                </th>
                                <th class="aurora-th cursor-pointer select-none" @click="toggleSort('title')">
                                    Judul <span v-if="sortColumn === 'title'">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                </th>
                                <th class="aurora-th cursor-pointer select-none" @click="toggleSort('room')">
                                    Ruangan <span v-if="sortColumn === 'room'">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                </th>
                                <th class="aurora-th cursor-pointer select-none" @click="toggleSort('created_at')">
                                    Tanggal Transaksi <span v-if="sortColumn === 'created_at'">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                </th>
                                <th class="aurora-th cursor-pointer select-none" @click="toggleSort('start_datetime')">
                                    Mulai <span v-if="sortColumn === 'start_datetime'">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                </th>
                                <th class="aurora-th text-center cursor-pointer select-none" @click="toggleSort('status')">
                                    Status <span v-if="sortColumn === 'status'">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                </th>
                                <th class="aurora-th text-center cursor-pointer select-none" @click="toggleSort('payment_status')">
                                    Pembayaran <span v-if="sortColumn === 'payment_status'">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                </th>
                                <th class="aurora-th text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/[0.06]">
                            <tr v-for="b in sortedBookings" :key="b.id" class="aurora-row">
                                <td class="aurora-td font-mono text-xs text-slate-500">{{ b.booking_code }}</td>
                                <td class="aurora-td font-medium text-white">{{ b.title }}</td>
                                <td class="aurora-td text-slate-400">{{ b.room?.name ?? '—' }}</td>
                                <td class="aurora-td text-slate-400">{{ fmt(b.created_at) }}</td>
                                <td class="aurora-td text-slate-400">{{ fmt(b.start_datetime) }}</td>
                                <td class="aurora-td text-center">
                                    <span class="aurora-pill" :class="statusBadge[b.status] ?? 'aurora-pill-muted'">{{ bookingStatusLabel[b.status] ?? b.status }}</span>
                                </td>
                                <td class="aurora-td text-center">
                                    <span v-if="b.payment_status === 'paid'" class="aurora-pill aurora-pill-ok">Lunas</span>
                                    <span v-else class="aurora-pill aurora-pill-warn">Belum bayar</span>
                                </td>
                                <td class="aurora-td text-right">
                                    <div class="flex justify-end gap-2">
                                        <button v-if="canPay(b)" class="aurora-btn-primary rounded-lg px-3 py-1.5 text-xs font-semibold" @click="openPay(b)">Bayar</button>
                                        <button v-if="canCancel(b)" class="rounded-md px-2 py-1.5 text-xs font-medium text-red-400 hover:bg-red-500/10 border border-red-500/20" @click="cancelBooking(b)">Batalkan</button>
                                        <span v-if="b.payment_status === 'paid'" class="text-xs text-slate-500">{{ formatRupiah(b.amount) }}</span>
                                        <span v-else-if="!canPay(b) && !canCancel(b)" class="text-xs text-slate-600">—</span>
                                    </div>
                                </td>
                            </tr>
                             <tr v-if="!sortedBookings.length">
                                <td colspan="8" class="aurora-td py-8 text-center text-slate-500">Tidak ada pemesanan.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4"><Pagination :links="bookings.links" /></div>
        </div>

        <div v-else-if="viewState === 'payment'" class="space-y-6">
            <h2 class="text-lg font-semibold text-white mb-2">Pembayaran (Simulasi)</h2>
            <div class="rounded-xl border border-indigo-500/20 bg-indigo-500/10 p-4 text-center">
                <p class="text-xs text-slate-400">Total tagihan untuk {{ selected?.booking_code }}</p>
                <p class="mt-1 text-3xl font-bold text-white">{{ formatRupiah(selected ? bookingAmount(selected) : 0) }}</p>
            </div>

            <div>
                <p class="mb-2 text-sm font-medium text-slate-300">Pilih metode pembayaran</p>
                <div class="grid grid-cols-2 gap-2">
                    <button
                        v-for="m in paymentMethods"
                        :key="m.key"
                        type="button"
                        class="flex items-center gap-2 rounded-xl border px-3 py-3 text-sm font-medium transition"
                        :class="payForm.method === m.key ? 'border-indigo-500 bg-indigo-500/15 text-white' : 'border-white/10 bg-white/5 text-slate-400 hover:bg-white/10'"
                        @click="payForm.method = m.key"
                    >
                        <span class="text-lg">{{ m.icon }}</span>
                        {{ m.label }}
                    </button>
                </div>
            </div>

            <p class="rounded-lg bg-white/5 px-3 py-2 text-xs text-slate-500">
                Ini pembayaran simulasi untuk keperluan demo — tidak ada transaksi nyata yang diproses.
            </p>

            <div class="flex justify-end gap-2 border-t border-white/[0.07] pt-4">
                <button type="button" class="aurora-btn-ghost rounded-lg px-4 py-2 text-sm" @click="viewState = 'index'">Batal</button>
                <button
                    type="button"
                    :disabled="payForm.processing"
                    class="aurora-btn-primary rounded-lg px-5 py-2 text-sm font-semibold disabled:opacity-60"
                    @click="pay(payForm.method)"
                >
                    {{ payForm.processing ? 'Memproses…' : 'Bayar Sekarang' }}
                </button>
            </div>
        </div>
    </MemberLayout>
</template>
