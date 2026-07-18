// Label Bahasa Indonesia untuk status transaksi — dipakai di seluruh tabel dan
// dashboard supaya konsisten (bukan menampilkan nilai status Inggris mentah
// dari database seperti "pending"/"approved").

export const bookingStatusLabel = {
    pending: 'Menunggu',
    approved: 'Disetujui',
    completed: 'Selesai',
    rejected: 'Ditolak',
    cancelled: 'Batal',
};

export const loanStatusLabel = {
    pending: 'Menunggu Persetujuan',
    approved: 'Disetujui',
    active: 'Sedang Dipinjam',
    overdue: 'Terlambat',
    returned: 'Selesai',
    rejected: 'Ditolak',
    cancelled: 'Batal',
};
