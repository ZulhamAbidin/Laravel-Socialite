<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use Illuminate\Http\Request;

class AnggotaController extends Controller
{
    // Menampilkan form untuk menambah anggota
    public function create()
    {
        return view('dashboard.anggota.create');
    }

    // Menyimpan data anggota ke dalam database
    public function store(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:anggota,email',
        ]);

        // Menyimpan anggota baru ke database
        $anggota = new Anggota;
        $anggota->nama = $request->input('nama');
        $anggota->email = $request->input('email');
        $anggota->save();

        // Redirect dengan pesan sukses
        return redirect()->route('anggota.create')->with('success', 'Anggota berhasil ditambahkan.');
    }
}
