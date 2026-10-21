<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use App\Models\User;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $log = LogAktivitas::with('user')
            ->when($request->filled('user'), fn ($q) => $q->where('id_user', $request->input('user')))
            ->when($request->filled('tanggal'), fn ($q) => $q->whereDate('waktu', $request->tanggal))
            ->when($request->filled('q'), fn ($q) => $q->where('aktivitas', 'like', '%'.addcslashes($request->q, '%_\\').'%'))
            ->orderByDesc('waktu')->orderByDesc('id_log')
            ->paginate(20)
            ->withQueryString();

        $pengguna = User::orderBy('nama_lengkap')->get();

        return view('log.index', compact('log', 'pengguna'));
    }
}
