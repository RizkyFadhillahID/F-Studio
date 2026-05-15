<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckIn\StoreCheckInRequest;
use App\Http\Resources\CheckInResource;
use App\Models\CheckIn;
use App\Services\CheckInService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckInController extends Controller
{
    public function __construct(private CheckInService $checkInService) {}

    public function index(Request $request): JsonResponse
    {
        $checkIns = CheckIn::with(['loan', 'user'])
            ->when($request->loan_id, fn($q) => $q->where('equipment_loan_id', $request->loan_id))
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->action, fn($q) => $q->where('action', $request->action))
            ->when($request->date_from, fn($q) => $q->whereDate('checked_at', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('checked_at', '<=', $request->date_to))
            ->latest('checked_at')
            ->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Log check-in berhasil diambil.',
            'data'    => CheckInResource::collection($checkIns),
            'meta'    => [
                'current_page' => $checkIns->currentPage(),
                'last_page'    => $checkIns->lastPage(),
                'per_page'     => $checkIns->perPage(),
                'total'        => $checkIns->total(),
            ],
        ]);
    }

    public function store(StoreCheckInRequest $request): JsonResponse
    {
        try {
            $checkIn = $this->checkInService->process($request->validated(), $request->user()->id);

            $statusCode = $request->action === 'check_in' ? 201 : 200;
            $message    = $request->action === 'check_in' ? 'Check-in berhasil.' : 'Check-out berhasil.';

            return response()->json([
                'success' => true,
                'message' => $message,
                'data'    => new CheckInResource($checkIn),
            ], $statusCode);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data'    => null,
            ], $e->getCode() ?: 422);
        }
    }

    public function show(CheckIn $checkIn): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail check-in berhasil diambil.',
            'data'    => new CheckInResource($checkIn->load(['loan', 'user'])),
        ]);
    }
}
