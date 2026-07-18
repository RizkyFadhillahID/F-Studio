<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Models\EquipmentLoan;
use App\Models\User;
use App\Services\EquipmentLoanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class EquipmentLoanController extends Controller
{
    public function __construct(private EquipmentLoanService $loanService) {}

    public function index(Request $request)
    {
        $loans = EquipmentLoan::with(['user', 'items.equipment', 'approvedBy'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->search, fn ($q) => $q->where(
                fn ($q2) => $q2->where('loan_code', 'like', "%{$request->search}%")
                    ->orWhere('customer_name', 'like', "%{$request->search}%")
                    ->orWhereHas('user', fn ($q3) => $q3->where('name', 'like', "%{$request->search}%"))
            ))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Loans/Index', [
            'loans'     => $loans,
            'equipment' => Equipment::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code', 'quantity_available', 'price_per_day']),
            'members'   => User::whereIn('role', ['member', 'receptionist'])->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'filters'   => ['status' => $request->status, 'search' => $request->search],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id'           => ['nullable', 'exists:users,id'],
            'customer_name'     => ['nullable', 'string', 'max:200'],
            'customer_phone'    => ['nullable', 'string', 'max:20'],
            'purpose'           => ['required', 'string', 'max:255'],
            'loan_date'         => ['required', 'date'],
            'due_date'          => ['required', 'date', 'after_or_equal:loan_date'],
            'notes'             => ['nullable', 'string', 'max:1000'],
            'items'             => ['required', 'array', 'min:1'],
            'items.*.equipment_id' => ['required', 'exists:equipment,id'],
            'items.*.quantity'  => ['required', 'integer', 'min:1'],
            'items.*.notes'     => ['nullable', 'string', 'max:255'],
        ]);

        $ownerId = $data['user_id'] ?? Auth::id();

        try {
            $this->loanService->create($data, $ownerId);

            return redirect()->route('loans.index')->with('success', 'Peminjaman berhasil dibuat.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function updateStatus(Request $request, EquipmentLoan $equipmentLoan)
    {
        $request->validate([
            'status'      => ['required', 'in:pending,approved,rejected,cancelled,returned'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $this->loanService->updateStatus($equipmentLoan, $request->status, Auth::id(), $request->admin_notes);

            return back()->with('success', 'Status peminjaman berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function processReturn(Request $request, EquipmentLoan $equipmentLoan)
    {
        $request->validate([
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->loanService->processReturn($equipmentLoan, $request->only(['notes']), Auth::id());

            return back()->with('success', 'Peralatan berhasil dikembalikan dan stok diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
