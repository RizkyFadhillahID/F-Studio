<!DOCTYPE html>
<html>

<body style="font-family:Arial,sans-serif;max-width:600px;margin:auto;padding:20px">
    <h2 style="color:#e94560">F-Studio SmartHub</h2>
    <h3 style="color:#dc3545">⚠️ Peringatan: Peminjaman Jatuh Tempo!</h3>
    <p>Halo <strong>{{ $loan->user->name }}</strong>,</p>
    <p>Peminjaman peralatan Anda telah <strong style="color:#dc3545">melewati batas waktu</strong> pengembalian.</p>
    <table style="border-collapse:collapse;width:100%">
        <tr>
            <td style="padding:8px;border:1px solid #ddd;font-weight:bold">Kode Peminjaman</td>
            <td style="padding:8px;border:1px solid #ddd">{{ $loan->loan_code }}</td>
        </tr>
        <tr>
            <td style="padding:8px;border:1px solid #ddd;font-weight:bold">Jatuh Tempo</td>
            <td style="padding:8px;border:1px solid #ddd;color:#dc3545">
                {{ \App\Helpers\DateHelper::formatDateID($loan->due_date) }}</td>
        </tr>
    </table>
    <p>Segera kembalikan peralatan ke F-Studio untuk menghindari sanksi lebih lanjut.</p>
    <p style="margin-top:20px;color:#666;font-size:12px">Email ini dikirim otomatis oleh sistem F-Studio SmartHub.</p>
</body>

</html>
