<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import Pagination from '../../Components/Pagination.vue';
import { formatRupiah } from '../../lib/payment';

const props = defineProps({
    rooms: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const viewState = ref('index'); // 'index' | 'create' | 'edit' | 'detail'
const editing = ref(null);
const search = ref(props.filters.search ?? '');

// Sorting columns reaktif
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

const sortedRooms = computed(() => {
    const list = [...(props.rooms.data || [])];
    if (sortColumn.value) {
        list.sort((a, b) => {
            let valA = a[sortColumn.value];
            let valB = b[sortColumn.value];

            if (sortColumn.value === 'price_per_hour') {
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

// Detail popup + galeri
const detailRoom = ref(null);
const activeImg = ref(0);

function openDetail(room) {
    detailRoom.value = room;
    activeImg.value = 0;
    viewState.value = 'detail';
}

const galleryOf = (room) => {
    const list = Array.isArray(room?.images) && room.images.length ? room.images : (room?.image ? [room.image] : []);
    return list;
};

const form = useForm({
    name: '', code: '', capacity: 1, price_per_hour: 50000, facilities: '', description: '', images: null, existing_images: [], is_active: true,
});
// Satu baris per foto (pola "+ Tambah item"), bisa berupa foto lama yang sudah
// tersimpan ({ type: 'existing', path }) atau foto baru yang belum diunggah
// ({ type: 'new', file, preview }) — masing-masing bisa dihapus satu per satu.
const photoRows = ref([]);

function addPhotoRow() {
    photoRows.value.push({ type: 'new', file: null, preview: null });
}
function removePhotoRow(idx) {
    photoRows.value.splice(idx, 1);
    syncImages();
}
function onRowFileChange(idx, e) {
    const file = e.target.files[0] ?? null;
    photoRows.value[idx] = { type: 'new', file, preview: file ? URL.createObjectURL(file) : null };
    syncImages();
}
function syncImages() {
    const files = photoRows.value.filter((r) => r.type === 'new' && r.file).map((r) => r.file);
    form.images = files.length ? files : null;
    form.existing_images = photoRows.value.filter((r) => r.type === 'existing').map((r) => r.path);
}

function openCreate() {
    editing.value = null;
    form.reset();
    form.clearErrors();
    form.images = null;
    form.existing_images = [];
    photoRows.value = [{ type: 'new', file: null, preview: null }];
    viewState.value = 'create';
}

function openEdit(room) {
    editing.value = room;
    form.clearErrors();
    form.name = room.name;
    form.code = room.code;
    form.capacity = room.capacity;
    form.price_per_hour = room.price_per_hour ?? 50000;
    form.facilities = Array.isArray(room.facilities) ? room.facilities.join(', ') : (room.facilities ?? '');
    form.description = room.description ?? '';
    form.is_active = !!room.is_active;
    form.images = null;
    const gallery = galleryOf(room);
    form.existing_images = [...gallery];
    photoRows.value = gallery.map((path) => ({ type: 'existing', path }));
    viewState.value = 'edit';
}

function submit() {
    const opts = { preserveScroll: true, forceFormData: true, onSuccess: () => { viewState.value = 'index'; } };
    if (editing.value) {
        form.transform((data) => ({ ...data, _method: 'put' }))
            .post(`/rooms/${editing.value.id}`, opts);
    } else {
        form.post('/rooms', opts);
    }
}

function destroy(room) {
    if (confirm(`Hapus ruangan "${room.name}"?`)) {
        router.delete(`/rooms/${room.id}`, { preserveScroll: true });
    }
}

function doSearch() {
    router.get('/rooms', { search: search.value }, { preserveState: true, replace: true });
}
</script>

<template>
    <Head title="Ruangan" />
    <AppLayout>
        <template #header><h1 class="text-base font-semibold text-white">Data Master · Ruangan</h1></template>
        <div v-if="viewState === 'index'">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <form class="flex gap-2" @submit.prevent="doSearch">
                    <input v-model="search" type="text" placeholder="Cari ruangan…" class="aurora-input sm:w-64" />
                    <button type="submit" class="aurora-btn-ghost rounded-lg px-3 py-2 text-sm">Cari</button>
                </form>
                <button class="aurora-btn-primary rounded-lg px-4 py-2 text-sm font-semibold" @click="openCreate">+ Tambah Ruangan</button>
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
                                <th class="aurora-th text-center cursor-pointer select-none" @click="toggleSort('capacity')">
                                    Kapasitas <span v-if="sortColumn === 'capacity'">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                </th>
                                <th class="aurora-th cursor-pointer select-none" @click="toggleSort('price_per_hour')">
                                    Harga/Jam <span v-if="sortColumn === 'price_per_hour'">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                </th>
                                <th class="aurora-th">Fasilitas</th>
                                <th class="aurora-th text-center cursor-pointer select-none" @click="toggleSort('is_active')">
                                    Status <span v-if="sortColumn === 'is_active'">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                </th>
                                <th class="aurora-th text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/[0.06]">
                            <tr v-for="room in sortedRooms" :key="room.id" class="aurora-row">
                                <td class="aurora-td">
                                    <button class="flex items-center gap-3 text-left" @click="openDetail(room)">
                                        <img v-if="room.image" :src="`/${room.image}`" :alt="room.name" class="h-10 w-14 rounded-lg object-cover" loading="lazy" />
                                        <div v-else class="flex h-10 w-14 items-center justify-center rounded-lg bg-white/5 text-slate-600">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18" /></svg>
                                        </div>
                                        <span class="font-medium text-indigo-300 hover:underline">{{ room.name }}</span>
                                    </button>
                                </td>
                                <td class="aurora-td text-slate-400">{{ room.code }}</td>
                                <td class="aurora-td text-center">{{ room.capacity }}</td>
                                <td class="aurora-td text-slate-400">{{ formatRupiah(room.price_per_hour) }}</td>
                                <td class="aurora-td text-slate-400">
                                    <span v-if="Array.isArray(room.facilities) && room.facilities.length">{{ room.facilities.join(', ') }}</span>
                                    <span v-else>—</span>
                                </td>
                                <td class="aurora-td text-center">
                                    <span class="aurora-pill" :class="room.is_active ? 'aurora-pill-ok' : 'aurora-pill-muted'">
                                        {{ room.is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="aurora-td text-right">
                                    <button class="rounded-md px-2 py-1 text-xs font-medium text-indigo-400 hover:bg-indigo-500/10" @click="openEdit(room)">Ubah</button>
                                    <button class="rounded-md px-2 py-1 text-xs font-medium text-red-400 hover:bg-red-500/10" @click="destroy(room)">Hapus</button>
                                </td>
                            </tr>
                            <tr v-if="!sortedRooms.length">
                                <td colspan="7" class="aurora-td py-8 text-center text-slate-500">Tidak ada ruangan.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4"><Pagination :links="rooms.links" /></div>
        </div>

        <div v-else-if="viewState === 'detail'" class="space-y-6">
            <h2 class="text-lg font-semibold text-white mb-2">{{ detailRoom?.name ?? 'Detail Ruangan' }}</h2>
            <div v-if="detailRoom" class="space-y-4">
                <div v-if="galleryOf(detailRoom).length">
                    <div class="overflow-hidden rounded-xl bg-slate-950/40 h-64 w-full flex items-center justify-center p-2">
                        <img :src="`/${galleryOf(detailRoom)[activeImg]}`" :alt="detailRoom.name" class="max-h-full max-w-full object-contain rounded-lg" />
                    </div>
                    <div v-if="galleryOf(detailRoom).length > 1" class="mt-2 flex gap-2 overflow-x-auto pb-1">
                        <button
                            v-for="(img, i) in galleryOf(detailRoom)"
                            :key="img"
                            class="h-14 w-20 shrink-0 overflow-hidden rounded-lg ring-2 transition"
                            :class="activeImg === i ? 'ring-indigo-500' : 'ring-transparent opacity-60 hover:opacity-100'"
                            @click="activeImg = i"
                        >
                            <img :src="`/${img}`" class="h-full w-full object-cover" />
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div class="rounded-lg bg-white/5 p-3">
                        <p class="text-xs text-slate-500">Kode</p>
                        <p class="font-medium text-white">{{ detailRoom.code }}</p>
                    </div>
                    <div class="rounded-lg bg-white/5 p-3">
                        <p class="text-xs text-slate-500">Kapasitas</p>
                        <p class="font-medium text-white">{{ detailRoom.capacity }} orang</p>
                    </div>
                    <div class="rounded-lg bg-white/5 p-3">
                        <p class="text-xs text-slate-500">Harga/Jam</p>
                        <p class="font-medium text-white">{{ formatRupiah(detailRoom.price_per_hour) }}</p>
                    </div>
                    <div class="rounded-lg bg-white/5 p-3">
                        <p class="text-xs text-slate-500">Status</p>
                        <span class="aurora-pill mt-0.5 inline-block" :class="detailRoom.is_active ? 'aurora-pill-ok' : 'aurora-pill-muted'">
                            {{ detailRoom.is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>
                    <div class="rounded-lg bg-white/5 p-3">
                        <p class="text-xs text-slate-500">Jumlah Foto</p>
                        <p class="font-medium text-white">{{ galleryOf(detailRoom).length }}</p>
                    </div>
                </div>

                <div v-if="Array.isArray(detailRoom.facilities) && detailRoom.facilities.length">
                    <p class="mb-1.5 text-xs font-medium uppercase tracking-wide text-slate-500">Fasilitas</p>
                    <div class="flex flex-wrap gap-1.5">
                        <span v-for="f in detailRoom.facilities" :key="f" class="rounded-full bg-indigo-500/10 px-2.5 py-1 text-xs text-indigo-300">{{ f }}</span>
                    </div>
                </div>

                <div v-if="detailRoom.description">
                    <p class="mb-1 text-xs font-medium uppercase tracking-wide text-slate-500">Deskripsi</p>
                    <p class="text-sm leading-relaxed text-slate-400">{{ detailRoom.description }}</p>
                </div>

                <div class="flex justify-end gap-2 border-t border-white/[0.07] pt-4">
                    <button class="aurora-btn-ghost rounded-lg px-4 py-2 text-sm" @click="viewState = 'index'">Tutup</button>
                    <button class="aurora-btn-primary rounded-lg px-4 py-2 text-sm font-semibold" @click="openEdit(detailRoom)">Ubah Ruangan</button>
                </div>
            </div>
        </div>

        <div v-else-if="viewState === 'create' || viewState === 'edit'" class="space-y-6">
            <h2 class="text-lg font-semibold text-white mb-4">{{ editing ? 'Ubah Ruangan' : 'Tambah Ruangan' }}</h2>
            <form class="space-y-4" @submit.prevent="submit">
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
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-300">Kapasitas</label>
                        <input v-model="form.capacity" type="number" min="1" class="aurora-input" />
                        <p v-if="form.errors.capacity" class="mt-1 text-sm text-red-400">{{ form.errors.capacity }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-300">Harga per Jam (Rp)</label>
                        <input v-model="form.price_per_hour" type="number" min="0" step="1000" class="aurora-input" />
                        <p v-if="form.errors.price_per_hour" class="mt-1 text-sm text-red-400">{{ form.errors.price_per_hour }}</p>
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-300">Fasilitas (pisahkan dengan koma)</label>
                    <input v-model="form.facilities" type="text" placeholder="AC, WiFi, Proyektor" class="aurora-input" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-300">Deskripsi (opsional)</label>
                    <textarea v-model="form.description" rows="2" class="aurora-input"></textarea>
                </div>
                <div>
                    <div class="mb-2 flex items-center justify-between">
                        <label class="text-sm font-medium text-slate-300">Foto Ruangan</label>
                        <button type="button" class="text-xs font-medium text-indigo-400 hover:underline" @click="addPhotoRow">+ Tambah Foto</button>
                    </div>

                    <div v-for="(row, i) in photoRows" :key="i" class="mb-2 grid grid-cols-12 gap-2 items-center">
                        <div class="col-span-9 flex items-center gap-2">
                            <template v-if="row.type === 'existing'">
                                <div class="h-10 w-14 shrink-0 overflow-hidden rounded-lg border border-white/10 bg-white/5">
                                    <img :src="`/${row.path}`" class="h-full w-full object-cover" />
                                </div>
                                <span class="text-xs text-slate-500">Foto tersimpan</span>
                            </template>
                            <template v-else>
                                <div v-if="row.preview" class="h-10 w-14 shrink-0 overflow-hidden rounded-lg border border-white/10 bg-white/5">
                                    <img :src="row.preview" class="h-full w-full object-cover" />
                                </div>
                                <input
                                    type="file"
                                    accept="image/jpeg,image/png,image/webp"
                                    class="block w-full text-xs text-slate-400 file:mr-2 file:rounded-lg file:border-0 file:bg-indigo-600 file:px-2.5 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-indigo-700"
                                    @change="onRowFileChange(i, $event)"
                                />
                            </template>
                        </div>
                        <button type="button" class="col-span-3 rounded-lg border border-white/10 bg-white/5 text-sm text-red-400 hover:bg-red-500/10" @click="removePhotoRow(i)">Hapus</button>
                    </div>
                    <p v-if="!photoRows.length" class="text-xs text-slate-500">Belum ada foto dipilih. Klik "+ Tambah Foto" untuk menambah.</p>

                    <p v-if="form.errors.images" class="mt-1 text-sm text-red-400">{{ form.errors.images }}</p>
                    <p class="mt-1 text-xs text-slate-500">JPG/PNG/WebP, maks 2 MB per file. Hapus baris untuk membuang foto tertentu tanpa memengaruhi foto lain.</p>
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-400">
                    <input v-model="form.is_active" type="checkbox" class="rounded border-white/20 bg-white/5 text-indigo-600 focus:ring-indigo-500" /> Aktif
                </label>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" class="aurora-btn-ghost rounded-lg px-4 py-2 text-sm" @click="viewState = 'index'">Batal</button>
                    <button type="submit" :disabled="form.processing" class="aurora-btn-primary rounded-lg px-4 py-2 text-sm font-semibold disabled:opacity-60">Simpan</button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
