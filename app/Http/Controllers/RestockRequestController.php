<?php

namespace App\Http\Controllers;

use App\Models\RestockRequest;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RestockRequestController extends Controller
{
    /**
     * Staff: Form pengajuan restock
     */
    public function create(Request $request)
    {
        $products = Product::with('category')->get();
        $selectedProduct = null;

        if ($kode = $request->query('produk')) {
            $selectedProduct = Product::find($kode);
        }

        return view('restock-request.create', compact('products', 'selectedProduct'));
    }

    /**
     * Staff: Simpan pengajuan restock
     */
    public function store(Request $request)
    {
        $request->validate([
            'produk_id' => 'required|exists:m_products,kode_produk',
            'qty_request' => 'required|integer|min:1',
            'alasan' => 'required|string|max:1000',
        ]);

        RestockRequest::create([
            'produk_id' => $request->produk_id,
            'qty_request' => $request->qty_request,
            'alasan' => $request->alasan,
            'status' => 'Menunggu Review',
            'requested_by' => auth()->id(),
        ]);

        return redirect()->route('restock-request.history')
            ->with('success', 'Pengajuan restock berhasil dikirim. Menunggu review Admin Gudang.');
    }

    /**
     * Staff: Riwayat pengajuan sendiri
     */
    public function history()
    {
        $requests = RestockRequest::with(['product', 'reviewer'])
            ->where('requested_by', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('restock-request.history', compact('requests'));
    }

    /**
     * Admin: Halaman review semua pengajuan
     */
    public function reviewIndex()
    {
        $requests = RestockRequest::with(['product', 'requester', 'reviewer', 'purchaseOrder'])
            ->orderByRaw("CASE WHEN status = 'Menunggu Review' THEN 0 ELSE 1 END")
            ->orderBy('created_at', 'desc')
            ->get();

        return view('restock-request.review', compact('requests'));
    }

    /**
     * Admin: Approve pengajuan → auto create PO Draft
     */
    public function approve(Request $request, $id)
    {
        $restockRequest = RestockRequest::findOrFail($id);

        if ($restockRequest->status !== 'Menunggu Review') {
            return redirect()->back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $request->validate([
            'supplier_id' => 'required|exists:m_suppliers,id',
        ]);

        DB::beginTransaction();
        try {
            $product = Product::findOrFail($restockRequest->produk_id);

            // Auto create PO Draft
            $poNumber = 'PO-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));
            $hargaBeli = (double) $product->harga_beli;
            $totalHarga = $restockRequest->qty_request * $hargaBeli;

            $po = PurchaseOrder::create([
                'po_number' => $poNumber,
                'supplier_id' => $request->supplier_id,
                'status' => 'Draft',
                'total_harga' => $totalHarga,
                'created_by' => auth()->id(),
            ]);

            PurchaseOrderDetail::create([
                'po_id' => $po->id,
                'produk_id' => $product->kode_produk,
                'qty_pesan' => $restockRequest->qty_request,
                'qty_diterima' => 0,
            ]);

            // Update restock request
            $restockRequest->update([
                'status' => 'Approved',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => Carbon::now(),
                'po_id' => $po->id,
            ]);

            DB::commit();
            return redirect()->route('restock-request.review')
                ->with('success', "Pengajuan disetujui. PO Draft {$poNumber} berhasil dibuat otomatis.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal approve: ' . $e->getMessage());
        }
    }

    /**
     * Admin: Reject pengajuan
     */
    public function reject(Request $request, $id)
    {
        $restockRequest = RestockRequest::findOrFail($id);

        if ($restockRequest->status !== 'Menunggu Review') {
            return redirect()->back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $request->validate([
            'alasan_reject' => 'required|string|max:1000',
        ]);

        $restockRequest->update([
            'status' => 'Rejected',
            'alasan_reject' => $request->alasan_reject,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => Carbon::now(),
        ]);

        return redirect()->route('restock-request.review')
            ->with('success', 'Pengajuan restock telah ditolak.');
    }
}
