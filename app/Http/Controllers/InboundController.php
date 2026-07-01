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
        return view('inbound.index', compact('inbounds'));
    }

    // Show form to select PO or perform inbound
    public function create(Request $request)
    {
        $po_id = $request->query('po_id');
        $selectedPo = null;
        $poDetails = [];

        if ($po_id) {
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
