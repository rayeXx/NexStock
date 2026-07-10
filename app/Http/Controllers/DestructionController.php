<?php

namespace App\Http\Controllers;

use App\Models\Destruction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DestructionController extends Controller
{
    // List all destructions (accessible by admin & staff)
    public function index(Request $request)
    {
        $query = Destruction::with(['product', 'assigner', 'confirmer']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('period')) {
            switch ($request->period) {
                case 'today':
                    $query->whereDate('created_at', Carbon::today());
                    break;
                case 'week':
                    $query->whereBetween('created_at', [
                        Carbon::now()->startOfWeek(), 
                        Carbon::now()->endOfWeek()
                    ]);
                    break;
                case 'month':
                    $query->whereMonth('created_at', Carbon::now()->month)
                          ->whereYear('created_at', Carbon::now()->year);
                    break;
            }
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $destructions = $query->orderBy('created_at', 'desc')->get();

        return view('destruction.index', compact('destructions'));
    }

    // Admin: Assign destruction task (changes status from Belum Ditugaskan to Menunggu Konfirmasi)
    public function assign(Request $request, $id)
    {
        abort_if(auth()->user()->role !== 'admin_gudang', 403, 'Akses Ditolak: Hanya Admin Gudang yang dapat menugaskan pemusnahan.');

        $destruction = Destruction::findOrFail($id);
        if ($destruction->status !== 'Belum Ditugaskan') {
            return redirect()->back()->with('error', 'Gagal: Tugas pemusnahan ini sudah ditugaskan sebelumnya.');
        }

        $request->validate([
            'catatan_pemusnahan' => 'nullable|string|max:500',
        ]);

        $destruction->status = 'Menunggu Konfirmasi';
        $destruction->catatan_pemusnahan = $request->catatan_pemusnahan;
        $destruction->assigned_by = auth()->id();
        $destruction->assigned_at = now();
        $destruction->save();

        return redirect()->route('destruction.index')->with('success', 'Tugas pemusnahan berhasil didelegasikan kepada staff.');
    }

    // Staff: Show confirmation form
    public function showConfirm($id)
    {
        abort_if(auth()->user()->role !== 'staff_gudang', 403, 'Akses Ditolak: Hanya Staff Gudang yang dapat mengonfirmasi pemusnahan.');

        $destruction = Destruction::with(['product', 'assigner'])->findOrFail($id);
        if ($destruction->status !== 'Menunggu Konfirmasi') {
            return redirect()->route('destruction.index')->with('error', 'Tugas pemusnahan ini sudah selesai diproses.');
        }

        return view('destruction.confirm', compact('destruction'));
    }

    // Staff: Confirm destruction with photo proof
    public function confirm(Request $request, $id)
    {
        abort_if(auth()->user()->role !== 'staff_gudang', 403, 'Akses Ditolak: Hanya Staff Gudang yang dapat mengonfirmasi pemusnahan.');

        $destruction = Destruction::findOrFail($id);
        if ($destruction->status !== 'Menunggu Konfirmasi') {
            return redirect()->route('destruction.index')->with('error', 'Tugas pemusnahan ini sudah selesai diproses.');
        }

        $request->validate([
            'foto_pemusnahan' => 'required|file|mimes:jpg,jpeg,png,webp|max:5120',
        ], [
            'foto_pemusnahan.required' => 'Foto bukti pemusnahan wajib diunggah.',
            'foto_pemusnahan.mimes' => 'Foto harus berformat JPG, PNG, atau WebP.',
            'foto_pemusnahan.max' => 'Ukuran foto maksimal 5MB.',
        ]);

        $path = $request->file('foto_pemusnahan')->store('destruction_proofs', 'public');

        $destruction->status = 'Selesai';
        $destruction->foto_pemusnahan = $path;
        $destruction->confirmed_by = auth()->id();
        $destruction->confirmed_at = now();
        $destruction->save();

        return redirect()->route('destruction.index')->with('success', 'Konfirmasi pemusnahan berhasil disimpan.');
    }
}
