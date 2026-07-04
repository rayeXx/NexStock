<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Rack;
use App\Models\BatchInbound;
use App\Models\PoReceivingHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PurchaseOrderController extends Controller
{
    // List all POs
    public function index()
    {
        $purchaseOrders = PurchaseOrder::with(['supplier', 'creator'])->orderBy('created_at', 'desc')->get();
        return view('po.index', compact('purchaseOrders'));
    }

    // Show single PO details
    public function show($id)
    {
        $po = PurchaseOrder::with(['supplier', 'creator', 'details.product', 'receivingHistory.product', 'receivingHistory.receiver'])->findOrFail($id);
        
        // Get all racks, order by most available capacity for recommendation
        $racks = Rack::orderByRaw('(kapasitas_maksimum_volume - kapasitas_terpakai) DESC')->get();
        
        return view('po.show', compact('po', 'racks'));
    }

    // Form to create PO
    public function create()
    {
        $suppliers = Supplier::all();
        $products = Product::all();
        return view('po.create', compact('suppliers', 'products'));
    }

    // Store draft PO
    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:m_suppliers,id',
            'items' => 'required|array',
            'items.*.produk_id' => 'required|exists:m_products,kode_produk',
            'items.*.qty_pesan' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $poNumber = 'PO-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));
            $po = PurchaseOrder::create([
                'po_number' => $poNumber,
                'supplier_id' => $request->supplier_id,
                'status' => 'Draft',
                'total_harga' => 0, // calculated from items
                'created_by' => auth()->id(),
            ]);

            $totalHarga = 0;
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['produk_id']);
                $qty = (int)$item['qty_pesan'];
                $hargaBeli = (double)$product->harga_beli;
                $subtotal = $qty * $hargaBeli;

                PurchaseOrderDetail::create([
                    'po_id' => $po->id,
                    'produk_id' => $product->kode_produk,
                    'qty_pesan' => $qty,
                    'qty_diterima' => 0,
                ]);

                $totalHarga += $subtotal;
            }

            $po->total_harga = $totalHarga;
            $po->save();

            DB::commit();
            return redirect()->route('po.index')->with('success', 'Draf Purchase Order berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal membuat PO: ' . $e->getMessage());
        }
    }

    // Admin manually orders a Draft PO
    public function order($id)
    {
        $po = PurchaseOrder::findOrFail($id);
        if ($po->status !== 'Draft') {
            return redirect()->back()->with('error', 'Hanya draf PO yang dapat dipesan ke supplier.');
        }

        $po->status = 'Ordered';
        $po->save();

        return redirect()->route('po.index')->with('success', 'Purchase Order berhasil dipesan ke supplier (Status: Ordered).');
    }

    // Delete PO (Draft only)
    public function destroy($id)
    {
        $po = PurchaseOrder::findOrFail($id);
        if ($po->status !== 'Draft') {
            return redirect()->back()->with('error', 'Hanya draf PO yang dapat dihapus.');
        }

        $po->delete();
        return redirect()->route('po.index')->with('success', 'Draf PO berhasil dihapus.');
    }
}
