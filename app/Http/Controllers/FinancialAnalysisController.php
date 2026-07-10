<?php

namespace App\Http\Controllers;

use App\Models\BatchInbound;
use App\Models\OutboundDetail;
use App\Models\DamagedReport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FinancialAnalysisController extends Controller
{
    public function index(Request $request)
    {
        abort_if(auth()->user()->role !== 'owner', 403, 'Akses Ditolak.');

        $period = $request->get('period', 'all');
        [$startDate, $endDate, $periodLabel] = $this->parsePeriod($period);

        // --- 1. Nilai Aset Inventaris (stok aktif × harga beli) ---
        $nilaiAset = 0;
        $batches = BatchInbound::with('product')->where('stok_sisa_batch', '>', 0)->get();
        foreach ($batches as $batch) {
            if ($batch->product) {
                $nilaiAset += $batch->stok_sisa_batch * (int)$batch->product->harga_beli;
            }
        }

        // --- 2. Total Pemasukan (subtotal outbound confirmed) ---
        $outboundQuery = OutboundDetail::whereHas('outbound', function ($q) use ($startDate, $endDate) {
            $q->where('status', 'Completed');
            if ($startDate) $q->whereDate('tanggal_keluar', '>=', $startDate);
            if ($endDate)   $q->whereDate('tanggal_keluar', '<=', $endDate);
        });
        $totalPemasukan = (int)$outboundQuery->sum('subtotal');

        // HPP barang keluar (qty × harga_beli) via Eloquent for decryption
        $outboundDetails = (clone $outboundQuery)->with('product')->get();
        $totalHpp = 0;
        foreach ($outboundDetails as $detail) {
            if ($detail->product) {
                $totalHpp += $detail->qty_keluar * (int)$detail->product->harga_beli;
            }
        }
        $grossMargin = $totalPemasukan - $totalHpp;
        $marginPct   = $totalPemasukan > 0 ? round(($grossMargin / $totalPemasukan) * 100, 1) : 0;

        // --- 3. Total Kerugian (damaged × harga_beli) ---
        $damagedQuery = DamagedReport::where('status', '!=', 'Rejected');
        if ($startDate) $damagedQuery->whereDate('created_at', '>=', $startDate);
        if ($endDate)   $damagedQuery->whereDate('created_at', '<=', $endDate);
        $damagedReports = $damagedQuery->with('product')->get();
        $totalKerugian = 0;
        foreach ($damagedReports as $report) {
            if ($report->product) {
                $totalKerugian += $report->qty_rusak * (int)$report->product->harga_beli;
            }
        }

        // --- 4. Top 5 Produk Penjualan Terbaik ---
        $topProducts = OutboundDetail::selectRaw('produk_id, SUM(qty_keluar) as total_qty, SUM(subtotal) as total_revenue')
            ->whereHas('outbound', function ($q) use ($startDate, $endDate) {
                $q->where('status', 'Completed');
                if ($startDate) $q->whereDate('tanggal_keluar', '>=', $startDate);
                if ($endDate)   $q->whereDate('tanggal_keluar', '<=', $endDate);
            })
            ->groupBy('produk_id')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->with('product')
            ->get();

        // --- 5. Top 5 Produk Kerugian Terbesar ---
        $topLossProducts = DamagedReport::where('status', '!=', 'Rejected')
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate,   fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->with('product')
            ->get()
            ->groupBy('produk_id')
            ->map(function ($group) {
                $product   = $group->first()->product;
                $totalQty  = $group->sum('qty_rusak');
                $totalLoss = $totalQty * (int)($product->harga_beli ?? 0);
                return ['product' => $product, 'total_qty' => $totalQty, 'total_loss' => $totalLoss];
            })
            ->sortByDesc('total_loss')
            ->take(5)
            ->values();

        // --- 6. Trend Chart (7 hari terakhir) ---
        $trendLabels    = [];
        $trendPemasukan = [];
        $trendKerugian  = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::today()->subDays($i);
            $trendLabels[] = $day->format('d M');

            $dayRevenue = OutboundDetail::whereHas('outbound', function ($q) use ($day) {
                $q->where('status', 'Completed')->whereDate('tanggal_keluar', $day);
            })->sum('subtotal');
            $trendPemasukan[] = (int)$dayRevenue;

            $dayLoss = 0;
            $dayDmg = DamagedReport::where('status', '!=', 'Rejected')
                ->whereDate('created_at', $day)->with('product')->get();
            foreach ($dayDmg as $r) {
                if ($r->product) $dayLoss += $r->qty_rusak * (int)$r->product->harga_beli;
            }
            $trendKerugian[] = $dayLoss;
        }

        // --- 7. Saran AI via OpenRouter ---
        $aiPayload = [
            'period'         => $periodLabel,
            'nilaiAset'      => $nilaiAset,
            'totalPemasukan' => $totalPemasukan,
            'totalKerugian'  => $totalKerugian,
            'grossMargin'    => $grossMargin,
            'marginPct'      => $marginPct,
            'totalHpp'       => $totalHpp,
            'jumlahBatch'    => $batches->count(),
            'jumlahRusak'    => $damagedReports->count(),
            'topProducts'    => $topProducts->map(fn($p) => [
                'nama' => $p->product?->nama_produk ?? '—',
                'qty'  => $p->total_qty,
                'rev'  => $p->total_revenue,
            ])->toArray(),
            'topLoss' => $topLossProducts->map(fn($p) => [
                'nama' => $p['product']?->nama_produk ?? '—',
                'qty'  => $p['total_qty'],
                'loss' => $p['total_loss'],
            ])->toArray(),
        ];
        $aiSuggestions = $this->getAiSuggestions($aiPayload);

        return view('financial.index', compact(
            'period', 'periodLabel',
            'nilaiAset', 'totalPemasukan', 'totalKerugian', 'grossMargin', 'marginPct',
            'topProducts', 'topLossProducts',
            'trendLabels', 'trendPemasukan', 'trendKerugian',
            'aiSuggestions'
        ));
    }

    public function aiRefresh(Request $request)
    {
        abort_if(auth()->user()->role !== 'owner', 403);
        $period = $request->get('period', 'all');
        [$startDate, $endDate, $periodLabel] = $this->parsePeriod($period);

        $batches = BatchInbound::with('product')->where('stok_sisa_batch', '>', 0)->get();
        $nilaiAset = $batches->sum(fn($b) => $b->stok_sisa_batch * (int)($b->product->harga_beli ?? 0));

        $outboundDetails = OutboundDetail::whereHas('outbound', function ($q) use ($startDate, $endDate) {
            $q->where('status', 'Completed');
            if ($startDate) $q->whereDate('tanggal_keluar', '>=', $startDate);
            if ($endDate)   $q->whereDate('tanggal_keluar', '<=', $endDate);
        })->with('product')->get();
        $totalPemasukan = (int)$outboundDetails->sum('subtotal');
        $totalHpp = $outboundDetails->sum(fn($d) => $d->qty_keluar * (int)($d->product->harga_beli ?? 0));
        $grossMargin = $totalPemasukan - $totalHpp;
        $marginPct   = $totalPemasukan > 0 ? round(($grossMargin / $totalPemasukan) * 100, 1) : 0;

        $damagedReports = DamagedReport::where('status', '!=', 'Rejected')
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate,   fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->with('product')->get();
        $totalKerugian = $damagedReports->sum(fn($r) => $r->qty_rusak * (int)($r->product->harga_beli ?? 0));

        $html = $this->getAiSuggestions([
            'period'         => $periodLabel,
            'nilaiAset'      => $nilaiAset,
            'totalPemasukan' => $totalPemasukan,
            'totalKerugian'  => $totalKerugian,
            'grossMargin'    => $grossMargin,
            'marginPct'      => $marginPct,
            'totalHpp'       => $totalHpp,
            'jumlahBatch'    => $batches->count(),
            'jumlahRusak'    => $damagedReports->count(),
            'topProducts'    => [],
            'topLoss'        => [],
        ]);

        return response()->json(['html' => $html]);
    }

    private function parsePeriod(string $period): array
    {
        $today = Carbon::today();
        return match ($period) {
            'today' => [$today, $today, 'Hari Ini'],
            'week'  => [$today->copy()->startOfWeek(), $today->copy()->endOfWeek(), 'Minggu Ini'],
            'month' => [$today->copy()->startOfMonth(), $today->copy()->endOfMonth(), 'Bulan Ini'],
            'year'  => [$today->copy()->startOfYear(), $today->copy()->endOfYear(), 'Tahun Ini'],
            default => [null, null, 'Semua Waktu'],
        };
    }

    private function getAiSuggestions(array $data): string
    {
        $apiKey = env('OPENROUTER_API_KEY');

        if ($apiKey) {
            $prompt = "Kamu adalah konsultan keuangan gudang bernama NexStock AI. Berikan analisis dan saran strategis berdasarkan data keuangan inventaris berikut dalam bahasa Indonesia yang profesional namun mudah dipahami.\n\n"
                . "**Data Keuangan Periode: {$data['period']}**\n"
                . "- Nilai Aset Inventaris (stok aktif): Rp " . number_format($data['nilaiAset'], 0, ',', '.') . "\n"
                . "- Total Pemasukan Penjualan: Rp " . number_format($data['totalPemasukan'], 0, ',', '.') . "\n"
                . "- Total HPP Barang Keluar: Rp " . number_format($data['totalHpp'], 0, ',', '.') . "\n"
                . "- Gross Margin: Rp " . number_format($data['grossMargin'], 0, ',', '.') . " ({$data['marginPct']}%)\n"
                . "- Total Kerugian Barang Rusak/Kadaluarsa: Rp " . number_format($data['totalKerugian'], 0, ',', '.') . "\n"
                . "- Jumlah Batch Aktif di Gudang: {$data['jumlahBatch']}\n"
                . "- Jumlah Laporan Barang Rusak: {$data['jumlahRusak']}\n"
                . "- Top Produk Penjualan: " . json_encode($data['topProducts'], JSON_UNESCAPED_UNICODE) . "\n"
                . "- Top Produk Kerugian: " . json_encode($data['topLoss'], JSON_UNESCAPED_UNICODE) . "\n\n"
                . "Berikan 3-5 poin saran strategis yang spesifik, actionable, dan relevan. "
                . "Format output menggunakan HTML sederhana: gunakan <ul><li> untuk daftar. "
                . "Setiap poin diawali dengan emoji yang sesuai (💡, ⚠️, ✅, 📦, 📉, 📈). "
                . "Jangan tambahkan judul atau heading, langsung saran saja.";

            try {
                $response = Http::timeout(25)
                    ->withHeaders([
                        'Authorization'  => 'Bearer ' . $apiKey,
                        'Content-Type'   => 'application/json',
                        'HTTP-Referer'   => config('app.url', 'http://localhost'),
                        'X-Title'        => 'NexStock Financial Analysis',
                    ])
                    ->post('https://openrouter.ai/api/v1/chat/completions', [
                        'model'    => 'google/gemma-4-26b-a4b-it:free',
                        'messages' => [
                            [
                                'role'    => 'system',
                                'content' => 'Kamu adalah konsultan keuangan gudang yang berpengalaman. Selalu jawab dalam bahasa Indonesia yang profesional dan mudah dipahami. Format jawaban dalam HTML menggunakan <ul><li>.',
                            ],
                            [
                                'role'    => 'user',
                                'content' => $prompt,
                            ],
                        ],
                        'max_tokens'  => 800,
                        'temperature' => 0.7,
                    ]);

                if ($response->successful()) {
                    $text = $response->json('choices.0.message.content') ?? '';
                    if ($text) {
                        return '<div class="ai-response">' . $text . '</div>';
                    }
                }

                Log::warning('OpenRouter API failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
            } catch (\Exception $e) {
                Log::warning('OpenRouter exception: ' . $e->getMessage());
            }
        }

        // Fallback: Rule-Based AI Analysis
        return $this->getRuleBasedSuggestions($data);
    }

    private function getRuleBasedSuggestions(array $data): string
    {
        $suggestions    = [];
        $nilaiAset      = $data['nilaiAset'];
        $totalPemasukan = $data['totalPemasukan'];
        $totalKerugian  = $data['totalKerugian'];
        $marginPct      = $data['marginPct'];
        $jumlahRusak    = $data['jumlahRusak'];
        $jumlahBatch    = $data['jumlahBatch'];

        // 1. Loss ratio
        if ($totalPemasukan > 0) {
            $lossRatio = round(($totalKerugian / $totalPemasukan) * 100, 1);
            if ($lossRatio > 10) {
                $suggestions[] = "⚠️ Rasio kerugian mencapai <strong>{$lossRatio}%</strong> dari total pemasukan — jauh di atas ambang aman 5%. Prioritaskan audit fisik gudang dan evaluasi sistem penyimpanan FEFO.";
            } elseif ($lossRatio > 5) {
                $suggestions[] = "⚠️ Rasio kerugian <strong>{$lossRatio}%</strong> perlu diwaspadai. Lakukan pengecekan rutin batch mendekati expired dan perbaiki rotasi stok.";
            } else {
                $suggestions[] = "✅ Rasio kerugian hanya <strong>{$lossRatio}%</strong> — dalam batas aman. Pertahankan sistem pengelolaan stok yang sudah berjalan baik.";
            }
        } elseif ($totalKerugian > 0) {
            $suggestions[] = "⚠️ Terdapat kerugian <strong>Rp " . number_format($totalKerugian, 0, ',', '.') . "</strong> namun belum ada pemasukan yang tercatat. Segera percepat siklus penjualan.";
        }

        // 2. Gross margin
        if ($totalPemasukan > 0) {
            if ($marginPct >= 25) {
                $suggestions[] = "📈 Gross margin <strong>{$marginPct}%</strong> — sangat sehat. Pertimbangkan reinvestasi margin ke perluasan kapasitas gudang atau diversifikasi produk.";
            } elseif ($marginPct >= 10) {
                $suggestions[] = "💡 Gross margin <strong>{$marginPct}%</strong> berada di level moderat. Evaluasi produk dengan margin rendah dan pertimbangkan penyesuaian harga jual.";
            } elseif ($marginPct >= 0) {
                $suggestions[] = "⚠️ Gross margin tipis <strong>{$marginPct}%</strong>. Tinjau harga beli dari supplier dan negosiasikan diskon volume untuk menekan HPP.";
            } else {
                $suggestions[] = "🚨 Gross margin <strong>negatif ({$marginPct}%)</strong>! Harga jual saat ini di bawah HPP. Segera evaluasi penetapan harga sebelum kerugian bertambah besar.";
            }
        }

        // 3. Asset turnover
        if ($nilaiAset > 0 && $totalPemasukan > 0) {
            $turnoverPct = round(($totalPemasukan / $nilaiAset) * 100, 1);
            if ($turnoverPct < 30) {
                $suggestions[] = "📦 Perputaran aset inventaris rendah (<strong>{$turnoverPct}%</strong>). Identifikasi produk dengan perputaran lambat dan pertimbangkan promosi untuk mempercepat penjualan.";
            } elseif ($turnoverPct >= 70) {
                $suggestions[] = "✅ Perputaran inventaris sangat baik (<strong>{$turnoverPct}%</strong>). Pastikan stok selalu tersedia agar tidak kehabisan saat permintaan tinggi.";
            }
        }

        // 4. Damaged count
        if ($jumlahRusak >= 10) {
            $suggestions[] = "📉 Terdapat <strong>{$jumlahRusak} laporan kerusakan</strong>. Jadwalkan pelatihan staf gudang mengenai penanganan dan penyimpanan barang yang benar.";
        } elseif ($jumlahRusak === 0) {
            $suggestions[] = "✅ Tidak ada laporan kerusakan pada periode ini — kinerja pengelolaan gudang sangat baik!";
        }

        // 5. Asset overview
        if ($nilaiAset > 0 && $jumlahBatch > 0) {
            $avgBatch = round($nilaiAset / $jumlahBatch, 0);
            $suggestions[] = "💡 Aset <strong>Rp " . number_format($nilaiAset, 0, ',', '.') . "</strong> tersebar di <strong>{$jumlahBatch} batch aktif</strong> (rata-rata Rp " . number_format($avgBatch, 0, ',', '.') . "/batch). Pantau batch bernilai tinggi yang mendekati tanggal expired.";
        }

        if (empty($suggestions)) {
            $suggestions[] = "💡 Belum cukup data untuk analisis. Pastikan ada transaksi dan laporan yang tercatat pada periode yang dipilih.";
        }

        $items = implode('', array_map(
            fn($s) => "<li style='padding:0.5rem 0;border-bottom:1px solid rgba(255,255,255,0.05);'>{$s}</li>",
            $suggestions
        ));

        return "<ul style='padding-left:0;margin:0;list-style:none;'>{$items}</ul>"
             . "<p style='margin-top:0.75rem;font-size:0.72rem;color:#475569;'>🔧 Mode Analisis Lokal aktif.</p>";
    }
}
