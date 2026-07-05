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

    // Show form to create outbound (Step 1)
    public function create()
    {
        $products = Product::all()->filter(function ($product) {
            return $product->total_stok > 0;
        });

        return view('outbound.create', compact('products'));
    }

    // ── Step 1: Preview ─────────────────────────────────────────────────
    // Jalankan FEFO di memori (TANPA menulis ke DB).
    // Simpan picking slip ke session dan redirect ke halaman konfirmasi.
    public function preview(Request $request)
    {
        abort_if(auth()->user()->role === 'admin_gudang', 403, 'Akses Ditolak: Admin tidak diizinkan memproses barang keluar.');

        $request->validate([
            'tujuan'               => 'required|string',
            'items'                => 'required|array',
            'items.*.produk_id'    => 'required|exists:m_products,kode_produk',
            'items.*.qty_keluar'   => 'required|integer|min:1',
        ]);

        $itemsData = $request->items;

        // Validasi kecukupan stok total
        foreach ($itemsData as $item) {
            $product    = Product::findOrFail($item['produk_id']);
            $totalStok  = $product->total_stok;

            if ($item['qty_keluar'] > $totalStok) {
                return redirect()->back()->withInput()->with('error',
                    'Gagal: Sisa total stok produk ' . $product->nama_produk .
                    ' di sistem tidak mencukupi permintaan (Stok: ' . $totalStok .
                    ', Diminta: ' . $item['qty_keluar'] . ').'
                );
            }
        }

        // Jalankan FEFO di memori — hasilkan picking slip
        $pickingSlip = [];

        foreach ($itemsData as $item) {
            $product    = Product::findOrFail($item['produk_id']);
            $qtyNeeded  = (int) $item['qty_keluar'];

            // FEFO: batch dengan expired_date paling dekat diambil lebih dulu
            $batches = BatchInbound::where('produk_id', $product->kode_produk)
                ->where('stok_sisa_batch', '>', 0)
                ->orderBy('expired_date', 'asc')
                ->get();

            foreach ($batches as $batch) {
                if ($qtyNeeded <= 0) {
                    break;
                }

                $qtyToTake    = min($qtyNeeded, $batch->stok_sisa_batch);
                $pickingSlip[] = [
                    'produk_id'    => $product->kode_produk,
                    'produk_nama'  => $product->nama_produk,
                    'batch_number' => $batch->batch_number,
                    'expired_date' => $batch->expired_date,
                    'rak_id'       => $batch->rak_id,
                    'qty_keluar'   => $qtyToTake,
                ];

                $qtyNeeded -= $qtyToTake;
            }
        }

        // Simpan ke session untuk step 2
        session([
            'outbound_tujuan'       => $request->tujuan,
            'outbound_picking_slip' => $pickingSlip,
        ]);

        return redirect()->route('outbound.confirm.show');
    }

    // ── Step 2a: Tampilkan halaman konfirmasi (picking slip + input scan) ─
    public function showConfirm()
    {
        abort_if(auth()->user()->role === 'admin_gudang', 403, 'Akses Ditolak.');

        $pickingSlip = session('outbound_picking_slip');
        $tujuan      = session('outbound_tujuan');

        if (!$pickingSlip || !$tujuan) {
            return redirect()->route('outbound.create')
                ->with('error', 'Sesi habis atau tidak ada proses outbound aktif. Silakan ulangi dari awal.');
        }

        return view('outbound.confirm', compact('pickingSlip', 'tujuan'));
    }

    // ── Step 2b: Validasi scan & commit ke DB ────────────────────────────
    // Sistem membandingkan setiap input batch_scanned staf dengan batch_number
    // yang dialokasikan FEFO. Jika ada yang tidak cocok → tolak seluruh transaksi.
    public function confirm(Request $request)
    {
        abort_if(auth()->user()->role === 'admin_gudang', 403, 'Akses Ditolak.');

        $request->validate([
            'batch_scanned'   => 'required|array',
            'batch_scanned.*' => 'required|string|max:100',
        ]);

        $pickingSlip = session('outbound_picking_slip');
        $tujuan      = session('outbound_tujuan');

        if (!$pickingSlip || !$tujuan) {
            return redirect()->route('outbound.create')
                ->with('error', 'Sesi habis. Mohon ulangi proses barang keluar dari awal.');
        }

        // ── Validasi Batch Anti-Blind Picking ───────────────────────────
        // Setiap input staf harus cocok PERSIS dengan batch yang ditentukan FEFO.
        foreach ($pickingSlip as $index => $pick) {
            $scanned = strtoupper(trim($request->batch_scanned[$index] ?? ''));
            $expected = strtoupper(trim($pick['batch_number']));

            if ($scanned !== $expected) {
                return redirect()->back()->withInput()->with(
                    'error',
                    "❌ Validasi Gagal: Batch untuk produk <strong>{$pick['produk_nama']}</strong> tidak cocok! " .
                    "FEFO mengharuskan batch <code>{$pick['batch_number']}</code>, " .
                    "tetapi Anda memasukkan <code>" . htmlspecialchars($request->batch_scanned[$index] ?? '') . "</code>. " .
                    "Ambil barang dari batch yang benar sesuai instruksi picking slip."
                );
            }
        }

        // ── Semua Batch Valid → Commit ke Database ───────────────────────
        DB::beginTransaction();
        try {
            $outboundNumber = 'OUT-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));

            $outbound = Outbound::create([
                'outbound_number' => $outboundNumber,
                'tujuan'          => $tujuan,
                'tanggal_keluar'  => date('Y-m-d'),
            ]);

            $instructions = [];

            foreach ($pickingSlip as $index => $pick) {
                // Re-fetch batch dengan lock untuk cegah race condition
                $batch = BatchInbound::where('batch_number', $pick['batch_number'])->lockForUpdate()->first();

                if (!$batch || $batch->stok_sisa_batch < $pick['qty_keluar']) {
                    DB::rollBack();
                    session()->forget(['outbound_picking_slip', 'outbound_tujuan']);
                    return redirect()->route('outbound.create')->with('error',
                        "Stok batch {$pick['batch_number']} berubah selama proses konfirmasi. " .
                        "Mohon ulangi proses barang keluar."
                    );
                }

                // Potong stok batch
                $batch->stok_sisa_batch -= $pick['qty_keluar'];
                $batch->save();

                // Bebaskan kapasitas rak
                $rack = Rack::where('kode_rak', $pick['rak_id'])->first();
                if ($rack) {
                    $rack->kapasitas_terpakai = max(0, $rack->kapasitas_terpakai - $pick['qty_keluar']);
                    $rack->save();
                }

                // Simpan detail outbound beserta audit trail batch_scanned
                OutboundDetail::create([
                    'outbound_id'   => $outbound->id,
                    'produk_id'     => $pick['produk_id'],
                    'batch_number'  => $pick['batch_number'],
                    'qty_keluar'    => $pick['qty_keluar'],
                    'rak_id'        => $pick['rak_id'],
                    'batch_scanned' => $request->batch_scanned[$index],
                ]);

                $instructions[] = [
                    'produk' => $pick['produk_nama'],
                    'qty'    => $pick['qty_keluar'],
                    'rak'    => $pick['rak_id'],
                    'batch'  => $pick['batch_number'],
                ];
            }

            DB::commit();
            session()->forget(['outbound_picking_slip', 'outbound_tujuan']);

            return redirect()->route('outbound.index')
                ->with('success', '✅ Outbound berhasil! Batch fisik telah divalidasi dan stok dipotong sesuai FEFO.')
                ->with('instructions', $instructions);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }
}
