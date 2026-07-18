<script setup>
import { useForm, Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    portal: { type: String, default: null },
});

const page = usePage();
const appName = computed(() => page.props.appName ?? 'F-Studio');

const portalMeta = computed(() => ({
    admin:        { label: 'Portal Admin',       badge: 'bg-indigo-100 text-indigo-700 ring-indigo-200',   desc: 'Kelola data master, persetujuan, dan operasional studio.' },
    receptionist: { label: 'Portal Resepsionis', badge: 'bg-amber-100 text-amber-700 ring-amber-200',      desc: 'Layani pemesanan dan peminjaman pelanggan walk-in.' },
    member:       { label: 'Portal Member',      badge: 'bg-emerald-100 text-emerald-700 ring-emerald-200', desc: 'Pesan ruangan studio dan pantau pemesanan Anda.' },
}[props.portal] ?? null));

const form = useForm({
    email: '',
    password: '',
    remember: false,
    portal: props.portal,
});

function submit() {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
}
</script>

<template>
    <Head title="Login" />

    <div class="flex min-h-screen">
        <!-- Brand panel (desktop) -->
        <div class="relative hidden w-1/2 flex-col justify-between overflow-hidden bg-slate-950 p-12 lg:flex">
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-600/40 via-slate-950 to-slate-950"></div>
            <div class="absolute -left-32 -top-32 h-96 w-96 rounded-full bg-indigo-600/20 blur-3xl"></div>
            <div class="absolute -bottom-24 right-0 h-80 w-80 rounded-full bg-violet-600/10 blur-3xl"></div>

            <Link href="/" class="relative flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-600 text-lg font-bold text-white shadow-lg shadow-indigo-600/30">F</div>
                <div>
                    <p class="text-lg font-bold text-white">{{ appName }}</p>
                    <p class="text-xs text-slate-400">Smart-Hub Management System</p>
                </div>
            </Link>

            <div class="relative">
                <h2 class="text-3xl font-bold leading-snug text-white">
                    Kelola studio kreatif Anda,<br />
                    <span class="bg-gradient-to-r from-indigo-400 to-violet-400 bg-clip-text text-transparent">semua dalam satu platform.</span>
                </h2>
                <ul class="mt-8 space-y-4">
                    <li v-for="f in ['Pemesanan ruangan real-time tanpa bentrok jadwal', 'Pelacakan stok peralatan otomatis']" :key="f" class="flex items-start gap-3 text-sm text-slate-300">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-indigo-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        {{ f }}
                    </li>
                </ul>
            </div>

            <p class="relative text-xs text-slate-500">&copy; 2026 Rizky Fadhillah. All rights reserved.</p>
        </div>

        <!-- Form panel -->
        <div class="flex w-full flex-col justify-center bg-white px-6 py-12 sm:px-12 lg:w-1/2 xl:px-24">
            <div class="mx-auto w-full max-w-md">
                <Link href="/" class="mb-10 flex items-center gap-2 lg:hidden">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600 text-base font-bold text-white">F</div>
                    <span class="text-lg font-bold text-slate-900">{{ appName }}</span>
                </Link>

                <span v-if="portalMeta" class="mb-3 inline-block rounded-full px-3 py-1 text-xs font-semibold ring-1" :class="portalMeta.badge">
                    {{ portalMeta.label }}
                </span>
                <h1 class="text-2xl font-bold text-slate-900">Selamat datang kembali</h1>
                <p class="mt-2 text-sm text-slate-500">{{ portalMeta?.desc ?? 'Masuk untuk mengelola pemesanan dan peminjaman Anda.' }}</p>

                <form class="mt-8 space-y-5" @submit.prevent="submit">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Email</label>
                        <input
                            v-model="form.email"
                            type="email"
                            autocomplete="username"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                            placeholder="nama@email.com"
                            required
                        />
                        <p v-if="form.errors.email" class="mt-1.5 text-sm text-red-600">{{ form.errors.email }}</p>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Password</label>
                        <input
                            v-model="form.password"
                            type="password"
                            autocomplete="current-password"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                            placeholder="••••••••"
                            required
                        />
                        <p v-if="form.errors.password" class="mt-1.5 text-sm text-red-600">{{ form.errors.password }}</p>
                    </div>

                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input v-model="form.remember" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" />
                        Ingat saya
                    </label>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-600/25 transition hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-200 disabled:opacity-60"
                    >
                        {{ form.processing ? 'Memproses…' : 'Masuk' }}
                    </button>
                </form>

                <p v-if="portal !== 'admin' && portal !== 'receptionist'" class="mt-8 text-center text-sm text-slate-500">
                    Belum punya akun?
                    <Link href="/register" class="font-semibold text-indigo-600 hover:text-indigo-700 hover:underline">Daftar sebagai member</Link>
                </p>
                <p v-else class="mt-8 text-center text-xs text-slate-400">
                    Akun {{ portalMeta?.label?.replace('Portal ', '') }} dibuat oleh administrator.
                </p>

                <div class="mt-6 border-t border-slate-100 pt-5 text-center text-xs text-slate-400">
                    Masuk sebagai:
                    <Link href="/login?portal=admin" class="mx-1 font-medium" :class="portal === 'admin' ? 'text-indigo-600' : 'text-slate-500 hover:text-indigo-600'">Admin</Link>·
                    <Link href="/login?portal=receptionist" class="mx-1 font-medium" :class="portal === 'receptionist' ? 'text-indigo-600' : 'text-slate-500 hover:text-indigo-600'">Resepsionis</Link>·
                    <Link href="/login?portal=member" class="mx-1 font-medium" :class="portal === 'member' ? 'text-indigo-600' : 'text-slate-500 hover:text-indigo-600'">Member</Link>
                </div>
            </div>
        </div>
    </div>
</template>
