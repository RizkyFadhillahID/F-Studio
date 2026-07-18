import { createApp, h } from 'vue';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { setApiToken } from './lib/api';

const appName = import.meta.env.VITE_APP_NAME || 'F-Studio';

// Sinkronkan token Sanctum (dibagikan lewat shared props) ke axios setiap
// kali navigasi Inertia, sehingga panggilan API dari browser selalu terotorisasi.
function syncToken(page) {
    const token = page?.props?.auth?.token ?? null;
    if (token) {
        setApiToken(token);
    }
}

router.on('navigate', (event) => {
    syncToken(event.detail.page);

    // Sapu bersih modal yang tertinggal di <body> setelah pindah halaman
    // (Teleport bisa meninggalkan overlay jika navigasi terjadi saat modal terbuka).
    setTimeout(() => {
        document.querySelectorAll('.fs-modal').forEach((el) => el.remove());
    }, 250);
});

createInertiaApp({
    title: (title) => (title ? `${title} — ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        syncToken(props.initialPage);
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#4f46e5',
    },
});
