<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $rooms = Room::when($request->search, fn ($q) => $q->where(
            fn ($q2) => $q2->where('name', 'like', "%{$request->search}%")
                ->orWhere('code', 'like', "%{$request->search}%")
        ))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Rooms/Index', [
            'rooms'   => $rooms,
            'filters' => ['search' => $request->search],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'code'        => ['required', 'string', 'max:20', 'unique:rooms,code'],
            'description'    => ['nullable', 'string'],
            'capacity'       => ['required', 'integer', 'min:1'],
            'price_per_hour' => ['required', 'numeric', 'min:0'],
            'facilities'     => ['nullable', 'string'],
            'images'         => ['nullable', 'array'],
            'images.*'       => ['image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'is_active'      => ['nullable', 'boolean'],
        ]);

        $validated['facilities'] = $this->parseFacilities($validated['facilities'] ?? null);
        $validated['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('images')) {
            $paths = [];
            foreach ($request->file('images') as $file) {
                $paths[] = 'storage/' . $file->store('rooms', 'public');
            }
            $validated['images'] = $paths;
            $validated['image'] = $paths[0] ?? null;
        }

        Room::create($validated);

        return redirect()->route('rooms.index')->with('success', 'Ruang berhasil ditambahkan.');
    }

    public function update(Request $request, Room $room)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'code'        => ['required', 'string', 'max:20', "unique:rooms,code,{$room->id}"],
            'description'    => ['nullable', 'string'],
            'capacity'       => ['required', 'integer', 'min:1'],
            'price_per_hour' => ['required', 'numeric', 'min:0'],
            'facilities'     => ['nullable', 'string'],
            'images'         => ['nullable', 'array'],
            'images.*'       => ['image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'is_active'      => ['nullable', 'boolean'],
        ]);

        $validated['facilities'] = $this->parseFacilities($validated['facilities'] ?? null);
        $validated['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('images')) {
            $oldImages = is_array($room->images) ? $room->images : ($room->image ? [$room->image] : []);
            foreach ($oldImages as $img) {
                if ($img && str_starts_with($img, 'storage/rooms/')) {
                    Storage::disk('public')->delete(str_replace('storage/', '', $img));
                }
            }

            $paths = [];
            foreach ($request->file('images') as $file) {
                $paths[] = 'storage/' . $file->store('rooms', 'public');
            }
            $validated['images'] = $paths;
            $validated['image'] = $paths[0] ?? null;
        }

        $room->update($validated);

        return redirect()->route('rooms.index')->with('success', 'Ruang berhasil diperbarui.');
    }

    public function destroy(Room $room)
    {
        if ($room->bookings()->whereIn('status', ['pending', 'approved'])->exists()) {
            return back()->with('error', 'Ruang tidak dapat dihapus karena masih ada pemesanan aktif.');
        }

        if ($room->image && str_starts_with($room->image, 'storage/')) {
            Storage::disk('public')->delete(str_replace('storage/', '', $room->image));
        }
        $room->delete();

        return redirect()->route('rooms.index')->with('success', 'Ruang berhasil dihapus.');
    }

    /** Ubah string dipisah koma menjadi array fasilitas. */
    private function parseFacilities(?string $facilities): ?array
    {
        if (empty($facilities)) {
            return null;
        }

        return array_values(array_filter(array_map('trim', explode(',', $facilities))));
    }
}
