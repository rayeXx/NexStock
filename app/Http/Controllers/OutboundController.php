<?php

namespace App\Http\Controllers;

use App\Models\Outbound;
use App\Models\OutboundDetail;
use App\Models\BatchInbound;
use App\Models\Product;
use App\Models\Rack;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OutboundController extends Controller
{
    // List outbound transactions
    public function index(Request $request)
    {
        $outbounds = Outbound::with(['details.product', 'details.batchInbound'])->orderBy('tanggal_keluar', 'desc')->get();
        return view('outbound.index', compact('outbounds'));
    }

    // Show form to create outbound (Step 1)
    public function create()
    {
        $quarantinedBatches = \App\Models\DamagedReport::whereIn('status', ['Pending', 'Approved'])
            ->pluck('batch_number')
            ->toArray();

        $products = Product::all()->map(function ($product) use ($quarantinedBatches) {
            $product->available_stok = $product->batchInbounds()
                ->whereNotIn('batch_number', $quarantinedBatches)
                ->sum('stok_sisa_batch');
            return $product;
        })->filter(function ($product) {
            return $product->available_stok > 0;
        });

        // Load all active batches grouped by product, ordered by expiry date (FEFO), excluding quarantined ones
        $batches = BatchInbound::where('stok_sisa_batch', '>', 0)
            ->whereNotIn('batch_number', $quarantinedBatches)
            ->orderBy('expired_date', 'asc')
            ->get()
            ->groupBy('produk_id');

        return view('outbound.create', compact('products', 'batches'));
    }

    // ── Step 1: Store Outbound in Database as Pending ────────────────────
    public function store(Request $request)
    {
        abort_if(auth()->user()->role === 'staff_gudang', 403, 'Akses Ditolak: Staff tidak diizinkan membuat outbound request.');

        $request->validate([
            'tujuan'               => 'required|string',
            'items'                => 'required|array',
            'items.*.produk_id'    => 'required|exists:m_products,kode_produk',
            'items.*.batch_number' => 'required|exists:t_batch_inbounds,batch_number',
            'items.*.qty_keluar'   => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $outboundNumber = 'OUT-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));

            $outbound = Outbound::create([
                'outbound_number' => $outboundNumber,
                'tujuan'          => $request->tujuan,
                'tanggal_keluar'  => date('Y-m-d'),
                'status'          => 'Pending',
            ]);

            foreach ($request->items as $item) {
                $product = Product::where('kode_produk', $item['produk_id'])->firstOrFail();
                
                // Re-fetch batch dengan lock untuk cegah race condition
                $batch = BatchInbound::where('batch_number', $item['batch_number'])
                    ->where('produk_id', $item['produk_id'])
                    ->lockForUpdate()
                    ->first();

                if (!$batch) {
                    throw new \Exception("Batch {$item['batch_number']} tidak terdaftar untuk produk {$product->nama_produk}.");
                }

                // Check if batch is quarantined
                $isQuarantined = \App\Models\DamagedReport::where('batch_number', $item['batch_number'])
                    ->whereIn('status', ['Pending', 'Approved'])
                    ->exists();

                if ($isQuarantined) {
                    throw new \Exception("Batch {$item['batch_number']} sedang dikarantina dan tidak dapat digunakan untuk barang keluar.");
                }

                if ($item['qty_keluar'] > $batch->stok_sisa_batch) {
                    throw new \Exception("Stok pada batch {$batch->batch_number} tidak mencukupi. (Tersedia: {$batch->stok_sisa_batch}, Diminta: {$item['qty_keluar']})");
                }

                // Expiry Discount Calculation Logic
                $expiredDate = Carbon::parse($batch->expired_date);
                $monthsLeft = Carbon::now()->diffInMonths($expiredDate, false);

                $persenDiskon = 0;
                if ($monthsLeft <= 3) {
                    $persenDiskon = (int) $product->diskon_bawah_3_bulan;
                } elseif ($monthsLeft <= 6) {
                    $persenDiskon = (int) $product->diskon_bawah_6_bulan;
                } elseif ($monthsLeft <= 12) {
                    $persenDiskon = (int) $product->diskon_bawah_1_tahun;
                }

                $hargaJual = (int) $product->harga_jual;
                $hargaSatuanFinal = $hargaJual - ($hargaJual * $persenDiskon / 100);
                $subtotal = $hargaSatuanFinal * (int) $item['qty_keluar'];

                // Potong stok batch secara langsung saat Pending untuk reservasi
                $batch->stok_sisa_batch -= $item['qty_keluar'];
                $batch->save();

                // Bebaskan kapasitas rak
                $rack = Rack::where('kode_rak', $batch->rak_id)->first();
                if ($rack) {
                    $rack->kapasitas_terpakai = max(0, $rack->kapasitas_terpakai - $item['qty_keluar']);
                    $rack->save();
                }

                // Simpan detail outbound
                OutboundDetail::create([
                    'outbound_id'        => $outbound->id,
                    'produk_id'          => $item['produk_id'],
                    'batch_number'       => $item['batch_number'],
                    'qty_keluar'         => $item['qty_keluar'],
                    'rak_id'             => $batch->rak_id,
                    'harga_satuan_final' => $hargaSatuanFinal,
                    'persentase_diskon'  => $persenDiskon,
                    'subtotal'           => $subtotal,
                ]);
            }

            DB::commit();
            return redirect()->route('outbound.index')
                ->with('success', "✅ Rencana barang keluar {$outboundNumber} berhasil dibuat. Menunggu konfirmasi pengambilan oleh staf.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // ── Step 2a: Tampilkan halaman konfirmasi (picking slip + input scan) ─
    public function showConfirm($id)
    {
        abort_if(auth()->user()->role === 'admin_gudang', 403, 'Akses Ditolak.');

        $outbound = Outbound::with(['details.product', 'details.batchInbound'])->findOrFail($id);

        if ($outbound->status === 'Completed') {
            return redirect()->route('outbound.index')
                ->with('error', 'Barang keluar ini sudah selesai diproses.');
        }

        $pickingSlip = $outbound->details->map(function ($detail) {
            return [
                'produk_id'           => $detail->produk_id,
                'produk_nama'         => $detail->product->nama_produk,
                'batch_number'        => $detail->batch_number,
                'expired_date'        => $detail->batchInbound->expired_date ?? '',
                'rak_id'              => $detail->rak_id,
                'qty_keluar'          => $detail->qty_keluar,
                'uom'                 => $detail->product->uom,
                'harga_satuan_final'  => $detail->harga_satuan_final,
                'persentase_diskon'   => $detail->persentase_diskon,
                'subtotal'            => $detail->subtotal,
            ];
        })->toArray();

        $tujuan = $outbound->tujuan;

        return view('outbound.confirm', compact('pickingSlip', 'tujuan', 'outbound'));
    }

    // ── Step 2b: Validasi scan & ubah status ke Completed ────────────────
    public function confirm($id, Request $request)
    {
        abort_if(auth()->user()->role === 'admin_gudang', 403, 'Akses Ditolak.');

        $request->validate([
            'batch_scanned'   => 'required|array',
            'batch_scanned.*' => 'required|string|max:100',
            'bukti_foto'      => 'required|image|max:2048',
        ]);

        $outbound = Outbound::with('details.product')->findOrFail($id);

        if ($outbound->status === 'Completed') {
            return redirect()->route('outbound.index')
                ->with('error', 'Barang keluar ini sudah selesai diproses.');
        }

        // ── Validasi Batch Anti-Blind Picking ───────────────────────────
        foreach ($outbound->details as $index => $detail) {
            $scanned = strtoupper(trim($request->batch_scanned[$index] ?? ''));
            $expected = strtoupper(trim($detail->batch_number));

            if ($scanned !== $expected) {
                return redirect()->back()->withInput()->with(
                    'error',
                    "❌ Validasi Gagal: Batch untuk produk <strong>{$detail->product->nama_produk}</strong> tidak cocok! " .
                    "FEFO mengharuskan batch <code>{$detail->batch_number}</code>, " .
                    "tetapi Anda memasukkan <code>" . htmlspecialchars($request->batch_scanned[$index] ?? '') . "</code>. " .
                    "Ambil barang dari batch yang benar sesuai instruksi picking slip."
                );
            }
        }

        DB::beginTransaction();
        try {
            $instructions = [];

            // Handle file upload
            $filePath = null;
            if ($request->hasFile('bukti_foto')) {
                $filePath = $request->file('bukti_foto')->store('outbound_confirmations', 'public');
            }

            foreach ($outbound->details as $index => $detail) {
                $detail->batch_scanned = $request->batch_scanned[$index];
                $detail->save();

                $instructions[] = [
                    'produk' => $detail->product->nama_produk,
                    'qty'    => $detail->qty_keluar,
                    'rak'    => $detail->rak_id,
                    'batch'  => $detail->batch_number,
                ];
            }

            $outbound->status = 'Completed';
            $outbound->bukti_foto = $filePath;
            $outbound->save();

            DB::commit();

            return redirect()->route('outbound.index')
                ->with('success', '✅ Outbound berhasil! Batch fisik telah divalidasi dan barang keluar selesai diproses.')
                ->with('instructions', $instructions);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }
}
