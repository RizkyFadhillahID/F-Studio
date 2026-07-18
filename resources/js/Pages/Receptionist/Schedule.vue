<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import { bookingStatusLabel } from '../../lib/status';

const props = defineProps({
    rooms: { type: Array, default: () => [] },
    bookings: { type: Array, default: () => [] },
    today: { type: String, default: '' },
});

const statusBadge = {
    pending: 'aurora-pill-warn',
    approved: 'aurora-pill-ok',
};

function fmtTime(dt) {
    return dt ? new Date(dt).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) : '—';
}

const byRoom = computed(() => {
    const map = new Map(props.rooms.map((r) => [r.id, { room: r, items: [] }]));
    for (const b of props.bookings) {
        const entry = map.get(b.room_id);
        if (entry) entry.items.push(b);
    }
    return [...map.values()];
});
</script>

<template>
    <Head title="Jadwal Hari Ini" />
    <AppLayout>
        <template #header><h1 class="text-base font-semibold text-white">Jadwal Hari Ini — {{ today }}</h1></template>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div v-for="entry in byRoom" :key="entry.room.id" class="aurora-card p-4">
                <h3 class="mb-2 text-sm font-semibold text-white">{{ entry.room.name }}</h3>
                <ul v-if="entry.items.length" class="space-y-2">
                    <li v-for="b in entry.items" :key="b.id" class="flex items-center justify-between rounded-lg bg-white/5 px-3 py-2 text-xs">
                        <span class="text-slate-300">{{ fmtTime(b.start_datetime) }}–{{ fmtTime(b.end_datetime) }} · {{ b.title }}</span>
                        <span class="aurora-pill" :class="statusBadge[b.status] ?? 'aurora-pill-muted'">{{ bookingStatusLabel[b.status] ?? b.status }}</span>
                    </li>
                </ul>
                <p v-else class="text-xs text-slate-500">Kosong hari ini.</p>
            </div>
        </div>
    </AppLayout>
</template>
