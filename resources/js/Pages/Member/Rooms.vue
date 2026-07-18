<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import MemberLayout from '../../Layouts/MemberLayout.vue';
import { formatRupiah } from '../../lib/payment';

const props = defineProps({
    rooms: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search ?? '');
const selectedRoom = ref(null);
const viewState = ref('index'); // 'index' | 'detail' | 'book'

// Detail page + galeri
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

const form = useForm({ room_id: '', title: '', start_datetime: '', end_datetime: '', notes: '' });

function openBook(room) {
    selectedRoom.value = room;
    form.reset();
    form.clearErrors();
    form.room_id = room.id;
    viewState.value = 'book';
}

function submit() {
    form.post('/member/bookings', {
        preserveScroll: true,
        onSuccess: () => { viewState.value = 'index'; },
    });
}

function doSearch() {
    router.get('/member/rooms', { search: search.value }, { preserveState: true, replace: true });
}
</script>

<template>
    <Head title="Ruangan" />
    <MemberLayout>
        <template #header><h1 class="text-base font-semibold text-white">Pemesanan Ruangan</h1></template>

        <div v-if="viewState === 'index'">
            <form class="mb-4 flex gap-2" @submit.prevent="doSearch">
                <input v-model="search" type="text" placeholder="Cari ruangan…" class="aurora-input max-w-xs" />
                <button type="submit" class="aurora-btn-ghost rounded-lg px-3 py-2 text-sm">Cari</button>
            </form>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <div v-for="room in rooms" :key="room.id" class="group flex flex-col overflow-hidden rounded-2xl border border-white/[0.08] bg-white/[0.03] transition hover:border-indigo-500/30">
                    <button class="relative block h-44 w-full shrink-0 cursor-pointer overflow-hidden bg-white/5" @click="openDetail(room)">
                        <img v-if="room.image" :src="`/${room.image}`" :alt="room.name" class="h-full w-full object-cover transition duration-300 group-hover:scale-105" loading="lazy" />
                        <div v-else class="flex h-full items-center justify-center text-slate-600">
                            <svg class="h-12 w-12" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18" /></svg>
                        </div>
                        <span class="absolute left-3 top-3 rounded-full bg-black/60 px-2.5 py-1 text-[11px] font-medium text-white backdrop-blur">{{ room.code }}</span>
                        <span class="absolute right-3 top-3 rounded-full bg-white/90 px-2.5 py-1 text-[11px] font-medium text-slate-700 backdrop-blur">{{ room.capacity }} orang</span>
                        <span v-if="galleryOf(room).length > 1" class="absolute bottom-3 right-3 flex items-center gap-1 rounded-full bg-black/60 px-2.5 py-1 text-[11px] font-medium text-white backdrop-blur">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z" /></svg>
                            {{ galleryOf(room).length }}
                        </span>
                    </button>
                    <div class="flex flex-1 flex-col p-5">
                        <button class="text-left font-semibold text-white hover:text-indigo-300 hover:underline" @click="openDetail(room)">{{ room.name }}</button>
                        <p class="mt-0.5 text-sm font-medium text-indigo-300">{{ formatRupiah(room.price_per_hour) }}<span class="text-xs text-slate-500">/jam</span></p>
                        <div class="flex-1">
                            <p v-if="room.description" class="mt-1 line-clamp-2 text-sm text-slate-400">{{ room.description }}</p>
                            <div v-if="Array.isArray(room.facilities) && room.facilities.length" class="mt-3 flex flex-wrap gap-1.5">
                                <span v-for="f in room.facilities.slice(0, 4)" :key="f" class="rounded-full bg-white/5 px-2 py-0.5 text-[11px] text-slate-400">{{ f }}</span>
                                <span v-if="room.facilities.length > 4" class="rounded-full bg-white/5 px-2 py-0.5 text-[11px] text-slate-500">+{{ room.facilities.length - 4 }}</span>
                            </div>
                        </div>
                        <button class="aurora-btn-primary mt-4 w-full rounded-xl px-3 py-2.5 text-sm font-semibold" @click="openBook(room)">
                            Pesan Ruangan Ini
                        </button>
                    </div>
                </div>
                <p v-if="!rooms.length" class="col-span-full text-center text-sm text-slate-500">Tidak ada ruangan tersedia.</p>
            </div>
        </div>

        <!-- Detail page dengan galeri -->
        <div v-else-if="viewState === 'detail'" class="space-y-6">
            <h2 class="text-lg font-semibold text-white mb-2">{{ detailRoom?.name ?? 'Detail Ruangan' }}</h2>
            <div v-if="detailRoom" class="space-y-4">
                <div v-if="galleryOf(detailRoom).length">
                    <div class="overflow-hidden rounded-xl bg-slate-950/40 h-64 w-full flex items-center justify-center p-2">
                        <img :src="`/${galleryOf(detailRoom)[activeImg]}`" :alt="detailRoom.name" class="max-h-full max-w-full object-contain rounded-lg" />
                    </div>
                    <div v-if="galleryOf(detailRoom).length > 1" class="mt-2 flex gap-2">
                        <button
                            v-for="(img, i) in galleryOf(detailRoom)"
                            :key="img"
                            class="h-14 w-20 overflow-hidden rounded-lg ring-2 transition"
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
                        <p class="text-xs text-slate-500">Harga</p>
                        <p class="font-medium text-white">{{ formatRupiah(detailRoom.price_per_hour) }}/jam</p>
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
                    <button class="aurora-btn-ghost rounded-lg px-4 py-2 text-sm" @click="viewState = 'index'">Kembali</button>
                    <button class="aurora-btn-primary rounded-lg px-4 py-2 text-sm font-semibold" @click="openBook(detailRoom)">Pesan Ruangan Ini</button>
                </div>
            </div>
        </div>

        <div v-else-if="viewState === 'book'" class="space-y-6">
            <h2 class="text-lg font-semibold text-white mb-4">Pesan — {{ selectedRoom?.name }}</h2>
            <form class="space-y-4" @submit.prevent="submit">
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
                    <textarea v-model="form.notes" rows="3" class="aurora-input"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-white/[0.07]">
                    <button type="button" class="aurora-btn-ghost rounded-lg px-4 py-2 text-sm" @click="viewState = 'index'">Batal</button>
                    <button type="submit" :disabled="form.processing" class="aurora-btn-primary rounded-lg px-5 py-2 text-sm font-semibold disabled:opacity-60">Pesan Sekarang</button>
                </div>
            </form>
        </div>
    </MemberLayout>
</template>
