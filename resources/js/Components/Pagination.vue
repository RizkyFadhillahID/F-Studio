<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: { type: Array, default: () => [] },
});
</script>

<template>
    <div v-if="links.length > 3" class="flex flex-wrap items-center gap-1">
        <template v-for="(link, i) in links" :key="i">
            <Link
                v-if="link.url"
                :href="link.url"
                preserve-scroll
                class="rounded-md px-3 py-1.5 text-sm transition"
                :class="link.active ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white'"
                v-html="link.label"
            />
            <span
                v-else
                class="rounded-md px-3 py-1.5 text-sm text-slate-600"
                v-html="link.label"
            />
        </template>
    </div>
</template>
