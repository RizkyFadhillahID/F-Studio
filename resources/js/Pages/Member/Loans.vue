<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import MemberLayout from '../../Layouts/MemberLayout.vue';
import Pagination from '../../Components/Pagination.vue';
import { loanAmount, formatRupiah, paymentMethods } from '../../lib/payment';

const props = defineProps({
    loans: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const statusMeta = {
    pending:   { label: 'Menunggu Persetujuan', badge: 'aurora-pill-warn' },
    approved:  { label: 'Disetujui — siap diambil', badge: 'aurora-pill-ok' },
    active:    { label: 'Sedang Dipinjam', badge: 'aurora-pill-info' },
    overdue:   { label: 'Terlambat', badge: 'aurora-pill-danger' },
    returned:  { label: 'Selesai', badge: 'aurora-pill-muted' },
    rejected:  { label: 'Ditolak', badge: 'aurora-pill-danger' },
    cancelled: { label: 'Dibatalkan', badge: 'aurora-pill-muted' },
};

const status = ref(props.filters.status ?? '');
function applyFilter() {
    router.get('/member/loans', { status: status.value || undefined }, { preserveState: true, replace: true });
}

const tableFilters = ref({
    query: '',
});

const filteredLoans = computed(() => {
    return (props.loans.data || []).filter(l => {
        const matchCode = l.loan_code.toLowerCase().includes(tableFilters.value.query.toLowerCase());
        const matchPurpose = l.purpose.toLowerCase().includes(tableFilters.value.query.toLowerCase());
        return matchCode || matchPurpose;
    });
});
const fmtDate = (d) => (d ? new Date(d).toLocaleDateString('id-ID', { dateStyle: 'medium' }) : '—');

// ── Pembayaran simulasi ──
const viewState = ref('index'); // 'index' | 'payment'
const selected = ref(null);
const payForm = useForm({ method: 'cash' });

const canPay = (l) => ['approved', 'active', 'overdue', 'returned'].includes(l.status) && l.payment_status !== 'paid';

function openPay(l) {
    selected.value = l;
    payForm.method = 'cash';
    viewState.value = 'payment';
}
function pay(methodName) {
    payForm.method = methodName;
    payForm.post(`/member/loans/${selected.value.id}/pay`, {
        preserveScroll: true,
        onSuccess: () => { viewState.value = 'index'; },
    });
}
</script>

<template>
    <Head title="Peminjaman Saya" />
    <MemberLayout>
        <template #header><h1 class="text-base font-semibold text-white">Peminjaman Saya</h1></template>

        <div v-if="viewState === 'index'">
            <div class="mb-4 flex flex-wrap items-center gap-3">
                <select v-model="status" class="aurora-input max-w-[12rem]" @change="applyFilter">
                    <option value="">Semua Status</option>
                    <option value="pending">Menunggu Persetujuan</option>
                    <option value="approved">Disetujui</option>
                    <option value="active">Sedang Dipinjam</option>
                    <option value="returned">Selesai</option>
                    <option value="rejected">Ditolak</option>
                </select>
                <input v-model="tableFilters.query" type="text" placeholder="Cari kode/keperluan…" class="aurora-input max-w-xs" />
                <Link href="/member/equipment" class="aurora-btn-primary ml-auto rounded-lg px-4 py-2 text-sm font-semibold">+ Pinjam Alat</Link>
            </div>

            <div class="space-y-3">
                <div v-for="l in filteredLoans" :key="l.id" class="aurora-card p-5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="flex items-center gap-3">
                            <span class="font-mono text-xs text-slate-500">{{ l.loan_code }}</span>
                            <span class="aurora-pill" :class="statusMeta[l.status]?.badge ?? 'aurora-pill-muted'">
                                {{ statusMeta[l.status]?.label ?? l.status }}
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
                        <button v-if="canPay(l)" class="aurora-btn-primary rounded-lg px-3 py-1.5 text-xs font-semibold" @click="openPay(l)">Bayar Sekarang</button>
                    </div>
                </div>
                <div v-if="!filteredLoans.length" class="rounded-2xl border border-dashed border-white/10 bg-white/[0.02] py-14 text-center">
                    <p class="text-sm text-slate-500">Tidak ada peminjaman yang cocok dengan filter.</p>
                    <Link href="/member/equipment" class="aurora-btn-primary mt-3 inline-block rounded-lg px-4 py-2 text-sm font-semibold">Pinjam Alat Sekarang</Link>
                </div>
            </div>

            <div class="mt-4"><Pagination :links="loans.links" /></div>
        </div>

        <div v-else-if="viewState === 'payment'" class="space-y-6">
            <h2 class="text-lg font-semibold text-white mb-2">Pembayaran (Simulasi)</h2>
            <div class="rounded-xl border border-indigo-500/20 bg-indigo-500/10 p-4 text-center">
                <p class="text-xs text-slate-400">Total tagihan untuk {{ selected?.loan_code }}</p>
                <p class="mt-1 text-3xl font-bold text-white">{{ formatRupiah(selected ? loanAmount(selected) : 0) }}</p>
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
