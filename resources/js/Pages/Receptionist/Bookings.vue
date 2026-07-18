<script setup>
import { ref, computed } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import Pagination from '../../Components/Pagination.vue';
import { bookingAmount, loanAmount, formatRupiah, paymentMethods } from '../../lib/payment';
import { bookingStatusLabel, loanStatusLabel } from '../../lib/status';

const props = defineProps({
    bookings: { type: Object, required: true },
    loans: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    activeTab: { type: String, default: 'bookings' },
});

const tab = ref(props.activeTab);
const bookingStatus = ref(props.filters.booking_status ?? '');
const loanStatus = ref(props.filters.loan_status ?? '');

function switchTab(t) {
    tab.value = t;
    router.get('/receptionist/bookings', {
        tab: t,
        booking_status: bookingStatus.value || undefined,
        loan_status: loanStatus.value || undefined,
    }, { preserveState: true, replace: true });
}

function applyBookingFilter() {
    router.get('/receptionist/bookings', {
        tab: 'bookings',
        booking_status: bookingStatus.value || undefined,
        loan_status: loanStatus.value || undefined,
    }, { preserveState: true, replace: true });
}

function applyLoanFilter() {
    router.get('/receptionist/bookings', {
        tab: 'loans',
        booking_status: bookingStatus.value || undefined,
        loan_status: loanStatus.value || undefined,
    }, { preserveState: true, replace: true });
}

const bookingStatusBadge = {
    pending: 'aurora-pill-warn',
    approved: 'aurora-pill-ok',
    completed: 'aurora-pill-info',
    rejected: 'aurora-pill-danger',
    cancelled: 'aurora-pill-muted',
};
const loanStatusBadge = {
    pending: 'aurora-pill-warn',
    approved: 'aurora-pill-ok',
    active: 'aurora-pill-info',
    overdue: 'aurora-pill-danger',
    returned: 'aurora-pill-muted',
    rejected: 'aurora-pill-danger',
    cancelled: 'aurora-pill-muted',
};

// ── Sorting (per-tab, default: transaksi terbaru) ──
const sortColumnB = ref('created_at');
const sortDirectionB = ref('desc');
function toggleSortB(col) {
    if (sortColumnB.value === col) {
        sortDirectionB.value = sortDirectionB.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortColumnB.value = col;
        sortDirectionB.value = 'asc';
    }
}
const sortedBookings = computed(() => {
    const list = [...(props.bookings.data || [])];
    if (sortColumnB.value) {
        list.sort((a, b) => {
            let valA = a[sortColumnB.value];
            let valB = b[sortColumnB.value];
            if (sortColumnB.value === 'room') {
                valA = a.room?.name ?? '';
                valB = b.room?.name ?? '';
            } else if (sortColumnB.value === 'customer') {
                valA = a.customer_name ?? a.user?.name ?? '';
                valB = b.customer_name ?? b.user?.name ?? '';
            }
            if (valA === null || valA === undefined) valA = '';
            if (valB === null || valB === undefined) valB = '';
            if (typeof valA === 'string') {
                return sortDirectionB.value === 'asc' ? valA.localeCompare(valB) : valB.localeCompare(valA);
            }
            return sortDirectionB.value === 'asc' ? valA - valB : valB - valA;
        });
    }
    return list;
});

const sortColumnL = ref('created_at');
const sortDirectionL = ref('desc');
function toggleSortL(col) {
    if (sortColumnL.value === col) {
        sortDirectionL.value = sortDirectionL.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortColumnL.value = col;
        sortDirectionL.value = 'asc';
    }
}
const sortedLoans = computed(() => {
    const list = [...(props.loans.data || [])];
    if (sortColumnL.value) {
        list.sort((a, b) => {
            let valA = a[sortColumnL.value];
            let valB = b[sortColumnL.value];
            if (sortColumnL.value === 'customer') {
                valA = a.customer_name ?? a.user?.name ?? '';
                valB = b.customer_name ?? b.user?.name ?? '';
            }
            if (valA === null || valA === undefined) valA = '';
            if (valB === null || valB === undefined) valB = '';
            if (typeof valA === 'string') {
                return sortDirectionL.value === 'asc' ? valA.localeCompare(valB) : valB.localeCompare(valA);
            }
            return sortDirectionL.value === 'asc' ? valA - valB : valB - valA;
        });
    }
    return list;
});

function fmt(dt) {
    return dt ? new Date(dt).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }) : '—';
}
function fmtDate(d) {
    return d ? new Date(d).toLocaleDateString('id-ID', { dateStyle: 'medium' }) : '—';
}

// ── Pembayaran simulasi (booking & peminjaman) ──
const mode = ref('list'); // 'list' | 'payment'
const selected = ref(null);
const selectedType = ref('booking'); // 'booking' | 'loan'
const payForm = useForm({ method: 'cash' });

const canPayBooking = (b) => ['approved', 'completed'].includes(b.status) && b.payment_status !== 'paid';
const canCancelBooking = (b) => ['pending', 'approved'].includes(b.status) && b.payment_status !== 'paid';
const canPayLoan = (l) => ['approved', 'active', 'overdue', 'returned'].includes(l.status) && l.payment_status !== 'paid';

function openPay(item, type) {
    selected.value = item;
    selectedType.value = type;
    payForm.method = 'cash';
    mode.value = 'payment';
}
function pay(methodName) {
    payForm.method = methodName;
    const url = selectedType.value === 'booking'
        ? `/receptionist/bookings/${selected.value.id}/pay`
        : `/receptionist/loans/${selected.value.id}/pay`;
    payForm.post(url, {
        preserveScroll: true,
        onSuccess: () => { mode.value = 'list'; },
    });
}

function cancelBooking(b) {
    if (confirm(`Batalkan pemesanan ruang "${b.room?.name ?? ''}" dengan kode ${b.booking_code}?`)) {
        router.post(`/receptionist/bookings/${b.id}/cancel`, {}, { preserveScroll: true });
    }
}
</script>

<template>
    <Head title="Transaksi" />
    <AppLayout>
        <template #header><h1 class="text-base font-semibold text-white">Transaksi</h1></template>

        <div v-if="mode === 'list'">
            <div class="mb-4 flex gap-2 border-b border-white/[0.07]">
                <button
                    class="px-4 py-2 text-sm font-semibold transition"
                    :class="tab === 'bookings' ? 'border-b-2 border-indigo-500 text-white' : 'text-slate-500 hover:text-slate-300'"
                    @click="switchTab('bookings')"
                >
                    Pemesanan Ruangan
                </button>
                <button
                    class="px-4 py-2 text-sm font-semibold transition"
                    :class="tab === 'loans' ? 'border-b-2 border-indigo-500 text-white' : 'text-slate-500 hover:text-slate-300'"
                    @click="switchTab('loans')"
                >
                    Peminjaman Alat
                </button>
            </div>

            <!-- Tab: Pemesanan Ruangan -->
            <div v-if="tab === 'bookings'">
                <div class="mb-4">
                    <select v-model="bookingStatus" class="aurora-input max-w-xs" @change="applyBookingFilter">
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
                                    <th class="aurora-th cursor-pointer select-none" @click="toggleSortB('booking_code')">
                                        Kode <span v-if="sortColumnB === 'booking_code'">{{ sortDirectionB === 'asc' ? '▲' : '▼' }}</span>
                                    </th>
                                    <th class="aurora-th cursor-pointer select-none" @click="toggleSortB('customer')">
                                        Pelanggan <span v-if="sortColumnB === 'customer'">{{ sortDirectionB === 'asc' ? '▲' : '▼' }}</span>
                                    </th>
                                    <th class="aurora-th cursor-pointer select-none" @click="toggleSortB('room')">
                                        Ruangan <span v-if="sortColumnB === 'room'">{{ sortDirectionB === 'asc' ? '▲' : '▼' }}</span>
                                    </th>
                                    <th class="aurora-th cursor-pointer select-none" @click="toggleSortB('created_at')">
                                        Tanggal Transaksi <span v-if="sortColumnB === 'created_at'">{{ sortDirectionB === 'asc' ? '▲' : '▼' }}</span>
                                    </th>
                                    <th class="aurora-th cursor-pointer select-none" @click="toggleSortB('start_datetime')">
                                        Waktu Mulai <span v-if="sortColumnB === 'start_datetime'">{{ sortDirectionB === 'asc' ? '▲' : '▼' }}</span>
                                    </th>
                                    <th class="aurora-th text-center cursor-pointer select-none" @click="toggleSortB('status')">
                                        Status <span v-if="sortColumnB === 'status'">{{ sortDirectionB === 'asc' ? '▲' : '▼' }}</span>
                                    </th>
                                    <th class="aurora-th text-center cursor-pointer select-none" @click="toggleSortB('payment_status')">
                                        Pembayaran <span v-if="sortColumnB === 'payment_status'">{{ sortDirectionB === 'asc' ? '▲' : '▼' }}</span>
                                    </th>
                                    <th class="aurora-th text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/[0.06]">
                                <tr v-for="b in sortedBookings" :key="b.id" class="aurora-row">
                                    <td class="aurora-td font-mono text-xs text-slate-500">{{ b.booking_code }}</td>
                                    <td class="aurora-td text-slate-300">{{ b.customer_name ?? b.user?.name ?? '—' }}</td>
                                    <td class="aurora-td text-slate-400">{{ b.room?.name ?? '—' }}</td>
                                    <td class="aurora-td text-slate-400">{{ fmt(b.created_at) }}</td>
                                    <td class="aurora-td text-slate-400">{{ fmt(b.start_datetime) }}</td>
                                    <td class="aurora-td text-center">
                                        <span class="aurora-pill" :class="bookingStatusBadge[b.status] ?? 'aurora-pill-muted'">{{ bookingStatusLabel[b.status] ?? b.status }}</span>
                                    </td>
                                    <td class="aurora-td text-center">
                                        <span v-if="b.payment_status === 'paid'" class="aurora-pill aurora-pill-ok">Lunas</span>
                                        <span v-else class="aurora-pill aurora-pill-warn">Belum bayar</span>
                                    </td>
                                    <td class="aurora-td text-right">
                                        <div class="flex justify-end gap-2">
                                            <button v-if="canPayBooking(b)" class="aurora-btn-primary rounded-lg px-3 py-1.5 text-xs font-semibold" @click="openPay(b, 'booking')">Bayar</button>
                                            <button v-if="canCancelBooking(b)" class="rounded-md px-2 py-1.5 text-xs font-medium text-red-400 hover:bg-red-500/10 border border-red-500/20" @click="cancelBooking(b)">Batalkan</button>
                                            <span v-if="b.payment_status === 'paid'" class="text-xs text-slate-500">{{ formatRupiah(b.amount) }}</span>
                                            <span v-else-if="!canPayBooking(b) && !canCancelBooking(b)" class="text-xs text-slate-600">—</span>
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

            <!-- Tab: Peminjaman Alat -->
            <div v-else-if="tab === 'loans'">
                <div class="mb-4">
                    <select v-model="loanStatus" class="aurora-input max-w-xs" @change="applyLoanFilter">
                        <option value="">Semua Status</option>
                        <option value="pending">Menunggu Persetujuan</option>
                        <option value="approved">Disetujui</option>
                        <option value="active">Sedang Dipinjam</option>
                        <option value="overdue">Terlambat</option>
                        <option value="returned">Selesai</option>
                    </select>
                </div>

                <div class="aurora-card overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-white/[0.06] text-sm">
                            <thead class="bg-white/[0.03]">
                                <tr>
                                    <th class="aurora-th cursor-pointer select-none" @click="toggleSortL('loan_code')">
                                        Kode <span v-if="sortColumnL === 'loan_code'">{{ sortDirectionL === 'asc' ? '▲' : '▼' }}</span>
                                    </th>
                                    <th class="aurora-th cursor-pointer select-none" @click="toggleSortL('customer')">
                                        Pelanggan <span v-if="sortColumnL === 'customer'">{{ sortDirectionL === 'asc' ? '▲' : '▼' }}</span>
                                    </th>
                                    <th class="aurora-th">Peralatan</th>
                                    <th class="aurora-th cursor-pointer select-none" @click="toggleSortL('created_at')">
                                        Tanggal Transaksi <span v-if="sortColumnL === 'created_at'">{{ sortDirectionL === 'asc' ? '▲' : '▼' }}</span>
                                    </th>
                                    <th class="aurora-th cursor-pointer select-none" @click="toggleSortL('due_date')">
                                        Jatuh Tempo <span v-if="sortColumnL === 'due_date'">{{ sortDirectionL === 'asc' ? '▲' : '▼' }}</span>
                                    </th>
                                    <th class="aurora-th text-center cursor-pointer select-none" @click="toggleSortL('status')">
                                        Status <span v-if="sortColumnL === 'status'">{{ sortDirectionL === 'asc' ? '▲' : '▼' }}</span>
                                    </th>
                                    <th class="aurora-th text-center cursor-pointer select-none" @click="toggleSortL('payment_status')">
                                        Pembayaran <span v-if="sortColumnL === 'payment_status'">{{ sortDirectionL === 'asc' ? '▲' : '▼' }}</span>
                                    </th>
                                    <th class="aurora-th text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/[0.06]">
                                <tr v-for="l in sortedLoans" :key="l.id" class="aurora-row">
                                    <td class="aurora-td font-mono text-xs text-slate-500">{{ l.loan_code }}</td>
                                    <td class="aurora-td text-slate-300">{{ l.customer_name ?? l.user?.name ?? '—' }}</td>
                                    <td class="aurora-td text-slate-400">
                                        <span v-for="(it, i) in l.items" :key="it.id">{{ it.equipment?.name ?? '—' }}<span v-if="i < l.items.length - 1">, </span></span>
                                    </td>
                                    <td class="aurora-td text-slate-400">{{ fmtDate(l.created_at) }}</td>
                                    <td class="aurora-td text-slate-400">{{ fmtDate(l.due_date) }}</td>
                                    <td class="aurora-td text-center">
                                        <span class="aurora-pill" :class="loanStatusBadge[l.status] ?? 'aurora-pill-muted'">{{ loanStatusLabel[l.status] ?? l.status }}</span>
                                    </td>
                                    <td class="aurora-td text-center">
                                        <span v-if="l.payment_status === 'paid'" class="aurora-pill aurora-pill-ok">Lunas</span>
                                        <span v-else class="aurora-pill aurora-pill-warn">Belum bayar</span>
                                    </td>
                                    <td class="aurora-td text-right">
                                        <div class="flex justify-end gap-2">
                                            <button v-if="canPayLoan(l)" class="aurora-btn-primary rounded-lg px-3 py-1.5 text-xs font-semibold" @click="openPay(l, 'loan')">Bayar</button>
                                            <span v-if="l.payment_status === 'paid'" class="text-xs text-slate-500">{{ formatRupiah(l.amount) }}</span>
                                            <span v-else-if="!canPayLoan(l)" class="text-xs text-slate-600">—</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!sortedLoans.length">
                                    <td colspan="8" class="aurora-td py-8 text-center text-slate-500">Tidak ada peminjaman.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-4"><Pagination :links="loans.links" /></div>
            </div>
        </div>

        <div v-else-if="mode === 'payment'" class="space-y-6">
            <h2 class="text-lg font-semibold text-white mb-2">Pembayaran (Simulasi)</h2>
            <div class="rounded-xl border border-indigo-500/20 bg-indigo-500/10 p-4 text-center">
                <p class="text-xs text-slate-400">Total tagihan untuk {{ selected?.booking_code ?? selected?.loan_code }}</p>
                <p class="mt-1 text-3xl font-bold text-white">
                    {{ formatRupiah(selected ? (selectedType === 'booking' ? bookingAmount(selected) : loanAmount(selected)) : 0) }}
                </p>
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
                <button type="button" class="aurora-btn-ghost rounded-lg px-4 py-2 text-sm" @click="mode = 'list'">Batal</button>
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
    </AppLayout>
</template>
