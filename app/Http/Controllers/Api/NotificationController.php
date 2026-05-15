<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\AppNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $notifications = AppNotification::where('user_id', $user->id)
            ->latest()
            ->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Daftar notifikasi berhasil diambil.',
            'data'    => NotificationResource::collection($notifications),
            'meta'    => [
                'current_page'  => $notifications->currentPage(),
                'last_page'     => $notifications->lastPage(),
                'per_page'      => $notifications->perPage(),
                'total'         => $notifications->total(),
                'unread_count'  => AppNotification::where('user_id', $user->id)->unread()->count(),
            ],
        ]);
    }

    public function markAsRead(Request $request, AppNotification $notification): JsonResponse
    {
        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.', 'data' => null], 403);
        }

        $notification->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi ditandai sebagai sudah dibaca.',
            'data'    => new NotificationResource($notification),
        ]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        AppNotification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Semua notifikasi telah ditandai sebagai sudah dibaca.',
            'data'    => null,
        ]);
    }
}
