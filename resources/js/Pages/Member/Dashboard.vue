<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import MemberLayout from '../../Layouts/MemberLayout.vue';
import { bookingStatusLabel, loanStatusLabel } from '../../lib/status';

const props = defineProps({
    myBookings: { type: Array, default: () => [] },
    myLoans: { type: Array, default: () => [] },
    stats: { type: Object, default: () => ({}) },
    featuredRooms: { type: Array, default: () => [] },
    featuredEquipment: { type: Array, default: () => [] },
});

const page = usePage();
const firstName = computed(() => (page.props.auth?.user?.name ?? 'Member').split(' ')[0]);

const statusBadge = {
    pending: 'aurora-pill-warn',
    approved: 'aurora-pill-ok',
    completed: 'aurora-pill-info',
    active: 'aurora-pill-info',
    overdue: 'aurora-pill-danger',
    returned: 'aurora-pill-muted',
    rejected: 'aurora-pill-danger',
    cancelled: 'aurora-pill-muted',
};

const fmt = (dt) => (dt ? new Date(dt).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }) : '—');
const fmtDate = (d) => (d ? new Date(d).toLocaleDateString('id-ID', { dateStyle: 'medium' }) : '—');
</script>

<template>
    <Head title="Dashboard Member" />
    <MemberLayout>
        <template #header><h1 class="text-base font-semibold text-white">Dashboard Member</h1></template>

        <!-- Hero banner -->
        <section class="relative overflow-hidden rounded-3xl border border-indigo-500/20 bg-gradient-to-br from-indigo-600/90 via-indigo-700/90 to-violet-800/90 p-8 text-white sm:p-10">
            <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/10 blur-2xl"></div>
            <div class="relative max-w-xl">
                <p class="text-sm text-indigo-200">Halo, {{ firstName }} </p>
                <h2 class="mt-1 text-2xl font-bold sm:text-3xl">Mau berkarya apa hari ini?</h2>
                <p class="mt-2 text-sm text-indigo-100">Pesan ruangan atau pinjam peralatan profesional — semuanya cuma beberapa klik.</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <Link href="/member/rooms" class="rounded-xl bg-white px-5 py-2.5 text-sm font-semibold text-indigo-700 shadow-lg transition hover:bg-indigo-50">Pesan Ruangan</Link>
                    <Link href="/member/equipment" class="rounded-xl border border-white/30 bg-white/10 px-5 py-2.5 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/20">Pinjam Alat</Link>
                </div>
            </div>
            <!-- Mini stats -->
            <div class="relative mt-8 grid grid-cols-2 gap-3 sm:grid-cols-4">
                <div v-for="s in [[stats.total ?? 0, 'Total Pemesanan'], [stats.pending ?? 0, 'Menunggu'], [stats.approved ?? 0, 'Disetujui'], [stats.loans ?? 0, 'Peminjaman Aktif']]" :key="s[1]" class="rounded-2xl bg-white/10 px-4 py-3 backdrop-blur">
                    <p class="text-xl font-bold">{{ s[0] }}</p>
                    <p class="text-xs text-indigo-200">{{ s[1] }}</p>
                </div>
            </div>
        </section>

        <!-- Ruangan unggulan -->
        <section class="mt-8">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-bold text-white">Ruangan untuk karyamu</h2>
                <Link href="/member/rooms" class="text-sm font-semibold text-indigo-400 hover:text-indigo-300">Lihat semua</Link>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Link v-for="room in featuredRooms" :key="room.id" href="/member/rooms" class="group overflow-hidden rounded-2xl border border-white/[0.08] bg-white/[0.03] transition hover:border-indigo-500/30">
                    <div class="relative h-32 overflow-hidden bg-white/5">
                        <img v-if="room.image" :src="`/${room.image}`" :alt="room.name" class="h-full w-full object-cover transition duration-300 group-hover:scale-105" loading="lazy" />
                        <span class="absolute right-2 top-2 rounded-full bg-white/90 px-2 py-0.5 text-[10px] font-medium text-slate-700">{{ room.capacity }} orang</span>
                    </div>
                    <div class="p-4">
                        <h3 class="truncate text-sm font-semibold text-white">{{ room.name }}</h3>
                        <p class="mt-1 text-xs font-medium text-indigo-400 opacity-0 transition group-hover:opacity-100">Pesan sekarang</p>
                    </div>
                </Link>
            </div>
        </section>

        <!-- Alat siap pinjam -->
        <section class="mt-8">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-bold text-white">Peralatan siap pinjam</h2>
                <Link href="/member/equipment" class="text-sm font-semibold text-indigo-400 hover:text-indigo-300">Lihat semua</Link>
            </div>
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4 lg:grid-cols-8">
                <Link v-for="item in featuredEquipment" :key="item.id" href="/member/equipment" class="group overflow-hidden rounded-2xl border border-white/[0.08] bg-white/[0.03] transition hover:border-indigo-500/30">
                    <div class="h-20 overflow-hidden bg-white/5">
                        <img v-if="item.image" :src="`/${item.image}`" :alt="item.name" class="h-full w-full object-cover transition duration-300 group-hover:scale-105" loading="lazy" />
                    </div>
                    <div class="p-2.5">
                        <p class="truncate text-xs font-semibold text-slate-300">{{ item.name }}</p>
                        <p class="text-[10px] text-emerald-400">{{ item.quantity_available }} tersedia</p>
                    </div>
                </Link>
            </div>
        </section>

        <!-- Pesanan saya -->
        <section class="mt-8 grid gap-5 lg:grid-cols-2">
            <div class="aurora-card p-5">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="text-sm font-bold text-white">Pemesanan Terbaru</h2>
                    <Link href="/member/bookings" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300">Semua pemesanan</Link>
                </div>
                <ul v-if="myBookings.length" class="divide-y divide-white/[0.06]">
                    <li v-for="b in myBookings" :key="b.id" class="flex items-center gap-3 py-2.5">
                        <img v-if="b.room?.image" :src="`/${b.room.image}`" class="h-10 w-14 rounded-lg object-cover" />
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-slate-300">{{ b.title }}</p>
                            <p class="text-xs text-slate-500">{{ b.room?.name }} · {{ fmt(b.start_datetime) }}</p>
                        </div>
                        <span class="aurora-pill" :class="statusBadge[b.status] ?? 'aurora-pill-muted'">{{ bookingStatusLabel[b.status] ?? b.status }}</span>
                    </li>
                </ul>
                <div v-else class="py-8 text-center">
                    <p class="text-sm text-slate-500">Belum ada pemesanan.</p>
                    <Link href="/member/rooms" class="mt-2 inline-block text-sm font-semibold text-indigo-400 hover:text-indigo-300">Pesan ruangan pertamamu</Link>
                </div>
            </div>

            <div class="aurora-card p-5">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="text-sm font-bold text-white">Peminjaman Terbaru</h2>
                    <Link href="/member/bookings?tab=loans" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300">Semua pinjaman</Link>
                </div>
                <ul v-if="myLoans.length" class="divide-y divide-white/[0.06]">
                    <li v-for="l in myLoans" :key="l.id" class="flex items-center gap-3 py-2.5">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-slate-300">{{ l.purpose }}</p>
                            <p class="truncate text-xs text-slate-500">
                                {{ (l.items ?? []).map((it) => it.equipment?.name).filter(Boolean).join(', ') }} · kembali {{ fmtDate(l.due_date) }}
                            </p>
                        </div>
                        <span class="aurora-pill" :class="statusBadge[l.status] ?? 'aurora-pill-muted'">{{ loanStatusLabel[l.status] ?? l.status }}</span>
                    </li>
                </ul>
                <div v-else class="py-8 text-center">
                    <p class="text-sm text-slate-500">Belum ada peminjaman alat.</p>
                    <Link href="/member/equipment" class="mt-2 inline-block text-sm font-semibold text-indigo-400 hover:text-indigo-300">Pinjam alat pertamamu</Link>
                </div>
            </div>
        </section>
    </MemberLayout>
</template>
