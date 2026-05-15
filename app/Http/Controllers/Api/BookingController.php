<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\StoreBookingRequest;
use App\Http\Requests\Booking\UpdateBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(private BookingService $bookingService) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $bookings = Booking::with(['user', 'room', 'approvedBy'])
            ->when(!$user->isAdmin(), fn($q) => $q->where('user_id', $user->id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->room_id, fn($q) => $q->where('room_id', $request->room_id))
            ->when($user->isAdmin() && $request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->date_from, fn($q) => $q->whereDate('start_datetime', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('start_datetime', '<=', $request->date_to))
            ->latest()
            ->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Daftar pemesanan berhasil diambil.',
            'data'    => BookingResource::collection($bookings),
            'meta'    => [
                'current_page' => $bookings->currentPage(),
                'last_page'    => $bookings->lastPage(),
                'per_page'     => $bookings->perPage(),
                'total'        => $bookings->total(),
            ],
        ]);
    }

    public function store(StoreBookingRequest $request): JsonResponse
    {
        try {
            $booking = $this->bookingService->create($request->validated(), $request->user()->id);
            return response()->json([
                'success' => true,
                'message' => 'Pemesanan berhasil diajukan.',
                'data'    => new BookingResource($booking),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data'    => null,
            ], $e->getCode() ?: 422);
        }
    }

    public function show(Request $request, Booking $booking): JsonResponse
    {
        if (!$request->user()->isAdmin() && $booking->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.', 'data' => null], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail pemesanan berhasil diambil.',
            'data'    => new BookingResource($booking->load(['user', 'room', 'approvedBy'])),
        ]);
    }

    public function update(UpdateBookingRequest $request, Booking $booking): JsonResponse
    {
        if ($booking->user_id !== $request->user()->id || $booking->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya pemesanan milik Anda dengan status pending yang dapat diubah.',
                'data'    => null,
            ], 403);
        }

        $data = $request->validated();

        // Re-check availability if dates changed
        if (isset($data['start_datetime']) || isset($data['end_datetime'])) {
            $start = $data['start_datetime'] ?? $booking->start_datetime;
            $end   = $data['end_datetime'] ?? $booking->end_datetime;
            $room  = $booking->room;

            if (!$room->isAvailable($start, $end, $booking->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ruang sudah dipesan pada waktu yang dipilih.',
                    'data'    => null,
                ], 422);
            }
        }

        $booking->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Pemesanan berhasil diperbarui.',
            'data'    => new BookingResource($booking->fresh()->load(['user', 'room', 'approvedBy'])),
        ]);
    }

    public function destroy(Request $request, Booking $booking): JsonResponse
    {
        try {
            $this->bookingService->cancel($booking, $request->user()->id);
            return response()->json([
                'success' => true,
                'message' => 'Pemesanan berhasil dibatalkan.',
                'data'    => null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data'    => null,
            ], $e->getCode() ?: 422);
        }
    }

    public function approve(Request $request, Booking $booking): JsonResponse
    {
        $request->validate(['admin_notes' => ['nullable', 'string', 'max:1000']]);

        try {
            $booking = $this->bookingService->approve($booking, $request->user()->id, $request->admin_notes);
            return response()->json([
                'success' => true,
                'message' => 'Pemesanan berhasil disetujui.',
                'data'    => new BookingResource($booking),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data'    => null,
            ], $e->getCode() ?: 422);
        }
    }

    public function reject(Request $request, Booking $booking): JsonResponse
    {
        $request->validate(['admin_notes' => ['required', 'string', 'max:1000']]);

        try {
            $booking = $this->bookingService->reject($booking, $request->user()->id, $request->admin_notes);
            return response()->json([
                'success' => true,
                'message' => 'Pemesanan berhasil ditolak.',
                'data'    => new BookingResource($booking),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data'    => null,
            ], $e->getCode() ?: 422);
        }
    }
}
