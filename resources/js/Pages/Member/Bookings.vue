<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import MemberLayout from '../../Layouts/MemberLayout.vue';
import Pagination from '../../Components/Pagination.vue';
import { bookingAmount, loanAmount, formatRupiah, paymentMethods } from '../../lib/payment';
import { bookingStatusLabel } from '../../lib/status';

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
    router.get('/member/bookings', {
        tab: t,
        booking_status: bookingStatus.value || undefined,
        loan_status: loanStatus.value || undefined,
    }, { preserveState: true, replace: true });
}

function applyBookingFilter() {
    router.get('/member/bookings', {
        tab: 'bookings',
        booking_status: bookingStatus.value || undefined,
        loan_status: loanStatus.value || undefined,
    }, { preserveState: true, replace: true });
}

function applyLoanFilter() {
    router.get('/member/bookings', {
        tab: 'loans',
        booking_status: bookingStatus.value || undefined,
        loan_status: loanStatus.value || undefined,
    }, { preserveState: true, replace: true });
}

const statusBadge = {
    pending: 'aurora-pill-warn',
    approved: 'aurora-pill-ok',
    completed: 'aurora-pill-info',
    rejected: 'aurora-pill-danger',
    cancelled: 'aurora-pill-muted',
};

const loanStatusMeta = {
    pending:   { label: 'Menunggu Persetujuan', badge: 'aurora-pill-warn' },
    approved:  { label: 'Disetujui — siap diambil', badge: 'aurora-pill-ok' },
    active:    { label: 'Sedang Dipinjam', badge: 'aurora-pill-info' },
    overdue:   { label: 'Terlambat', badge: 'aurora-pill-danger' },
    returned:  { label: 'Selesai', badge: 'aurora-pill-muted' },
    rejected:  { label: 'Ditolak', badge: 'aurora-pill-danger' },
    cancelled: { label: 'Dibatalkan', badge: 'aurora-pill-muted' },
};

// ── Tab: Pemesanan Ruangan (tabel + sorting) ──
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
            if (sortColumn.value === 'room') {
                valA = a.room?.name ?? '';
                valB = b.room?.name ?? '';
            }
            if (valA === null || valA === undefined) valA = '';
            if (valB === null || valB === undefined) valB = '';
            if (typeof valA === 'string') {
                return sortDirection.value === 'asc' ? valA.localeCompare(valB) : valB.localeCompare(valA);
            }
            return sortDirection.value === 'asc' ? valA - valB : valB - valA;
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

// ── Tab: Peminjaman Alat (kartu + pencarian client-side) ──
const loanQuery = ref('');
const filteredLoans = computed(() => {
    return (props.loans.data || []).filter((l) => {
        const q = loanQuery.value.toLowerCase();
        return l.loan_code.toLowerCase().includes(q) || l.purpose.toLowerCase().includes(q);
    });
});

// ── Pembayaran & pembatalan (gabungan booking & loan) ──
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
        ? `/member/bookings/${selected.value.id}/pay`
        : `/member/loans/${selected.value.id}/pay`;
    payForm.post(url, {
        preserveScroll: true,
        onSuccess: () => { mode.value = 'list'; },
    });
}

function cancelBooking(b) {
    if (confirm(`Batalkan pemesanan ruang "${b.room?.name ?? ''}" dengan kode ${b.booking_code}?`)) {
        router.post(`/member/bookings/${b.id}/cancel`, {}, { preserveScroll: true });
    }
}
</script>

<template>
    <Head title="Transaksi" />
    <MemberLayout>
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
                <div class="mb-4 flex flex-wrap items-center gap-3">
                    <select v-model="loanStatus" class="aurora-input max-w-[12rem]" @change="applyLoanFilter">
                        <option value="">Semua Status</option>
                        <option value="pending">Menunggu Persetujuan</option>
                        <option value="approved">Disetujui</option>
                        <option value="active">Sedang Dipinjam</option>
                        <option value="returned">Selesai</option>
                        <option value="rejected">Ditolak</option>
                    </select>
                    <input v-model="loanQuery" type="text" placeholder="Cari kode/keperluan…" class="aurora-input max-w-xs" />
                    <Link href="/member/equipment" class="aurora-btn-primary ml-auto rounded-lg px-4 py-2 text-sm font-semibold">+ Pinjam Alat</Link>
                </div>

                <div class="space-y-3">
                    <div v-for="l in filteredLoans" :key="l.id" class="aurora-card p-5">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div class="flex items-center gap-3">
                                <span class="font-mono text-xs text-slate-500">{{ l.loan_code }}</span>
                                <span class="aurora-pill" :class="loanStatusMeta[l.status]?.badge ?? 'aurora-pill-muted'">
                                    {{ loanStatusMeta[l.status]?.label ?? l.status }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-500">{{ fmtDate(l.loan_date) }} – {{ fmtDate(l.due_date) }}</p>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">Transaksi: {{ fmtDate(l.created_at) }}</p>
                        <p class="mt-2 text-sm font-medium text-white">{{ l.purpose }}</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <div v-for="it in l.items" :key="it.id" class="flex items-center gap-2 rounded-lg bg-white/5 py-1 pl-1 pr-3">
                                <img v-if="it.equipment?.image" :src="`/${it.equipment.image}`" class="h-8 w-10 rounded-md object-cover" />
                                <span class="text-xs text-slate-400">{{ it.equipment?.name ?? '—' }} × {{ it.quantity }}</span>
                            </div>
                        </div>
                        <p v-if="l.admin_notes" class="mt-3 rounded-lg bg-amber-500/10 px-3 py-2 text-xs text-amber-300">Catatan admin: {{ l.admin_notes }}</p>

                        <div class="mt-3 flex items-center justify-between border-t border-white/[0.06] pt-3">
                            <span v-if="l.payment_status === 'paid'" class="flex items-center gap-2 text-xs">
                                <span class="aurora-pill aurora-pill-ok">Lunas</span>
                                <span class="text-slate-400">{{ formatRupiah(l.amount) }}</span>
                            </span>
                            <span v-else class="text-xs text-slate-400">
                                Estimasi biaya: <span class="font-semibold text-white">{{ formatRupiah(loanAmount(l)) }}</span>
                            </span>
                            <button v-if="canPayLoan(l)" class="aurora-btn-primary rounded-lg px-3 py-1.5 text-xs font-semibold" @click="openPay(l, 'loan')">Bayar Sekarang</button>
                        </div>
                    </div>
                    <div v-if="!filteredLoans.length" class="rounded-2xl border border-dashed border-white/10 bg-white/[0.02] py-14 text-center">
                        <p class="text-sm text-slate-500">Tidak ada peminjaman yang cocok dengan filter.</p>
                        <Link href="/member/equipment" class="aurora-btn-primary mt-3 inline-block rounded-lg px-4 py-2 text-sm font-semibold">Pinjam Alat Sekarang</Link>
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
    </MemberLayout>
</template>
