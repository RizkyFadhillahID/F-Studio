<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import Pagination from '../../Components/Pagination.vue';
import { formatRupiah } from '../../lib/payment';
import { bookingStatusLabel } from '../../lib/status';

const props = defineProps({
    bookings: { type: Object, required: true },
    rooms: { type: Array, default: () => [] },
    members: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const statusBadge = {
    pending: 'aurora-pill-warn',
    approved: 'aurora-pill-ok',
    completed: 'aurora-pill-info',
    rejected: 'aurora-pill-danger',
    cancelled: 'aurora-pill-muted',
};

const viewState = ref('index'); // 'index' | 'create' | 'status'
const selected = ref(null);
const search = ref(props.filters.search ?? '');

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
            } else if (sortColumn.value === 'customer') {
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

const form = useForm({
    room_id: '', user_id: '', customer_name: '', customer_phone: '',
    title: '', start_datetime: '', end_datetime: '', notes: '',
});
const statusForm = useForm({ status: '', admin_notes: '' });

function openCreate() {
    form.reset();
    form.clearErrors();
    viewState.value = 'create';
}

function submit() {
    form.post('/bookings', {
        preserveScroll: true,
        onSuccess: () => { viewState.value = 'index'; },
    });
}

function openStatus(booking) {
    selected.value = booking;
    statusForm.reset();
    statusForm.status = booking.status;
    viewState.value = 'status';
}

function submitStatus() {
    statusForm.post(`/bookings/${selected.value.id}/status`, {
        preserveScroll: true,
        onSuccess: () => { viewState.value = 'index'; },
    });
}

function fmt(dt) {
    if (!dt) return '—';
    return new Date(dt).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' });
}

function doSearch() {
    router.get('/bookings', { search: search.value, status: props.filters.status }, { preserveState: true, replace: true });
}
</script>

<template>
    <Head title="Pemesanan Ruangan" />
    <AppLayout>
        <template #header><h1 class="text-base font-semibold text-white">Transaksi · Pemesanan Ruangan</h1></template>

        <div v-if="viewState === 'index'">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <form class="flex gap-2" @submit.prevent="doSearch">
                    <input v-model="search" type="text" placeholder="Cari kode/peminjam…" class="aurora-input sm:w-64" />
                    <button type="submit" class="aurora-btn-ghost rounded-lg px-3 py-2 text-sm">Cari</button>
                </form>
                <button class="aurora-btn-primary rounded-lg px-4 py-2 text-sm font-semibold" @click="openCreate">+ Buat Pemesanan</button>
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
                                <th class="aurora-th cursor-pointer select-none" @click="toggleSort('customer')">
                                    Pelanggan <span v-if="sortColumn === 'customer'">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                </th>
                                <th class="aurora-th cursor-pointer select-none" @click="toggleSort('created_at')">
                                    Tanggal Transaksi <span v-if="sortColumn === 'created_at'">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                </th>
                                <th class="aurora-th cursor-pointer select-none" @click="toggleSort('start_datetime')">
                                    Waktu Mulai <span v-if="sortColumn === 'start_datetime'">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
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
                                <td class="aurora-td text-slate-400">{{ b.user?.name ?? b.customer_name ?? '—' }}</td>
                                <td class="aurora-td text-slate-400">{{ fmt(b.created_at) }}</td>
                                <td class="aurora-td text-slate-400">{{ fmt(b.start_datetime) }}</td>
                                <td class="aurora-td text-center">
                                    <span class="aurora-pill" :class="statusBadge[b.status] ?? 'aurora-pill-muted'">{{ bookingStatusLabel[b.status] ?? b.status }}</span>
                                </td>
                                <td class="aurora-td text-center">
                                    <span v-if="b.payment_status === 'paid'" class="aurora-pill aurora-pill-ok">Lunas</span>
                                    <span v-else class="aurora-pill aurora-pill-warn">Belum bayar</span>
                                    <p v-if="b.payment_status === 'paid'" class="mt-1 text-[11px] text-slate-500">{{ formatRupiah(b.amount) }}</p>
                                </td>
                                <td class="aurora-td text-right">
                                    <button class="rounded-md px-2 py-1 text-xs font-medium text-indigo-400 hover:bg-indigo-500/10" @click="openStatus(b)">Ubah Status</button>
                                </td>
                            </tr>
                            <tr v-if="!sortedBookings.length">
                                <td colspan="9" class="aurora-td py-8 text-center text-slate-500">Tidak ada pemesanan.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4"><Pagination :links="bookings.links" /></div>
        </div>

        <!-- Create -->
        <div v-else-if="viewState === 'create'" class="space-y-6">
            <h2 class="text-lg font-semibold text-white mb-4">Buat Pemesanan Ruangan</h2>
            <form class="space-y-4" @submit.prevent="submit">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-300">Ruangan</label>
                    <select v-model="form.room_id" class="aurora-input">
                        <option value="" disabled>Pilih ruangan…</option>
                        <option v-for="r in rooms" :key="r.id" :value="r.id">{{ r.name }} ({{ r.code }}) — {{ formatRupiah(r.price_per_hour) }}/jam</option>
                    </select>
                    <p v-if="form.errors.room_id" class="mt-1 text-sm text-red-400">{{ form.errors.room_id }}</p>
                </div>
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
                    <label class="mb-1 block text-sm font-medium text-slate-300">Judul / Keperluan</label>
                    <input v-model="form.title" type="text" class="aurora-input" />
                    <p v-if="form.errors.title" class="mt-1 text-sm text-red-400">{{ form.errors.title }}</p>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-300">Mulai</label>
                        <input v-model="form.start_datetime" type="datetime-local" class="aurora-input" />
                        <p v-if="form.errors.start_datetime" class="mt-1 text-sm text-red-400">{{ form.errors.start_datetime }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-300">Selesai</label>
                        <input v-model="form.end_datetime" type="datetime-local" class="aurora-input" />
                        <p v-if="form.errors.end_datetime" class="mt-1 text-sm text-red-400">{{ form.errors.end_datetime }}</p>
                    </div>
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
            <h2 class="text-lg font-semibold text-white mb-4">Ubah Status — {{ selected?.booking_code }}</h2>
            <form class="space-y-4" @submit.prevent="submitStatus">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-300">Status</label>
                    <select v-model="statusForm.status" class="aurora-input">
                        <option value="approved">Disetujui</option>
                        <option value="completed">Selesai</option>
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
