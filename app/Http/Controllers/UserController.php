<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** Pengelolaan akun pengguna — hanya untuk Admin (KF-02). */
class UserController extends Controller
{
    public function index(Request $request)
    {
        $pengguna = User::query()
            ->when($request->filled('q'), function ($q) use ($request) {
                $kata = '%'.addcslashes($request->q, '%_\\').'%';
                $q->where(fn ($w) => $w->where('nama_lengkap', 'like', $kata)->orWhere('username', 'like', $kata));
            })
            ->orderBy('nama_lengkap')
            ->paginate(12)
            ->withQueryString();

        return view('pengguna.index', compact('pengguna'));
    }

    public function create()
    {
        return view('pengguna.form', ['pengguna' => new User(['role' => 'operator', 'status_aktif' => true])]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9._-]+$/', 'unique:users,username'],
            'role' => ['required', 'in:admin,operator,pimpinan'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'username.regex' => 'Nama pengguna hanya boleh berisi huruf, angka, titik, garis bawah, dan strip.',
        ]);

        $user = User::create($data + ['status_aktif' => true]);
        LogAktivitas::catat('Menambah akun pengguna: '.$user->username.' ('.$user->role.')');

        return redirect()->route('pengguna.index')->with('sukses', 'Akun '.$user->username.' berhasil dibuat.');
    }

    public function edit(User $user)
    {
        return view('pengguna.form', ['pengguna' => $user]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9._-]+$/', Rule::unique('users', 'username')->ignore($user->id_user, 'id_user')],
            'role' => ['required', 'in:admin,operator,pimpinan'],
        ]);

        if ($user->id_user === $request->user()->id_user && $data['role'] !== 'admin') {
            return back()->withInput()->with('galat', 'Anda tidak dapat menurunkan peran akun Anda sendiri.');
        }

        $user->update($data);
        LogAktivitas::catat('Mengubah akun pengguna: '.$user->username);

        return redirect()->route('pengguna.index')->with('sukses', 'Akun berhasil diperbarui.');
    }

    public function ubahStatus(Request $request, User $user)
    {
        if ($user->id_user === $request->user()->id_user) {
            return back()->with('galat', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $user->update(['status_aktif' => ! $user->status_aktif]);
        LogAktivitas::catat(($user->status_aktif ? 'Mengaktifkan' : 'Menonaktifkan').' akun: '.$user->username);

        return back()->with('sukses', 'Akun '.$user->username.($user->status_aktif ? ' diaktifkan.' : ' dinonaktifkan.'));
    }

    public function resetPassword(User $user)
    {
        $sementara = Str::lower(Str::random(4)).random_int(1000, 9999).Str::upper(Str::random(2));
        $user->update(['password' => $sementara]);
        LogAktivitas::catat('Mengatur ulang kata sandi akun: '.$user->username);

        return back()->with('sukses', 'Kata sandi akun '.$user->username.' berhasil diatur ulang.')
            ->with('sandi_baru', ['username' => $user->username, 'password' => $sementara]);
    }
}
