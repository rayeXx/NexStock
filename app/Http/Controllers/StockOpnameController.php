<?php

namespace App\Http\Controllers;

use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use App\Models\BatchInbound;
use App\Models\Rack;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockOpnameController extends Controller
{
    // List opname history
    public function index()
    {
        $opnames = StockOpname::with(['details.product', 'creator'])->orderBy('created_at', 'desc')->get();
        return view('opname.index', compact('opnames'));
    }

    // Show opname history detail
    public function show($id)
    {
        $opname = StockOpname::with(['details.product', 'details.batchInbound', 'creator'])->findOrFail($id);
        return view('opname.show', compact('opname'));
    }

    // Show create form
    public function create()
    {
        // Get all active batches with non-zero stock
        $batches = BatchInbound::with('product')
            ->where('stok_sisa_batch', '>', 0)
            ->get();
        return view('opname.create', compact('batches'));
    }

    // Store opname results (Pending Approval)
    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.batch_number' => 'required|exists:t_batch_inbounds,batch_number',
            'items.*.qty_fisik' => 'required|integer|min:0',
            'items.*.catatan' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Create Stock Opname header
            $opname = StockOpname::create([
                'tanggal_opname' => date('Y-m-d'),
                'created_by' => auth()->id(),
                'status' => 'Pending Approval',
            ]);

            foreach ($request->items as $item) {
                $batch = BatchInbound::findOrFail($item['batch_number']);
                $qtySistem = $batch->stok_sisa_batch;
                $qtyFisik = (int)$item['qty_fisik'];
                $selisih = $qtyFisik - $qtySistem;

                // Create Detail
                StockOpnameDetail::create([
                    'stock_opname_id' => $opname->id,
                    'produk_id' => $batch->produk_id,
                    'batch_number' => $batch->batch_number,
                    'qty_sistem' => $qtySistem,
                    'qty_fisik' => $qtyFisik,
                    'selisih' => $selisih,
                    'catatan' => $item['catatan'] ?? null,
                ]);

                // We DO NOT update batch stock and rack capacity here.
                // It will only be updated after approval.
            }

            DB::commit();
            return redirect()->route('opname.index')->with('success', 'Stock Opname berhasil diajukan dan menunggu persetujuan Owner / Admin Gudang.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // Sahkan/Approve Stock Opname (Owner or Admin Gudang)
    public function approve($id)
    {
        abort_if(!in_array(auth()->user()->role, ['owner', 'admin_gudang']), 403, 'Akses Ditolak: Hanya Owner atau Admin Gudang yang dapat mensahkan hasil audit.');

        $opname = StockOpname::with('details')->findOrFail($id);
        if ($opname->status !== 'Pending Approval') {
            return redirect()->back()->with('error', 'Gagal: Hasil opname ini sudah diproses atau disahkan sebelumnya.');
        }

        DB::beginTransaction();
        try {
            $opname->status = 'Approved';
            $opname->approved_by = auth()->id();
            $opname->approved_at = now();
            $opname->save();

            foreach ($opname->details as $detail) {
                $batch = BatchInbound::where('batch_number', $detail->batch_number)->first();
                if ($batch) {
                    // Update batch stock
                    $batch->stok_sisa_batch = $detail->qty_fisik;
                    $batch->save();

                    // Update rack used capacity based on difference (selisih)
                    $rack = Rack::where('kode_rak', $batch->rak_id)->first();
                    if ($rack) {
                        $rack->kapasitas_terpakai = max(0, $rack->kapasitas_terpakai + $detail->selisih);
                        $rack->save();
                    }
                }
            }

            DB::commit();
            return redirect()->route('opname.show', $opname->id)->with('success', 'Hasil audit Stock Opname berhasil disahkan. Stok sistem dan kapasitas rak telah disesuaikan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mensahkan hasil opname: ' . $e->getMessage());
        }
    }
}
