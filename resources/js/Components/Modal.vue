<script setup>
import { onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';

defineProps({
    show: { type: Boolean, default: false },
    title: { type: String, default: '' },
});
const emit = defineEmits(['close']);

// Tutup modal setiap navigasi Inertia agar tidak ada overlay yang
// tertinggal saat berpindah halaman dalam keadaan modal terbuka.
let offNavigate;
onMounted(() => {
    offNavigate = router.on('navigate', () => emit('close'));
});
onUnmounted(() => offNavigate?.());
</script>

<template>
    <Teleport to="body">
        <Transition name="fade">
            <div v-if="show" class="fs-modal fixed inset-0 z-50 flex items-end justify-center bg-black/60 backdrop-blur-sm p-0 sm:items-center sm:p-4" @click.self="emit('close')">
                <div class="w-full max-w-lg rounded-t-2xl border border-white/10 bg-[#12141f] shadow-2xl shadow-black/50 sm:rounded-2xl">
                    <div class="flex items-center justify-between border-b border-white/[0.07] px-5 py-4">
                        <h3 class="text-base font-semibold text-white">{{ title }}</h3>
                        <button class="rounded-lg p-1 text-slate-500 hover:bg-white/5 hover:text-slate-300" @click="emit('close')" aria-label="Tutup">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <div class="max-h-[70vh] overflow-y-auto px-5 py-4">
                        <slot />
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.15s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
