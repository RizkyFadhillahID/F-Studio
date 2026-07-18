<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import { bookingStatusLabel } from '../../lib/status';

const props = defineProps({
    stats: { type: Object, default: () => ({}) },
    todayBookings: { type: Array, default: () => [] },
    recentBookings: { type: Array, default: () => [] },
});

const statusBadge = {
    pending: 'aurora-pill-warn',
    approved: 'aurora-pill-ok',
    completed: 'aurora-pill-info',
    rejected: 'aurora-pill-danger',
    cancelled: 'aurora-pill-muted',
};

function fmtTime(dt) {
    return dt ? new Date(dt).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) : '—';
}
</script>

<template>
    <Head title="Dashboard Resepsionis" />
    <AppLayout>
        <template #header><h1 class="text-base font-semibold text-white">Dashboard Resepsionis</h1></template>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
            <div class="aurora-card p-4">
                <p class="text-2xl font-bold text-sky-400">{{ stats.today ?? 0 }}</p>
                <p class="text-xs text-slate-500">Pemesanan Hari Ini</p>
            </div>
            <div class="aurora-card p-4">
                <p class="text-2xl font-bold text-amber-400">{{ stats.pending ?? 0 }}</p>
                <p class="text-xs text-slate-500">Menunggu Persetujuan</p>
            </div>
            <div class="aurora-card p-4">
                <p class="text-2xl font-bold text-emerald-400">{{ stats.approved ?? 0 }}</p>
                <p class="text-xs text-slate-500">Disetujui Hari Ini</p>
            </div>
            <div class="aurora-card p-4">
                <p class="text-2xl font-bold text-indigo-400">{{ stats.total_month ?? 0 }}</p>
                <p class="text-xs text-slate-500">Total Bulan Ini</p>
            </div>
            <div class="aurora-card p-4">
                <p class="text-2xl font-bold text-orange-400">{{ stats.loan_pending ?? 0 }}</p>
                <p class="text-xs text-slate-500">Peminjaman Menunggu</p>
            </div>
        </div>

        <div class="mt-6 flex flex-wrap gap-3">
            <Link href="/receptionist/rooms" class="aurora-btn-primary rounded-lg px-4 py-2 text-sm font-semibold">+ Pemesanan Walk-in</Link>
            <Link href="/receptionist/loans" class="aurora-btn-ghost rounded-lg px-4 py-2 text-sm font-semibold">+ Peminjaman Alat</Link>
        </div>

        <div class="mt-6 grid gap-4 lg:grid-cols-2">
            <section class="aurora-card p-5">
                <h2 class="mb-3 text-sm font-semibold text-white">Jadwal Hari Ini</h2>
                <ul v-if="todayBookings.length" class="divide-y divide-white/[0.06]">
                    <li v-for="b in todayBookings" :key="b.id" class="flex items-center justify-between gap-3 py-2 text-sm">
                        <span class="min-w-0 truncate text-slate-300">{{ fmtTime(b.start_datetime) }} · {{ b.room?.name }} — {{ b.title }}</span>
                        <span class="aurora-pill shrink-0" :class="statusBadge[b.status] ?? 'aurora-pill-muted'">{{ bookingStatusLabel[b.status] ?? b.status }}</span>
                    </li>
                </ul>
                <p v-else class="py-6 text-center text-sm text-slate-500">Tidak ada pemesanan hari ini.</p>
            </section>

            <section class="aurora-card p-5">
                <h2 class="mb-3 text-sm font-semibold text-white">Pemesanan Saya Terbaru</h2>
                <ul v-if="recentBookings.length" class="divide-y divide-white/[0.06]">
                    <li v-for="b in recentBookings" :key="b.id" class="flex items-center justify-between gap-3 py-2 text-sm">
                        <span class="min-w-0 truncate text-slate-300">{{ b.room?.name }} — {{ b.title }}</span>
                        <span class="aurora-pill shrink-0" :class="statusBadge[b.status] ?? 'aurora-pill-muted'">{{ bookingStatusLabel[b.status] ?? b.status }}</span>
                    </li>
                </ul>
                <p v-else class="py-6 text-center text-sm text-slate-500">Belum ada pemesanan yang dibuat.</p>
            </section>
        </div>
    </AppLayout>
</template>
