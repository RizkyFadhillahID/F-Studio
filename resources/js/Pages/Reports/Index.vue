<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import TransactionReportView from '../../Components/TransactionReportView.vue';

defineProps({
    rows: { type: Array, default: () => [] },
    summary: { type: Object, default: () => ({}) },
    filters: { type: Object, default: () => ({}) },
});
</script>

<template>
    <Head title="Laporan Transaksi" />
    <AppLayout>
        <template #header><h1 class="text-base font-semibold text-white">Laporan Transaksi</h1></template>

        <TransactionReportView
            :rows="rows"
            :summary="summary"
            :filters="filters"
            base-url="/reports"
            export-url="/reports/export"
        />
    </AppLayout>
</template>
