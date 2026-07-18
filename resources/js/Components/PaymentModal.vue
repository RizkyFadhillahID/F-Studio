<script setup>
import { ref, watch } from 'vue';
import Modal from './Modal.vue';
import { formatRupiah, paymentMethods } from '../lib/payment';

const props = defineProps({
    show: { type: Boolean, default: false },
    code: { type: String, default: '' },
    amount: { type: Number, default: 0 },
    processing: { type: Boolean, default: false },
});
const emit = defineEmits(['close', 'pay']);

const method = ref('cash');

watch(() => props.show, (v) => {
    if (v) method.value = 'cash';
});
</script>

<template>
    <Modal :show="show" title="Pembayaran (Simulasi)" @close="emit('close')">
        <div class="space-y-5">
            <div class="rounded-xl border border-indigo-500/20 bg-indigo-500/10 p-4 text-center">
                <p class="text-xs text-slate-400">Total tagihan untuk {{ code }}</p>
                <p class="mt-1 text-3xl font-bold text-white">{{ formatRupiah(amount) }}</p>
            </div>

            <div>
                <p class="mb-2 text-sm font-medium text-slate-300">Pilih metode pembayaran</p>
                <div class="grid grid-cols-2 gap-2">
                    <button
                        v-for="m in paymentMethods"
                        :key="m.key"
                        type="button"
                        class="flex items-center gap-2 rounded-xl border px-3 py-3 text-sm font-medium transition"
                        :class="method === m.key ? 'border-indigo-500 bg-indigo-500/15 text-white' : 'border-white/10 bg-white/5 text-slate-400 hover:bg-white/10'"
                        @click="method = m.key"
                    >
                        <span class="text-lg">{{ m.icon }}</span>
                        {{ m.label }}
                    </button>
                </div>
            </div>

            <p class="rounded-lg bg-white/5 px-3 py-2 text-xs text-slate-500">
                Ini pembayaran simulasi untuk keperluan demo — tidak ada transaksi nyata yang diproses.
            </p>

            <div class="flex justify-end gap-2">
                <button type="button" class="aurora-btn-ghost rounded-lg px-4 py-2 text-sm" @click="emit('close')">Batal</button>
                <button
                    type="button"
                    :disabled="processing"
                    class="aurora-btn-primary rounded-lg px-5 py-2 text-sm font-semibold disabled:opacity-60"
                    @click="emit('pay', method)"
                >
                    {{ processing ? 'Memproses…' : 'Bayar Sekarang' }}
                </button>
            </div>
        </div>
    </Modal>
</template>
