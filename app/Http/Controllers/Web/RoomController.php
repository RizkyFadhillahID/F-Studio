<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $rooms = Room::when($request->search, fn($q) =>
        $q->where(
            fn($q2) =>
            $q2->where('name', 'like', "%{$request->search}%")
                ->orWhere('code', 'like', "%{$request->search}%")
        ))
            ->latest()
            ->paginate(15);

        return view('rooms.index', compact('rooms'));
    }

    public function create()
    {
        return view('rooms.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'code'        => ['required', 'string', 'max:20', 'unique:rooms,code'],
            'description' => ['nullable', 'string'],
            'capacity'    => ['required', 'integer', 'min:1'],
            'facilities'  => ['nullable', 'string'],
            'image'       => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
            'is_active'   => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('rooms', 'public');
        }

        // Parse comma-separated facilities
        if (!empty($validated['facilities'])) {
            $validated['facilities'] = array_map('trim', explode(',', $validated['facilities']));
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        Room::create($validated);

        return redirect()->route('rooms.index')->with('success', 'Ruang berhasil ditambahkan.');
    }

    public function show(Room $room)
    {
        $bookings = $room->bookings()->with('user')
            ->whereIn('status', ['pending', 'approved'])
            ->orderBy('start_datetime')
            ->get();

        return view('rooms.show', compact('room', 'bookings'));
    }

    public function edit(Room $room)
    {
        return view('rooms.edit', compact('room'));
    }

    public function update(Request $request, Room $room)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'code'        => ['required', 'string', 'max:20', "unique:rooms,code,{$room->id}"],
            'description' => ['nullable', 'string'],
            'capacity'    => ['required', 'integer', 'min:1'],
            'facilities'  => ['nullable', 'string'],
            'image'       => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
            'is_active'   => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('image')) {
            if ($room->image) Storage::disk('public')->delete($room->image);
            $validated['image'] = $request->file('image')->store('rooms', 'public');
        }

        if (!empty($validated['facilities'])) {
            $validated['facilities'] = array_map('trim', explode(',', $validated['facilities']));
        }

        $validated['is_active'] = $request->boolean('is_active');
        $room->update($validated);

        return redirect()->route('rooms.index')->with('success', 'Ruang berhasil diperbarui.');
    }

    public function destroy(Room $room)
    {
        if ($room->bookings()->whereIn('status', ['pending', 'approved'])->exists()) {
            return back()->withErrors(['error' => 'Ruang tidak dapat dihapus karena masih ada pemesanan aktif.']);
        }

        if ($room->image) Storage::disk('public')->delete($room->image);
        $room->delete();

        return redirect()->route('rooms.index')->with('success', 'Ruang berhasil dihapus.');
    }
}
