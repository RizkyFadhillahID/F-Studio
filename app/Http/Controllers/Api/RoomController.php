<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RoomResource;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RoomController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $rooms = Room::when($request->search, fn($q) =>
        $q->where(
            fn($q2) =>
            $q2->where('name', 'like', "%{$request->search}%")
                ->orWhere('code', 'like', "%{$request->search}%")
        ))
            ->when($request->available_on, function ($q) use ($request) {
                $date = $request->available_on;
                $q->whereDoesntHave(
                    'bookings',
                    fn($q2) =>
                    $q2->whereIn('status', ['pending', 'approved'])
                        ->whereDate('start_datetime', '<=', $date)
                        ->whereDate('end_datetime', '>=', $date)
                );
            })
            ->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Daftar ruang berhasil diambil.',
            'data'    => RoomResource::collection($rooms),
            'meta'    => [
                'current_page' => $rooms->currentPage(),
                'last_page'    => $rooms->lastPage(),
                'per_page'     => $rooms->perPage(),
                'total'        => $rooms->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'code'        => ['required', 'string', 'max:20', 'unique:rooms,code'],
            'description' => ['nullable', 'string'],
            'capacity'    => ['required', 'integer', 'min:1'],
            'facilities'  => ['nullable', 'array'],
            'image'       => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
            'is_active'   => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('rooms', 'public');
        }

        $room = Room::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Ruang berhasil ditambahkan.',
            'data'    => new RoomResource($room),
        ], 201);
    }

    public function show(Room $room): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail ruang berhasil diambil.',
            'data'    => new RoomResource($room),
        ]);
    }

    public function update(Request $request, Room $room): JsonResponse
    {
        $validated = $request->validate([
            'name'        => ['sometimes', 'string', 'max:100'],
            'code'        => ['sometimes', 'string', 'max:20', "unique:rooms,code,{$room->id}"],
            'description' => ['nullable', 'string'],
            'capacity'    => ['sometimes', 'integer', 'min:1'],
            'facilities'  => ['nullable', 'array'],
            'image'       => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
            'is_active'   => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('image')) {
            if ($room->image) Storage::disk('public')->delete($room->image);
            $validated['image'] = $request->file('image')->store('rooms', 'public');
        }

        $room->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Ruang berhasil diperbarui.',
            'data'    => new RoomResource($room->fresh()),
        ]);
    }

    public function destroy(Room $room): JsonResponse
    {
        $hasActive = $room->bookings()
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($hasActive) {
            return response()->json([
                'success' => false,
                'message' => 'Ruang tidak dapat dihapus karena masih ada pemesanan aktif.',
                'data'    => null,
            ], 422);
        }

        if ($room->image) Storage::disk('public')->delete($room->image);
        $room->delete();

        return response()->json([
            'success' => true,
            'message' => 'Ruang berhasil dihapus.',
            'data'    => null,
        ]);
    }
}
