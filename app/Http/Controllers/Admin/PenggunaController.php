<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use App\Models\Laboratorium;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PenggunaController extends Controller
{
    public function index()
    {
        $penggunas = Pengguna::with('laboratoriums')
            ->where('id_pengguna', '!=', auth()->id())
            ->orderBy('id_pengguna')
            ->get();

        return view('admin.pengguna.index', compact('penggunas'));
    }

    public function create()
    {
        $laboratoriums = Laboratorium::all();
        return view('admin.pengguna.create', compact('laboratoriums'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_pengguna' => 'required|string|max:100',
            'email' => 'required|email|unique:pengguna,email',
            'password' => 'required|min:6|confirmed',
            'role' => 'required|in:admin,kepalalab,asistenlab',
            'lab_akses' => 'array',
        ]);

        $pengguna = Pengguna::create([
            'nama_pengguna' => $request->nama_pengguna,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        if ($request->lab_akses) {
            $pengguna->laboratoriums()->attach($request->lab_akses);
        }

        return redirect()->route('admin.pengguna.index')->with('success', 'Pengguna berhasil ditambahkan');
    }

    public function edit(Pengguna $pengguna)
    {
        $laboratoriums = Laboratorium::all();
        $selectedLabs = $pengguna->laboratoriums->pluck('kode_lab')->toArray();

        return view('admin.pengguna.edit', compact('pengguna', 'laboratoriums', 'selectedLabs'));
    }

    public function update(Request $request, Pengguna $pengguna)
    {
        $request->validate([
            'nama_pengguna' => 'required|string|max:100',
            'email' => 'required|email|unique:pengguna,email,' . $pengguna->id_pengguna . ',id_pengguna',
            'role' => 'required|in:admin,kepalalab,asistenlab',
            'lab_akses' => 'array',
        ]);

        $updateData = [
            'nama_pengguna' => $request->nama_pengguna,
            'email' => $request->email,
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:6|confirmed']);
            $updateData['password'] = Hash::make($request->password);
        }

        $pengguna->update($updateData);
        $pengguna->laboratoriums()->sync($request->lab_akses ?? []);

        return redirect()->route('admin.pengguna.index')->with('success', 'Pengguna berhasil diperbarui');
    }

    public function destroy(Pengguna $pengguna)
    {
        $pengguna->delete();
        return redirect()->route('admin.pengguna.index')->with('success', 'Pengguna berhasil dihapus');
    }
}
