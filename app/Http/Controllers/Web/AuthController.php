<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class AuthController extends Controller
{
    private const PORTALS = ['admin', 'receptionist', 'member'];

    private const ROLE_LABELS = [
        'admin'        => 'Admin',
        'receptionist' => 'Resepsionis',
        'member'       => 'Member',
    ];

    public function showLogin(Request $request)
    {
        if (Auth::check()) {
            return redirect()->to($this->homeRoute(Auth::user()));
        }

        $portal = in_array($request->query('portal'), self::PORTALS, true)
            ? $request->query('portal')
            : null;

        return Inertia::render('Auth/Login', ['portal' => $portal]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $portal = in_array($request->input('portal'), self::PORTALS, true)
            ? $request->input('portal')
            : null;

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Email atau password salah.']);
        }

        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();

            return back()->withErrors(['email' => 'Akun Anda telah dinonaktifkan.']);
        }

        // Pastikan akun masuk lewat portal yang sesuai dengan role-nya.
        if ($portal && $user->role !== $portal) {
            $actual = self::ROLE_LABELS[$user->role] ?? $user->role;
            $chosen = self::ROLE_LABELS[$portal];
            Auth::logout();

            return back()->withErrors([
                'email' => "Akun ini terdaftar sebagai {$actual}, bukan {$chosen}. Silakan masuk melalui portal {$actual}.",
            ]);
        }

        $request->session()->regenerate();

        // Terbitkan token Sanctum agar frontend (Vue/axios) dapat memanggil
        // endpoint /api/v1 yang diamankan token — sesuai kebutuhan integrasi API.
        $user->tokens()->where('name', 'web-frontend')->delete();
        $token = $user->createToken('web-frontend')->plainTextToken;
        $request->session()->put('api_token', $token);

        return redirect()->to($this->homeRoute($user));
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->to($this->homeRoute(Auth::user()));
        }

        return Inertia::render('Auth/Register');
    }

    /** Registrasi mandiri — selalu sebagai member. */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'email.unique'       => 'Email sudah terdaftar. Silakan login.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min'       => 'Password minimal 8 karakter.',
        ]);

        $user = User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'phone'     => $validated['phone'] ?? null,
            'password'  => Hash::make($validated['password']),
            'role'      => 'member',
            'member_id' => $this->generateMemberId(),
            'is_active' => true,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        $token = $user->createToken('web-frontend')->plainTextToken;
        $request->session()->put('api_token', $token);

        return redirect()->route('member.dashboard')
            ->with('success', "Selamat datang, {$user->name}! Akun member Anda ({$user->member_id}) berhasil dibuat.");
    }

    /** Halaman tujuan setelah login, berbeda per role. */
    private function homeRoute($user): string
    {
        return match ($user->role) {
            'receptionist' => route('receptionist.dashboard'),
            'member'       => route('member.dashboard'),
            default        => route('dashboard'),
        };
    }

    private function generateMemberId(): string
    {
        $year = now()->format('Y');
        // Ambil suffix terbesar yang sudah ada agar tidak pernah bentrok
        // (count+1 bisa tabrakan jika ada ID hasil seed/registrasi lama).
        $last = User::where('member_id', 'like', "FS-{$year}%")
            ->orderByDesc('member_id')
            ->value('member_id');
        $next = $last ? ((int) substr($last, -4)) + 1 : 1;

        return 'FS-' . $year . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    public function logout(Request $request)
    {
        if ($user = Auth::user()) {
            $user->tokens()->where('name', 'web-frontend')->delete();
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
