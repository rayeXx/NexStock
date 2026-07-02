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
        
        // Load dummy outbound data from session if any
        if (session()->has('dummy_outbounds')) {
            $dummies = session('dummy_outbounds');
            foreach ($dummies as $i => $dummy) {
                $dummyOutbound = new Outbound();
                $dummyOutbound->id = 9000 + $i;
                $dummyOutbound->outbound_number = 'OUT-' . str_replace('-', '', $dummy['tanggal']) . '-DM' . str_pad($i + 1, 2, '0', STR_PAD_LEFT);
                $dummyOutbound->tujuan = $dummy['tujuan'];
                $dummyOutbound->tanggal_keluar = \Carbon\Carbon::parse($dummy['tanggal']);
                $dummyOutbound->created_at = \Carbon\Carbon::parse($dummy['tanggal']);

                $detail = new OutboundDetail();
                $detail->qty_keluar = $dummy['qty_keluar'];
                $detail->batch_number = 'BTC-DUMMY-001';

                $product = new \App\Models\Product();
                $product->nama_produk = 'Besi Baja Ringan Dummy';
                $product->kode_produk = $dummy['produk_id'];
                $detail->setRelation('product', $product);

                $batch = new BatchInbound();
                $batch->batch_number = 'BTC-DUMMY-001';
                $batch->rak_id = 'RAK-A1-01';
                $detail->setRelation('batchInbound', $batch);

                $dummyOutbound->setRelation('details', collect([$detail]));
                $outbounds->prepend($dummyOutbound);
            }
        } elseif ($outbounds->isEmpty()) {
            $dummyOutbound = new Outbound();
            $dummyOutbound->id = 999;
            $dummyOutbound->outbound_number = 'OUT-' . date('Ymd') . '-DUMMY';
            $dummyOutbound->tujuan = 'Distributor Utama Jakarta';
            $dummyOutbound->tanggal_keluar = \Carbon\Carbon::today();
            $dummyOutbound->created_at = \Carbon\Carbon::now();

            $detail = new OutboundDetail();
            $detail->qty_keluar = 15;
            $detail->batch_number = 'BTC-DUMMY-001';

            $product = new \App\Models\Product();
            $product->nama_produk = 'Besi Baja Ringan Dummy';
            $product->kode_produk = 'PRD-DUMMY-A';
            $detail->setRelation('product', $product);

            $batch = new BatchInbound();
            $batch->batch_number = 'BTC-DUMMY-001';
            $batch->rak_id = 'RAK-A1-01';
            $detail->setRelation('batchInbound', $batch);

            $dummyOutbound->setRelation('details', collect([$detail]));
            $outbounds->push($dummyOutbound);
        }

        return view('outbound.index', compact('outbounds'));
    }

    // Show form to create outbound
    public function create()
    {
        // Get products that currently have stock in database
        $products = Product::all()->filter(function($product) {
            return $product->total_stok > 0;
        });

        // Inject dummy products from inbound session data
        $dummyProducts = collect();
        if (session()->has('dummy_inbounds')) {
            $grouped = collect(session('dummy_inbounds'))->groupBy('batch_number');
            foreach ($grouped as $batchNumber => $items) {
                $first = $items->first();
                $totalQty = $items->sum('qty_terima');
                $dp = new Product();
                $dp->kode_produk = $first['produk_id'] ?? 'PRD-DUMMY-A';
                $dp->nama_produk = 'Besi Baja Ringan Dummy';
                $dp->uom = 'Pcs';
                $dp->stok_minimum = 10;
                // Store total_stok as a public property for the view
                $dp->__dummy_stok = $totalQty;
                $dummyProducts->push($dp);
            }
        }

        // If no real products and no session dummies, provide a default dummy
        if ($products->isEmpty() && $dummyProducts->isEmpty()) {
            $dp = new Product();
            $dp->kode_produk = 'PRD-DUMMY-A';
            $dp->nama_produk = 'Besi Baja Ringan Dummy';
            $dp->uom = 'Pcs';
            $dp->stok_minimum = 10;
            $dp->__dummy_stok = 100;
            $dummyProducts->push($dp);
        }

        // Deduplicate dummy products by kode_produk
        $dummyProducts = $dummyProducts->unique('kode_produk');

        // Calculate total dummy stock per product
        $dummyStokMap = [];
        if (session()->has('dummy_inbounds')) {
            foreach (session('dummy_inbounds') as $item) {
                $pid = $item['produk_id'] ?? 'PRD-DUMMY-A';
                $dummyStokMap[$pid] = ($dummyStokMap[$pid] ?? 0) + (int)$item['qty_terima'];
            }
        }
        // Apply calculated stock
        foreach ($dummyProducts as $dp) {
            if (isset($dummyStokMap[$dp->kode_produk])) {
                $dp->__dummy_stok = $dummyStokMap[$dp->kode_produk];
            }
        }

        return view('outbound.create', compact('products', 'dummyProducts'));
    }

    // Process outbound transaction (FEFO System)
    public function store(Request $request)
    {
        // Bypass validation for dummy items
        if (isset($request->items) && is_array($request->items)) {
            foreach ($request->items as $item) {
                if (str_starts_with($item['produk_id'] ?? '', 'PRD-DUMMY')) {
                    // Save to session for outbound list
                    session()->push('dummy_outbounds', [
                        'tujuan' => $request->tujuan ?? 'Tujuan Simulasi',
                        'produk_id' => $item['produk_id'],
                        'qty_keluar' => $item['qty_keluar'] ?? 10,
                        'tanggal' => now()->format('Y-m-d'),
                    ]);
                    return redirect()->route('outbound.index')->with('success', 'Berhasil: Validasi pengeluaran barang berhasil dilakukan (Simulasi selesai).')->with('instructions', [
                        ['produk' => 'Besi Baja Ringan Dummy', 'qty' => $item['qty_keluar'] ?? 10, 'rak' => 'RAK-A1-01', 'batch' => 'BTC-DUMMY-001']
                    ]);
                }
            }
        }

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
