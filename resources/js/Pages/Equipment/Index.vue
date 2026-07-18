<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import Pagination from '../../Components/Pagination.vue';
import { formatRupiah } from '../../lib/payment';

const props = defineProps({
    equipment: { type: Object, required: true },
    categories: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const viewState = ref('index'); // 'index' | 'create' | 'edit' | 'detail'
const editing = ref(null);
const search = ref(props.filters.search ?? '');

const sortColumn = ref('name');
const sortDirection = ref('asc');

function toggleSort(col) {
    if (sortColumn.value === col) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortColumn.value = col;
        sortDirection.value = 'asc';
    }
}

const sortedEquipment = computed(() => {
    const list = [...(props.equipment.data || [])];
    if (sortColumn.value) {
        list.sort((a, b) => {
            let valA = a[sortColumn.value];
            let valB = b[sortColumn.value];
            
            // Nested relationship sorting
            if (sortColumn.value === 'category') {
                valA = a.category?.name ?? '';
                valB = b.category?.name ?? '';
            } else if (sortColumn.value === 'price_per_day') {
                valA = Number(valA ?? 0);
                valB = Number(valB ?? 0);
                return sortDirection.value === 'asc' ? valA - valB : valB - valA;
            }

            if (valA === null || valA === undefined) valA = '';
            if (valB === null || valB === undefined) valB = '';
            
            if (typeof valA === 'string') {
                return sortDirection.value === 'asc'
                    ? valA.localeCompare(valB)
                    : valB.localeCompare(valA);
            } else if (typeof valA === 'boolean') {
                const numA = valA ? 1 : 0;
                const numB = valB ? 1 : 0;
                return sortDirection.value === 'asc' ? numA - numB : numB - numA;
            } else {
                return sortDirection.value === 'asc'
                    ? valA - valB
                    : valB - valA;
            }
        });
    }
    return list;
});

// Detail popup
const detailItem = ref(null);

function openDetail(item) {
    detailItem.value = item;
    viewState.value = 'detail';
}

const form = useForm({
    category_id: '', name: '', code: '', quantity_total: 1, price_per_day: 15000, location: '', description: '', image: null, is_active: true,
});
const imagePreview = ref(null);

function onImageChange(e) {
    const file = e.target.files[0];
    form.image = file ?? null;
    imagePreview.value = file ? URL.createObjectURL(file) : null;
}

function openCreate() {
    editing.value = null;
    form.reset();
    form.clearErrors();
    imagePreview.value = null;
    viewState.value = 'create';
}

function openEdit(item) {
    editing.value = item;
    form.clearErrors();
    form.category_id = item.category_id;
    form.name = item.name;
    form.code = item.code;
    form.quantity_total = item.quantity_total;
    form.price_per_day = item.price_per_day ?? 15000;
    form.location = item.location ?? '';
    form.description = item.description ?? '';
    form.is_active = !!item.is_active;
    form.image = null;
    imagePreview.value = item.image ? `/${item.image}` : null;
    viewState.value = 'edit';
}

function submit() {
    const opts = { preserveScroll: true, forceFormData: true, onSuccess: () => { viewState.value = 'index'; } };
    if (editing.value) {
        form.transform((data) => ({ ...data, _method: 'put' }))
            .post(`/equipment/${editing.value.id}`, opts);
    } else {
        form.post('/equipment', opts);
    }
}

function destroy(item) {
    if (confirm(`Hapus peralatan "${item.name}"?`)) {
        router.delete(`/equipment/${item.id}`, { preserveScroll: true });
    }
}

function doSearch() {
    router.get('/equipment', { search: search.value }, { preserveState: true, replace: true });
}
</script>

<template>
    <Head title="Peralatan" />
    <AppLayout>
        <template #header><h1 class="text-base font-semibold text-white">Data Master · Peralatan</h1></template>
        <div v-if="viewState === 'index'">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <form class="flex gap-2" @submit.prevent="doSearch">
                    <input v-model="search" type="text" placeholder="Cari peralatan…" class="aurora-input sm:w-64" />
                    <button type="submit" class="aurora-btn-ghost rounded-lg px-3 py-2 text-sm">Cari</button>
                </form>
                <button class="aurora-btn-primary rounded-lg px-4 py-2 text-sm font-semibold" @click="openCreate">+ Tambah Peralatan</button>
            </div>

            <div class="aurora-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-white/[0.06] text-sm">
                        <thead class="bg-white/[0.03]">
                            <tr>
                                <th class="aurora-th cursor-pointer select-none" @click="toggleSort('name')">
                                    Nama <span v-if="sortColumn === 'name'">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                </th>
                                <th class="aurora-th cursor-pointer select-none" @click="toggleSort('code')">
                                    Kode <span v-if="sortColumn === 'code'">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                </th>
                                <th class="aurora-th cursor-pointer select-none" @click="toggleSort('category')">
                                    Kategori <span v-if="sortColumn === 'category'">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                </th>
                                <th class="aurora-th text-center cursor-pointer select-none" @click="toggleSort('quantity_available')">
                                    Tersedia / Total <span v-if="sortColumn === 'quantity_available'">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                </th>
                                <th class="aurora-th cursor-pointer select-none" @click="toggleSort('price_per_day')">
                                    Harga/Hari <span v-if="sortColumn === 'price_per_day'">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                </th>
                                <th class="aurora-th text-center cursor-pointer select-none" @click="toggleSort('is_active')">
                                    Status <span v-if="sortColumn === 'is_active'">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                </th>
                                <th class="aurora-th text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/[0.06]">
                            <tr v-for="item in sortedEquipment" :key="item.id" class="aurora-row">
                                <td class="aurora-td">
                                    <button class="flex items-center gap-3 text-left" @click="openDetail(item)">
                                        <img v-if="item.image" :src="`/${item.image}`" :alt="item.name" class="h-10 w-14 rounded-lg object-cover" loading="lazy" />
                                        <div v-else class="flex h-10 w-14 items-center justify-center rounded-lg bg-white/5 text-slate-600">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" /></svg>
                                        </div>
                                        <span class="font-medium text-indigo-300 hover:underline">{{ item.name }}</span>
                                    </button>
                                </td>
                                <td class="aurora-td text-slate-400">{{ item.code }}</td>
                                <td class="aurora-td text-slate-400">{{ item.category?.name ?? '—' }}</td>
                                <td class="aurora-td text-center">{{ item.quantity_available }} / {{ item.quantity_total }}</td>
                                <td class="aurora-td text-slate-400">{{ formatRupiah(item.price_per_day) }}</td>
                                <td class="aurora-td text-center">
                                    <span class="aurora-pill" :class="item.is_active ? 'aurora-pill-ok' : 'aurora-pill-muted'">
                                        {{ item.is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="aurora-td text-right">
                                    <button class="rounded-md px-2 py-1 text-xs font-medium text-indigo-400 hover:bg-indigo-500/10" @click="openEdit(item)">Ubah</button>
                                    <button class="rounded-md px-2 py-1 text-xs font-medium text-red-400 hover:bg-red-500/10" @click="destroy(item)">Hapus</button>
                                </td>
                            </tr>
                            <tr v-if="!sortedEquipment.length">
                                <td colspan="7" class="aurora-td py-8 text-center text-slate-500">Tidak ada peralatan.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4"><Pagination :links="equipment.links" /></div>
        </div>

        <div v-else-if="viewState === 'detail'" class="space-y-6">
            <h2 class="text-lg font-semibold text-white mb-2">{{ detailItem?.name ?? 'Detail Peralatan' }}</h2>
            <div v-if="detailItem" class="space-y-4">
                <div v-if="detailItem.image" class="overflow-hidden rounded-xl bg-slate-950/40 h-64 w-full flex items-center justify-center p-2">
                    <img :src="`/${detailItem.image}`" :alt="detailItem.name" class="max-h-full max-w-full object-contain rounded-lg" />
                </div>

                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div class="rounded-lg bg-white/5 p-3">
                        <p class="text-xs text-slate-500">Kode</p>
                        <p class="font-medium text-white">{{ detailItem.code }}</p>
                    </div>
                    <div class="rounded-lg bg-white/5 p-3">
                        <p class="text-xs text-slate-500">Kategori</p>
                        <p class="font-medium text-white">{{ detailItem.category?.name ?? '—' }}</p>
                    </div>
                    <div class="rounded-lg bg-white/5 p-3">
                        <p class="text-xs text-slate-500">Ketersediaan</p>
                        <p class="font-medium" :class="detailItem.quantity_available > 0 ? 'text-emerald-400' : 'text-red-400'">
                            {{ detailItem.quantity_available }} dari {{ detailItem.quantity_total }} unit
                        </p>
                    </div>
                    <div class="rounded-lg bg-white/5 p-3">
                        <p class="text-xs text-slate-500">Lokasi</p>
                        <p class="font-medium text-white">{{ detailItem.location ?? '—' }}</p>
                    </div>
                    <div class="rounded-lg bg-white/5 p-3">
                        <p class="text-xs text-slate-500">Harga/Hari</p>
                        <p class="font-medium text-white">{{ formatRupiah(detailItem.price_per_day) }}</p>
                    </div>
                </div>

                <div v-if="detailItem.description">
                    <p class="mb-1 text-xs font-medium uppercase tracking-wide text-slate-500">Deskripsi</p>
                    <p class="text-sm leading-relaxed text-slate-400">{{ detailItem.description }}</p>
                </div>

                <div class="flex justify-end gap-2 border-t border-white/[0.07] pt-4">
                    <button class="aurora-btn-ghost rounded-lg px-4 py-2 text-sm" @click="viewState = 'index'">Kembali</button>
                    <button class="aurora-btn-primary rounded-lg px-4 py-2 text-sm font-semibold" @click="openEdit(detailItem)">Ubah Peralatan</button>
                </div>
            </div>
        </div>

        <div v-else-if="viewState === 'create' || viewState === 'edit'" class="space-y-6">
            <h2 class="text-lg font-semibold text-white mb-4">{{ editing ? 'Ubah Peralatan' : 'Tambah Peralatan' }}</h2>
            <form class="space-y-4" @submit.prevent="submit">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-300">Kategori</label>
                    <select v-model="form.category_id" class="aurora-input">
                        <option value="" disabled>Pilih kategori…</option>
                        <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                    <p v-if="form.errors.category_id" class="mt-1 text-sm text-red-400">{{ form.errors.category_id }}</p>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-300">Nama</label>
                        <input v-model="form.name" type="text" class="aurora-input" />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-400">{{ form.errors.name }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-300">Kode</label>
                        <input v-model="form.code" type="text" class="aurora-input" />
                        <p v-if="form.errors.code" class="mt-1 text-sm text-red-400">{{ form.errors.code }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-300">Jumlah Total</label>
                        <input v-model="form.quantity_total" type="number" min="1" class="aurora-input" />
                        <p v-if="form.errors.quantity_total" class="mt-1 text-sm text-red-400">{{ form.errors.quantity_total }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-300">Harga per Hari (Rp)</label>
                        <input v-model="form.price_per_day" type="number" min="0" step="1000" class="aurora-input" />
                        <p v-if="form.errors.price_per_day" class="mt-1 text-sm text-red-400">{{ form.errors.price_per_day }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-300">Lokasi (opsional)</label>
                        <input v-model="form.location" type="text" class="aurora-input" />
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-300">Deskripsi (opsional)</label>
                    <textarea v-model="form.description" rows="2" class="aurora-input"></textarea>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-300">Foto Peralatan (opsional)</label>
                    <div class="flex items-center gap-3">
                        <div class="h-16 w-24 shrink-0 overflow-hidden rounded-lg border border-white/10 bg-white/5">
                            <img v-if="imagePreview" :src="imagePreview" class="h-full w-full object-cover" />
                            <div v-else class="flex h-full items-center justify-center text-slate-600">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z" /></svg>
                            </div>
                        </div>
                        <input type="file" accept="image/jpeg,image/png,image/webp" class="block w-full text-sm text-slate-400 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-600 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-indigo-700" @change="onImageChange" />
                    </div>
                    <p v-if="form.errors.image" class="mt-1 text-sm text-red-400">{{ form.errors.image }}</p>
                    <p class="mt-1 text-xs text-slate-500">JPG/PNG/WebP, maks 2 MB.</p>
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-400">
                    <input v-model="form.is_active" type="checkbox" class="rounded border-white/20 bg-white/5 text-indigo-600 focus:ring-indigo-500" /> Aktif
                </label>
                <div class="flex justify-end gap-2 pt-2 border-t border-white/[0.07]">
                    <button type="button" class="aurora-btn-ghost rounded-lg px-4 py-2 text-sm" @click="viewState = 'index'">Batal</button>
                    <button type="submit" :disabled="form.processing" class="aurora-btn-primary rounded-lg px-4 py-2 text-sm font-semibold disabled:opacity-60">Simpan</button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
