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
        
        // Dummy data for visualization if empty
        if ($reports->isEmpty()) {
            $dummyReport = new DamagedReport();
            $dummyReport->id = 999;
            $dummyReport->qty_rusak = 5;
            $dummyReport->alasan = 'Kardus penyok terkena rembesan air saat hujan (Simulasi)';
            $dummyReport->status = 'Pending';
            $dummyReport->foto_bukti = null;
            $dummyReport->created_at = \Carbon\Carbon::now();

            $product = new Product();
            $product->nama_produk = 'Besi Baja Ringan Dummy';
            $product->kode_produk = 'PRD-DUMMY-A';
            $dummyReport->setRelation('product', $product);

            $batch = new BatchInbound();
            $batch->batch_number = 'BTC-DUMMY-001';
            $dummyReport->setRelation('batchInbound', $batch);

            $rack = new Rack();
            $rack->kode_rak = 'RAK-A1-01';
            $dummyReport->setRelation('rack', $rack);
            
            $user = new \App\Models\User();
            $user->name = 'Staff NexStock (Simulasi)';
            $dummyReport->setRelation('creator', $user);

            $reports->push($dummyReport);
        }

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
        if ($request->batch_number === 'BATCH-DUMMY-001' || $request->batch_number === 'BATCH-DUMMY-002') {
            return redirect()->route('damaged.index')->with('success', 'Berhasil: Laporan barang rusak (Dummy) berhasil diajukan (Simulasi selesai).');
        }

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

            // Partially free up rack capacity
            $rack = Rack::where('kode_rak', $batch->rak_id)->first();
            if ($rack) {
                $rack->kapasitas_terpakai = max(0, $rack->kapasitas_terpakai - $request->qty_rusak);
                $rack->save();
            }

            DB::commit();
            return redirect()->route('damaged.index')->with('success', 'Laporan barang rusak berhasil diajukan dan stok dipindahkan ke status Karantina.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // Owner approves the quarantine (disposal)
    public function approve($id)
    {
        $report = DamagedReport::findOrFail($id);
        if ($report->status !== 'Pending') {
            return redirect()->back()->with('error', 'Laporan sudah diproses sebelumnya.');
        }

        $report->status = 'Approved';
        $report->save();

        return redirect()->route('damaged.index')->with('success', 'Laporan disetujui. Stok rusak telah dihapus permanen dari sistem.');
    }

    // Owner rejects the quarantine (rollback to stock)
    public function reject($id)
    {
        $report = DamagedReport::findOrFail($id);
        if ($report->status !== 'Pending') {
            return redirect()->back()->with('error', 'Laporan sudah diproses sebelumnya.');
        }

        DB::beginTransaction();
        try {
            $report->status = 'Rejected';
            $report->save();

            // Restore stock to the batch
            $batch = BatchInbound::findOrFail($report->batch_number);
            $batch->stok_sisa_batch += $report->qty_rusak;
            $batch->save();

            // Add back to rack capacity
            $rack = Rack::where('kode_rak', $report->rak_id)->first();
            if ($rack) {
                $rack->kapasitas_terpakai += $report->qty_rusak;
                $rack->save();
            }

            DB::commit();
            return redirect()->route('damaged.index')->with('success', 'Laporan ditolak. Stok telah dikembalikan ke area rak siap jual.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal membatalkan laporan: ' . $e->getMessage());
        }
    }
}
