<?php

namespace App\Http\Controllers;

use App\Models\DamagedReport;
use App\Models\BatchInbound;
use App\Models\Product;
use App\Models\Rack;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DamagedReportController extends Controller
{
    // List reports (accessible by staff & owner)
    public function index()
    {
        $reports = DamagedReport::with(['product', 'batchInbound', 'rack', 'creator'])->orderBy('created_at', 'desc')->get();
        
        return view('damaged.index', compact('reports'));
    }

    // Show create form
    public function create()
    {
        // Get active batches that have remaining stock
        $activeBatches = BatchInbound::with('product')
            ->where('stok_sisa_batch', '>', 0)
            ->get();
        return view('damaged.create', compact('activeBatches'));
    }

    // Store the report and isolate stock (Pending status)
    public function store(Request $request)
    {
        abort_if(auth()->user()->role !== 'staff_gudang', 403, 'Akses Ditolak: Hanya Staff Gudang yang dapat mengajukan laporan barang rusak.');

        $request->validate([
            'batch_number' => 'required|exists:t_batch_inbounds,batch_number',
            'qty_rusak' => 'required|integer|min:1',
            'alasan' => 'required|string',
            'foto_bukti' => 'required|image|max:2048', // max 2MB
        ]);

        $batch = BatchInbound::findOrFail($request->batch_number);

        if ($request->qty_rusak > $batch->stok_sisa_batch) {
            return redirect()->back()->withInput()->with('error', 'Gagal: Jumlah kuantitas barang rusak melebihi sisa stok batch ini (Maks: ' . $batch->stok_sisa_batch . ').');
        }

        DB::beginTransaction();
        try {
            // Handle file upload
            $filePath = null;
            if ($request->hasFile('foto_bukti')) {
                $filePath = $request->file('foto_bukti')->store('damaged_reports', 'public');
            }

            // Create Damaged Report (Pending)
            DamagedReport::create([
                'produk_id' => $batch->produk_id,
                'batch_number' => $batch->batch_number,
                'rak_id' => $batch->rak_id,
                'qty_rusak' => $request->qty_rusak,
                'foto_bukti' => $filePath,
                'alasan' => $request->alasan,
                'status' => 'Pending',
                'created_by' => auth()->id(),
            ]);

            // Deduct stock from the active batch immediately (to prevent selling/moving it)
            $batch->stok_sisa_batch -= $request->qty_rusak;
            $batch->save();

            // DO NOT reduce rack capacity here, because the damaged goods are still physically on the rack.

            DB::commit();
            return redirect()->route('damaged.index')->with('success', 'Laporan barang rusak berhasil diajukan dan stok dipindahkan ke status Karantina.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function approve($id)
    {
        abort_if(auth()->user()->role !== 'admin_gudang', 403, 'Akses Ditolak: Hanya Admin Gudang yang dapat menyetujui laporan barang rusak.');

        $report = DamagedReport::findOrFail($id);
        if ($report->status !== 'Pending') {
            return redirect()->back()->with('error', 'Gagal: Laporan ini sudah diproses sebelumnya.');
        }

        DB::beginTransaction();
        try {
            $report->status = 'Approved';
            $report->save();

            // Reduce rack capacity now, because physical items are discarded/moved out of the warehouse.
            $rack = Rack::where('kode_rak', $report->rak_id)->first();
            if ($rack) {
                $rack->kapasitas_terpakai = max(0, $rack->kapasitas_terpakai - $report->qty_rusak);
                $rack->save();
            }

            DB::commit();
            return redirect()->route('damaged.index')->with('success', 'Laporan barang rusak berhasil disetujui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyetujui laporan: ' . $e->getMessage());
        }
    }

    public function reject($id)
    {
        abort_if(auth()->user()->role !== 'admin_gudang', 403, 'Akses Ditolak: Hanya Admin Gudang yang dapat menolak laporan barang rusak.');

        $report = DamagedReport::findOrFail($id);
        if ($report->status !== 'Pending') {
            return redirect()->back()->with('error', 'Gagal: Laporan ini sudah diproses sebelumnya.');
        }

        DB::beginTransaction();
        try {
            $report->status = 'Rejected';
            $report->save();

            // Restore stock back to the batch
            $batch = BatchInbound::where('batch_number', $report->batch_number)->first();
            if ($batch) {
                $batch->stok_sisa_batch += $report->qty_rusak;
                $batch->save();
            }
            // DO NOT restore/change rack capacity because it was never deducted during store!

            DB::commit();
            return redirect()->route('damaged.index')->with('success', 'Laporan barang rusak ditolak dan stok dikembalikan ke posisi semula.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menolak laporan: ' . $e->getMessage());
        }
    }
}
