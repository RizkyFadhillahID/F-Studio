<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import { formatRupiah } from '../../lib/payment';

const props = defineProps({
    rooms: { type: Array, default: () => [] },
    equipment: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search ?? '');
const selectedRoom = ref(null);
const viewState = ref('index'); // 'index' | 'book'

const form = useForm({
    room_id: '', customer_name: '', customer_phone: '', title: '',
    start_datetime: '', end_datetime: '', notes: '',
    equipment_items: [],
});

function openBook(room) {
    selectedRoom.value = room;
    form.reset();
    form.clearErrors();
    form.room_id = room.id;
    form.equipment_items = [];
    viewState.value = 'book';
}

function addEquipment() {
    form.equipment_items.push({ equipment_id: '', quantity: 1 });
}
function removeEquipment(i) {
    form.equipment_items.splice(i, 1);
}

function submit() {
    form.post('/receptionist/bookings', {
        preserveScroll: true,
        onSuccess: () => { viewState.value = 'index'; },
    });
}

function doSearch() {
    router.get('/receptionist/rooms', { search: search.value }, { preserveState: true, replace: true });
}
</script>

<template>
    <Head title="Pemesanan Ruangan" />
    <AppLayout>
        <template #header><h1 class="text-base font-semibold text-white">Pemesanan Ruangan (Walk-in)</h1></template>

        <div v-if="viewState === 'index'">
            <form class="mb-4 flex gap-2" @submit.prevent="doSearch">
                <input v-model="search" type="text" placeholder="Cari ruangan…" class="aurora-input max-w-xs" />
                <button type="submit" class="aurora-btn-ghost rounded-lg px-3 py-2 text-sm">Cari</button>
            </form>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <div v-for="room in rooms" :key="room.id" class="group flex flex-col overflow-hidden rounded-2xl border border-white/[0.08] bg-white/[0.03] transition hover:border-indigo-500/30">
                    <div class="relative h-40 shrink-0 overflow-hidden bg-white/5">
                        <img v-if="room.image" :src="`/${room.image}`" :alt="room.name" class="h-full w-full object-cover transition duration-300 group-hover:scale-105" loading="lazy" />
                        <div v-else class="flex h-full items-center justify-center text-slate-600">
                            <svg class="h-12 w-12" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18" /></svg>
                        </div>
                        <span class="absolute left-3 top-3 rounded-full bg-black/60 px-2.5 py-1 text-[11px] font-medium text-white backdrop-blur">{{ room.code }}</span>
                        <span class="absolute right-3 top-3 rounded-full bg-white/90 px-2.5 py-1 text-[11px] font-medium text-slate-700 backdrop-blur">{{ room.capacity }} orang</span>
                    </div>
                    <div class="flex flex-1 flex-col p-5">
                        <h3 class="flex-1 font-semibold text-white">{{ room.name }}</h3>
                        <p class="text-xs text-slate-500">{{ formatRupiah(room.price_per_hour) }}/jam</p>
                        <button class="aurora-btn-primary mt-3 w-full rounded-xl px-3 py-2.5 text-sm font-semibold" @click="openBook(room)">
                            Pesan untuk Pelanggan
                        </button>
                    </div>
                </div>
                <p v-if="!rooms.length" class="col-span-full text-center text-sm text-slate-500">Tidak ada ruangan tersedia.</p>
            </div>
        </div>

        <div v-else-if="viewState === 'book'" class="space-y-6">
            <h2 class="text-lg font-semibold text-white mb-4">Pemesanan Walk-in — {{ selectedRoom?.name }}</h2>
            <form class="space-y-4" @submit.prevent="submit">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-300">Nama Pelanggan</label>
                        <input v-model="form.customer_name" type="text" class="aurora-input" />
                        <p v-if="form.errors.customer_name" class="mt-1 text-sm text-red-400">{{ form.errors.customer_name }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-300">Telepon</label>
                        <input v-model="form.customer_phone" type="text" class="aurora-input" />
                        <p v-if="form.errors.customer_phone" class="mt-1 text-sm text-red-400">{{ form.errors.customer_phone }}</p>
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
                    <div class="mb-2 flex items-center justify-between">
                        <label class="text-sm font-medium text-slate-300">Peralatan (opsional)</label>
                        <button type="button" class="text-xs font-medium text-indigo-400 hover:underline" @click="addEquipment">+ Tambah alat</button>
                    </div>
                    <div v-for="(item, i) in form.equipment_items" :key="i" class="mb-2 grid grid-cols-12 gap-2">
                        <select v-model="item.equipment_id" class="aurora-input col-span-7 !py-2">
                            <option value="" disabled>Pilih alat…</option>
                            <option v-for="e in equipment" :key="e.id" :value="e.id">{{ e.name }} (tersedia {{ e.quantity_available }}, {{ formatRupiah(e.price_per_day) }}/hari)</option>
                        </select>
                        <input v-model="item.quantity" type="number" min="1" :max="equipment.find(e => e.id === item.equipment_id)?.quantity_available" class="aurora-input col-span-3 !py-2" />
                        <button type="button" class="col-span-2 rounded-lg border border-white/10 bg-white/5 text-sm text-red-400 hover:bg-red-500/10" @click="removeEquipment(i)">Hapus</button>
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-300">Catatan (opsional)</label>
                    <textarea v-model="form.notes" rows="2" class="aurora-input"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-white/[0.07]">
                    <button type="button" class="aurora-btn-ghost rounded-lg px-4 py-2 text-sm" @click="viewState = 'index'">Batal</button>
                    <button type="submit" :disabled="form.processing" class="aurora-btn-primary rounded-lg px-4 py-2 text-sm font-semibold disabled:opacity-60">Buat Pemesanan</button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
