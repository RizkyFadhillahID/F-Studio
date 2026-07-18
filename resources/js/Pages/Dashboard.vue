<script setup>
import { computed, ref } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';
import { formatRupiah } from '../lib/payment';
import { bookingStatusLabel, loanStatusLabel } from '../lib/status';

const checkInActionLabel = { check_in: 'Check-in', check_out: 'Check-out' };

const props = defineProps({
    stats: { type: Object, default: () => ({}) },
    pendingLoans: { type: Array, default: () => [] },
    pendingBookings: { type: Array, default: () => [] },
    overdueLoans: { type: Array, default: () => [] },
    recentCheckIns: { type: Array, default: () => [] },
    recentBookingPayments: { type: Array, default: () => [] },
    recentLoanPayments: { type: Array, default: () => [] },
    revenueTrend: { type: Array, default: () => [] },
    bookingStatusBreakdown: { type: Object, default: () => ({}) },
    loanStatusBreakdown: { type: Object, default: () => ({}) },
});

const page = usePage();
const firstName = computed(() => (page.props.auth?.user?.name ?? 'Admin').split(' ')[0]);
const today = new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });

// KPI tiles dikelompokkan per konteks, bukan satu grid rata 10 kotak.
const kpiGroups = computed(() => [
    {
        title: 'Operasional',
        items: [
            { key: 'bookings_today', label: 'Pemesanan Hari Ini', icon: 'calendar', tone: 'indigo' },
            { key: 'pending_bookings', label: 'Pemesanan Menunggu', icon: 'clock', tone: 'amber' },
            { key: 'pending_loans', label: 'Peminjaman Menunggu', icon: 'clock', tone: 'amber' },
            { key: 'active_loans', label: 'Alat Sedang Keluar', icon: 'box', tone: 'sky' },
        ],
    },
    {
        title: 'Pembayaran',
        items: [
            { key: 'unpaid_bookings', label: 'Pemesanan Belum Bayar', icon: 'cash', tone: 'amber' },
            { key: 'unpaid_loans', label: 'Peminjaman Belum Bayar', icon: 'cash', tone: 'amber' },
            { key: 'overdue_loans', label: 'Peminjaman Terlambat', icon: 'warn', tone: 'red' },
        ],
    },
    {
        title: 'Data Master',
        items: [
            { key: 'total_rooms', label: 'Ruangan Terdaftar', icon: 'room', tone: 'violet' },
            { key: 'total_equipment', label: 'Peralatan Terdaftar', icon: 'camera', tone: 'emerald' },
            { key: 'total_members', label: 'Anggota Aktif', icon: 'users', tone: 'cyan' },
        ],
    },
]);

const toneClasses = {
    indigo: 'bg-indigo-500/15 text-indigo-300 ring-indigo-500/25',
    amber: 'bg-amber-500/15 text-amber-300 ring-amber-500/25',
    sky: 'bg-sky-500/15 text-sky-300 ring-sky-500/25',
    red: 'bg-red-500/15 text-red-300 ring-red-500/25',
    violet: 'bg-violet-500/15 text-violet-300 ring-violet-500/25',
    emerald: 'bg-emerald-500/15 text-emerald-300 ring-emerald-500/25',
    cyan: 'bg-cyan-500/15 text-cyan-300 ring-cyan-500/25',
};

const iconPaths = {
    calendar: 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5',
    clock: 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
    box: 'M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z',
    warn: 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z',
    room: 'M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15',
    camera: 'M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316zM16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z',
    users: 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
    cash: 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-4.5-9.75h16.5a1.5 1.5 0 011.5 1.5v9a1.5 1.5 0 01-1.5 1.5H3.75a1.5 1.5 0 01-1.5-1.5v-9a1.5 1.5 0 011.5-1.5z',
};

function fmt(dt) {
    return dt ? new Date(dt).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }) : '—';
}
function fmtShortDate(d) {
    return d ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }) : '';
}

// ── Grafik tren pendapatan (14 hari terakhir) ──
const chart = { width: 600, height: 180, padTop: 16, padRight: 12, padBottom: 28, padLeft: 12 };

const revenueMax = computed(() => Math.max(...props.revenueTrend.map((d) => d.amount), 1));

const revenuePoints = computed(() => {
    const data = props.revenueTrend;
    const n = data.length;
    if (!n) return [];
    const innerW = chart.width - chart.padLeft - chart.padRight;
    const innerH = chart.height - chart.padTop - chart.padBottom;
    return data.map((d, i) => ({
        x: chart.padLeft + (n === 1 ? innerW / 2 : (i / (n - 1)) * innerW),
        y: chart.padTop + innerH - (d.amount / revenueMax.value) * innerH,
        date: d.date,
        amount: d.amount,
    }));
});

const revenueLinePath = computed(() =>
    revenuePoints.value.map((p, i) => `${i === 0 ? 'M' : 'L'} ${p.x.toFixed(1)} ${p.y.toFixed(1)}`).join(' ')
);

const revenueAreaPath = computed(() => {
    const pts = revenuePoints.value;
    if (!pts.length) return '';
    const baseline = chart.height - chart.padBottom;
    return `${revenueLinePath.value} L ${pts[pts.length - 1].x.toFixed(1)} ${baseline} L ${pts[0].x.toFixed(1)} ${baseline} Z`;
});

const revenueTotal14d = computed(() => props.revenueTrend.reduce((s, d) => s + d.amount, 0));

const hoverIndex = ref(null);
const hoveredPoint = computed(() => (hoverIndex.value === null ? null : revenuePoints.value[hoverIndex.value]));

// ── Grafik status transaksi (batang bertumpuk, warna sama dengan aurora-pill) ──
const statusColor = {
    pending: '#fbbf24',
    approved: '#4ade80',
    completed: '#7dd3fc',
    active: '#7dd3fc',
    overdue: '#f87171',
    returned: '#9aa0c0',
    rejected: '#f87171',
    cancelled: '#9aa0c0',
};

function buildSegments(breakdown, order, labelMap) {
    const total = order.reduce((s, key) => s + Number(breakdown[key] ?? 0), 0);
    const segments = order
        .map((key) => ({
            key,
            label: labelMap[key] ?? key,
            count: Number(breakdown[key] ?? 0),
            color: statusColor[key] ?? '#9aa0c0',
            pct: total ? (Number(breakdown[key] ?? 0) / total) * 100 : 0,
        }))
        .filter((s) => s.count > 0);
    return { total, segments };
}

const bookingBreakdown = computed(() =>
    buildSegments(props.bookingStatusBreakdown, ['approved', 'completed', 'pending', 'rejected', 'cancelled'], bookingStatusLabel)
);
const loanBreakdown = computed(() =>
    buildSegments(props.loanStatusBreakdown, ['approved', 'active', 'pending', 'overdue', 'returned', 'rejected', 'cancelled'], loanStatusLabel)
);
</script>

<template>
    <Head title="Dashboard" />
    <AppLayout>
        <template #header>
            <div>
                <h1 class="text-base font-semibold text-white">Selamat datang, {{ firstName }}</h1>
                <p class="text-xs text-slate-500">{{ today }}</p>
            </div>
        </template>

        <!-- KPI tiles, dikelompokkan per konteks -->
        <div class="space-y-3">
            <div v-for="group in kpiGroups" :key="group.title">
                <h3 class="mb-1.5 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ group.title }}</h3>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                    <div v-for="k in group.items" :key="k.key" class="aurora-card flex items-center gap-2.5 p-3">
                        <div :class="['inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg ring-1', toneClasses[k.tone]]">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" :d="iconPaths[k.icon]" /></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-lg font-bold leading-tight text-white">{{ stats[k.key] ?? 0 }}</p>
                            <p class="truncate text-[11px] text-slate-500">{{ k.label }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analitik -->
        <section class="aurora-card mt-4 p-5">
            <div class="mb-1 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-white">Tren Pendapatan · 14 Hari Terakhir</h2>
                <span class="text-xs text-slate-500">Total {{ formatRupiah(revenueTotal14d) }}</span>
            </div>
            <div class="relative mt-3">
                <svg :viewBox="`0 0 ${chart.width} ${chart.height}`" class="w-full" style="height: 180px;" preserveAspectRatio="none">
                    <line
                        :x1="chart.padLeft" :x2="chart.width - chart.padRight"
                        :y1="chart.height - chart.padBottom" :y2="chart.height - chart.padBottom"
                        stroke="#2c2c2a" stroke-width="1"
                    />
                    <path v-if="revenueAreaPath" :d="revenueAreaPath" fill="#6366f1" fill-opacity="0.1" stroke="none" />
                    <path v-if="revenueLinePath" :d="revenueLinePath" fill="none" stroke="#6366f1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    <g v-for="(p, i) in revenuePoints" :key="p.date">
                        <circle v-if="hoverIndex === i" :cx="p.x" :cy="p.y" r="4" fill="#6366f1" stroke="#12141f" stroke-width="2" />
                        <circle
                            :cx="p.x" :cy="p.y" r="10" fill="transparent" class="cursor-pointer"
                            @mouseenter="hoverIndex = i" @mouseleave="hoverIndex = null"
                        >
                            <title>{{ fmtShortDate(p.date) }}: {{ formatRupiah(p.amount) }}</title>
                        </circle>
                    </g>
                </svg>
                <div
                    v-if="hoveredPoint"
                    class="pointer-events-none absolute -translate-x-1/2 -translate-y-full whitespace-nowrap rounded-lg border border-white/10 bg-[#12141f] px-2.5 py-1.5 text-xs shadow-xl"
                    :style="{ left: `${(hoveredPoint.x / chart.width) * 100}%`, top: `${(hoveredPoint.y / chart.height) * 100}%` }"
                >
                    <p class="font-semibold text-white">{{ formatRupiah(hoveredPoint.amount) }}</p>
                    <p class="text-slate-500">{{ fmtShortDate(hoveredPoint.date) }}</p>
                </div>
            </div>
            <div class="mt-1 flex justify-between text-[11px] text-slate-600">
                <span>{{ fmtShortDate(revenueTrend[0]?.date) }}</span>
                <span>{{ fmtShortDate(revenueTrend[revenueTrend.length - 1]?.date) }}</span>
            </div>
        </section>

        <div class="mt-4 grid gap-4 lg:grid-cols-2">
            <section class="aurora-card p-5">
                <h2 class="mb-3 text-sm font-semibold text-white">Status Pemesanan Ruangan</h2>
                <div v-if="bookingBreakdown.total" class="space-y-3">
                    <div class="flex h-3 w-full overflow-hidden rounded-full bg-white/5">
                        <div
                            v-for="seg in bookingBreakdown.segments" :key="seg.key"
                            :style="{ width: seg.pct + '%', backgroundColor: seg.color }"
                            class="h-full border-l-2 border-white/10 first:border-l-0"
                        ></div>
                    </div>
                    <ul class="flex flex-wrap gap-x-4 gap-y-1.5 text-xs">
                        <li v-for="seg in bookingBreakdown.segments" :key="seg.key" class="flex items-center gap-1.5 text-slate-400">
                            <span class="h-2 w-2 shrink-0 rounded-full" :style="{ backgroundColor: seg.color }"></span>
                            {{ seg.label }} <span class="font-semibold text-white">{{ seg.count }}</span>
                        </li>
                    </ul>
                </div>
                <p v-else class="py-6 text-center text-sm text-slate-500">Belum ada data pemesanan.</p>
            </section>

            <section class="aurora-card p-5">
                <h2 class="mb-3 text-sm font-semibold text-white">Status Peminjaman Alat</h2>
                <div v-if="loanBreakdown.total" class="space-y-3">
                    <div class="flex h-3 w-full overflow-hidden rounded-full bg-white/5">
                        <div
                            v-for="seg in loanBreakdown.segments" :key="seg.key"
                            :style="{ width: seg.pct + '%', backgroundColor: seg.color }"
                            class="h-full border-l-2 border-white/10 first:border-l-0"
                        ></div>
                    </div>
                    <ul class="flex flex-wrap gap-x-4 gap-y-1.5 text-xs">
                        <li v-for="seg in loanBreakdown.segments" :key="seg.key" class="flex items-center gap-1.5 text-slate-400">
                            <span class="h-2 w-2 shrink-0 rounded-full" :style="{ backgroundColor: seg.color }"></span>
                            {{ seg.label }} <span class="font-semibold text-white">{{ seg.count }}</span>
                        </li>
                    </ul>
                </div>
                <p v-else class="py-6 text-center text-sm text-slate-500">Belum ada data peminjaman.</p>
            </section>
        </div>

        <div class="mt-4 grid gap-4 lg:grid-cols-2">
            <!-- Menunggu persetujuan -->
            <section class="aurora-card p-5">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-white">Menunggu Persetujuan</h2>
                    <Link href="/bookings" class="text-xs font-medium text-indigo-400 hover:text-indigo-300">Lihat semua</Link>
                </div>
                <ul v-if="pendingBookings.length || pendingLoans.length" class="divide-y divide-white/[0.06]">
                    <li v-for="b in pendingBookings" :key="'b' + b.id" class="flex items-center justify-between gap-3 py-2.5 text-sm">
                        <span class="min-w-0 truncate text-slate-300">📅 {{ b.user?.name ?? b.customer_name ?? '—' }} · {{ b.room?.name ?? '—' }}</span>
                        <span class="aurora-pill aurora-pill-warn shrink-0">{{ bookingStatusLabel.pending }}</span>
                    </li>
                    <li v-for="l in pendingLoans" :key="'l' + l.id" class="flex items-center justify-between gap-3 py-2.5 text-sm">
                        <span class="min-w-0 truncate text-slate-300">📦 {{ l.user?.name ?? l.customer_name ?? '—' }} · {{ l.loan_code }}</span>
                        <span class="aurora-pill aurora-pill-warn shrink-0">{{ loanStatusLabel.pending }}</span>
                    </li>
                </ul>
                <p v-else class="py-6 text-center text-sm text-slate-500">Tidak ada yang menunggu persetujuan.</p>
            </section>

            <!-- Terlambat -->
            <section class="aurora-card p-5">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-white">Peminjaman Terlambat</h2>
                    <Link href="/loans" class="text-xs font-medium text-indigo-400 hover:text-indigo-300">Lihat semua</Link>
                </div>
                <ul v-if="overdueLoans.length" class="divide-y divide-white/[0.06]">
                    <li v-for="l in overdueLoans" :key="l.id" class="flex items-center justify-between gap-3 py-2.5 text-sm">
                        <span class="min-w-0 truncate text-slate-300">{{ l.user?.name ?? l.customer_name ?? '—' }} · {{ l.loan_code }}</span>
                        <span class="aurora-pill aurora-pill-danger shrink-0">{{ loanStatusLabel.overdue }}</span>
                    </li>
                </ul>
                <p v-else class="py-6 text-center text-sm text-slate-500">Tidak ada keterlambatan. 🎉</p>
            </section>
        </div>

        <!-- Status Pembayaran -->
        <div class="mt-4 grid gap-4 lg:grid-cols-2">
            <section class="aurora-card p-5">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-white">Pembayaran · Pemesanan Ruangan</h2>
                    <Link href="/bookings" class="text-xs font-medium text-indigo-400 hover:text-indigo-300">Lihat semua</Link>
                </div>
                <ul v-if="recentBookingPayments.length" class="divide-y divide-white/[0.06]">
                    <li v-for="b in recentBookingPayments" :key="b.id" class="flex items-center justify-between gap-3 py-2.5 text-sm">
                        <span class="min-w-0 truncate text-slate-300">{{ b.user?.name ?? b.customer_name ?? '—' }} · {{ b.room?.name ?? '—' }}</span>
                        <div class="flex shrink-0 items-center gap-2">
                            <span v-if="b.payment_status === 'paid'" class="text-xs text-slate-500">{{ formatRupiah(b.amount) }}</span>
                            <span class="aurora-pill shrink-0" :class="b.payment_status === 'paid' ? 'aurora-pill-ok' : 'aurora-pill-warn'">
                                {{ b.payment_status === 'paid' ? 'Lunas' : 'Belum bayar' }}
                            </span>
                        </div>
                    </li>
                </ul>
                <p v-else class="py-6 text-center text-sm text-slate-500">Belum ada pemesanan ruangan.</p>
            </section>

            <section class="aurora-card p-5">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-white">Pembayaran · Peminjaman Alat</h2>
                    <Link href="/loans" class="text-xs font-medium text-indigo-400 hover:text-indigo-300">Lihat semua</Link>
                </div>
                <ul v-if="recentLoanPayments.length" class="divide-y divide-white/[0.06]">
                    <li v-for="l in recentLoanPayments" :key="l.id" class="flex items-center justify-between gap-3 py-2.5 text-sm">
                        <span class="min-w-0 truncate text-slate-300">{{ l.user?.name ?? l.customer_name ?? '—' }} · {{ l.loan_code }}</span>
                        <div class="flex shrink-0 items-center gap-2">
                            <span v-if="l.payment_status === 'paid'" class="text-xs text-slate-500">{{ formatRupiah(l.amount) }}</span>
                            <span class="aurora-pill shrink-0" :class="l.payment_status === 'paid' ? 'aurora-pill-ok' : 'aurora-pill-warn'">
                                {{ l.payment_status === 'paid' ? 'Lunas' : 'Belum bayar' }}
                            </span>
                        </div>
                    </li>
                </ul>
                <p v-else class="py-6 text-center text-sm text-slate-500">Belum ada peminjaman alat.</p>
            </section>
        </div>

        <!-- Check-in terbaru -->
        <section class="aurora-card mt-4 p-5">
            <h2 class="mb-3 text-sm font-semibold text-white">Aktivitas Check-in Terbaru</h2>
            <ul v-if="recentCheckIns.length" class="divide-y divide-white/[0.06]">
                <li v-for="c in recentCheckIns" :key="c.id" class="flex items-center justify-between gap-3 py-2.5 text-sm">
                    <span class="min-w-0 truncate text-slate-300">{{ c.loan?.loan_code ?? '—' }} · {{ c.user?.name ?? '—' }}</span>
                    <div class="flex shrink-0 items-center gap-2">
                        <span class="aurora-pill" :class="c.action === 'check_in' ? 'aurora-pill-info' : 'aurora-pill-muted'">{{ checkInActionLabel[c.action] ?? c.action }}</span>
                        <span class="text-xs text-slate-500">{{ fmt(c.checked_at) }}</span>
                    </div>
                </li>
            </ul>
            <p v-else class="py-6 text-center text-sm text-slate-500">Belum ada aktivitas check-in.</p>
        </section>
    </AppLayout>
</template>
