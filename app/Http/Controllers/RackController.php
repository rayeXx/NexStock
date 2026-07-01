<?php

namespace App\Http\Controllers;

use App\Models\Rack;
use Illuminate\Http\Request;

class RackController extends Controller
{
    public function index()
    {
        $racks = Rack::all();
        return view('rack.index', compact('racks'));
    }

    public function create()
    {
        return view('rack.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_rak' => 'required|string|unique:m_racks,kode_rak|max:50',
            'kapasitas_maksimum_volume' => 'required|integer|min:1',
        ]);

        Rack::create([
            'kode_rak' => strtoupper($request->kode_rak),
            'kapasitas_maksimum_volume' => $request->kapasitas_maksimum_volume,
            'kapasitas_terpakai' => 0,
        ]);

        return redirect()->route('rack.index')->with('success', 'Rak berhasil ditambahkan.');
    }

    public function edit($kode_rak)
    {
        $rack = Rack::findOrFail($kode_rak);
        return view('rack.edit', compact('rack'));
    }

    public function update(Request $request, $kode_rak)
    {
        $rack = Rack::findOrFail($kode_rak);

        $request->validate([
            'kapasitas_maksimum_volume' => 'required|integer|min:1',
        ]);

        $rack->update($request->all());

        return redirect()->route('rack.index')->with('success', 'Kapasitas rak berhasil diperbarui.');
    }

    public function destroy($kode_rak)
    {
        $rack = Rack::findOrFail($kode_rak);
        if ($rack->kapasitas_terpakai > 0) {
            return redirect()->route('rack.index')->with('error', 'Gagal menghapus rak: Masih ada barang di rak ini.');
        }

        $rack->delete();
        return redirect()->route('rack.index')->with('success', 'Rak berhasil dihapus.');
    }
}
