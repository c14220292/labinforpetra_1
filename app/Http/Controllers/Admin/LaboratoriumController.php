<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Laboratorium;
use Illuminate\Http\Request;

class LaboratoriumController extends Controller
{
    public function index()
    {
        $laboratoriums = Laboratorium::orderBy('kode_lab')->get();
        return view('admin.laboratorium.index', compact('laboratoriums'));
    }

    public function create()
    {
        return view('admin.laboratorium.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_lab' => 'required|string|max:20|unique:laboratorium,kode_lab',
            'nama_lab' => 'required|string|max:100',
            'gedung' => 'required|in:P,T',
        ]);

        Laboratorium::create($request->all());

        return redirect()->route('admin.laboratorium.index')->with('success', 'Laboratorium berhasil ditambahkan');
    }

    public function edit(Laboratorium $laboratorium)
    {
        return view('admin.laboratorium.edit', compact('laboratorium'));
    }

    public function update(Request $request, Laboratorium $laboratorium)
    {
        $request->validate([
            'kode_lab' => 'required|string|max:20|unique:laboratorium,kode_lab,' . $laboratorium->id_lab . ',id_lab',
            'nama_lab' => 'required|string|max:100',
            'gedung' => 'required|in:P,T',
        ]);

        $laboratorium->update($request->all());

        return redirect()->route('admin.laboratorium.index')->with('success', 'Laboratorium berhasil diperbarui');
    }

    public function destroy(Laboratorium $laboratorium)
    {
        $laboratorium->delete();
        return redirect()->route('admin.laboratorium.index')->with('success', 'Laboratorium berhasil dihapus');
    }
}
