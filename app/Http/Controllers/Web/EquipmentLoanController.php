<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\EquipmentLoan;
use App\Services\EquipmentLoanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EquipmentLoanController extends Controller
{
    public function __construct(private EquipmentLoanService $loanService) {}

    public function index(Request $request)
    {
        $loans = EquipmentLoan::with(['user', 'items.equipment', 'approvedBy'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->search, fn($q) =>
            $q->where(
                fn($q2) =>
                $q2->where('loan_code', 'like', "%{$request->search}%")
                    ->orWhereHas('user', fn($q3) => $q3->where('name', 'like', "%{$request->search}%"))
            ))
            ->latest()
            ->paginate(15);

        return view('loans.index', compact('loans'));
    }

    public function show(EquipmentLoan $equipmentLoan)
    {
        $equipmentLoan->load(['user', 'items.equipment', 'approvedBy', 'checkIns.user']);
        $loan = $equipmentLoan;
        return view('loans.show', compact('loan'));
    }

    public function approve(Request $request, EquipmentLoan $equipmentLoan)
    {
        $request->validate(['admin_notes' => ['nullable', 'string', 'max:1000']]);

        try {
            $this->loanService->approve($equipmentLoan, Auth::id(), $request->admin_notes);
            return back()->with('success', 'Peminjaman berhasil disetujui.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function reject(Request $request, EquipmentLoan $equipmentLoan)
    {
        $request->validate(['admin_notes' => ['required', 'string', 'max:1000']]);

        try {
            $this->loanService->reject($equipmentLoan, Auth::id(), $request->admin_notes);
            return back()->with('success', 'Peminjaman berhasil ditolak.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function updateStatus(Request $request, EquipmentLoan $equipmentLoan)
    {
        $request->validate([
            'status'      => ['required', 'in:pending,approved,rejected,cancelled'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $this->loanService->updateStatus($equipmentLoan, $request->status, Auth::id(), $request->admin_notes);
            return back()->with('success', 'Status peminjaman berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function processReturn(Request $request, EquipmentLoan $equipmentLoan)
    {
        $request->validate([
            'notes'         => ['nullable', 'string', 'max:500'],
            'items'         => ['nullable', 'array'],
            'items.*.id'    => ['required', 'integer', 'exists:equipment_loan_items,id'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $this->loanService->processReturn($equipmentLoan, $request->only(['notes', 'items']), Auth::id());
            return back()->with('success', 'Peralatan berhasil dikembalikan dan stok diperbarui.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
