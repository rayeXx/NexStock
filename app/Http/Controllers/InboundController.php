<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\BatchInbound;
use App\Models\Rack;
use App\Models\Product;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InboundController extends Controller
{
    // List inbound transactions
    public function index()
    {
        $inbounds = BatchInbound::with(['product', 'purchaseOrder', 'rack'])->orderBy('created_at', 'desc')->get();
        
        // Load dummy data from session if any
        if (session()->has('dummy_inbounds')) {
            // Do not reverse; prepending them in their original order will naturally put the newest at the top of the collection
            $dummies = session('dummy_inbounds');
            foreach($dummies as $dummy) {
                $dummyInbound = new BatchInbound();
                $dummyInbound->batch_number = $dummy['batch_number'];
                $dummyInbound->stok_awal_batch = $dummy['qty_terima'];
                $dummyInbound->stok_sisa_batch = $dummy['qty_terima'];
                $dummyInbound->expired_date = \Carbon\Carbon::parse($dummy['expired_date']);
                $dummyInbound->created_at = \Carbon\Carbon::now();
                
                $product = new \App\Models\Product();
                $product->nama_produk = 'Besi Baja Ringan Dummy';
                $product->kode_produk = 'PRD-DUMMY-A';
                $dummyInbound->setRelation('product', $product);
                $dummyInbound->produk_id = 'PRD-DUMMY-A';

                $po = new \App\Models\PurchaseOrder();
                $po->id = $dummy['po_id'];
                $po->po_number = $dummy['po_id'] == 999 ? 'PO-DUMMY-001' : 'PO-DUMMY-002';
                $dummyInbound->setRelation('purchaseOrder', $po);
                $dummyInbound->po_id = $dummy['po_id'];

                $rack = new \App\Models\Rack();
                $rack->kode_rak = 'RAK-A1-01';
                $dummyInbound->setRelation('rack', $rack);
                $dummyInbound->rak_id = 'RAK-A1-01';

                $inbounds->prepend($dummyInbound);
            }
        } elseif ($inbounds->isEmpty()) {
            $dummyInbound = new BatchInbound();
            $dummyInbound->batch_number = 'BTC-DUMMY-001';
            $dummyInbound->stok_awal_batch = 100;
            $dummyInbound->stok_sisa_batch = 100;
            $dummyInbound->expired_date = \Carbon\Carbon::now()->addYear();
            $dummyInbound->created_at = \Carbon\Carbon::now();
            
            $product = new \App\Models\Product();
            $product->nama_produk = 'Besi Baja Ringan Dummy';
            $product->kode_produk = 'PRD-DUMMY-A';
            $dummyInbound->setRelation('product', $product);
            $dummyInbound->produk_id = 'PRD-DUMMY-A';

            $po = new \App\Models\PurchaseOrder();
            $po->id = 999;
            $po->po_number = 'PO-DUMMY-001';
            $dummyInbound->setRelation('purchaseOrder', $po);
            $dummyInbound->po_id = 999;

            $rack = new \App\Models\Rack();
            $rack->kode_rak = 'RAK-A1-01';
            $dummyInbound->setRelation('rack', $rack);
            $dummyInbound->rak_id = 'RAK-A1-01';

            $inbounds->push($dummyInbound);
        }

        return view('inbound.index', compact('inbounds'));
    }

    // Show form to select PO or perform inbound
    public function create(Request $request)
    {
        $po_id = $request->query('po_id');
        $selectedPo = null;
        $poDetails = [];

        if ($po_id) {
            if ($po_id == 999 || $po_id == 998) {
                // Mock selected PO for dummy
                $selectedPo = new PurchaseOrder();
                $selectedPo->id = $po_id;
                $selectedPo->po_number = $po_id == 999 ? 'PO-DUMMY-001' : 'PO-DUMMY-002';
                $supplier = new \App\Models\Supplier();
                $supplier->nama_supplier = $po_id == 999 ? 'PT Dummy Supplier A' : 'CV Dummy Makmur';
                $selectedPo->setRelation('supplier', $supplier);
                
                $dummyProduct1 = new Product(['kode_produk' => 'PRD-DUMMY-A', 'nama_produk' => 'Besi Baja Ringan Dummy', 'uom' => 'Pcs']);
                $detail1 = new PurchaseOrderDetail(['produk_id' => 'PRD-DUMMY-A', 'qty_pesan' => 100, 'qty_diterima' => 0]);
                $detail1->setRelation('product', $dummyProduct1);
                
                $poDetails = collect([$detail1]);
            } else {
                $selectedPo = PurchaseOrder::where('id', $po_id)->first();
                if ($selectedPo) {
                    if ($selectedPo->status !== 'Approved') {
                        return redirect()->route('inbound.create')
                            ->with('error', 'Akses Ditolak: Dokumen PO Belum Disetujui Owner atau Tidak Ditemukan');
                    }
                    $poDetails = PurchaseOrderDetail::with('product')
                        ->where('po_id', $po_id)
                        ->get()
                        ->filter(function($detail) {
                            return ($detail->qty_pesan - $detail->qty_diterima) > 0;
                        });
                }
            }
        }

        // List approved POs that are not completed yet
        $approvedPos = PurchaseOrder::where('status', 'Approved')
            ->whereHas('details', function($query) {
                $query->whereRaw('qty_pesan > qty_diterima');
            })->get();

        return view('inbound.create', compact('approvedPos', 'selectedPo', 'poDetails'));
    }

    // Process the inbound transaction
    public function store(Request $request)
    {
        if ($request->po_id == 999 || $request->po_id == 998) {
            $dummyItems = collect($request->items)->first();
            if ($dummyItems) {
                // Simpan ke Session agar bisa ditampilkan di riwayat simulasi
                session()->push('dummy_inbounds', [
                    'po_id' => $request->po_id,
                    'batch_number' => $dummyItems['batch_number'] ?? 'BTC-DUMMY-NEW',
                    'qty_terima' => $dummyItems['qty_terima'] ?? 10,
                    'expired_date' => $dummyItems['expired_date'] ?? \Carbon\Carbon::now()->addYear()->format('Y-m-d')
                ]);
            }
            return redirect()->route('inbound.index')->with('success', 'Berhasil: Barang masuk dari PO Dummy berhasil didaftarkan (Simulasi selesai).');
        }

        $request->validate([
            'po_id' => 'required|exists:t_purchase_orders,id',
            'items' => 'required|array',
            'items.*.produk_id' => 'required|exists:m_products,kode_produk',
            'items.*.qty_terima' => 'required|integer|min:1',
            'items.*.batch_number' => 'required|string|unique:t_batch_inbounds,batch_number',
            'items.*.expired_date' => 'required|date',
        ]);

        $po = PurchaseOrder::findOrFail($request->po_id);
        if ($po->status !== 'Approved') {
            return redirect()->back()->with('error', 'Akses Ditolak: Dokumen PO Belum Disetujui Owner atau Tidak Ditemukan');
        }

        $itemsData = $request->items;
        $today = Carbon::today();

        // 1. Validations first (atomic check)
        foreach ($itemsData as $item) {
            $poDetail = PurchaseOrderDetail::where('po_id', $po->id)
                ->where('produk_id', $item['produk_id'])
                ->first();

            if (!$poDetail) {
                return redirect()->back()->withInput()->with('error', 'Gagal: Produk ' . $item['produk_id'] . ' tidak ditemukan dalam Purchase Order ini.');
            }

            // Qty check
            $remainingQty = $poDetail->qty_pesan - $poDetail->qty_diterima;
            if ($item['qty_terima'] > $remainingQty) {
                return redirect()->back()->withInput()->with('error', 'Gagal: Jumlah input barang datang untuk produk ' . $poDetail->product->nama_produk . ' melebihi kuantitas pesanan resmi (Sisa: ' . $remainingQty . ').');
            }

            // Expired date check
            $expDate = Carbon::parse($item['expired_date']);
            if ($expDate->lte($today)) {
                return redirect()->back()->withInput()->with('error', 'Gagal: Tanggal kedaluwarsa produk tidak valid atau sudah terlampaui!');
            }
        }

        // 2. Process transactions inside Database Transaction
        DB::beginTransaction();
        try {
            foreach ($itemsData as $item) {
                $qty = (int)$item['qty_terima'];

                // Smart Rack Recommendation:
                // Find a rack that has enough available capacity, prioritize the one with the most available space.
                $recommendedRack = Rack::orderByRaw('(kapasitas_maksimum_volume - kapasitas_terpakai) DESC')
                    ->first();

                if (!$recommendedRack || ($recommendedRack->kapasitas_maksimum_volume - $recommendedRack->kapasitas_terpakai) < $qty) {
                    // If the best rack cannot hold it, find any rack or error out
                    throw new \Exception('Gagal: Kapasitas seluruh rak tidak mencukupi untuk menampung ' . $qty . ' unit.');
                }

                // Create the batch inbound
                BatchInbound::create([
                    'batch_number' => $item['batch_number'],
                    'produk_id' => $item['produk_id'],
                    'po_id' => $po->id,
                    'rak_id' => $recommendedRack->kode_rak,
                    'expired_date' => $item['expired_date'],
                    'stok_awal_batch' => $qty,
                    'stok_sisa_batch' => $qty,
                ]);

                // Update PO detail received quantity
                $poDetail = PurchaseOrderDetail::where('po_id', $po->id)
                    ->where('produk_id', $item['produk_id'])
                    ->first();
                $poDetail->qty_diterima += $qty;
                $poDetail->save();

                // Update rack used capacity
                $recommendedRack->kapasitas_terpakai += $qty;
                $recommendedRack->save();
            }

            // Check if PO is now completed (all items received)
            $allReceived = true;
            $updatedDetails = PurchaseOrderDetail::where('po_id', $po->id)->get();
            foreach ($updatedDetails as $detail) {
                if ($detail->qty_pesan > $detail->qty_diterima) {
                    $allReceived = false;
                    break;
                }
            }

            if ($allReceived) {
                $po->status = 'Completed';
                $po->save();
            }

            DB::commit();
            return redirect()->route('inbound.index')->with('success', 'Barang masuk berhasil didaftarkan dan diletakkan di rak rekomendasi.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }
}
