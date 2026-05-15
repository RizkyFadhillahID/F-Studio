<!DOCTYPE html>
<html>

<body style="font-family:Arial,sans-serif;max-width:600px;margin:auto;padding:20px">
    <h2 style="color:#e94560">F-Studio SmartHub</h2>
    <h3>Pembaruan Status Booking</h3>
    <p>Halo <strong>{{ $booking->user->name }}</strong>,</p>
    <p>Status pemesanan ruangan Anda telah diperbarui.</p>
    <table style="border-collapse:collapse;width:100%">
        <tr>
            <td style="padding:8px;border:1px solid #ddd;font-weight:bold">Kode Booking</td>
            <td style="padding:8px;border:1px solid #ddd">{{ $booking->booking_code }}</td>
        </tr>
        <tr>
            <td style="padding:8px;border:1px solid #ddd;font-weight:bold">Judul</td>
            <td style="padding:8px;border:1px solid #ddd">{{ $booking->title }}</td>
        </tr>
        <tr>
            <td style="padding:8px;border:1px solid #ddd;font-weight:bold">Ruangan</td>
            <td style="padding:8px;border:1px solid #ddd">{{ $booking->room->name }}</td>
        </tr>
        <tr>
            <td style="padding:8px;border:1px solid #ddd;font-weight:bold">Status</td>
            <td style="padding:8px;border:1px solid #ddd;text-transform:capitalize">{{ $booking->status }}</td>
        </tr>
        @if ($booking->admin_notes)
            <tr>
                <td style="padding:8px;border:1px solid #ddd;font-weight:bold">Catatan Admin</td>
                <td style="padding:8px;border:1px solid #ddd">{{ $booking->admin_notes }}</td>
            </tr>
        @endif
    </table>
    <p style="margin-top:20px;color:#666;font-size:12px">Email ini dikirim otomatis oleh sistem F-Studio SmartHub.</p>
</body>

</html>
