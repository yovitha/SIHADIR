<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use Illuminate\Http\Request;

class ProfilController extends Controller
{
    public function edit(Request $request)
    {
        return view('profil', ['pengguna' => $request->user()]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:100'],
            'password_lama' => ['nullable', 'required_with:password_baru', 'current_password'],
            'password_baru' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user->nama_lengkap = $data['nama_lengkap'];
        if (! empty($data['password_baru'])) {
            $user->password = $data['password_baru'];
        }
        $user->save();

        LogAktivitas::catat(! empty($data['password_baru']) ? 'Mengganti kata sandi sendiri' : 'Memperbarui profil');

        return back()->with('sukses', 'Profil berhasil disimpan.');
    }
}
