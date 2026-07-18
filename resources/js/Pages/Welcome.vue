<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    rooms: { type: Array, default: () => [] },
    equipment: { type: Array, default: () => [] },
});

const page = usePage();
const appName = computed(() => page.props.appName ?? 'F-Studio');

const loginOpen = ref(false);
const dropdownRef = ref(null);

function onClickOutside(e) {
    if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
        loginOpen.value = false;
    }
}
onMounted(() => document.addEventListener('click', onClickOutside));
onUnmounted(() => document.removeEventListener('click', onClickOutside));

const portals = [
    { key: 'member', label: 'Member', desc: 'Pemesanan ruangan mandiri', color: 'text-emerald-400' },
    { key: 'receptionist', label: 'Resepsionis', desc: 'Front desk & walk-in', color: 'text-amber-400' },
    { key: 'admin', label: 'Admin', desc: 'Kelola operasional studio', color: 'text-indigo-400' },
];

const benefits = [
    {
        title: 'Pemesanan dalam hitungan menit',
        desc: 'Pilih ruangan, tentukan jadwal, ajukan, selesai. Tidak perlu telepon atau antre, semua dari genggaman Anda.',
        icon: 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
    },
    {
        title: 'Jadwal selalu pasti',
        desc: 'Sekali jadwal Anda dikonfirmasi, ruangan itu milik Anda. Tidak akan ada yang menyerobot slot yang sama.',
        icon: 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    },
    {
        title: 'Peralatan pro siap pakai',
        desc: 'Kamera mirrorless, lensa premium, lighting, audio, sampai drone, tinggal pinjam, semua terawat dan siap produksi.',
        icon: 'M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316zM16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z',
    },
    {
        title: 'Ambil & kembalikan tanpa ribet',
        desc: 'Tunjukkan kode peminjaman, alat langsung di tangan. Pengembalian tercatat rapi, tanpa formulir kertas.',
        icon: 'M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z',
    },
    {
        title: 'Harga komunitas, kualitas studio',
        desc: 'Dibangun untuk komunitas kreatif lokal. Nikmati fasilitas profesional tanpa biaya sewa studio komersial.',
        icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    },
    {
        title: 'Selalu tahu status pengajuan',
        desc: 'Disetujui? Ditolak? Anda langsung tahu. Riwayat pemesanan dan peminjaman tersimpan rapi di dashboard Anda.',
        icon: 'M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0',
    },
];

const steps = [
    { title: 'Daftar gratis', desc: 'Buat akun member dalam satu menit, langsung dapat ID member tanpa biaya apa pun.' },
    { title: 'Pilih & pesan', desc: 'Lihat foto ruangan, cek fasilitasnya, tentukan jadwal, lalu ajukan pemesanan Anda.' },
    { title: 'Datang & berkarya', desc: 'Pemesanan dikonfirmasi, tinggal datang. Ruangan dan peralatan siap menyambut Anda.' },
];
</script>

<template>
    <Head title="Ruang Kreatif untuk Karya Terbaikmu" />

    <div class="min-h-screen bg-slate-950 text-slate-300">
        <!-- Navbar -->
        <header class="sticky top-0 z-30 border-b border-white/5 bg-slate-950/80 backdrop-blur">
            <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-6">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-600 text-sm font-bold text-white shadow-lg shadow-indigo-600/30">F</div>
                    <span class="text-base font-bold text-white">{{ appName }}</span>
                </div>
                <nav class="hidden items-center gap-8 text-sm text-slate-400 md:flex">
                    <a href="#kenapa" class="transition hover:text-white">Kenapa {{ appName }}?</a>
                    <a href="#ruangan" class="transition hover:text-white">Ruangan</a>
                    <a href="#peralatan" class="transition hover:text-white">Peralatan</a>
                    <a href="#mulai" class="transition hover:text-white">Cara Mulai</a>
                </nav>
                <div class="flex items-center gap-3">
                    <!-- Dropdown login per portal -->
                    <div
                        ref="dropdownRef"
                        class="relative"
                        @mouseenter="loginOpen = true"
                        @mouseleave="loginOpen = false"
                    >
                        <button
                            class="flex items-center gap-1.5 rounded-lg border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-white transition hover:bg-white/10"
                        >
                            Masuk
                            <svg class="h-4 w-4 transition" :class="loginOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                        </button>
                        <Transition name="fade">
                            <div v-if="loginOpen" class="absolute right-0 mt-2 w-60 overflow-hidden rounded-xl border border-white/10 bg-slate-900 p-1.5 shadow-2xl">
                                <Link
                                    v-for="p in portals"
                                    :key="p.key"
                                    :href="`/login?portal=${p.key}`"
                                    class="flex items-center gap-3 rounded-lg px-3 py-2.5 transition hover:bg-white/5"
                                >
                                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/5" :class="p.color">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                                    </span>
                                    <span>
                                        <span class="block text-sm font-semibold text-white">{{ p.label }}</span>
                                        <span class="block text-[11px] text-slate-500">{{ p.desc }}</span>
                                    </span>
                                </Link>
                            </div>
                        </Transition>
                    </div>
                    <Link href="/register" class="hidden rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 transition hover:bg-indigo-500 sm:block">Daftar Gratis</Link>
                </div>
            </div>
        </header>

        <!-- Hero -->
        <section class="relative overflow-hidden">
            <div class="absolute -top-40 left-1/2 h-[32rem] w-[52rem] -translate-x-1/2 rounded-full bg-indigo-600/20 blur-3xl"></div>
            <div class="relative mx-auto max-w-6xl px-6 pb-24 pt-20 text-center sm:pt-28">
                <p class="mx-auto mb-6 inline-flex items-center gap-2 rounded-full border border-indigo-500/30 bg-indigo-500/10 px-4 py-1.5 text-xs font-medium text-indigo-300">
                     Ruang kreatif untuk komunitasmu
                </p>
                <h1 class="mx-auto max-w-3xl text-4xl font-bold leading-tight tracking-tight text-white sm:text-5xl">
                    Wujudkan karya terbaikmu di
                    <span class="bg-gradient-to-r from-indigo-400 via-violet-400 to-indigo-400 bg-clip-text text-transparent">studio impian</span>
                </h1>
                <p class="mx-auto mt-6 max-w-2xl text-base leading-relaxed text-slate-400 sm:text-lg">
                    Studio foto, ruang podcast, peralatan profesional, semuanya bisa kamu pesan
                    dalam hitungan menit. Tanpa antre, tanpa ribet, tanpa biaya keanggotaan.
                </p>
                <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                    <Link href="/register" class="w-full rounded-xl bg-indigo-600 px-8 py-3.5 text-sm font-semibold text-white shadow-xl shadow-indigo-600/30 transition hover:bg-indigo-500 sm:w-auto">
                        Mulai Pesan Sekarang, Gratis
                    </Link>
                    <a href="#kenapa" class="w-full rounded-xl border border-white/10 bg-white/5 px-8 py-3.5 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/10 sm:w-auto">
                        Lihat Fasilitasnya ↓
                    </a>
                </div>
                <p class="mt-6 text-xs text-slate-500">Gratis untuk komunitas · Tanpa kartu kredit · Langsung aktif</p>

                <!-- Stats -->
                <div class="mx-auto mt-20 grid max-w-3xl grid-cols-2 gap-px overflow-hidden rounded-2xl border border-white/10 bg-white/10 sm:grid-cols-4">
                    <div v-for="s in [['6+', 'Ruangan Kreatif'], ['16+', 'Peralatan Pro'], ['100%', 'Jadwal Pasti'], ['1 mnt', 'Daftar Member']]" :key="s[1]" class="bg-slate-950 px-6 py-6">
                        <p class="text-2xl font-bold text-white">{{ s[0] }}</p>
                        <p class="mt-1 text-xs text-slate-500">{{ s[1] }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Benefits -->
        <section id="kenapa" class="mx-auto max-w-6xl px-6 py-24">
            <div class="mb-14 text-center">
                <h2 class="text-3xl font-bold text-white">Kenapa berkarya di {{ appName }}?</h2>
                <p class="mt-3 text-slate-400">Karena waktu kamu lebih berharga untuk berkarya, bukan mengurus administrasi.</p>
            </div>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div v-for="b in benefits" :key="b.title" class="group rounded-2xl border border-white/5 bg-white/[0.02] p-6 transition hover:border-indigo-500/30 hover:bg-indigo-500/[0.04]">
                    <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-400 ring-1 ring-indigo-500/20 transition group-hover:bg-indigo-500/20">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" :d="b.icon" /></svg>
                    </div>
                    <h3 class="text-base font-semibold text-white">{{ b.title }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-400">{{ b.desc }}</p>
                </div>
            </div>
        </section>

        <!-- Katalog Ruangan -->
        <section id="ruangan" class="border-t border-white/5 bg-white/[0.02]">
            <div class="mx-auto max-w-6xl px-6 py-24">
                <div class="mb-12 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-end">
                    <div>
                        <h2 class="text-3xl font-bold text-white">Pilih ruanganmu</h2>
                        <p class="mt-3 text-slate-400">{{ rooms.length }} ruangan kreatif siap dipesan hari ini.</p>
                    </div>
                    <Link href="/login?portal=member" class="text-sm font-semibold text-indigo-400 transition hover:text-indigo-300">Lihat semua & pesan</Link>
                </div>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="room in rooms" :key="room.id" class="group flex flex-col overflow-hidden rounded-2xl border border-white/5 bg-slate-900/60 transition hover:border-indigo-500/30">
                        <div class="relative h-44 shrink-0 overflow-hidden bg-slate-800">
                            <img v-if="room.image" :src="`/${room.image}`" :alt="room.name" class="h-full w-full object-cover transition duration-300 group-hover:scale-105" loading="lazy" />
                            <span class="absolute right-3 top-3 rounded-full bg-slate-950/70 px-2.5 py-1 text-[11px] font-medium text-white backdrop-blur">{{ room.capacity }} orang</span>
                        </div>
                        <div class="flex flex-1 flex-col p-5">
                            <h3 class="font-semibold text-white">{{ room.name }}</h3>
                            <div class="flex-1">
                                <p v-if="room.description" class="mt-1 line-clamp-2 text-sm text-slate-400">{{ room.description }}</p>
                                <div v-if="Array.isArray(room.facilities) && room.facilities.length" class="mt-3 flex flex-wrap gap-1.5">
                                    <span v-for="f in room.facilities.slice(0, 3)" :key="f" class="rounded-full bg-white/5 px-2 py-0.5 text-[11px] text-slate-400">{{ f }}</span>
                                    <span v-if="room.facilities.length > 3" class="rounded-full bg-white/5 px-2 py-0.5 text-[11px] text-slate-500">+{{ room.facilities.length - 3 }}</span>
                                </div>
                            </div>
                            <Link href="/login?portal=member" class="mt-4 block w-full rounded-xl bg-indigo-600 px-3 py-2.5 text-center text-sm font-semibold text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-500">
                                Pesan Sekarang
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Katalog Peralatan -->
        <section id="peralatan" class="border-t border-white/5">
            <div class="mx-auto max-w-6xl px-6 py-24">
                <div class="mb-12 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-end">
                    <div>
                        <h2 class="text-3xl font-bold text-white">Peralatan siap pinjam</h2>
                        <p class="mt-3 text-slate-400">Gear profesional yang terawat, tinggal pinjam, langsung produksi.</p>
                    </div>
                    <Link href="/login?portal=member" class="text-sm font-semibold text-indigo-400 transition hover:text-indigo-300">Pinjam sebagai member</Link>
                </div>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                    <div v-for="item in equipment" :key="item.id" class="group overflow-hidden rounded-2xl border border-white/5 bg-slate-900/60 transition hover:border-indigo-500/30">
                        <div class="relative h-32 overflow-hidden bg-slate-800">
                            <img v-if="item.image" :src="`/${item.image}`" :alt="item.name" class="h-full w-full object-cover transition duration-300 group-hover:scale-105" loading="lazy" />
                            <span
                                class="absolute right-2 top-2 rounded-full px-2 py-0.5 text-[10px] font-medium backdrop-blur"
                                :class="item.quantity_available > 0 ? 'bg-emerald-500/80 text-white' : 'bg-slate-700/80 text-slate-300'"
                            >{{ item.quantity_available > 0 ? `${item.quantity_available} tersedia` : 'Habis' }}</span>
                        </div>
                        <div class="p-4">
                            <p class="text-[11px] text-indigo-400">{{ item.category?.name ?? '—' }}</p>
                            <h3 class="mt-0.5 truncate text-sm font-semibold text-white">{{ item.name }}</h3>
                            <Link href="/login?portal=member" class="mt-3 block w-full rounded-lg border border-white/10 px-2 py-1.5 text-center text-xs font-semibold text-white transition hover:bg-white/10">
                                Pinjam
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Steps -->
        <section id="mulai" class="border-y border-white/5 bg-white/[0.02]">
            <div class="mx-auto max-w-6xl px-6 py-24">
                <div class="mb-14 text-center">
                    <h2 class="text-3xl font-bold text-white">Mulai dalam 3 langkah</h2>
                    <p class="mt-3 text-slate-400">Dari daftar sampai berkarya, tidak sampai lima menit.</p>
                </div>
                <div class="grid gap-6 md:grid-cols-3">
                    <div v-for="(s, i) in steps" :key="s.title" class="relative rounded-2xl border border-white/5 bg-slate-950 p-8">
                        <span class="absolute -top-4 left-8 flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600 text-sm font-bold text-white shadow-lg shadow-indigo-600/40">{{ i + 1 }}</span>
                        <h3 class="mt-2 text-lg font-semibold text-white">{{ s.title }}</h3>
                        <p class="mt-3 text-sm leading-relaxed text-slate-400">{{ s.desc }}</p>
                    </div>
                </div>
                <div class="mt-12 text-center">
                    <Link href="/register" class="inline-block rounded-xl bg-indigo-600 px-8 py-3.5 text-sm font-semibold text-white shadow-xl shadow-indigo-600/30 transition hover:bg-indigo-500">
                        Daftar & Pesan Hari Ini
                    </Link>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="mx-auto max-w-6xl px-6 py-24">
            <div class="relative overflow-hidden rounded-3xl border border-indigo-500/20 bg-gradient-to-br from-indigo-600/20 via-slate-950 to-slate-950 p-12 text-center sm:p-16">
                <div class="absolute -top-24 left-1/2 h-64 w-96 -translate-x-1/2 rounded-full bg-indigo-600/30 blur-3xl"></div>
                <h2 class="relative text-3xl font-bold text-white">Studio impianmu menunggu.</h2>
                <p class="relative mx-auto mt-3 max-w-xl text-slate-400">Ribuan ide hebat gagal karena tidak punya tempat memulai. Jangan biarkan idemu jadi salah satunya.</p>
                <div class="relative mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                    <Link href="/register" class="w-full rounded-xl bg-white px-8 py-3.5 text-sm font-semibold text-slate-900 shadow-xl transition hover:bg-slate-100 sm:w-auto">
                        Buat Akun Gratis
                    </Link>
                    <Link href="/login?portal=member" class="w-full rounded-xl border border-white/20 px-8 py-3.5 text-sm font-semibold text-white transition hover:bg-white/10 sm:w-auto">
                        Sudah Member? Masuk
                    </Link>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-white/5">
            <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-4 px-6 py-8 text-xs text-slate-500 sm:flex-row">
                <div class="flex items-center gap-2">
                    <div class="flex h-6 w-6 items-center justify-center rounded-md bg-indigo-600 text-[10px] font-bold text-white">F</div>
                    <span>&copy; 2026 Rizky Fadhillah. All rights reserved.</span>
                </div>
                <div class="flex items-center gap-4">
                    <Link href="/login?portal=admin" class="transition hover:text-slate-300">Portal Admin</Link>
                    <Link href="/login?portal=receptionist" class="transition hover:text-slate-300">Portal Resepsionis</Link>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.12s ease, transform 0.12s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(-4px); }
</style>

<style>
/* Scroll halus untuk anchor navbar landing */
html { scroll-behavior: smooth; }
</style>
