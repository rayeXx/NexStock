<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\BatchInbound;
use App\Models\PoReceivingHistory;
use App\Models\Rack;
use App\Models\Product;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InboundController extends Controller
{
    // List inbound transactions
    public function index(Request $request)
    {
        $inbounds = BatchInbound::with(['product', 'purchaseOrder', 'rack'])->orderBy('created_at', 'desc')->get();
        
        return view('inbound.index', compact('inbounds'));
    }

    // Show form to select PO or perform inbound
    public function create(Request $request)
    {
        abort_if(auth()->user()->role !== 'staff_gudang', 403, 'Akses Ditolak: Hanya Staff Gudang yang diizinkan memproses penerimaan barang.');

        $po_id = $request->query('po_id');
        $selectedPo = null;
        $poDetails = [];

        if ($po_id) {
            $selectedPo = PurchaseOrder::with('receivingHistory')->where('id', $po_id)->first();
            if ($selectedPo) {
                if (!in_array($selectedPo->status, ['Ordered', 'Partially Received'])) {
                    return redirect()->route('inbound.create')
                        ->with('error', 'Akses Ditolak: Dokumen PO Belum Dipesan atau Tidak Ditemukan');
                }
                $poDetails = PurchaseOrderDetail::with('product')
                    ->where('po_id', $po_id)
                    ->get()
                    ->filter(function($detail) {
                        return ($detail->qty_pesan - $detail->qty_diterima) > 0;
                    });
            }
        }

        // List approved or partial POs that are not completed yet
        $approvedPos = PurchaseOrder::whereIn('status', ['Ordered', 'Partially Received'])
            ->whereHas('details', function($query) {
                $query->whereColumn('qty_pesan', '>', 'qty_diterima');
            })->get();

        $racks = \App\Models\Rack::all();

        return view('inbound.create', compact('approvedPos', 'selectedPo', 'poDetails', 'racks'));
    }

    public function store(Request $request)
    {
        abort_if(auth()->user()->role !== 'staff_gudang', 403, 'Akses Ditolak: Hanya Staff Gudang yang diizinkan memproses penerimaan barang.');

        $po = PurchaseOrder::with('details.product')->findOrFail($request->po_id);

        if (!in_array($po->status, ['Ordered', 'Partially Received'])) {
            return redirect()->back()->with('error', 'Hanya PO berstatus Ordered atau Partially Received yang dapat diproses penerimaannya.');
        }

        $validationRules = [
            'items' => 'required|array',
            'items.*.produk_id' => 'required|exists:m_products,kode_produk',
            'items.*.qty_datang' => 'required|integer|min:0',
            'items.*.qty_rusak' => 'required|integer|min:0',
            'items.*.alasan_kerusakan' => 'nullable|string',
            'items.*.catatan' => 'nullable|string',
            'items.*.batch_supplier' => 'nullable|string',
            'items.*.expired_date' => 'nullable|date',
            'items.*.rak_id' => 'nullable|exists:m_racks,kode_rak',
        ];

        if (auth()->user()->role === 'staff_gudang') {
            $validationRules['foto_bukti'] = 'required|image|max:2048';
        } else {
            $validationRules['foto_bukti'] = 'nullable|image|max:2048';
        }

        $request->validate($validationRules);

        // Validasi Kelebihan Pengiriman / Over-receiving (Money Mastery)
        foreach ($request->items as $item) {
            $qtyDatang = (int)($item['qty_datang'] ?? 0);
            if ($qtyDatang <= 0) continue;

            $poDetail = PurchaseOrderDetail::where('po_id', $po->id)
                ->where('produk_id', $item['produk_id'])
                ->first();

            if (!$poDetail) {
                return redirect()->back()->withInput()->with('error', 'Item ' . $item['produk_id'] . ' tidak ditemukan pada PO ini.');
            }

            $remaining = $poDetail->qty_pesan - $poDetail->qty_diterima;
            if ($qtyDatang > $remaining) {
                return redirect()->back()->withInput()->with('error', 'Jumlah barang datang tidak boleh melebihi sisa pesanan PO.');
            }
        }

        DB::beginTransaction();
        try {
            $hasAnyReceive = false;
            $now = Carbon::now();

            $filePath = null;
            if ($request->hasFile('foto_bukti')) {
                $filePath = $request->file('foto_bukti')->store('inbound_receipts', 'public');
            }

            foreach ($request->items as $item) {
                $qtyDatang = (int)($item['qty_datang'] ?? 0);
                if ($qtyDatang <= 0) continue;

                $hasAnyReceive = true;
                $qtyRusak = (int)($item['qty_rusak'] ?? 0);
                $qtyDiterima = $qtyDatang - $qtyRusak;

                // Validate Expired Date if qtyDiterima > 0
                if ($qtyDiterima > 0) {
                    if (empty($item['expired_date'])) {
                        throw new \Exception('Tanggal Expired wajib diisi untuk barang yang diterima dengan kondisi baik (Produk: ' . $item['produk_id'] . ').');
                    }
                    $expDate = Carbon::parse($item['expired_date']);
                    if ($expDate->lte($now->copy()->startOfDay())) {
                        throw new \Exception('Tanggal Expired tidak boleh kurang dari atau sama dengan hari ini (Produk: ' . $item['produk_id'] . ').');
                    }
                    if (empty($item['rak_id'])) {
                        throw new \Exception('Rak penyimpanan wajib dipilih untuk barang yang diterima (Produk: ' . $item['produk_id'] . ').');
                    }
                }

                // Determine Kondisi Barang & Status Retur (No return flow is initiated; treated as partial/short delivery)
                $kondisiBarang = 'Baik';
                $statusRetur = null;
                
                if ($qtyRusak > 0) {
                    if ($qtyDiterima > 0) {
                        $kondisiBarang = 'Rusak Sebagian';
                    } else {
                        $kondisiBarang = 'Ditolak';
                    }
                }

                $poDetail = PurchaseOrderDetail::where('po_id', $po->id)
                    ->where('produk_id', $item['produk_id'])
                    ->first();

                $batchInternal = null;

                // Process Inbound for good items
                if ($qtyDiterima > 0) {
                    $product = $poDetail->product;
                    $rasio = $product->rasio_konversi ?? 1;
                    $qtyDiterimaJual = $qtyDiterima * $rasio;

                    $rack = Rack::where('kode_rak', $item['rak_id'])->first();
                    if (($rack->kapasitas_maksimum_volume - $rack->kapasitas_terpakai) < $qtyDiterimaJual) {
                        throw new \Exception('Kapasitas Rak ' . $rack->nama_rak . ' tidak mencukupi untuk ' . $qtyDiterimaJual . ' unit.');
                    }

                    $batchInternal = 'BTC-PO' . $po->id . '-' . $item['produk_id'] . '-' . time();

                    BatchInbound::create([
                        'batch_number' => $batchInternal,
                        'batch_supplier' => $item['batch_supplier'] ?? null,
                        'produk_id' => $item['produk_id'],
                        'po_id' => $po->id,
                        'rak_id' => $rack->kode_rak,
                        'expired_date' => $item['expired_date'],
                        'stok_awal_batch' => $qtyDiterimaJual,
                        'stok_sisa_batch' => $qtyDiterimaJual,
                    ]);

                    $rack->kapasitas_terpakai += $qtyDiterimaJual;
                    $rack->save();

                    $poDetail->qty_diterima += $qtyDiterima;
                    $poDetail->save();
                }

                // Determine catatan
                $catatan = null;
                if (!empty($item['alasan_kerusakan']) && $item['alasan_kerusakan'] === 'Lainnya') {
                    $catatan = $item['catatan'] ?? null;
                } else if (!empty($item['catatan'])) {
                    $catatan = $item['catatan'];
                }

                // Save History
                PoReceivingHistory::create([
                    'po_id' => $po->id,
                    'produk_id' => $item['produk_id'],
                    'qty_datang' => $qtyDatang,
                    'qty_rusak' => $qtyRusak,
                    'qty_received' => $qtyDiterima, // qty_received is equivalent to qty_diterima
                    'kondisi_barang' => $kondisiBarang,
                    'alasan_kerusakan' => $qtyRusak > 0 ? $item['alasan_kerusakan'] : null,
                    'catatan' => $catatan,
                    'batch_number' => $batchInternal,
                    'batch_supplier' => $item['batch_supplier'] ?? null,
                    'expired_date' => $qtyDiterima > 0 ? $item['expired_date'] : null,
                    'rak_id' => $qtyDiterima > 0 ? $item['rak_id'] : null,
                    'foto_bukti' => $filePath,
                    'status_retur' => $statusRetur,
                    'received_at' => $now,
                    'received_by' => auth()->id(),
                ]);
            }

            if (!$hasAnyReceive) {
                throw new \Exception('Tidak ada data penerimaan yang diisi.');
            }

            // Update PO Status
            $allReceived = true;
            $anyReceived = false;
            
            // Re-fetch details to check totals
            $updatedDetails = PurchaseOrderDetail::where('po_id', $po->id)->get();
            foreach ($updatedDetails as $d) {
                if ($d->qty_diterima > 0) {
                    $anyReceived = true;
                }
                if ($d->qty_pesan > $d->qty_diterima) {
                    $allReceived = false;
                }
            }

            if ($allReceived) {
                $po->status = 'Completed';
            } elseif ($anyReceived) {
                $po->status = 'Partially Received';
            } else {
                $po->status = 'Ordered';
            }
            $po->save();

            DB::commit();
            return redirect()->route('inbound.index')->with('success', 'Berhasil memproses penerimaan barang (Inbound). Stok telah bertambah sesuai jumlah barang yang diterima dalam kondisi baik.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal memproses penerimaan: ' . $e->getMessage());
        }
    }

    public function updateRetur(Request $request, $id, $historyId)
    {
        abort_if(auth()->user()->role === 'admin_gudang', 403, 'Akses Ditolak: Admin tidak diizinkan memproses retur.');

        $po = PurchaseOrder::findOrFail($id);
        $history = PoReceivingHistory::where('po_id', $po->id)->findOrFail($historyId);

        $request->validate([
            'status_retur' => 'required|in:Menunggu Retur,Sudah Diretur',
            'tanggal_retur' => 'required_if:status_retur,Sudah Diretur|nullable|date',
            'catatan_retur' => 'nullable|string'
        ]);

        $history->status_retur = $request->status_retur;
        
        if ($request->status_retur === 'Sudah Diretur') {
            $history->tanggal_retur = $request->tanggal_retur;
            $history->catatan_retur = $request->catatan_retur;
        } else {
            $history->tanggal_retur = null;
            $history->catatan_retur = null;
        }

        $history->save();

        return redirect()->route('po.show', $po->id)->with('success', 'Status retur berhasil diperbarui.');
    }
}
