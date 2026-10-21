<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $berhasil = Auth::attempt([
            'username' => $data['username'],
            'password' => $data['password'],
            'status_aktif' => true,
        ], $request->boolean('ingat'));

        if ($berhasil) {
            $request->session()->regenerate();
            LogAktivitas::catat('Masuk ke sistem');

            return redirect()->intended(route('dashboard'));
        }

        $pesan = 'Nama pengguna atau kata sandi salah.';
        $user = User::where('username', $data['username'])->first();
        if ($user && ! $user->status_aktif && Hash::check($data['password'], $user->password)) {
            $pesan = 'Akun Anda dinonaktifkan. Hubungi Admin.';
        }

        return back()->withErrors(['username' => $pesan])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        LogAktivitas::catat('Keluar dari sistem');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('beranda');
    }
}
