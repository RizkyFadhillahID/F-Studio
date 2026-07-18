// Perhitungan pembayaran SIMULASI — harus sinkron dengan App\Services\PaymentService.
// Dipakai hanya sebagai fallback kalau harga per-item tidak tersedia (mis. ruangan/alat sudah dihapus).
const FALLBACK_ROOM_HOURLY_RATE = 50000;
const FALLBACK_LOAN_ITEM_DAILY_RATE = 15000;

export function formatRupiah(n) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(n || 0);
}

/** Nominal booking = jumlah jam (dibulatkan ke atas) × harga per jam ruangan. */
export function bookingAmount(booking) {
    const start = new Date(booking.start_datetime);
    const end = new Date(booking.end_datetime);
    const hours = Math.max(1, Math.ceil((end - start) / (1000 * 60 * 60)));
    const rate = Number(booking.room?.price_per_hour ?? FALLBACK_ROOM_HOURLY_RATE);
    return Math.round(hours * rate);
}

/** Nominal peminjaman = jumlah hari × total harga harian tiap item (quantity × harga/hari alat). */
export function loanAmount(loan) {
    const dailySubtotal = (loan.items ?? []).reduce(
        (s, it) => s + Number(it.quantity || 0) * Number(it.equipment?.price_per_day ?? FALLBACK_LOAN_ITEM_DAILY_RATE),
        0
    );
    const loanDate = loan.loan_date ? new Date(loan.loan_date) : new Date(loan.created_at);
    const dueDate = new Date(loan.due_date);
    const days = Math.max(1, Math.round((dueDate - loanDate) / (1000 * 60 * 60 * 24)) + 1);
    return Math.round(Math.max(dailySubtotal, FALLBACK_LOAN_ITEM_DAILY_RATE) * days);
}

export const paymentMethods = [
    { key: 'cash', label: 'Tunai', icon: '💵' },
    { key: 'transfer', label: 'Transfer Bank', icon: '🏦' },
    { key: 'ewallet', label: 'E-Wallet', icon: '📱' },
    { key: 'qris', label: 'QRIS', icon: '🔳' },
];
