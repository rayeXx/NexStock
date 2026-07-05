<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\PoReceivingHistory;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::all();
        return view('supplier.index', compact('suppliers'));
    }

    /**
     * Halaman detail supplier dengan KPI evaluasi performa:
     * - Rata-rata keterlambatan pengiriman (Lead Time Delay)
     * - Rata-rata tingkat kecacatan barang (Defect Rate)
     */
    public function show($id)
    {
        $supplier = Supplier::with(['purchaseOrders.receivingHistory'])->findOrFail($id);

        // ── Total PO & Nilai Pengadaan ───────────────────────────────────
        $totalPO    = $supplier->purchaseOrders->count();
        $totalNilai = $supplier->purchaseOrders->sum('total_harga');

        // ── KPI: Defect Rate ─────────────────────────────────────────────
        // Persentase barang rusak dari seluruh pengiriman supplier ini
        $allHistory  = $supplier->purchaseOrders->flatMap->receivingHistory;
        $totalDatang = $allHistory->sum('qty_datang');
        $totalRusak  = $allHistory->sum('qty_rusak');
        $defectRate  = $totalDatang > 0 ? round(($totalRusak / $totalDatang) * 100, 2) : null;

        // ── KPI: Lead Time Delay (hari) ─────────────────────────────────
        // Dihitung per-PO: selisih hari antara target_tanggal_kirim dan
        // tanggal PERTAMA kali barang diterima (received_at) pada PO tersebut.
        // Nilai positif = terlambat, negatif = lebih cepat dari jadwal.
        $leadTimeDelays = [];
        foreach ($supplier->purchaseOrders->whereNotNull('target_tanggal_kirim') as $po) {
            $firstHistory = $po->receivingHistory->sortBy('received_at')->first();
            if ($firstHistory && $firstHistory->received_at) {
                $targetDate   = Carbon::parse($po->target_tanggal_kirim)->startOfDay();
                $receivedDate = Carbon::instance($firstHistory->received_at)->startOfDay();
                // positive = late (penalty), negative = early (good)
                $delayDays = $targetDate->diffInDays($receivedDate, false);
                $leadTimeDelays[] = $delayDays;
            }
        }
        $avgLeadTimeDelay = count($leadTimeDelays) > 0
            ? round(array_sum($leadTimeDelays) / count($leadTimeDelays), 1)
            : null;

        // ── Riwayat PO ──────────────────────────────────────────────────
        $purchaseOrders = $supplier->purchaseOrders->sortByDesc('created_at');

        return view('supplier.show', compact(
            'supplier',
            'totalPO',
            'totalNilai',
            'defectRate',
            'avgLeadTimeDelay',
            'leadTimeDelays',
            'purchaseOrders'
        ));
    }

    public function create()
    {
        return view('supplier.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_supplier' => 'required|string|max:255',
            'kontak'        => 'required|string|max:255',
        ]);

        Supplier::create($request->all());

        return redirect()->route('supplier.index')->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $supplier = Supplier::findOrFail($id);
        return view('supplier.edit', compact('supplier'));
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $request->validate([
            'nama_supplier' => 'required|string|max:255',
            'kontak'        => 'required|string|max:255',
        ]);

        $supplier->update($request->all());

        return redirect()->route('supplier.index')->with('success', 'Supplier berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();

        return redirect()->route('supplier.index')->with('success', 'Supplier berhasil dihapus.');
    }
}
