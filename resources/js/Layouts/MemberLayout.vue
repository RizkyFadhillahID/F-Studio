<script setup>
import { computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { setApiToken } from '../lib/api';

const page = usePage();
const user = computed(() => page.props.auth?.user ?? {});
const appName = computed(() => page.props.appName ?? 'F-Studio');
const flash = computed(() => page.props.flash ?? {});

const icons = {
    home: 'M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75',
    room: 'M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21',
    camera: 'M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316zM16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z',
    calendar: 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5',
};

const navItems = [
    { label: 'Beranda', href: '/member', icon: icons.home },
    { label: 'Ruangan', href: '/member/rooms', icon: icons.room },
    { label: 'Peralatan', href: '/member/equipment', icon: icons.camera },
    { label: 'Transaksi', href: '/member/bookings', icon: icons.calendar },
];

const currentPath = computed(() => page.url.split('?')[0]);
function isActive(href) {
    return href === '/member' ? currentPath.value === '/member' : currentPath.value.startsWith(href);
}

function logout() {
    setApiToken(null);
    router.post('/logout');
}
</script>

<template>
    <div class="aurora-page flex flex-col min-h-screen">
        <!-- Header -->
        <header class="sticky top-0 z-20 border-b border-white/[0.07] bg-[#0a0b12]/90 backdrop-blur-xl">
            <div class="relative flex h-16 items-center gap-3 px-4 sm:px-6">
                <div class="flex shrink-0 items-center gap-2.5">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-500 to-violet-600 text-xs font-bold text-white shadow-lg shadow-indigo-600/30">F</div>
                    <span class="hidden text-sm font-bold text-white sm:block">{{ appName }}</span>
                </div>

                <!-- Menu navigasi — di dalam header, dipusatkan presisi terlepas dari lebar sisi kiri/kanan -->
                <nav class="absolute left-1/2 flex max-w-[calc(100%-13rem)] -translate-x-1/2 items-center gap-1 overflow-x-auto px-1">
                    <Link
                        v-for="item in navItems"
                        :key="item.href"
                        :href="item.href"
                        class="flex shrink-0 items-center gap-1.5 rounded-t-lg border-b-2 px-3 py-1.5 text-sm font-medium transition sm:px-3.5"
                        :class="isActive(item.href) ? 'border-indigo-500 text-white' : 'border-transparent text-slate-400 hover:bg-white/5 hover:text-white'"
                    >
                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" /></svg>
                        <span class="hidden sm:inline">{{ item.label }}</span>
                    </Link>
                </nav>

                <div class="ml-auto flex shrink-0 items-center gap-2">
                    <Link href="/profile" class="flex items-center gap-2 rounded-full py-1 pl-1 pr-3 transition hover:bg-white/5">
                        <div class="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 text-xs font-semibold text-white">
                            {{ (user.name || '?').charAt(0).toUpperCase() }}
                        </div>
                        <span class="hidden text-sm font-medium text-white lg:block">{{ user.name }}</span>
                    </Link>
                    <button class="rounded-lg p-2 text-slate-400 transition hover:bg-white/5 hover:text-red-300" aria-label="Keluar" @click="logout">
                        <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" /></svg>
                    </button>
                </div>
            </div>
        </header>

        <!-- Flash -->
        <div v-if="flash.success || flash.error" class="px-4 pt-4 sm:px-6">
            <div v-if="flash.success" class="flex items-center gap-2 rounded-xl border border-emerald-500/25 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
                <svg class="h-5 w-5 shrink-0 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                {{ flash.success }}
            </div>
            <div v-if="flash.error" class="flex items-center gap-2 rounded-xl border border-red-500/25 bg-red-500/10 px-4 py-3 text-sm text-red-300">
                <svg class="h-5 w-5 shrink-0 text-red-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                {{ flash.error }}
            </div>
        </div>

        <!-- Content -->
        <main class="mx-auto max-w-6xl p-4 sm:p-6 flex-1 w-full">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="border-t border-white/[0.05] py-4 px-6 text-center text-xs text-slate-600 bg-white/[0.01]">
            &copy; 2026 Rizky Fadhillah. All rights reserved.
        </footer>
    </div>
</template>
