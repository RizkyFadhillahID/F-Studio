<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import Pagination from '../../Components/Pagination.vue';

const props = defineProps({
    users: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    roleCounts: { type: Object, default: () => ({}) },
});

const viewState = ref('index'); // 'index' | 'create' | 'edit'
const editing = ref(null);
const search = ref(props.filters.search ?? '');

const tabs = [
    { key: '', label: 'Semua', countKey: 'all' },
    { key: 'admin', label: 'Admin', countKey: 'admin' },
    { key: 'receptionist', label: 'Resepsionis', countKey: 'receptionist' },
    { key: 'member', label: 'Anggota', countKey: 'member' },
];

function setTab(role) {
    router.get('/users', { role: role || undefined, search: search.value || undefined }, { preserveState: true, replace: true });
}

const roleLabels = { admin: 'Administrator', receptionist: 'Resepsionis', member: 'Anggota' };
const roleBadge = { admin: 'aurora-pill-info', receptionist: 'aurora-pill-warn', member: 'aurora-pill-ok' };

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

const sortedUsers = computed(() => {
    const list = [...(props.users.data || [])];
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
            } else if (typeof valA === 'boolean') {
                const numA = valA ? 1 : 0;
                const numB = valB ? 1 : 0;
                return sortDirection.value === 'asc' ? numA - numB : numB - numA;
            } else {
                return sortDirection.value === 'asc'
                    ? valA - valB
                    : valB - valA;
            }
        });
    }
    return list;
});

const form = useForm({
    name: '', email: '', password: '', password_confirmation: '', role: 'member', phone: '', is_active: true,
});

function openCreate() {
    editing.value = null;
    form.reset();
    form.clearErrors();
    viewState.value = 'create';
}

function openEdit(user) {
    editing.value = user;
    form.clearErrors();
    form.name = user.name;
    form.email = user.email;
    form.role = user.role;
    form.phone = user.phone ?? '';
    form.is_active = !!user.is_active;
    form.password = '';
    form.password_confirmation = '';
    viewState.value = 'edit';
}

function submit() {
    const opts = { preserveScroll: true, onSuccess: () => { viewState.value = 'index'; } };
    if (editing.value) {
        form.put(`/users/${editing.value.id}`, opts);
    } else {
        form.post('/users', opts);
    }
}

function destroy(user) {
    if (confirm(`Hapus pengguna "${user.name}"?`)) {
        router.delete(`/users/${user.id}`, { preserveScroll: true });
    }
}

function toggleActive(user) {
    router.patch(`/users/${user.id}/toggle-active`, {}, { preserveScroll: true });
}

function doSearch() {
    router.get('/users', { search: search.value || undefined, role: props.filters.role || undefined }, { preserveState: true, replace: true });
}
</script>

<template>
    <Head title="Pengguna" />
    <AppLayout>
        <template #header><h1 class="text-base font-semibold text-white">Data Master · Pengguna</h1></template>

        <div v-if="viewState === 'index'">
            <!-- Tab role: pisahkan admin / resepsionis / member -->
            <div class="aurora-card mb-4 flex flex-wrap gap-1.5 p-1.5">
                <button
                    v-for="tab in tabs"
                    :key="tab.key"
                    class="flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition"
                    :class="(filters.role ?? '') === tab.key ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25' : 'text-slate-400 hover:bg-white/5 hover:text-white'"
                    @click="setTab(tab.key)"
                >
                    {{ tab.label }}
                    <span
                        class="rounded-full px-1.5 py-0.5 text-[11px] font-semibold"
                        :class="(filters.role ?? '') === tab.key ? 'bg-white/20 text-white' : 'bg-white/5 text-slate-500'"
                    >{{ roleCounts[tab.countKey] ?? 0 }}</span>
                </button>
            </div>

            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <form class="flex gap-2" @submit.prevent="doSearch">
                    <input v-model="search" type="text" placeholder="Cari nama/email…" class="aurora-input sm:w-64" />
                    <button type="submit" class="aurora-btn-ghost rounded-lg px-3 py-2 text-sm">Cari</button>
                </form>
                <button class="aurora-btn-primary rounded-lg px-4 py-2 text-sm font-semibold" @click="openCreate">+ Tambah Pengguna</button>
            </div>

            <div class="aurora-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-white/[0.06] text-sm">
                        <thead class="bg-white/[0.03]">
                            <tr>
                                <th class="aurora-th cursor-pointer select-none" @click="toggleSort('name')">
                                    Nama <span v-if="sortColumn === 'name'">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                </th>
                                <th class="aurora-th cursor-pointer select-none" @click="toggleSort('email')">
                                    Email <span v-if="sortColumn === 'email'">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                </th>
                                <th class="aurora-th cursor-pointer select-none" @click="toggleSort('role')">
                                    Peran <span v-if="sortColumn === 'role'">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                </th>
                                <th class="aurora-th text-center cursor-pointer select-none" @click="toggleSort('is_active')">
                                    Status <span v-if="sortColumn === 'is_active'">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                </th>
                                <th class="aurora-th text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/[0.06]">
                            <tr v-for="user in sortedUsers" :key="user.id" class="aurora-row">
                                <td class="aurora-td font-medium text-white">{{ user.name }}</td>
                                <td class="aurora-td text-slate-400">{{ user.email }}</td>
                                <td class="aurora-td">
                                    <span class="aurora-pill" :class="roleBadge[user.role] ?? 'aurora-pill-muted'">{{ roleLabels[user.role] ?? user.role }}</span>
                                </td>
                                <td class="aurora-td text-center">
                                    <button @click="toggleActive(user)" class="aurora-pill" :class="user.is_active ? 'aurora-pill-ok' : 'aurora-pill-muted'">
                                        {{ user.is_active ? 'Aktif' : 'Nonaktif' }}
                                    </button>
                                </td>
                                <td class="aurora-td text-right">
                                    <button class="rounded-md px-2 py-1 text-xs font-medium text-indigo-400 hover:bg-indigo-500/10" @click="openEdit(user)">Ubah</button>
                                    <button class="rounded-md px-2 py-1 text-xs font-medium text-red-400 hover:bg-red-500/10" @click="destroy(user)">Hapus</button>
                                </td>
                            </tr>
                            <tr v-if="!sortedUsers.length">
                                <td colspan="5" class="aurora-td py-8 text-center text-slate-500">Tidak ada pengguna.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4"><Pagination :links="users.links" /></div>
        </div>

        <div v-else-if="viewState === 'create' || viewState === 'edit'" class="space-y-6">
            <h2 class="text-lg font-semibold text-white">{{ editing ? 'Ubah Pengguna' : 'Tambah Pengguna' }}</h2>
            <form class="space-y-4" @submit.prevent="submit">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-300">Nama</label>
                        <input v-model="form.name" type="text" class="aurora-input" />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-400">{{ form.errors.name }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-300">Email</label>
                        <input v-model="form.email" type="email" class="aurora-input" />
                        <p v-if="form.errors.email" class="mt-1 text-sm text-red-400">{{ form.errors.email }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-300">Peran</label>
                        <select v-model="form.role" class="aurora-input">
                            <option value="member">Anggota</option>
                            <option value="receptionist">Resepsionis</option>
                            <option value="admin">Administrator</option>
                        </select>
                        <p v-if="form.errors.role" class="mt-1 text-sm text-red-400">{{ form.errors.role }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-300">Telepon (opsional)</label>
                        <input v-model="form.phone" type="text" class="aurora-input" />
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-300">
                            {{ editing ? 'Password baru (opsional)' : 'Password' }}
                        </label>
                        <input v-model="form.password" type="password" autocomplete="new-password" class="aurora-input" />
                        <p v-if="form.errors.password" class="mt-1 text-sm text-red-400">{{ form.errors.password }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-300">Konfirmasi Password</label>
                        <input v-model="form.password_confirmation" type="password" autocomplete="new-password" class="aurora-input" />
                    </div>
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-400">
                    <input v-model="form.is_active" type="checkbox" class="rounded border-white/20 bg-white/5 text-indigo-600 focus:ring-indigo-500" /> Akun aktif
                </label>
                <div class="flex justify-end gap-2 pt-2 border-t border-white/[0.07]">
                    <button type="button" class="aurora-btn-ghost rounded-lg px-4 py-2 text-sm" @click="viewState = 'index'">Batal</button>
                    <button type="submit" :disabled="form.processing" class="aurora-btn-primary rounded-lg px-4 py-2 text-sm font-semibold disabled:opacity-60">Simpan</button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
