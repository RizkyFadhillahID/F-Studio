<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { setApiToken } from '../lib/api';

const page = usePage();
const sidebarOpen = ref(false);

// Sidebar selalu tampil di layar lebar (lg ≥ 1024px), dan bisa di-toggle di
// tablet/mobile. Transform di-set eksplisit agar tidak bergantung pada quirk
// generasi kelas Tailwind.
const isDesktop = ref(false);
function syncDesktop() {
    isDesktop.value = window.innerWidth >= 1024;
}
onMounted(() => {
    syncDesktop();
    window.addEventListener('resize', syncDesktop);
});
onUnmounted(() => window.removeEventListener('resize', syncDesktop));

const sidebarStyle = computed(() => ({
    transform: sidebarOpen.value || isDesktop.value ? 'translateX(0)' : 'translateX(-100%)',
}));

// Collapse/expand sidebar (desktop) — preferensi disimpan per browser.
const collapsed = ref(false);
onMounted(() => {
    collapsed.value = window.localStorage.getItem('fs_sidebar_collapsed') === '1';
});
function toggleCollapsed() {
    collapsed.value = !collapsed.value;
    window.localStorage.setItem('fs_sidebar_collapsed', collapsed.value ? '1' : '0');
}

const user = computed(() => page.props.auth?.user ?? {});
const appName = computed(() => page.props.appName ?? 'F-Studio');
const flash = computed(() => page.props.flash ?? {});
const isAdmin = computed(() => user.value.role === 'admin');
const isReceptionist = computed(() => user.value.role === 'receptionist');

const dashboardHref = computed(() => (isReceptionist.value ? '/receptionist' : '/dashboard'));

// Heroicons (outline) path data
const icons = {
    home: 'M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75',
    room: 'M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21',
    camera: 'M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316zM16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z',
    tag: 'M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z M6 6h.008v.008H6V6z',
    users: 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
    calendar: 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5',
    box: 'M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z',
    tablet: 'M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3',
    clock: 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
    plus: 'M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z',
};

const nav = computed(() => [
    { group: 'Umum', items: [
        { label: 'Dashboard', href: dashboardHref.value, icon: icons.home, show: true },
    ]},
    // Data Master didahulukan — Kategori didahulukan karena jadi rujukan
    // saat mengisi form Peralatan; Ruangan berdiri sendiri jadi ditaruh terakhir.
    { group: 'Data Master', items: [
        { label: 'Kategori', href: '/categories', icon: icons.tag, show: isAdmin.value },
        { label: 'Peralatan', href: '/equipment', icon: icons.camera, show: isAdmin.value },
        { label: 'Ruangan', href: '/rooms', icon: icons.room, show: isAdmin.value },
    ]},
    { group: 'Transaksi', items: [
        { label: 'Pemesanan Ruangan', href: '/bookings', icon: icons.calendar, show: isAdmin.value },
        { label: 'Peminjaman Alat', href: '/loans', icon: icons.box, show: isAdmin.value },
    ]},
    // Manajemen Akun dipisah dari Data Master — akun pengguna beda sifat
    // dari aset fisik (ruangan/alat/kategori).
    { group: 'Manajemen Akun', items: [
        { label: 'Pengguna', href: '/users', icon: icons.users, show: isAdmin.value },
    ]},
    // Front Desk: orientasi (jadwal) → aksi layani pelanggan (booking & pinjam alat)
    // → tinjau riwayat booking sendiri.
    { group: 'Front Desk', items: [
        { label: 'Jadwal Hari Ini', href: '/receptionist/schedule', icon: icons.clock, show: isReceptionist.value },
        { label: 'Pemesanan Ruangan', href: '/receptionist/rooms', icon: icons.plus, show: isReceptionist.value },
        { label: 'Peminjaman Alat', href: '/receptionist/loans', icon: icons.box, show: isReceptionist.value },
        { label: 'Transaksi', href: '/receptionist/bookings', icon: icons.calendar, show: isReceptionist.value },
    ]},
].filter((section) => section.items.some((i) => i.show)));

const currentPath = computed(() => page.url.split('?')[0]);
function isActive(href) {
    if (href === '/receptionist' || href === '/dashboard') {
        return currentPath.value === href;
    }
    return currentPath.value === href || currentPath.value.startsWith(href + '/');
}

function logout() {
    setApiToken(null);
    router.post('/logout');
}

const roleMeta = computed(() => ({
    admin:        { label: 'Administrator', badge: 'bg-indigo-500/15 text-indigo-300 ring-indigo-500/30' },
    receptionist: { label: 'Resepsionis',   badge: 'bg-amber-500/15 text-amber-300 ring-amber-500/30' },
    member:       { label: 'Member',        badge: 'bg-emerald-500/15 text-emerald-300 ring-emerald-500/30' },
}[user.value.role] ?? { label: user.value.role, badge: 'bg-slate-500/15 text-slate-300 ring-slate-500/30' }));
</script>

<template>
    <div class="aurora-page">
        <!-- Overlay (mobile) -->
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 z-30 bg-black/60 backdrop-blur-sm lg:hidden"
            @click="sidebarOpen = false"
        ></div>

        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col border-r border-white/[0.07] bg-[#0d0e17]/95 text-slate-300 backdrop-blur-xl transition-[transform,width] duration-200"
            :class="collapsed ? 'lg:w-20' : 'lg:w-64'"
            :style="sidebarStyle"
        >
            <div class="flex h-16 items-center gap-2.5 border-b border-white/[0.07] px-5" :class="collapsed && 'lg:justify-center lg:px-0'">
                <template v-if="!collapsed">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-500 to-violet-600 text-sm font-bold text-white shadow-lg shadow-indigo-600/30">F</div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-bold text-white">{{ appName }}</p>
                        <p class="truncate text-[10px] uppercase tracking-wider text-slate-500">Smart-Hub System</p>
                    </div>
                </template>
                <!-- Toggle collapse (desktop only, icon saja) -->
                <button
                    class="hidden shrink-0 rounded-lg p-1.5 text-slate-500 transition hover:bg-white/5 hover:text-white lg:flex"
                    @click="toggleCollapsed"
                    :aria-label="collapsed ? 'Perluas menu' : 'Ciutkan menu'"
                    :title="collapsed ? 'Perluas menu' : 'Ciutkan menu'"
                >
                    <svg class="h-5 w-5 shrink-0 transition-transform" :class="collapsed && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" /></svg>
                </button>
            </div>

            <nav class="flex-1 space-y-6 overflow-y-auto overflow-x-hidden px-3 py-5">
                <div v-for="section in nav" :key="section.group">
                    <p v-show="!collapsed" class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-widest text-slate-600">{{ section.group }}</p>
                    <div class="space-y-0.5">
                        <template v-for="item in section.items" :key="item.href">
                            <Link
                                v-if="item.show"
                                :href="item.href"
                                :title="collapsed ? item.label : null"
                                class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition"
                                :class="[isActive(item.href) ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25' : 'text-slate-400 hover:bg-white/5 hover:text-white', collapsed && 'lg:justify-center']"
                                @click="sidebarOpen = false"
                            >
                                <svg class="h-[18px] w-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" /></svg>
                                <span v-show="!collapsed">{{ item.label }}</span>
                            </Link>
                        </template>
                    </div>
                </div>
            </nav>

            <!-- User card → profil -->
            <div class="border-t border-white/[0.07] p-4">
                <Link href="/profile" class="flex items-center gap-3 rounded-xl p-1 transition hover:bg-white/5" :class="collapsed && 'lg:justify-center'" @click="sidebarOpen = false">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 text-sm font-semibold text-white">
                        {{ (user.name || '?').charAt(0).toUpperCase() }}
                    </div>
                    <div v-show="!collapsed" class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-white">{{ user.name }}</p>
                        <span class="mt-0.5 inline-block rounded-full px-2 py-0.5 text-[10px] font-medium ring-1" :class="roleMeta.badge">{{ roleMeta.label }}</span>
                    </div>
                    <svg v-show="!collapsed" class="h-4 w-4 shrink-0 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.28z M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                </Link>
            </div>
        </aside>

        <!-- Main -->
        <div class="transition-[padding] duration-200 flex flex-col min-h-screen" :class="collapsed ? 'lg:pl-20' : 'lg:pl-64'">
            <!-- Topbar -->
            <header class="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-white/[0.07] bg-[#0a0b12]/80 px-4 backdrop-blur-xl sm:px-6">
                <button
                    class="rounded-lg p-2 text-slate-400 transition hover:bg-white/5 hover:text-white lg:hidden"
                    @click="sidebarOpen = true"
                    aria-label="Buka menu"
                >
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
                </button>

                <div class="flex-1">
                    <slot name="header">
                        <h1 class="text-base font-semibold text-white">{{ $page.props.title ?? '' }}</h1>
                    </slot>
                </div>

                <div class="flex items-center gap-3">
                    <div class="hidden text-right sm:block">
                        <p class="text-sm font-semibold text-white">{{ user.name }}</p>
                        <p class="text-xs text-slate-500">{{ roleMeta.label }}</p>
                    </div>
                    <button
                        class="flex items-center gap-1.5 rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm font-medium text-slate-300 transition hover:border-red-500/30 hover:bg-red-500/10 hover:text-red-300"
                        @click="logout"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" /></svg>
                        Keluar
                    </button>
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
            <main class="flex-1 p-4 sm:p-6">
                <slot />
            </main>

            <!-- Footer -->
            <footer class="border-t border-white/[0.05] py-4 px-6 text-center text-xs text-slate-600 bg-white/[0.01]">
                &copy; 2026 Rizky Fadhillah. All rights reserved.
            </footer>
        </div>
    </div>
</template>
