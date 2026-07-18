<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\EquipmentLoan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Laporan transaksi (pemesanan ruangan + peminjaman alat digabung jadi satu
 * daftar) untuk admin (semua transaksi) dan resepsionis (transaksi yang dia
 * buat sendiri, sama seperti scope halaman Transaksi resepsionis).
 */
class ReportController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Reports/Index', $this->buildReportData($request, null));
    }

    public function receptionist(Request $request)
    {
        return Inertia::render('Receptionist/Reports', $this->buildReportData($request, $request->user()->id));
    }

    public function export(Request $request): StreamedResponse
    {
        return $this->streamCsv($this->reportRows($request, null));
    }

    public function receptionistExport(Request $request): StreamedResponse
    {
        return $this->streamCsv($this->reportRows($request, $request->user()->id));
    }

    private function buildReportData(Request $request, ?int $userId): array
    {
        $rows = $this->reportRows($request, $userId);

        $paid = $rows->where('payment_status', 'paid');

        return [
            'rows'    => $rows->values(),
            'summary' => [
                'total_transaksi'  => $rows->count(),
                'total_pendapatan' => (float) $paid->sum('amount'),
                'total_booking'    => $rows->where('type', 'booking')->count(),
                'total_loan'       => $rows->where('type', 'loan')->count(),
                'lunas'            => $paid->count(),
                'belum_bayar'      => $rows->where('payment_status', 'unpaid')->count(),
            ],
            'filters' => $request->only(['date_from', 'date_to', 'jenis', 'payment_status']),
        ];
    }

    /** Ambil booking + loan dalam rentang filter, digabung jadi satu koleksi baris seragam. */
    private function reportRows(Request $request, ?int $userId): Collection
    {
        [$start, $end] = $this->resolveDateRange($request);
        $jenis = in_array($request->jenis, ['booking', 'loan'], true) ? $request->jenis : null;
        $paymentStatus = in_array($request->payment_status, ['paid', 'unpaid'], true) ? $request->payment_status : null;

        $rows = collect();

        if ($jenis !== 'loan') {
            $bookings = Booking::with(['user', 'room'])
                ->when($userId, fn ($q) => $q->where('user_id', $userId))
                ->when($paymentStatus, fn ($q) => $q->where('payment_status', $paymentStatus))
                ->whereBetween('created_at', [$start, $end])
                ->get();

            foreach ($bookings as $b) {
                $rows->push([
                    'id'             => $b->id,
                    'type'           => 'booking',
                    'code'           => $b->booking_code,
                    'customer'       => $b->user?->name ?? $b->customer_name ?? '—',
                    'item'           => $b->room?->name ?? '—',
                    'created_at'     => $b->created_at,
                    'status'         => $b->status,
                    'payment_status' => $b->payment_status,
                    'payment_method' => $b->payment_method,
                    'amount'         => (float) $b->amount,
                ]);
            }
        }

        if ($jenis !== 'booking') {
            $loans = EquipmentLoan::with(['user', 'items.equipment'])
                ->when($userId, fn ($q) => $q->where('user_id', $userId))
                ->when($paymentStatus, fn ($q) => $q->where('payment_status', $paymentStatus))
                ->whereBetween('created_at', [$start, $end])
                ->get();

            foreach ($loans as $l) {
                $rows->push([
                    'id'             => $l->id,
                    'type'           => 'loan',
                    'code'           => $l->loan_code,
                    'customer'       => $l->user?->name ?? $l->customer_name ?? '—',
                    'item'           => $l->items->map(fn ($it) => $it->equipment?->name)->filter()->implode(', ') ?: '—',
                    'created_at'     => $l->created_at,
                    'status'         => $l->status,
                    'payment_status' => $l->payment_status,
                    'payment_method' => $l->payment_method,
                    'amount'         => (float) $l->amount,
                ]);
            }
        }

        return $rows->sortByDesc('created_at')->values();
    }

    /** @return array{0: Carbon, 1: Carbon} */
    private function resolveDateRange(Request $request): array
    {
        $start = $request->date_from ? Carbon::parse($request->date_from)->startOfDay() : now()->startOfMonth();
        $end   = $request->date_to ? Carbon::parse($request->date_to)->endOfDay() : now()->endOfDay();

        return [$start, $end];
    }

    private function streamCsv(Collection $rows): StreamedResponse
    {
        $filename = 'laporan-transaksi-' . now()->format('Y-m-d_His') . '.csv';

        $callback = function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Kode', 'Jenis', 'Pelanggan', 'Item', 'Tanggal Transaksi', 'Status', 'Pembayaran', 'Metode', 'Nominal']);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row['code'],
                    $row['type'] === 'booking' ? 'Pemesanan Ruangan' : 'Peminjaman Alat',
                    $row['customer'],
                    $row['item'],
                    $row['created_at']->format('Y-m-d H:i'),
                    $row['status'],
                    $row['payment_status'] === 'paid' ? 'Lunas' : 'Belum bayar',
                    $row['payment_method'] ?? '—',
                    $row['amount'],
                ]);
            }

            fclose($handle);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
