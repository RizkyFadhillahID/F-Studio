<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import Pagination from '../../Components/Pagination.vue';

const props = defineProps({
    categories: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const viewState = ref('index'); // 'index' | 'create'
const search = ref(props.filters.search ?? '');

const sortColumn = ref('name');
const sortDirection = ref('asc');

function toggleSort(col) {
    if (sortColumn.value === col) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortColumn.value = col;
        sortDirection.value = 'asc';
    }
}

const sortedCategories = computed(() => {
    const list = [...(props.categories.data || [])];
    if (sortColumn.value) {
        list.sort((a, b) => {
            let valA = a[sortColumn.value];
            let valB = b[sortColumn.value];
            
            if (valA === null || valA === undefined) valA = '';
            if (valB === null || valB === undefined) valB = '';
            
            if (typeof valA === 'string') {
                return sortDirection.value === 'asc'
                    ? valA.localeCompare(valB)
                    : valB.localeCompare(valA);
            } else {
                return sortDirection.value === 'asc'
                    ? valA - valB
                    : valB - valA;
            }
        });
    }
    return list;
});

const form = useForm({ name: '', description: '' });

function openCreate() {
    form.reset();
    form.clearErrors();
    viewState.value = 'create';
}

function submit() {
    form.post('/categories', {
        preserveScroll: true,
        onSuccess: () => { viewState.value = 'index'; },
    });
}

function destroy(cat) {
    if (confirm(`Hapus kategori "${cat.name}"?`)) {
        router.delete(`/categories/${cat.id}`, { preserveScroll: true });
    }
}

function doSearch() {
    router.get('/categories', { search: search.value }, { preserveState: true, replace: true });
}
</script>

<template>
    <Head title="Kategori" />
    <AppLayout>
        <template #header><h1 class="text-base font-semibold text-white">Data Master · Kategori</h1></template>

        <div v-if="viewState === 'index'">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <form class="flex gap-2" @submit.prevent="doSearch">
                    <input v-model="search" type="text" placeholder="Cari kategori…" class="aurora-input sm:w-64" />
                    <button type="submit" class="aurora-btn-ghost rounded-lg px-3 py-2 text-sm">Cari</button>
                </form>
                <button class="aurora-btn-primary rounded-lg px-4 py-2 text-sm font-semibold" @click="openCreate">+ Tambah Kategori</button>
            </div>

            <div class="aurora-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-white/[0.06] text-sm">
                        <thead class="bg-white/[0.03]">
                            <tr>
                                <th class="aurora-th cursor-pointer select-none" @click="toggleSort('name')">
                                    Nama <span v-if="sortColumn === 'name'">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                </th>
                                <th class="aurora-th cursor-pointer select-none" @click="toggleSort('description')">
                                    Deskripsi <span v-if="sortColumn === 'description'">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                </th>
                                <th class="aurora-th text-center cursor-pointer select-none" @click="toggleSort('equipment_count')">
                                    Jumlah Alat <span v-if="sortColumn === 'equipment_count'">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                </th>
                                <th class="aurora-th text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/[0.06]">
                            <tr v-for="cat in sortedCategories" :key="cat.id" class="aurora-row">
                                <td class="aurora-td font-medium text-white">{{ cat.name }}</td>
                                <td class="aurora-td text-slate-400">{{ cat.description || '—' }}</td>
                                <td class="aurora-td text-center">{{ cat.equipment_count }}</td>
                                <td class="aurora-td text-right">
                                    <button class="rounded-md px-2 py-1 text-xs font-medium text-red-400 hover:bg-red-500/10" @click="destroy(cat)">Hapus</button>
                                </td>
                            </tr>
                             <tr v-if="!sortedCategories.length">
                                <td colspan="4" class="aurora-td py-8 text-center text-slate-500">Tidak ada kategori.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4"><Pagination :links="categories.links" /></div>
        </div>

        <div v-else-if="viewState === 'create'" class="space-y-6">
            <h2 class="text-lg font-semibold text-white mb-4">Tambah Kategori</h2>
            <form class="space-y-4" @submit.prevent="submit">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-300">Nama Kategori</label>
                    <input v-model="form.name" type="text" class="aurora-input" />
                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-400">{{ form.errors.name }}</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-300">Deskripsi (opsional)</label>
                    <textarea v-model="form.description" rows="3" class="aurora-input"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-white/[0.07]">
                    <button type="button" class="aurora-btn-ghost rounded-lg px-4 py-2 text-sm" @click="viewState = 'index'">Batal</button>
                    <button type="submit" :disabled="form.processing" class="aurora-btn-primary rounded-lg px-4 py-2 text-sm font-semibold disabled:opacity-60">Simpan</button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
