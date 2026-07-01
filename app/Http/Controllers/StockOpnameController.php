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

    // Store opname results
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

                // Update batch stock
                $batch->stok_sisa_batch = $qtyFisik;
                $batch->save();

                // Update rack used capacity based on difference (selisih)
                $rack = Rack::where('kode_rak', $batch->rak_id)->first();
                if ($rack) {
                    $rack->kapasitas_terpakai = max(0, $rack->kapasitas_terpakai + $selisih);
                    $rack->save();
                }
            }

            DB::commit();
            return redirect()->route('opname.index')->with('success', 'Stock Opname berhasil disimpan dan stok sistem telah direkonsiliasi.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
