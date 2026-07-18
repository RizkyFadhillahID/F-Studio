<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Equipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class EquipmentController extends Controller
{
    public function index(Request $request)
    {
        $equipment = Equipment::with('category')
            ->when($request->search, fn ($q) => $q->where(
                fn ($q2) => $q2->where('name', 'like', "%{$request->search}%")
                    ->orWhere('code', 'like', "%{$request->search}%")
            ))
            ->when($request->category_id, fn ($q) => $q->where('category_id', $request->category_id))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Equipment/Index', [
            'equipment'  => $equipment,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'filters'    => ['search' => $request->search, 'category_id' => $request->category_id],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id'    => ['required', 'exists:categories,id'],
            'name'           => ['required', 'string', 'max:150'],
            'code'           => ['required', 'string', 'max:50', 'unique:equipment,code'],
            'description'    => ['nullable', 'string'],
            'quantity_total' => ['required', 'integer', 'min:1'],
            'price_per_day'  => ['required', 'numeric', 'min:0'],
            'location'       => ['nullable', 'string', 'max:100'],
            'image'          => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
            'is_active'      => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = 'storage/' . $request->file('image')->store('equipment', 'public');
        } else {
            unset($validated['image']);
        }

        $validated['quantity_available'] = $validated['quantity_total'];
        $validated['is_active'] = $request->boolean('is_active', true);

        Equipment::create($validated);

        return redirect()->route('equipment.index')->with('success', 'Peralatan berhasil ditambahkan.');
    }

    public function update(Request $request, Equipment $equipment)
    {
        $validated = $request->validate([
            'category_id'    => ['required', 'exists:categories,id'],
            'name'           => ['required', 'string', 'max:150'],
            'code'           => ['required', 'string', 'max:50', "unique:equipment,code,{$equipment->id}"],
            'description'    => ['nullable', 'string'],
            'quantity_total' => ['required', 'integer', 'min:1'],
            'price_per_day'  => ['required', 'numeric', 'min:0'],
            'location'       => ['nullable', 'string', 'max:100'],
            'image'          => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
            'is_active'      => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('image')) {
            if ($equipment->image && str_starts_with($equipment->image, 'storage/equipment/')) {
                Storage::disk('public')->delete(str_replace('storage/', '', $equipment->image));
            }
            $validated['image'] = 'storage/' . $request->file('image')->store('equipment', 'public');
        } else {
            unset($validated['image']);
        }

        $validated['is_active'] = $request->boolean('is_active');

        $equipment->update($validated);

        return redirect()->route('equipment.index')->with('success', 'Peralatan berhasil diperbarui.');
    }

    public function destroy(Equipment $equipment)
    {
        $hasActive = $equipment->loanItems()
            ->whereHas('loan', fn ($q) => $q->whereIn('status', ['pending', 'approved', 'active']))
            ->exists();

        if ($hasActive) {
            return back()->with('error', 'Peralatan tidak dapat dihapus karena masih ada peminjaman aktif.');
        }

        if ($equipment->image && str_starts_with($equipment->image, 'storage/')) {
            Storage::disk('public')->delete(str_replace('storage/', '', $equipment->image));
        }
        $equipment->delete();

        return redirect()->route('equipment.index')->with('success', 'Peralatan berhasil dihapus.');
    }
}
