<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AuditLog;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'Username atau email wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $fieldType = filter_var($credentials['username'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $authAttempt = [
            $fieldType => $credentials['username'],
            'password' => $credentials['password'],
        ];

        if (Auth::attempt($authAttempt, $request->boolean('remember'))) {
            $user = Auth::user();

            if (!$user->aktif) {
                Auth::logout();
                return back()->withErrors(['username' => 'Akun dinonaktifkan. Silakan hubungi Administrator IT PDE.']);
            }

            $request->session()->regenerate();

            AuditLog::record('LOGIN', 'Autentikasi', "Pengguna {$user->nama} ({$user->role}) berhasil masuk ke sistem.");

            return redirect()->intended(route('dashboard'))
                ->with('sukses', "Selamat datang kembali, {$user->nama}!");
        }

        return back()->withErrors([
            'username' => 'Kombinasi kredensial login (username/kata sandi) tidak cocok.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            AuditLog::record('LOGOUT', 'Autentikasi', "Pengguna {$user->nama} keluar dari sistem.");
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('sukses', 'Anda telah berhasil keluar.');
    }
}
