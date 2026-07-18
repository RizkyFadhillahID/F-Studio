<script setup>
import { computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import MemberLayout from '../../Layouts/MemberLayout.vue';

const props = defineProps({
    profile: { type: Object, required: true },
});

const page = usePage();
// Member pakai layout floating-nav; admin & resepsionis pakai sidebar.
const layout = computed(() => (page.props.auth?.user?.role === 'member' ? MemberLayout : AppLayout));

const roleMeta = computed(() => ({
    admin:        { label: 'Administrator', badge: 'aurora-pill-info' },
    receptionist: { label: 'Resepsionis',   badge: 'aurora-pill-warn' },
    member:       { label: 'Member',        badge: 'aurora-pill-ok' },
}[props.profile.role] ?? { label: props.profile.role, badge: 'aurora-pill-muted' }));

const infoForm = useForm({
    name: props.profile.name,
    email: props.profile.email,
    phone: props.profile.phone ?? '',
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

function saveInfo() {
    infoForm.put('/profile', { preserveScroll: true });
}

function savePassword() {
    passwordForm.put('/profile/password', {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
    });
}
</script>

<template>
    <Head title="Profil Saya" />
    <component :is="layout">
        <template #header><h1 class="text-base font-semibold text-white">Profil Saya</h1></template>

        <div class="mx-auto max-w-3xl space-y-6">
            <!-- Kartu identitas -->
            <section class="aurora-card overflow-hidden">
                <div class="h-20 bg-gradient-to-r from-indigo-600 to-violet-600"></div>
                <div class="px-6 pb-6">
                    <div class="-mt-8 flex items-end gap-4">
                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 text-2xl font-bold text-white ring-4 ring-[#12141f]">
                            {{ (profile.name || '?').charAt(0).toUpperCase() }}
                        </div>
                        <div class="pb-1">
                            <p class="text-lg font-bold text-white">{{ profile.name }}</p>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="aurora-pill" :class="roleMeta.badge">{{ roleMeta.label }}</span>
                                <span v-if="profile.member_id" class="rounded-full bg-white/5 px-2.5 py-0.5 font-mono text-xs text-slate-400">{{ profile.member_id }}</span>
                            </div>
                        </div>
                    </div>
                    <p v-if="profile.joined_at" class="mt-3 text-xs text-slate-500">Bergabung sejak {{ profile.joined_at }}</p>
                </div>
            </section>

            <!-- Informasi profil -->
            <section class="aurora-card p-6">
                <h2 class="text-sm font-bold text-white">Informasi Profil</h2>
                <p class="mt-1 text-xs text-slate-500">Perbarui nama, email, dan nomor telepon akun Anda.</p>

                <form class="mt-5 space-y-4" @submit.prevent="saveInfo">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-300">Nama Lengkap</label>
                        <input v-model="infoForm.name" type="text" class="aurora-input" />
                        <p v-if="infoForm.errors.name" class="mt-1 text-sm text-red-400">{{ infoForm.errors.name }}</p>
                    </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-300">Email</label>
                            <input v-model="infoForm.email" type="email" class="aurora-input" />
                            <p v-if="infoForm.errors.email" class="mt-1 text-sm text-red-400">{{ infoForm.errors.email }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-300">No. Telepon</label>
                            <input v-model="infoForm.phone" type="tel" class="aurora-input" />
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" :disabled="infoForm.processing" class="aurora-btn-primary rounded-lg px-5 py-2.5 text-sm font-semibold disabled:opacity-60">
                            {{ infoForm.processing ? 'Menyimpan…' : 'Simpan Perubahan' }}
                        </button>
                    </div>
                </form>
            </section>

            <!-- Ganti password -->
            <section class="aurora-card p-6">
                <h2 class="text-sm font-bold text-white">Ganti Password</h2>
                <p class="mt-1 text-xs text-slate-500">Gunakan password yang kuat dan tidak dipakai di layanan lain.</p>

                <form class="mt-5 space-y-4" @submit.prevent="savePassword">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-300">Password Saat Ini</label>
                        <input v-model="passwordForm.current_password" type="password" autocomplete="current-password" class="aurora-input" />
                        <p v-if="passwordForm.errors.current_password" class="mt-1 text-sm text-red-400">{{ passwordForm.errors.current_password }}</p>
                    </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-300">Password Baru</label>
                            <input v-model="passwordForm.password" type="password" autocomplete="new-password" class="aurora-input" />
                            <p v-if="passwordForm.errors.password" class="mt-1 text-sm text-red-400">{{ passwordForm.errors.password }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-300">Konfirmasi Password Baru</label>
                            <input v-model="passwordForm.password_confirmation" type="password" autocomplete="new-password" class="aurora-input" />
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" :disabled="passwordForm.processing" class="aurora-btn-ghost rounded-lg px-5 py-2.5 text-sm font-semibold disabled:opacity-60">
                            {{ passwordForm.processing ? 'Menyimpan…' : 'Ganti Password' }}
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </component>
</template>
