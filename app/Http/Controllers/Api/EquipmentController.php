<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Equipment\StoreEquipmentRequest;
use App\Http\Requests\Equipment\UpdateEquipmentRequest;
use App\Http\Resources\EquipmentResource;
use App\Models\Equipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EquipmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $equipment = Equipment::with('category')
            ->when($request->search, fn($q) =>
            $q->where(
                fn($q2) =>
                $q2->where('name', 'like', "%{$request->search}%")
                    ->orWhere('code', 'like', "%{$request->search}%")
            ))
            ->when($request->category_id, fn($q) => $q->where('category_id', $request->category_id))
            ->when($request->condition, fn($q) => $q->where('condition', $request->condition))
            ->when(
                $request->available === 'true' || $request->available === '1',
                fn($q) =>
                $q->where('is_active', true)->where('quantity_available', '>', 0)
            )
            ->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Daftar peralatan berhasil diambil.',
            'data'    => EquipmentResource::collection($equipment),
            'meta'    => [
                'current_page' => $equipment->currentPage(),
                'last_page'    => $equipment->lastPage(),
                'per_page'     => $equipment->perPage(),
                'total'        => $equipment->total(),
            ],
        ]);
    }

    public function store(StoreEquipmentRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('equipment', 'public');
        }

        $data['quantity_available'] = $data['quantity_available'] ?? $data['quantity_total'];

        $equipment = Equipment::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Peralatan berhasil ditambahkan.',
            'data'    => new EquipmentResource($equipment->load('category')),
        ], 201);
    }

    public function show(Equipment $equipment): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail peralatan berhasil diambil.',
            'data'    => new EquipmentResource($equipment->load('category')),
        ]);
    }

    public function update(UpdateEquipmentRequest $request, Equipment $equipment): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($equipment->image) {
                Storage::disk('public')->delete($equipment->image);
            }
            $data['image'] = $request->file('image')->store('equipment', 'public');
        }

        $equipment->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Peralatan berhasil diperbarui.',
            'data'    => new EquipmentResource($equipment->fresh()->load('category')),
        ]);
    }

    public function destroy(Equipment $equipment): JsonResponse
    {
        $hasActive = $equipment->loanItems()
            ->whereHas('loan', fn($q) => $q->whereIn('status', ['pending', 'approved', 'active']))
            ->exists();

        if ($hasActive) {
            return response()->json([
                'success' => false,
                'message' => 'Peralatan tidak dapat dihapus karena masih ada peminjaman aktif.',
                'data'    => null,
            ], 422);
        }

        if ($equipment->image) {
            Storage::disk('public')->delete($equipment->image);
        }

        $equipment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Peralatan berhasil dihapus.',
            'data'    => null,
        ]);
    }
}
