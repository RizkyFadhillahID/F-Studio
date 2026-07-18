<script setup>
import { useForm, Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const appName = computed(() => page.props.appName ?? 'F-Studio');

const form = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
});

function submit() {
    form.post('/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
}
</script>

<template>
    <Head title="Daftar Member" />

    <div class="flex min-h-screen">
        <!-- Brand panel (desktop) -->
        <div class="relative hidden w-1/2 flex-col justify-between overflow-hidden bg-slate-950 p-12 lg:flex">
            <div class="absolute inset-0 bg-gradient-to-br from-violet-600/40 via-slate-950 to-slate-950"></div>
            <div class="absolute -right-32 -top-32 h-96 w-96 rounded-full bg-violet-600/20 blur-3xl"></div>

            <Link href="/" class="relative flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-600 text-lg font-bold text-white shadow-lg shadow-indigo-600/30">F</div>
                <div>
                    <p class="text-lg font-bold text-white">{{ appName }}</p>
                    <p class="text-xs text-slate-400">Smart-Hub Management System</p>
                </div>
            </Link>

            <div class="relative">
                <h2 class="text-3xl font-bold leading-snug text-white">
                    Jadilah member dan<br />
                    <span class="bg-gradient-to-r from-violet-400 to-indigo-400 bg-clip-text text-transparent">pesan studio secara mandiri.</span>
                </h2>
                <ul class="mt-8 space-y-4">
                    <li v-for="f in ['Gratis langsung dapat ID member', 'Pesan ruangan kapan saja dari perangkat apa pun', 'Pantau status pemesanan secara real-time']" :key="f" class="flex items-start gap-3 text-sm text-slate-300">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-violet-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
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

                <h1 class="text-2xl font-bold text-slate-900">Buat akun member</h1>
                <p class="mt-2 text-sm text-slate-500">Daftar gratis dan mulai pesan ruangan studio.</p>

                <form class="mt-8 space-y-5" @submit.prevent="submit">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Nama Lengkap</label>
                        <input v-model="form.name" type="text" autocomplete="name" required
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                            placeholder="Nama Anda" />
                        <p v-if="form.errors.name" class="mt-1.5 text-sm text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Email</label>
                            <input v-model="form.email" type="email" autocomplete="username" required
                                class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                                placeholder="nama@email.com" />
                            <p v-if="form.errors.email" class="mt-1.5 text-sm text-red-600">{{ form.errors.email }}</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">No. Telepon <span class="font-normal text-slate-400">(opsional)</span></label>
                            <input v-model="form.phone" type="tel" autocomplete="tel"
                                class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                                placeholder="08xxxxxxxxxx" />
                        </div>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Password</label>
                        <input v-model="form.password" type="password" autocomplete="new-password" required
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                            placeholder="Minimal 8 karakter" />
                        <p v-if="form.errors.password" class="mt-1.5 text-sm text-red-600">{{ form.errors.password }}</p>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Konfirmasi Password</label>
                        <input v-model="form.password_confirmation" type="password" autocomplete="new-password" required
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                            placeholder="Ulangi password" />
                    </div>

                    <button type="submit" :disabled="form.processing"
                        class="w-full rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-600/25 transition hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-200 disabled:opacity-60">
                        {{ form.processing ? 'Mendaftarkan…' : 'Daftar Sekarang' }}
                    </button>
                </form>

                <p class="mt-8 text-center text-sm text-slate-500">
                    Sudah punya akun?
                    <Link href="/login" class="font-semibold text-indigo-600 hover:text-indigo-700 hover:underline">Masuk di sini</Link>
                </p>
            </div>
        </div>
    </div>
</template>
