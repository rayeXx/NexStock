<?php

namespace App\Http\Controllers;

use App\Models\Outbound;
use App\Models\OutboundDetail;
use App\Models\BatchInbound;
use App\Models\Product;
use App\Models\Rack;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OutboundController extends Controller
{
    // List outbound transactions
    public function index()
    {
        $outbounds = Outbound::with(['details.product', 'details.batchInbound'])->orderBy('created_at', 'desc')->get();
        return view('outbound.index', compact('outbounds'));
    }

    // Show form to create outbound
    public function create()
    {
        // Get products that currently have stock in database
        $products = Product::all()->filter(function($product) {
            return $product->total_stok > 0;
        });
        return view('outbound.create', compact('products'));
    }

    // Process outbound transaction (FEFO System)
    public function store(Request $request)
    {
        $request->validate([
            'tujuan' => 'required|string',
            'items' => 'required|array',
            'items.*.produk_id' => 'required|exists:m_products,kode_produk',
            'items.*.qty_keluar' => 'required|integer|min:1',
        ]);

        $itemsData = $request->items;

        // 1. Validation phase
        foreach ($itemsData as $item) {
            $product = Product::findOrFail($item['produk_id']);
            $totalStok = $product->total_stok;

            if ($item['qty_keluar'] > $totalStok) {
                return redirect()->back()->withInput()->with('error', 'Gagal: Sisa total stok produk ' . $product->nama_produk . ' di sistem tidak mencukupi permintaan (Stok: ' . $totalStok . ', Diminta: ' . $item['qty_keluar'] . ').');
            }
        }

        // 2. Database Transaction block
        DB::beginTransaction();
        try {
            // Generate unique outbound number (e.g. OUT-YYYYMMDD-XXXX)
            $outboundNumber = 'OUT-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));

            $outbound = Outbound::create([
                'outbound_number' => $outboundNumber,
                'tujuan' => $request->tujuan,
                'tanggal_keluar' => date('Y-m-d'),
            ]);

            $instructions = []; // Details to show to the staff

            foreach ($itemsData as $item) {
                $product = Product::findOrFail($item['produk_id']);
                $qtyNeeded = (int)$item['qty_keluar'];

                // FEFO: Get active batches ordered by expired_date ASC (earliest expired first)
                $batches = BatchInbound::where('produk_id', $product->kode_produk)
                    ->where('stok_sisa_batch', '>', 0)
                    ->orderBy('expired_date', 'asc')
                    ->get();

                foreach ($batches as $batch) {
                    if ($qtyNeeded <= 0) {
                        break;
                    }

                    $available = $batch->stok_sisa_batch;
                    $qtyToTake = min($qtyNeeded, $available);

                    // Deduct from batch
                    $batch->stok_sisa_batch -= $qtyToTake;
                    $batch->save();

                    // Free up rack capacity
                    $rack = Rack::where('kode_rak', $batch->rak_id)->first();
                    if ($rack) {
                        $rack->kapasitas_terpakai = max(0, $rack->kapasitas_terpakai - $qtyToTake);
                        $rack->save();
                    }

                    // Create outbound detail
                    OutboundDetail::create([
                        'outbound_id' => $outbound->id,
                        'produk_id' => $product->kode_produk,
                        'batch_number' => $batch->batch_number,
                        'qty_keluar' => $qtyToTake,
                    ]);

                    $instructions[] = [
                        'produk' => $product->nama_produk,
                        'qty' => $qtyToTake,
                        'rak' => $batch->rak_id,
                        'batch' => $batch->batch_number,
                    ];

                    $qtyNeeded -= $qtyToTake;
                }
            }

            DB::commit();

            // Redirect back with details of where to pick items
            return redirect()->route('outbound.index')
                ->with('success', 'Barang keluar berhasil divalidasi!')
                ->with('instructions', $instructions);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
