<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::when($request->search, fn ($q) => $q->where(
            fn ($q2) => $q2->where('name', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%")
        ))
            ->when($request->role, fn ($q) => $q->where('role', $request->role))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $roleCounts = User::selectRaw('role, count(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role');

        return Inertia::render('Users/Index', [
            'users'      => $users,
            'filters'    => ['search' => $request->search, 'role' => $request->role],
            'roleCounts' => [
                'all'          => $roleCounts->sum(),
                'admin'        => $roleCounts['admin'] ?? 0,
                'receptionist' => $roleCounts['receptionist'] ?? 0,
                'member'       => $roleCounts['member'] ?? 0,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:100'],
            'email'     => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'  => ['required', 'string', 'min:8', 'confirmed'],
            'role'      => ['required', 'in:admin,member,receptionist'],
            'phone'     => ['nullable', 'string', 'max:20'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->boolean('is_active', true);

        if ($validated['role'] === 'member') {
            $validated['member_id'] = $this->generateMemberId();
        }

        User::create($validated);

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:100'],
            'email'     => ['required', 'email', "unique:users,email,{$user->id}"],
            'role'      => ['required', 'in:admin,member,receptionist'],
            'phone'     => ['nullable', 'string', 'max:20'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        if ($request->filled('password')) {
            $request->validate(['password' => ['string', 'min:8', 'confirmed']]);
            $validated['password'] = Hash::make($request->password);
        }

        $user->update($validated);

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus.');
    }

    public function toggleActive(User $user)
    {
        $user->update(['is_active' => ! $user->is_active]);
        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Akun pengguna berhasil {$status}.");
    }

    private function generateMemberId(): string
    {
        $year = now()->format('Y');
        // Ambil suffix terbesar yang sudah ada agar tidak pernah bentrok.
        $last = User::where('member_id', 'like', "FS-{$year}%")
            ->orderByDesc('member_id')
            ->value('member_id');
        $next = $last ? ((int) substr($last, -4)) + 1 : 1;

        return 'FS-' . $year . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
