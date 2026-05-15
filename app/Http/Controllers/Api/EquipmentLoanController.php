<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EquipmentLoan\StoreEquipmentLoanRequest;
use App\Http\Resources\EquipmentLoanResource;
use App\Models\EquipmentLoan;
use App\Services\EquipmentLoanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EquipmentLoanController extends Controller
{
    public function __construct(private EquipmentLoanService $loanService) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $loans = EquipmentLoan::with(['user', 'items.equipment', 'approvedBy'])
            ->when(!$user->isAdmin(), fn($q) => $q->where('user_id', $user->id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($user->isAdmin() && $request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->due_date_from, fn($q) => $q->where('due_date', '>=', $request->due_date_from))
            ->when($request->due_date_to, fn($q) => $q->where('due_date', '<=', $request->due_date_to))
            ->latest()
            ->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Daftar peminjaman berhasil diambil.',
            'data'    => EquipmentLoanResource::collection($loans),
            'meta'    => [
                'current_page' => $loans->currentPage(),
                'last_page'    => $loans->lastPage(),
                'per_page'     => $loans->perPage(),
                'total'        => $loans->total(),
            ],
        ]);
    }

    public function store(StoreEquipmentLoanRequest $request): JsonResponse
    {
        try {
            $loan = $this->loanService->create($request->validated(), $request->user()->id);
            return response()->json([
                'success' => true,
                'message' => 'Peminjaman berhasil diajukan.',
                'data'    => new EquipmentLoanResource($loan),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data'    => null,
            ], $e->getCode() ?: 422);
        }
    }

    public function show(Request $request, EquipmentLoan $equipmentLoan): JsonResponse
    {
        if (!$request->user()->isAdmin() && $equipmentLoan->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.', 'data' => null], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail peminjaman berhasil diambil.',
            'data'    => new EquipmentLoanResource($equipmentLoan->load(['user', 'items.equipment', 'approvedBy', 'booking'])),
        ]);
    }

    public function update(Request $request, EquipmentLoan $equipmentLoan): JsonResponse
    {
        if ($equipmentLoan->user_id !== $request->user()->id || $equipmentLoan->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya peminjaman milik Anda dengan status pending yang dapat diubah.',
                'data'    => null,
            ], 403);
        }

        $validated = $request->validate([
            'purpose'  => ['sometimes', 'string'],
            'due_date' => ['sometimes', 'date', 'after_or_equal:today'],
            'notes'    => ['nullable', 'string', 'max:1000'],
        ]);

        $equipmentLoan->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Peminjaman berhasil diperbarui.',
            'data'    => new EquipmentLoanResource($equipmentLoan->fresh()->load(['user', 'items.equipment'])),
        ]);
    }

    public function approve(Request $request, EquipmentLoan $equipmentLoan): JsonResponse
    {
        $request->validate(['admin_notes' => ['nullable', 'string', 'max:1000']]);

        try {
            $loan = $this->loanService->approve($equipmentLoan, $request->user()->id, $request->admin_notes);
            return response()->json([
                'success' => true,
                'message' => 'Peminjaman berhasil disetujui.',
                'data'    => new EquipmentLoanResource($loan),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data'    => null,
            ], $e->getCode() ?: 422);
        }
    }

    public function reject(Request $request, EquipmentLoan $equipmentLoan): JsonResponse
    {
        $request->validate(['admin_notes' => ['required', 'string', 'max:1000']]);

        try {
            $loan = $this->loanService->reject($equipmentLoan, $request->user()->id, $request->admin_notes);
            return response()->json([
                'success' => true,
                'message' => 'Peminjaman berhasil ditolak.',
                'data'    => new EquipmentLoanResource($loan),
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
