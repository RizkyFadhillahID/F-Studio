<!DOCTYPE html>
<html>

<body style="font-family:Arial,sans-serif;max-width:600px;margin:auto;padding:20px">
    <h2 style="color:#e94560">F-Studio SmartHub</h2>
    <h3>Pembaruan Status Peminjaman</h3>
    <p>Halo <strong>{{ $loan->user->name }}</strong>,</p>
    <p>Status peminjaman peralatan Anda telah diperbarui.</p>
    <table style="border-collapse:collapse;width:100%">
        <tr>
            <td style="padding:8px;border:1px solid #ddd;font-weight:bold">Kode Peminjaman</td>
            <td style="padding:8px;border:1px solid #ddd">{{ $loan->loan_code }}</td>
        </tr>
        <tr>
            <td style="padding:8px;border:1px solid #ddd;font-weight:bold">Tujuan</td>
            <td style="padding:8px;border:1px solid #ddd">{{ $loan->purpose }}</td>
        </tr>
        <tr>
            <td style="padding:8px;border:1px solid #ddd;font-weight:bold">Status</td>
            <td style="padding:8px;border:1px solid #ddd;text-transform:capitalize">{{ $loan->status }}</td>
        </tr>
        @if ($loan->admin_notes)
            <tr>
                <td style="padding:8px;border:1px solid #ddd;font-weight:bold">Catatan Admin</td>
                <td style="padding:8px;border:1px solid #ddd">{{ $loan->admin_notes }}</td>
            </tr>
        @endif
    </table>
    <p style="margin-top:20px;color:#666;font-size:12px">Email ini dikirim otomatis oleh sistem F-Studio SmartHub.</p>
</body>

</html>
