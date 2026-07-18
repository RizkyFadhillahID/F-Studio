<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import { formatRupiah } from '../../lib/payment';

const props = defineProps({
    equipment: { type: Array, default: () => [] },
});

const today = new Date().toISOString().slice(0, 10);

const form = useForm({
    customer_name: '', customer_phone: '', purpose: '',
    loan_date: today, due_date: today, notes: '',
    items: [{ equipment_id: '', quantity: 1 }],
});

function addItem() { form.items.push({ equipment_id: '', quantity: 1 }); }
function removeItem(i) { if (form.items.length > 1) form.items.splice(i, 1); }

function submit() {
    form.post('/receptionist/loans', { preserveScroll: true });
}
</script>

<template>
    <Head title="Peminjaman Alat" />
    <AppLayout>
        <template #header><h1 class="text-base font-semibold text-white">Peminjaman Alat (Walk-in)</h1></template>

        <div class="space-y-6">
            <h2 class="text-lg font-semibold text-white mb-4">Buat Peminjaman Alat</h2>
            <form class="space-y-4" @submit.prevent="submit">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-300">Nama Pelanggan</label>
                        <input v-model="form.customer_name" type="text" class="aurora-input" />
                        <p v-if="form.errors.customer_name" class="mt-1 text-sm text-red-400">{{ form.errors.customer_name }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-300">Telepon</label>
                        <input v-model="form.customer_phone" type="text" class="aurora-input" />
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-300">Keperluan</label>
                    <input v-model="form.purpose" type="text" class="aurora-input" />
                    <p v-if="form.errors.purpose" class="mt-1 text-sm text-red-400">{{ form.errors.purpose }}</p>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-300">Tanggal Pinjam</label>
                        <input v-model="form.loan_date" type="date" class="aurora-input" />
                        <p v-if="form.errors.loan_date" class="mt-1 text-sm text-red-400">{{ form.errors.loan_date }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-300">Jatuh Tempo</label>
                        <input v-model="form.due_date" type="date" class="aurora-input" />
                        <p v-if="form.errors.due_date" class="mt-1 text-sm text-red-400">{{ form.errors.due_date }}</p>
                    </div>
                </div>

                <div>
                    <div class="mb-2 flex items-center justify-between">
                        <label class="text-sm font-medium text-slate-300">Peralatan Dipinjam</label>
                        <button type="button" class="text-xs font-medium text-indigo-400 hover:underline" @click="addItem">+ Tambah item</button>
                    </div>
                    <div v-for="(item, i) in form.items" :key="i" class="mb-2 grid grid-cols-12 gap-2">
                        <select v-model="item.equipment_id" class="aurora-input col-span-7 !py-2">
                            <option value="" disabled>Pilih alat…</option>
                            <option v-for="e in equipment" :key="e.id" :value="e.id">{{ e.name }} (tersedia {{ e.quantity_available }}, {{ formatRupiah(e.price_per_day) }}/hari)</option>
                        </select>
                        <input v-model="item.quantity" type="number" min="1" :max="equipment.find(e => e.id === item.equipment_id)?.quantity_available" class="aurora-input col-span-3 !py-2" />
                        <button type="button" class="col-span-2 rounded-lg border border-white/10 bg-white/5 text-sm text-red-400 hover:bg-red-500/10" @click="removeItem(i)">Hapus</button>
                    </div>
                    <p v-if="form.errors.items" class="mt-1 text-sm text-red-400">{{ form.errors.items }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-300">Catatan (opsional)</label>
                    <textarea v-model="form.notes" rows="2" class="aurora-input"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-white/[0.07]">
                    <Link href="/receptionist/bookings?tab=loans" class="aurora-btn-ghost rounded-lg px-4 py-2 text-sm">Batal</Link>
                    <button type="submit" :disabled="form.processing" class="aurora-btn-primary rounded-lg px-4 py-2 text-sm font-semibold disabled:opacity-60">Simpan</button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
