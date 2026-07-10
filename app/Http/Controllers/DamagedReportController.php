<?php

namespace App\Http\Controllers;

use App\Models\DamagedReport;
use App\Models\BatchInbound;
use App\Models\Product;
use App\Models\Rack;
use App\Models\Destruction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DamagedReportController extends Controller
{
    // List reports (accessible by staff & owner)
    public function index(Request $request)
    {
        self::autoIsolateExpired();

        $query = DamagedReport::with(['product', 'batchInbound', 'rack', 'creator']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reports = $query->orderBy('created_at', 'desc')->get();
        
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

            // Automatically copy to t_destructions with status 'Belum Ditugaskan'
            Destruction::create([
                'damaged_report_id' => $report->id,
                'produk_id' => $report->produk_id,
                'batch_number' => $report->batch_number,
                'rak_id' => $report->rak_id,
                'qty_dimusnahkan' => $report->qty_rusak,
                'alasan' => $report->alasan,
                'status' => 'Belum Ditugaskan',
            ]);

            DB::commit();
            return redirect()->route('damaged.index')->with('success', 'Laporan barang rusak disetujui dan diteruskan ke Pemusnahan Barang.');
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

    /**
     * Helper: Automatically isolate expired batches
     */
    public static function autoIsolateExpired()
    {
        $today = Carbon::today();
        
        // Find all active batches that are expired
        $expiredBatches = BatchInbound::where('expired_date', '<=', $today)
            ->where('stok_sisa_batch', '>', 0)
            ->get();

        if ($expiredBatches->isEmpty()) {
            return;
        }

        // Get a default admin user ID for created_by
        $defaultUser = \App\Models\User::where('role', 'admin_gudang')->first();
        $defaultUserId = $defaultUser ? $defaultUser->id : 1;

        foreach ($expiredBatches as $batch) {
            // Check if this batch is already isolated/quarantined
            $exists = DamagedReport::where('batch_number', $batch->batch_number)->exists();
            if ($exists) {
                continue;
            }

            DB::beginTransaction();
            try {
                $qty = $batch->stok_sisa_batch;

                // Create DamagedReport with 'Expired Pending Check' status
                DamagedReport::create([
                    'produk_id' => $batch->produk_id,
                    'batch_number' => $batch->batch_number,
                    'rak_id' => $batch->rak_id,
                    'qty_rusak' => $qty,
                    'foto_bukti' => null,
                    'alasan' => 'Expired',
                    'status' => 'Expired Pending Check',
                    'created_by' => auth()->id() ?? $defaultUserId,
                ]);

                // Reduce the remaining stock in batch to 0
                $batch->stok_sisa_batch = 0;
                $batch->save();

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                logger()->error('Auto isolation failed for batch ' . $batch->batch_number . ': ' . $e->getMessage());
            }
        }
    }

    /**
     * Staff: Show confirmation form for expired item
     */
    public function showConfirmExpired($id)
    {
        abort_if(auth()->user()->role !== 'staff_gudang', 403, 'Akses Ditolak: Hanya Staff Gudang yang dapat mengonfirmasi barang expired.');

        $report = DamagedReport::with(['product', 'rack', 'creator'])->findOrFail($id);
        if ($report->status !== 'Expired Pending Check') {
            return redirect()->route('damaged.index')->with('error', 'Laporan ini tidak memerlukan konfirmasi expired.');
        }

        return view('damaged.confirm_expired', compact('report'));
    }

    /**
     * Staff: Confirm expired item by uploading photo proof
     */
    public function confirmExpired(Request $request, $id)
    {
        abort_if(auth()->user()->role !== 'staff_gudang', 403, 'Akses Ditolak: Hanya Staff Gudang yang dapat mengonfirmasi barang expired.');

        $report = DamagedReport::findOrFail($id);
        if ($report->status !== 'Expired Pending Check') {
            return redirect()->route('damaged.index')->with('error', 'Laporan ini tidak memerlukan konfirmasi expired.');
        }

        $request->validate([
            'foto_bukti' => 'required|file|mimes:jpg,jpeg,png,webp|max:5120',
        ], [
            'foto_bukti.required' => 'Foto bukti barang expired wajib diunggah.',
            'foto_bukti.mimes' => 'Foto harus berformat JPG, PNG, atau WebP.',
            'foto_bukti.max' => 'Ukuran foto maksimal 5MB.',
        ]);

        $path = $request->file('foto_bukti')->store('damaged_reports', 'public');

        $report->status = 'Pending';
        $report->foto_bukti = $path;
        $report->save();

        return redirect()->route('damaged.index')->with('success', 'Konfirmasi barang expired berhasil disimpan. Menunggu persetujuan admin.');
    }
}

