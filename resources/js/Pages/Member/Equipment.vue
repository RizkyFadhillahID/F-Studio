<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import MemberLayout from '../../Layouts/MemberLayout.vue';
import { formatRupiah } from '../../lib/payment';

const props = defineProps({
    equipment: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search ?? '');
const today = new Date().toISOString().slice(0, 10);

// ── Keranjang pinjaman ────────────────────────────────────────────────
const cart = ref([]); // [{equipment, quantity}]
const viewState = ref('index'); // 'index' | 'detail' | 'checkout'

// ── Detail peralatan ────────────────────────────────────────────────
const detailItem = ref(null);

function openDetail(item) {
    detailItem.value = item;
    viewState.value = 'detail';
}

function inCart(item) {
    return cart.value.some((c) => c.equipment.id === item.id);
}
function removeFromCart(id) {
    cart.value = cart.value.filter((c) => c.equipment.id !== id);
}
/** Klik pertama menambah ke keranjang, klik lagi membatalkan pilihan. */
function toggleCart(item) {
    if (inCart(item)) {
        removeFromCart(item.id);
    } else if (item.quantity_available > 0) {
        cart.value.push({ equipment: item, quantity: 1 });
    }
}
const cartCount = computed(() => cart.value.reduce((s, c) => s + Number(c.quantity || 0), 0));

// ── Form pengajuan ────────────────────────────────────────────────────
const form = useForm({
    purpose: '', loan_date: today, due_date: today, notes: '', items: [],
});

function openForm() {
    form.clearErrors();
    viewState.value = 'checkout';
}

function submit() {
    form.items = cart.value.map((c) => ({ equipment_id: c.equipment.id, quantity: Number(c.quantity) }));
    form.post('/member/loans', {
        preserveScroll: true,
        onSuccess: () => {
            viewState.value = 'index';
            cart.value = [];
        },
    });
}

function applyFilter(categoryId) {
    router.get('/member/equipment', {
        search: search.value || undefined,
        category_id: categoryId ?? props.filters.category_id ?? undefined,
    }, { preserveState: true, replace: true });
}
</script>

<template>
    <Head title="Peralatan" />
    <MemberLayout>
        <template #header><h1 class="text-base font-semibold text-white">Peminjaman Peralatan</h1></template>

        <div v-if="viewState === 'index'">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <form class="flex gap-2" @submit.prevent="applyFilter()">
                    <input v-model="search" type="text" placeholder="Cari alat…" class="aurora-input sm:w-64" />
                    <button type="submit" class="aurora-btn-ghost rounded-lg px-3 py-2 text-sm">Cari</button>
                </form>

                <div class="flex flex-wrap gap-1.5">
                    <button
                        class="rounded-lg px-3 py-1.5 text-xs font-semibold transition"
                        :class="!props.filters.category_id ? 'bg-indigo-600 text-white' : 'bg-white/5 text-slate-400 hover:bg-white/10'"
                        @click="applyFilter(null)"
                    >
                        Semua
                    </button>
                    <button
                        v-for="c in categories"
                        :key="c.id"
                        class="rounded-lg px-3 py-1.5 text-xs font-semibold transition"
                        :class="props.filters.category_id == c.id ? 'bg-indigo-600 text-white' : 'bg-white/5 text-slate-400 hover:bg-white/10'"
                        @click="applyFilter(c.id)"
                    >
                        {{ c.name }}
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                <div v-for="item in equipment" :key="item.id" class="group flex flex-col overflow-hidden rounded-2xl border border-white/[0.08] bg-white/[0.03] transition hover:border-indigo-500/30">
                    <button class="relative block h-36 w-full shrink-0 cursor-pointer overflow-hidden bg-white/5" @click="openDetail(item)">
                        <img v-if="item.image" :src="`/${item.image}`" :alt="item.name" class="h-full w-full object-cover transition duration-300 group-hover:scale-105" loading="lazy" />
                        <div v-else class="flex h-full items-center justify-center text-slate-600">
                            <svg class="h-10 w-10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 21m0-12h.008v.008H9V9zm0 6h.008v.008H9V15zm0-3h.008v.008H9v-.008zm3-3h.008v.008H12V9zm0 6h.008v.008H12V15zm0-3h.008v.008H12v-.008zm3-3h.008v.008H15V9zm0 6h.008v.008H15V15zm0-3h.008v.008H15v-.008zm3-3h.008v.008H18V9zm0 6h.008v.008H18V15zm0-3h.008v.008H18v-.008zM10.5 4.5h3A1.5 1.5 0 0115 6v3h-6V6a1.5 1.5 0 011.5-1.5z" /></svg>
                        </div>
                        <span class="absolute left-2.5 top-2.5 rounded-full bg-black/60 px-2 py-0.5 text-[10px] font-medium text-indigo-300 backdrop-blur">{{ item.category?.name }}</span>
                    </button>
                    <div class="flex flex-1 flex-col p-4">
                        <button class="text-left font-medium text-white line-clamp-1 hover:text-indigo-300 hover:underline" @click="openDetail(item)">{{ item.name }}</button>
                        <p class="mt-1 text-xs text-slate-500 font-mono">Kode: {{ item.code }}</p>
                        <p class="mt-1 text-sm font-medium text-indigo-300">{{ formatRupiah(item.price_per_day) }}<span class="text-xs text-slate-500">/hari</span></p>
                        <div class="mt-auto pt-3 flex items-center justify-between gap-2">
                            <div class="text-[11px]">
                                <span class="block text-slate-500">Tersedia</span>
                                <span class="font-semibold text-white">{{ item.quantity_available }} unit</span>
                            </div>
                            <button
                                v-if="item.quantity_available > 0"
                                class="rounded-lg px-2.5 py-1.5 text-xs font-semibold transition"
                                :class="inCart(item) ? 'border border-indigo-500/40 bg-indigo-500/20 text-indigo-300 hover:bg-red-500/10 hover:border-red-500/30 hover:text-red-300' : 'aurora-btn-primary'"
                                @click="toggleCart(item)"
                            >
                                {{ inCart(item) ? '✕ Batalkan' : '+ Pilih' }}
                            </button>
                            <span v-else class="text-xs font-semibold text-red-400">Habis</span>
                        </div>
                    </div>
                </div>
                <p v-if="!equipment.length" class="col-span-full text-center text-sm text-slate-500">Tidak ada peralatan tersedia.</p>
            </div>

            <!-- Bar keranjang melayang -->
            <Transition name="slide-up">
                <div v-if="cart.length" class="fixed inset-x-0 bottom-4 z-30 flex justify-center px-4">
                    <div class="flex w-full max-w-lg items-center justify-between gap-4 rounded-2xl border border-white/10 bg-[#12141f]/95 px-4 py-3 shadow-2xl shadow-black/40 backdrop-blur-xl">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-white">{{ cart.length }} alat dipilih ({{ cartCount }} unit)</p>
                            <p class="truncate text-xs text-slate-500">{{ cart.map((c) => c.equipment.name).join(', ') }}</p>
                        </div>
                        <button class="aurora-btn-primary shrink-0 rounded-xl px-5 py-2.5 text-sm font-semibold" @click="openForm">
                            Ajukan
                        </button>
                    </div>
                </div>
            </Transition>
        </div>

        <!-- Detail peralatan -->
        <div v-else-if="viewState === 'detail'" class="space-y-6">
            <h2 class="text-lg font-semibold text-white mb-2">{{ detailItem?.name ?? 'Detail Peralatan' }}</h2>
            <div v-if="detailItem" class="space-y-4">
                <div class="overflow-hidden rounded-xl bg-slate-950/40 h-64 w-full flex items-center justify-center p-2">
                    <img v-if="detailItem.image" :src="`/${detailItem.image}`" :alt="detailItem.name" class="max-h-full max-w-full object-contain rounded-lg" />
                    <svg v-else class="h-16 w-16 text-slate-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 21m0-12h.008v.008H9V9zm0 6h.008v.008H9V15zm0-3h.008v.008H9v-.008zm3-3h.008v.008H12V9zm0 6h.008v.008H12V15zm0-3h.008v.008H12v-.008zm3-3h.008v.008H15V9zm0 6h.008v.008H15V15zm0-3h.008v.008H15v-.008zm3-3h.008v.008H18V9zm0 6h.008v.008H18V15zm0-3h.008v.008H18v-.008zM10.5 4.5h3A1.5 1.5 0 0115 6v3h-6V6a1.5 1.5 0 011.5-1.5z" /></svg>
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
                        <p class="text-xs text-slate-500">Harga</p>
                        <p class="font-medium text-white">{{ formatRupiah(detailItem.price_per_day) }}/hari</p>
                    </div>
                    <div class="rounded-lg bg-white/5 p-3">
                        <p class="text-xs text-slate-500">Ketersediaan</p>
                        <p class="font-medium" :class="detailItem.quantity_available > 0 ? 'text-emerald-400' : 'text-red-400'">
                            {{ detailItem.quantity_available }} dari {{ detailItem.quantity_total }} unit
                        </p>
                    </div>
                </div>

                <div v-if="detailItem.description">
                    <p class="mb-1 text-xs font-medium uppercase tracking-wide text-slate-500">Deskripsi</p>
                    <p class="text-sm leading-relaxed text-slate-400">{{ detailItem.description }}</p>
                </div>

                <div class="flex justify-end gap-2 border-t border-white/[0.07] pt-4">
                    <button class="aurora-btn-ghost rounded-lg px-4 py-2 text-sm" @click="viewState = 'index'">Kembali</button>
                    <button
                        v-if="detailItem.quantity_available > 0"
                        class="rounded-lg px-4 py-2 text-sm font-semibold transition"
                        :class="inCart(detailItem) ? 'border border-indigo-500/40 bg-indigo-500/20 text-indigo-300 hover:bg-red-500/10 hover:border-red-500/30 hover:text-red-300' : 'aurora-btn-primary'"
                        @click="toggleCart(detailItem)"
                    >
                        {{ inCart(detailItem) ? '✕ Batalkan Pilihan' : '+ Pilih Alat Ini' }}
                    </button>
                    <span v-else class="rounded-lg border border-red-500/20 bg-red-500/10 px-4 py-2 text-sm font-semibold text-red-400">Stok Habis</span>
                </div>
            </div>
        </div>

        <!-- Checkout page view -->
        <div v-else-if="viewState === 'checkout'" class="space-y-6">
            <h2 class="text-lg font-semibold text-white mb-2">Ajukan Peminjaman</h2>
            <form class="space-y-4" @submit.prevent="submit">
                <div class="rounded-xl bg-white/5 p-3">
                    <p class="mb-2 text-xs font-medium uppercase tracking-wide text-slate-500">Alat yang dipinjam</p>
                    <div v-for="c in cart" :key="c.equipment.id" class="mb-2 flex items-center gap-3">
                        <img v-if="c.equipment.image" :src="`/${c.equipment.image}`" class="h-9 w-12 rounded-lg object-cover" />
                        <span class="flex-1 truncate text-sm text-slate-300">{{ c.equipment.name }} <span class="text-xs text-slate-500">({{ formatRupiah(c.equipment.price_per_day) }}/hari)</span></span>
                        <input v-model="c.quantity" type="number" min="1" :max="c.equipment.quantity_available" class="aurora-input w-16 !py-1 text-center" />
                        <button type="button" class="text-xs text-red-400 hover:underline" @click="removeFromCart(c.equipment.id)">Hapus</button>
                    </div>
                    <p v-if="form.errors.items" class="text-sm text-red-400">{{ form.errors.items }}</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-300">Keperluan</label>
                    <input v-model="form.purpose" type="text" class="aurora-input" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-300">Tgl Pinjam</label>
                        <input v-model="form.loan_date" type="date" class="aurora-input" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-300">Tgl Kembali</label>
                        <input v-model="form.due_date" type="date" class="aurora-input" />
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-300">Catatan (opsional)</label>
                    <textarea v-model="form.notes" rows="2" class="aurora-input"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-white/[0.07]">
                    <button type="button" class="aurora-btn-ghost rounded-lg px-4 py-2 text-sm" @click="viewState = 'index'">Batal</button>
                    <button type="submit" :disabled="form.processing || !cart.length" class="aurora-btn-primary rounded-lg px-4 py-2 text-sm font-semibold disabled:opacity-60">
                        {{ form.processing ? 'Mengajukan…' : 'Ajukan Peminjaman' }}
                    </button>
                </div>
            </form>
        </div>
    </MemberLayout>
</template>

<style scoped>
.slide-up-enter-active, .slide-up-leave-active { transition: transform 0.2s ease, opacity 0.2s ease; }
.slide-up-enter-from, .slide-up-leave-to { transform: translateY(100%); opacity: 0; }
</style>
