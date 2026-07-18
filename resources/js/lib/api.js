import axios from 'axios';

// Instance axios untuk memanggil REST API /api/v1 (diamankan token Sanctum).
// Dipakai frontend untuk integrasi API langsung dari browser (mis. check-in).
const api = axios.create({
    baseURL: '/api/v1',
    headers: {
        Accept: 'application/json',
    },
});

// Set / hapus Bearer token pada header default.
export function setApiToken(token) {
    if (token) {
        api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        window.localStorage.setItem('api_token', token);
    } else {
        delete api.defaults.headers.common['Authorization'];
        window.localStorage.removeItem('api_token');
    }
}

// Pulihkan token dari localStorage saat halaman dimuat ulang.
const stored = window.localStorage.getItem('api_token');
if (stored) {
    setApiToken(stored);
}

export default api;
