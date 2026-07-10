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
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'creator']);

        if ($request->filled('status')) {
            $status = $request->status;
            if (strtolower($status) === 'partial') {
                $status = 'Partially Received';
            }
            $query->where('status', $status);
        }

        if ($request->filled('period')) {
            switch ($request->period) {
                case 'today':
                    $query->whereDate('created_at', Carbon::today());
                    break;
                case 'week':
                    $query->whereBetween('created_at', [
                        Carbon::now()->startOfWeek(), 
                        Carbon::now()->endOfWeek()
                    ]);
                    break;
                case 'month':
                    $query->whereMonth('created_at', Carbon::now()->month)
                          ->whereYear('created_at', Carbon::now()->year);
                    break;
                case 'year':
                    $query->whereYear('created_at', Carbon::now()->year);
                    break;
            }
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $purchaseOrders = $query->orderBy('created_at', 'desc')->get();
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

    // Store draft PO (dengan harga_satuan per-item dan target_tanggal_kirim)
    public function store(Request $request)
    {
        $request->validate([
            'supplier_id'              => 'required|exists:m_suppliers,id',
            'target_tanggal_kirim'     => 'nullable|date|after_or_equal:today',
            'items'                    => 'required|array',
            'items.*.produk_id'        => 'required|exists:m_products,kode_produk',
            'items.*.qty_pesan'        => 'required|integer|min:1',
            'items.*.harga_satuan'     => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $poNumber = 'PO-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));
            $po = PurchaseOrder::create([
                'po_number'            => $poNumber,
                'supplier_id'          => $request->supplier_id,
                'status'               => 'Draft',
                'total_harga'          => 0, // will be updated below
                'target_tanggal_kirim' => $request->target_tanggal_kirim ?: null,
                'created_by'           => auth()->id(),
            ]);

            $totalHarga = 0;
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['produk_id']);
                $qty = (int) $item['qty_pesan'];

                // Use custom harga_satuan from form if provided; fallback to product master price
                $hargaSatuan = isset($item['harga_satuan']) && $item['harga_satuan'] !== ''
                    ? (float) $item['harga_satuan']
                    : (float) $product->harga_beli;

                $subtotal = $qty * $hargaSatuan;

                PurchaseOrderDetail::create([
                    'po_id'        => $po->id,
                    'produk_id'    => $product->kode_produk,
                    'qty_pesan'    => $qty,
                    'qty_diterima' => 0,
                    'harga_satuan' => $hargaSatuan,
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

    // Confirm shipping return of damaged items (Admin Gudang only)
    public function markAsReturned($id, $historyId)
    {
        abort_if(auth()->user()->role !== 'admin_gudang', 403, 'Akses Ditolak: Hanya Admin Gudang yang dapat memproses pengiriman retur.');

        $po = PurchaseOrder::findOrFail($id);
        $history = PoReceivingHistory::where('po_id', $po->id)->findOrFail($historyId);

        if ($history->status_retur !== 'Menunggu Retur') {
            return redirect()->back()->with('error', 'Gagal: Status retur haruslah Menunggu Retur.');
        }

        $history->status_retur = 'Sudah Diretur';
        $history->tanggal_retur = Carbon::now();
        $history->save();

        return redirect()->route('po.show', $po->id)->with('success', 'Status retur berhasil diperbarui menjadi Sudah Diretur.');
    }
}
