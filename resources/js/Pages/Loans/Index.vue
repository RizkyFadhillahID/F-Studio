<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import Pagination from '../../Components/Pagination.vue';
import { formatRupiah } from '../../lib/payment';
import { loanStatusLabel } from '../../lib/status';

const props = defineProps({
    loans: { type: Object, required: true },
    equipment: { type: Array, default: () => [] },
    members: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

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

const sortedLoans = computed(() => {
    const list = [...(props.loans.data || [])];
    if (sortColumn.value) {
        list.sort((a, b) => {
            let valA = a[sortColumn.value];
            let valB = b[sortColumn.value];
            
            // Nested relationship sorting
            if (sortColumn.value === 'customer') {
                valA = a.user?.name ?? a.customer_name ?? '';
                valB = b.user?.name ?? b.customer_name ?? '';
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

const statusBadge = {
    pending: 'aurora-pill-warn',
    approved: 'aurora-pill-ok',
    active: 'aurora-pill-info',
    overdue: 'aurora-pill-danger',
    returned: 'aurora-pill-muted',
    rejected: 'aurora-pill-danger',
    cancelled: 'aurora-pill-muted',
};

const today = new Date().toISOString().slice(0, 10);

const fmtDate = (d) => (d ? new Date(d).toLocaleDateString('id-ID', { dateStyle: 'medium' }) : '—');
const viewState = ref('index'); // 'index' | 'create' | 'status'
const selected = ref(null);
const search = ref(props.filters.search ?? '');

const form = useForm({
    user_id: '', customer_name: '', customer_phone: '', purpose: '',
    loan_date: today, due_date: today, notes: '',
    items: [{ equipment_id: '', quantity: 1, notes: '' }],
});
const statusForm = useForm({ status: '', admin_notes: '' });

function openCreate() {
    form.reset();
    form.loan_date = today;
    form.due_date = today;
    form.items = [{ equipment_id: '', quantity: 1, notes: '' }];
    form.clearErrors();
    viewState.value = 'create';
}

function addItem() {
    form.items.push({ equipment_id: '', quantity: 1, notes: '' });
}
function removeItem(i) {
    if (form.items.length > 1) form.items.splice(i, 1);
}

function submit() {
    form.post('/loans', {
        preserveScroll: true,
        onSuccess: () => { viewState.value = 'index'; },
    });
}

function openStatus(loan) {
    selected.value = loan;
    statusForm.reset();
    statusForm.status = loan.status;
    viewState.value = 'status';
}
function submitStatus() {
    statusForm.post(`/loans/${selected.value.id}/status`, {
        preserveScroll: true,
        onSuccess: () => { viewState.value = 'index'; },
    });
}
function returnLoan(loan) {
    if (confirm(`Proses pengembalian untuk ${loan.loan_code}?`)) {
        router.post(`/loans/${loan.id}/return`, {}, { preserveScroll: true });
    }
}

function doSearch() {
    router.get('/loans', { search: search.value, status: props.filters.status }, { preserveState: true, replace: true });
}
</script>

<template>
    <Head title="Peminjaman Alat" />
    <AppLayout>
        <template #header><h1 class="text-base font-semibold text-white">Transaksi · Peminjaman Alat</h1></template>

        <div v-if="viewState === 'index'">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <form class="flex gap-2" @submit.prevent="doSearch">
                    <input v-model="search" type="text" placeholder="Cari kode/peminjam…" class="aurora-input sm:w-64" />
                    <button type="submit" class="aurora-btn-ghost rounded-lg px-3 py-2 text-sm">Cari</button>
                </form>
                <button class="aurora-btn-primary rounded-lg px-4 py-2 text-sm font-semibold" @click="openCreate">+ Buat Peminjaman</button>
            </div>

            <div class="aurora-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-white/[0.06] text-sm">
                        <thead class="bg-white/[0.03]">
                            <tr>
                                <th class="aurora-th cursor-pointer select-none" @click="toggleSort('loan_code')">
                                    Kode <span v-if="sortColumn === 'loan_code'">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                </th>
                                <th class="aurora-th cursor-pointer select-none" @click="toggleSort('customer')">
                                    Peminjam <span v-if="sortColumn === 'customer'">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                </th>
                                <th class="aurora-th">Peralatan</th>
                                <th class="aurora-th cursor-pointer select-none" @click="toggleSort('created_at')">
                                    Tanggal Transaksi <span v-if="sortColumn === 'created_at'">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                </th>
                                <th class="aurora-th cursor-pointer select-none" @click="toggleSort('due_date')">
                                    Jatuh Tempo <span v-if="sortColumn === 'due_date'">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
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
                            <tr v-for="l in sortedLoans" :key="l.id" class="aurora-row">
                                <td class="aurora-td font-mono text-xs text-slate-500">{{ l.loan_code }}</td>
                                <td class="aurora-td text-slate-400">{{ l.user?.name ?? l.customer_name ?? '—' }}</td>
                                <td class="aurora-td text-slate-400">
                                    <span v-for="(it, i) in l.items" :key="it.id">{{ it.equipment?.name ?? '—' }}<span v-if="i < l.items.length - 1">, </span></span>
                                </td>
                                <td class="aurora-td text-slate-400">{{ fmtDate(l.created_at) }}</td>
                                <td class="aurora-td text-slate-400">{{ fmtDate(l.due_date) }}</td>
                                <td class="aurora-td text-center">
                                    <span class="aurora-pill" :class="statusBadge[l.status] ?? 'aurora-pill-muted'">{{ loanStatusLabel[l.status] ?? l.status }}</span>
                                </td>
                                <td class="aurora-td text-center">
                                    <span v-if="l.payment_status === 'paid'" class="aurora-pill aurora-pill-ok">Lunas</span>
                                    <span v-else class="aurora-pill aurora-pill-warn">Belum bayar</span>
                                    <p v-if="l.payment_status === 'paid'" class="mt-1 text-[11px] text-slate-500">{{ formatRupiah(l.amount) }}</p>
                                </td>
                                <td class="aurora-td text-right whitespace-nowrap">
                                    <button class="rounded-md px-2 py-1 text-xs font-medium text-indigo-400 hover:bg-indigo-500/10" @click="openStatus(l)">Ubah Status</button>
                                    <button v-if="['approved','active','overdue'].includes(l.status)" class="rounded-md px-2 py-1 text-xs font-medium text-emerald-400 hover:bg-emerald-500/10" @click="returnLoan(l)">Kembalikan</button>
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

        <!-- Create -->
        <div v-else-if="viewState === 'create'" class="space-y-6">
            <h2 class="text-lg font-semibold text-white mb-4">Buat Peminjaman Alat</h2>
            <form class="space-y-4" @submit.prevent="submit">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-300">Peminjam (anggota)</label>
                    <select v-model="form.user_id" class="aurora-input">
                        <option value="">— Walk-in / tamu —</option>
                        <option v-for="m in members" :key="m.id" :value="m.id">{{ m.name }}</option>
                    </select>
                </div>
                <div v-if="!form.user_id" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-300">Nama Tamu</label>
                        <input v-model="form.customer_name" type="text" class="aurora-input" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-300">Telepon Tamu</label>
                        <input v-model="form.customer_phone" type="text" class="aurora-input" />
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-300">Keperluan</label>
                    <input v-model="form.purpose" type="text" class="aurora-input" />
                    <p v-if="form.errors.purpose" class="mt-1 text-sm text-red-400">{{ form.errors.purpose }}</p>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-300">Tanggal Pinjam</label>
                        <input v-model="form.loan_date" type="date" class="aurora-input" />
                        <p v-if="form.errors.loan_date" class="mt-1 text-sm text-red-400">{{ form.errors.loan_date }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-300">Jatuh Tempo</label>
                        <input v-model="form.due_date" type="date" class="aurora-input" />
                        <p v-if="form.errors.due_date" class="mt-1 text-sm text-red-400">{{ form.errors.due_date }}</p>
                    </div>
                </div>

                <div>
                    <div class="mb-2 flex items-center justify-between">
                        <label class="text-sm font-medium text-slate-300">Peralatan Dipinjam</label>
                        <button type="button" class="text-xs font-medium text-indigo-400 hover:underline" @click="addItem">+ Tambah item</button>
                    </div>
                    <div v-for="(item, i) in form.items" :key="i" class="mb-2 grid grid-cols-12 gap-2">
                        <select v-model="item.equipment_id" class="aurora-input col-span-7 !py-2">
                            <option value="" disabled>Pilih alat…</option>
                            <option v-for="e in equipment" :key="e.id" :value="e.id">{{ e.name }} (tersedia {{ e.quantity_available }}, {{ formatRupiah(e.price_per_day) }}/hari)</option>
                        </select>
                        <input v-model="item.quantity" type="number" min="1" :max="equipment.find(e => e.id === item.equipment_id)?.quantity_available" class="aurora-input col-span-3 !py-2" />
                        <button type="button" class="col-span-2 rounded-lg border border-white/10 bg-white/5 text-sm text-red-400 hover:bg-red-500/10" @click="removeItem(i)">Hapus</button>
                    </div>
                    <p v-if="form.errors.items" class="mt-1 text-sm text-red-400">{{ form.errors.items }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-300">Catatan (opsional)</label>
                    <textarea v-model="form.notes" rows="2" class="aurora-input"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-white/[0.07]">
                    <button type="button" class="aurora-btn-ghost rounded-lg px-4 py-2 text-sm" @click="viewState = 'index'">Batal</button>
                    <button type="submit" :disabled="form.processing" class="aurora-btn-primary rounded-lg px-4 py-2 text-sm font-semibold disabled:opacity-60">Simpan</button>
                </div>
            </form>
        </div>

        <!-- Update status -->
        <div v-else-if="viewState === 'status'" class="space-y-6">
            <h2 class="text-lg font-semibold text-white mb-4">Ubah Status — {{ selected?.loan_code }}</h2>
            <form class="space-y-4" @submit.prevent="submitStatus">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-300">Status</label>
                    <select v-model="statusForm.status" class="aurora-input">
                        <option value="approved">Disetujui</option>
                        <option value="returned">Selesai</option>
                        <option value="rejected">Ditolak</option>
                        <option value="cancelled">Batal</option>
                    </select>
                    <p v-if="statusForm.errors.status" class="mt-1 text-sm text-red-400">{{ statusForm.errors.status }}</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-300">Catatan admin (opsional)</label>
                    <textarea v-model="statusForm.admin_notes" rows="2" class="aurora-input"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-white/[0.07]">
                    <button type="button" class="aurora-btn-ghost rounded-lg px-4 py-2 text-sm" @click="viewState = 'index'">Batal</button>
                    <button type="submit" :disabled="statusForm.processing" class="aurora-btn-primary rounded-lg px-4 py-2 text-sm font-semibold disabled:opacity-60">Simpan</button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
