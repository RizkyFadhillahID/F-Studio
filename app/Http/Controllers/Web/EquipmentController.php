<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Equipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EquipmentController extends Controller
{
    public function index(Request $request)
    {
        $equipment = Equipment::with('category')
            ->when($request->search, fn($q) =>
            $q->where(
                fn($q2) =>
                $q2->where('name', 'like', "%{$request->search}%")
                    ->orWhere('code', 'like', "%{$request->search}%")
            ))
            ->when($request->category_id, fn($q) => $q->where('category_id', $request->category_id))
            ->latest()
            ->paginate(10);

        $categories = Category::all();

        return view('equipment.index', compact('equipment', 'categories'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('equipment.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id'    => ['required', 'exists:categories,id'],
            'name'           => ['required', 'string', 'max:150'],
            'code'           => ['required', 'string', 'max:50', 'unique:equipment,code'],
            'description'    => ['nullable', 'string'],
            'quantity_total' => ['required', 'integer', 'min:1'],
            'location'       => ['nullable', 'string', 'max:100'],
            'image'          => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
            'is_active'      => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('equipment', 'public');
        }

        $validated['quantity_available'] = $validated['quantity_total'];
        $validated['is_active'] = $request->boolean('is_active', true);

        Equipment::create($validated);

        return redirect()->route('equipment.index')->with('success', 'Peralatan berhasil ditambahkan.');
    }

    public function show(Equipment $equipment)
    {
        $equipment->load('category');
        return view('equipment.show', compact('equipment'));
    }

    public function edit(Equipment $equipment)
    {
        $categories = Category::all();
        return view('equipment.edit', compact('equipment', 'categories'));
    }

    public function update(Request $request, Equipment $equipment)
    {
        $validated = $request->validate([
            'category_id'    => ['required', 'exists:categories,id'],
            'name'           => ['required', 'string', 'max:150'],
            'code'           => ['required', 'string', 'max:50', "unique:equipment,code,{$equipment->id}"],
            'description'    => ['nullable', 'string'],
            'quantity_total' => ['required', 'integer', 'min:1'],
            'location'       => ['nullable', 'string', 'max:100'],
            'image'          => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
            'is_active'      => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('image')) {
            if ($equipment->image) Storage::disk('public')->delete($equipment->image);
            $validated['image'] = $request->file('image')->store('equipment', 'public');
        }

        $validated['is_active'] = $request->boolean('is_active');

        $equipment->update($validated);

        return redirect()->route('equipment.index')->with('success', 'Peralatan berhasil diperbarui.');
    }

    public function destroy(Equipment $equipment)
    {
        $hasActive = $equipment->loanItems()
            ->whereHas('loan', fn($q) => $q->whereIn('status', ['pending', 'approved', 'active']))
            ->exists();

        if ($hasActive) {
            return back()->withErrors(['error' => 'Peralatan tidak dapat dihapus karena masih ada peminjaman aktif.']);
        }

        if ($equipment->image) Storage::disk('public')->delete($equipment->image);
        $equipment->delete();

        return redirect()->route('equipment.index')->with('success', 'Peralatan berhasil dihapus.');
    }
}
