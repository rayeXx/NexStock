<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\BatchInbound;
use App\Models\Supplier;
use App\Models\Rack;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\OutboundDetail;
use App\Models\Outbound;
use App\Models\DamagedReport;
use App\Models\StockOpnameDetail;
use App\Models\PoReceivingHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $thirtyDaysAgo = Carbon::today()->subDays(30);

        // 1. KPI: Total Nilai Kapitalisasi Seluruh Stok Gudang
        $totalAsetValue = 0;
        $products = Product::with('batchInbounds')->get();
        foreach ($products as $product) {
            $totalStok = $product->total_stok;
            $hargaBeli = (double)$product->harga_beli; // Decrypts automatically
            $totalAsetValue += $totalStok * $hargaBeli;
        }

        // 2. KPI: Persentase Ranking Efisiensi Pengiriman Pemasok (Supplier Performance)
        // Formula: (Total Qty Diterima / Total Qty Dipesan) * 100
        $suppliers = Supplier::with(['purchaseOrders.details'])->get();
        $supplierRankings = [];
        foreach ($suppliers as $supplier) {
            $totalPesan = 0;
            $totalTerima = 0;

            foreach ($supplier->purchaseOrders as $po) {
                // Only count POs that have been processed/sent (Approved, Completed, Rejected)
                if ($po->status !== 'Draft') {
                    foreach ($po->details as $detail) {
                        $totalPesan += $detail->qty_pesan;
                        $totalTerima += $detail->qty_diterima;
                    }
                }
            }

            $percentage = $totalPesan > 0 ? round(($totalTerima / $totalPesan) * 100, 1) : 0;

            $supplierRankings[] = [
                'nama' => $supplier->nama_supplier,
                'kontak' => $supplier->kontak,
                'total_po' => $supplier->purchaseOrders->count(),
                'persentase' => $percentage,
            ];
        }
        // Sort supplier rankings by percentage DESC
        usort($supplierRankings, function($a, $b) {
            return $b['persentase'] <=> $a['persentase'];
        });


        // 3. Smart Feature: Restock Forecast (Analisis Transaksi 30 Hari Terakhir)
        // ROP (Reorder Point) = (Rata-rata Keluar Harian * Lead Time 3 Hari) + Stok Minimum
        $restockForecasts = [];
        foreach ($products as $product) {
            // Outbound last 30 days
            $outboundQty = OutboundDetail::where('produk_id', $product->kode_produk)
                ->whereHas('outbound', function($q) use ($thirtyDaysAgo) {
                    $q->where('tanggal_keluar', '>=', $thirtyDaysAgo);
                })->sum('qty_keluar');

            $dailyDemand = $outboundQty / 30;
            $leadTime = 3; // 3 Days assumption
            $rop = round(($dailyDemand * $leadTime) + $product->stok_minimum);
            $currentStock = $product->total_stok;

            $status = 'Aman';
            if ($currentStock <= $product->stok_minimum) {
                $status = 'Kritis (Di bawah Stok Minimum)';
            } elseif ($currentStock <= $rop) {
                $status = 'Peringatan (Mendekati Reorder Point)';
            }

            $estDays = $dailyDemand > 0 ? round($currentStock / $dailyDemand) : 999;

            $restockForecasts[] = [
                'kode' => $product->kode_produk,
                'nama' => $product->nama_produk,
                'stok_sekarang' => $currentStock,
                'stok_min' => $product->stok_minimum,
                'daily_demand' => round($dailyDemand, 2),
                'rop' => $rop,
                'est_days_remaining' => $estDays == 999 ? 'N/A' : $estDays . ' Hari',
                'status' => $status,
            ];
        }


        // 4. Smart Feature: Expired Risk Detection
        // Find batches with remaining stock that expire soon
        $expiredBatches = BatchInbound::with('product')
            ->where('stok_sisa_batch', '>', 0)
            ->get()
            ->map(function($batch) use ($today) {
                $daysToExpiry = $today->diffInDays($batch->expired_date, false);

                $status = 'Aman';
                if ($daysToExpiry <= 0) {
                    $status = 'Kedaluwarsa';
                } elseif ($daysToExpiry <= 30) {
                    $status = 'Risiko Tinggi (< 30 Hari)';
                } elseif ($daysToExpiry <= 90) {
                    $status = 'Risiko Sedang (< 90 Hari)';
                }

                return [
                    'batch_number' => $batch->batch_number,
                    'nama_produk' => $batch->product->nama_produk,
                    'rak' => $batch->rak_id,
                    'stok_sisa' => $batch->stok_sisa_batch,
                    'expired_date' => $batch->expired_date->format('d M Y'),
                    'days_remaining' => $daysToExpiry,
                    'status' => $status,
                ];
            })
            ->filter(function($item) {
                return $item['status'] !== 'Aman';
            })
            ->sortBy('days_remaining')
            ->values();


        // 5. Smart Feature: Error/Discrepancy Detection
        $errorsDetected = [];

        // Check if any rack capacity exceeded
        $overloadedRacks = Rack::whereRaw('kapasitas_terpakai > kapasitas_maksimum_volume')->get();
        foreach ($overloadedRacks as $rack) {
            $errorsDetected[] = "Kelebihan Kapasitas: Rak {$rack->kode_rak} melebihi batas maksimum volume ({$rack->kapasitas_terpakai}/{$rack->kapasitas_maksimum_volume}).";
        }

        // Check for any negative stock batches
        $negativeBatches = BatchInbound::where('stok_sisa_batch', '<', 0)->get();
        foreach ($negativeBatches as $batch) {
            $errorsDetected[] = "Anomali Stok: Batch {$batch->batch_number} memiliki sisa stok negatif ({$batch->stok_sisa_batch}).";
        }

        // Check for any over-received PO details
        $overReceivedPos = PurchaseOrderDetail::whereRaw('qty_diterima > qty_pesan')->with('purchaseOrder')->get();
        foreach ($overReceivedPos as $detail) {
            $errorsDetected[] = "Anomali Transaksi: PO {$detail->purchaseOrder->po_number} menerima barang melebihi kuantitas yang dipesan ({$detail->qty_diterima}/{$detail->qty_pesan} unit).";
        }

        // Check for recent stock opname discrepancies (negative difference, i.e., missing stock)
        $recentOpnames = StockOpnameDetail::where('selisih', '<', 0)
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->get();
        foreach ($recentOpnames as $detail) {
            $errorsDetected[] = "Audit Selisih: Ditemukan selisih kurang sebanyak " . abs($detail->selisih) . " unit pada produk {$detail->product->nama_produk} (Batch {$detail->batch_number}) dalam audit minggu ini.";
        }

        // Overall stats counters
        $stats = [
            'total_produk' => Product::count(),
            'total_stok' => BatchInbound::sum('stok_sisa_batch'),
            'total_rak' => Rack::count(),
            'total_supplier' => Supplier::count(),
            'karantina_count' => DamagedReport::count(),
            'barang_hampir_habis' => 0,
            'inbound_hari_ini' => BatchInbound::whereDate('created_at', $today)->sum('stok_awal_batch'),
            'outbound_hari_ini' => OutboundDetail::whereHas('outbound', fn($q) => $q->whereDate('tanggal_keluar', $today))->sum('qty_keluar'),
        ];

        // Count products near minimum stock
        foreach ($products as $p) {
            if ($p->total_stok <= $p->stok_minimum * 1.5 && $p->total_stok > 0) {
                $stats['barang_hampir_habis']++;
            }
        }

        // PO stats (for Owner dashboard)
        $poStats = [
            'ordered' => PurchaseOrder::where('status', 'Ordered')->count(),
            'partial' => PurchaseOrder::where('status', 'Partially Received')->count(),
            'completed' => PurchaseOrder::where('status', 'Completed')->count(),
            'damaged_today' => PoReceivingHistory::where('qty_rusak', '>', 0)->whereDate('received_at', Carbon::today())->sum('qty_rusak'),
            'damaged_week' => PoReceivingHistory::where('qty_rusak', '>', 0)->where('received_at', '>=', Carbon::today()->startOfWeek())->sum('qty_rusak'),
            'damaged_month' => PoReceivingHistory::where('qty_rusak', '>', 0)->whereMonth('received_at', Carbon::now()->month)->whereYear('received_at', Carbon::now()->year)->sum('qty_rusak'),
            'menunggu_retur' => PoReceivingHistory::where('status_retur', 'Menunggu Retur')->count(),
            'sudah_diretur' => PoReceivingHistory::where('status_retur', 'Sudah Diretur')->count(),
        ];
        // 6. Chart Data: Sales/Outbound (for Owner only)
        $userRole = auth()->user()->role;
        $chartMonthLabels = [];
        $chartMonthData = [];
        $chartWeekLabels = [];
        $chartWeekData = [];
        $chartTodayLabels = [];
        $chartTodayData = [];
        $chartThirtyLabels = [];
        $chartThirtyData = [];
        $chartThreeMonthsLabels = [];
        $chartThreeMonthsData = [];
        $chartSixMonthsLabels = [];
        $chartSixMonthsData = [];
        $chartYearLabels = [];
        $chartYearData = [];
        $topSellingProducts = [];
        $slowMovingProducts = [];
        $aiInsights = [];

        if ($userRole === 'owner') {
            $currentMonth = Carbon::now()->month;
            $currentYear = Carbon::now()->year;

            // Monthly (bulan ini) data
            $salesMonthly = DB::table('t_outbounds')
                ->join('t_outbound_details', 't_outbounds.id', '=', 't_outbound_details.outbound_id')
                ->select(DB::raw('DATE(t_outbounds.tanggal_keluar) as date'), DB::raw('SUM(t_outbound_details.qty_keluar) as total_qty'))
                ->whereMonth('t_outbounds.tanggal_keluar', $currentMonth)
                ->whereYear('t_outbounds.tanggal_keluar', $currentYear)
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            $chartMonthLabels = $salesMonthly->map(fn($item) => Carbon::parse($item->date)->format('d M'))->toArray();
            $chartMonthData = $salesMonthly->pluck('total_qty')->map(fn($v) => (int)$v)->toArray();

            // Weekly (7 hari terakhir) data
            $weekStart = Carbon::now()->subDays(6)->startOfDay();
            $salesWeekly = DB::table('t_outbounds')
                ->join('t_outbound_details', 't_outbounds.id', '=', 't_outbound_details.outbound_id')
                ->select(DB::raw('DATE(t_outbounds.tanggal_keluar) as date'), DB::raw('SUM(t_outbound_details.qty_keluar) as total_qty'))
                ->where('t_outbounds.tanggal_keluar', '>=', $weekStart)
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            $weekGroups = [];
            for ($i = 6; $i >= 0; $i--) {
                $dateStr = Carbon::now()->subDays($i)->format('Y-m-d');
                $labelStr = Carbon::now()->subDays($i)->format('D, d M');
                $weekGroups[$labelStr] = 0;
                $found = $salesWeekly->firstWhere('date', $dateStr);
                if ($found) {
                    $weekGroups[$labelStr] = (int)$found->total_qty;
                }
            }
            $chartWeekLabels = array_keys($weekGroups);
            $chartWeekData = array_values($weekGroups);

            $hourSelect = DB::getDriverName() === 'mysql'
                ? 'DATE_FORMAT(t_outbounds.tanggal_keluar, "%H") as hour'
                : 'STRFTIME(\'%H\', t_outbounds.tanggal_keluar) as hour';

            // Today (hari ini) data — per hour
            $salesToday = DB::table('t_outbounds')
                ->join('t_outbound_details', 't_outbounds.id', '=', 't_outbound_details.outbound_id')
                ->select(DB::raw($hourSelect), DB::raw('SUM(t_outbound_details.qty_keluar) as total_qty'))
                ->whereDate('t_outbounds.tanggal_keluar', Carbon::today())
                ->groupBy('hour')
                ->orderBy('hour')
                ->get();

            $todayGroups = [];
            for ($h = 8; $h <= 18; $h++) {
                $todayGroups[sprintf('%02d:00', $h)] = 0;
            }
            foreach ($salesToday as $item) {
                $hour = $item->hour . ':00';
                $todayGroups[$hour] = (int)$item->total_qty;
            }
            $chartTodayLabels = array_keys($todayGroups);
            $chartTodayData = array_values($todayGroups);

            // 30 hari terakhir data (1 Bulan)
            $thirtyDaysStart = Carbon::now()->subDays(29)->startOfDay();
            $salesThirty = DB::table('t_outbounds')
                ->join('t_outbound_details', 't_outbounds.id', '=', 't_outbound_details.outbound_id')
                ->select(DB::raw('DATE(t_outbounds.tanggal_keluar) as date'), DB::raw('SUM(t_outbound_details.qty_keluar) as total_qty'))
                ->where('t_outbounds.tanggal_keluar', '>=', $thirtyDaysStart)
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            $monthGroups = [];
            for ($i = 29; $i >= 0; $i--) {
                $dateStr = Carbon::now()->subDays($i)->format('Y-m-d');
                $labelStr = Carbon::now()->subDays($i)->format('d M');
                $monthGroups[$labelStr] = 0;
                $found = $salesThirty->firstWhere('date', $dateStr);
                if ($found) {
                    $monthGroups[$labelStr] = (int)$found->total_qty;
                }
            }
            $chartThirtyLabels = array_keys($monthGroups);
            $chartThirtyData = array_values($monthGroups);

            // 3 Bulan (90 hari terakhir, grouped by week)
            $threeMonthsStart = Carbon::now()->subDays(89)->startOfDay();
            $sales3M = DB::table('t_outbounds')
                ->join('t_outbound_details', 't_outbounds.id', '=', 't_outbound_details.outbound_id')
                ->select('t_outbounds.tanggal_keluar', 't_outbound_details.qty_keluar')
                ->where('t_outbounds.tanggal_keluar', '>=', $threeMonthsStart)
                ->get();
            
            $groups3M = [];
            for ($i = 12; $i >= 0; $i--) {
                $wStart = Carbon::now()->subWeeks($i)->startOfWeek();
                $label = 'W-' . $wStart->format('d M');
                $groups3M[$label] = 0;
            }
            foreach ($sales3M as $item) {
                $itemWStart = Carbon::parse($item->tanggal_keluar)->startOfWeek();
                $label = 'W-' . $itemWStart->format('d M');
                if (isset($groups3M[$label])) {
                    $groups3M[$label] += $item->qty_keluar;
                }
            }
            $chartThreeMonthsLabels = array_keys($groups3M);
            $chartThreeMonthsData = array_values($groups3M);

            // 6 Bulan (180 hari terakhir, grouped by month)
            $sixMonthsStart = Carbon::now()->subMonths(5)->startOfMonth();
            $sales6M = DB::table('t_outbounds')
                ->join('t_outbound_details', 't_outbounds.id', '=', 't_outbound_details.outbound_id')
                ->select('t_outbounds.tanggal_keluar', 't_outbound_details.qty_keluar')
                ->where('t_outbounds.tanggal_keluar', '>=', $sixMonthsStart)
                ->get();
            
            $groups6M = [];
            $indonesianMonths = [
                'January' => 'Jan', 'February' => 'Feb', 'March' => 'Mar', 'April' => 'Apr', 'May' => 'Mei', 'June' => 'Jun',
                'July' => 'Jul', 'August' => 'Agu', 'September' => 'Sep', 'October' => 'Okt', 'November' => 'Nov', 'December' => 'Des'
            ];
            for ($i = 5; $i >= 0; $i--) {
                $mStart = Carbon::now()->subMonths($i)->startOfMonth();
                $engMonth = $mStart->format('F');
                $indoMonth = $indonesianMonths[$engMonth] ?? $engMonth;
                $label = $indoMonth . ' ' . $mStart->format('Y');
                $groups6M[$label] = 0;
            }
            foreach ($sales6M as $item) {
                $itemMonth = Carbon::parse($item->tanggal_keluar)->startOfMonth();
                $engMonth = $itemMonth->format('F');
                $indoMonth = $indonesianMonths[$engMonth] ?? $engMonth;
                $label = $indoMonth . ' ' . $itemMonth->format('Y');
                if (isset($groups6M[$label])) {
                    $groups6M[$label] += $item->qty_keluar;
                }
            }
            $chartSixMonthsLabels = array_keys($groups6M);
            $chartSixMonthsData = array_values($groups6M);

            // 1 Tahun (365 hari terakhir, grouped by month)
            $oneYearStart = Carbon::now()->subMonths(11)->startOfMonth();
            $sales1Y = DB::table('t_outbounds')
                ->join('t_outbound_details', 't_outbounds.id', '=', 't_outbound_details.outbound_id')
                ->select('t_outbounds.tanggal_keluar', 't_outbound_details.qty_keluar')
                ->where('t_outbounds.tanggal_keluar', '>=', $oneYearStart)
                ->get();
            
            $groups1Y = [];
            for ($i = 11; $i >= 0; $i--) {
                $mStart = Carbon::now()->subMonths($i)->startOfMonth();
                $engMonth = $mStart->format('F');
                $indoMonth = $indonesianMonths[$engMonth] ?? $engMonth;
                $label = $indoMonth . ' ' . $mStart->format('Y');
                $groups1Y[$label] = 0;
            }
            foreach ($sales1Y as $item) {
                $itemMonth = Carbon::parse($item->tanggal_keluar)->startOfMonth();
                $engMonth = $itemMonth->format('F');
                $indoMonth = $indonesianMonths[$engMonth] ?? $engMonth;
                $label = $indoMonth . ' ' . $itemMonth->format('Y');
                if (isset($groups1Y[$label])) {
                    $groups1Y[$label] += $item->qty_keluar;
                }
            }
            $chartYearLabels = array_keys($groups1Y);
            $chartYearData = array_values($groups1Y);

            // Top 5 Best Selling Products (30 hari terakhir)
            $topSelling = DB::table('t_outbound_details')
                ->join('t_outbounds', 't_outbounds.id', '=', 't_outbound_details.outbound_id')
                ->join('m_products', 'm_products.kode_produk', '=', 't_outbound_details.produk_id')
                ->select('m_products.kode_produk', 'm_products.nama_produk', DB::raw('SUM(t_outbound_details.qty_keluar) as total_sold'))
                ->where('t_outbounds.tanggal_keluar', '>=', Carbon::now()->subDays(30))
                ->groupBy('m_products.kode_produk', 'm_products.nama_produk')
                ->orderByDesc('total_sold')
                ->limit(5)
                ->get();

            // Top 5 Slow Moving Products (30 hari terakhir, stok masih ada)
            // Filter stok > 0 inside the subquery so SQLite doesn't choke on HAVING non-aggregate
            $topSlowMoving = DB::table('m_products')
                ->leftJoin(DB::raw('(SELECT produk_id, SUM(qty_keluar) as total_sold FROM t_outbound_details JOIN t_outbounds ON t_outbounds.id = t_outbound_details.outbound_id WHERE t_outbounds.tanggal_keluar >= \'' . Carbon::now()->subDays(30)->format('Y-m-d') . '\' GROUP BY produk_id) as sales'), 'm_products.kode_produk', '=', 'sales.produk_id')
                ->join(DB::raw('(SELECT produk_id, SUM(stok_sisa_batch) as stok FROM t_batch_inbounds GROUP BY produk_id HAVING SUM(stok_sisa_batch) > 0) as stock'), 'm_products.kode_produk', '=', 'stock.produk_id')
                ->select('m_products.kode_produk', 'm_products.nama_produk', DB::raw('COALESCE(sales.total_sold, 0) as total_sold'), DB::raw('stock.stok as stok_sisa'))
                ->orderBy(DB::raw('COALESCE(sales.total_sold, 0)'))
                ->limit(5)
                ->get();

            // Find max for percentage bars
            $maxTopSell = $topSelling->max('total_sold') ?: 1;
            $maxSlowSell = $topSlowMoving->max('total_sold') ?: 1;

            foreach ($topSelling as $item) {
                $topSellingProducts[] = [
                    'kode' => $item->kode_produk,
                    'nama' => $item->nama_produk,
                    'total_sold' => (int)$item->total_sold,
                    'percentage' => round(($item->total_sold / $maxTopSell) * 100),
                ];
            }

            // No fallback — empty array will trigger empty-state in the view

            foreach ($topSlowMoving as $item) {
                $slowMovingProducts[] = [
                    'kode' => $item->kode_produk,
                    'nama' => $item->nama_produk,
                    'total_sold' => (int)$item->total_sold,
                    'stok_sisa' => (int)$item->stok_sisa,
                    'percentage' => $maxSlowSell > 0 ? round(($item->total_sold / $maxSlowSell) * 100) : 0,
                ];
            }

            // No fallback — empty array will trigger empty-state in the view

            // AI Insights Generation
            $criticalRestockItems = array_filter($restockForecasts, fn($f) => $f['status'] === 'Kritis (Di bawah Stok Minimum)');
            $warningRestockItems = array_filter($restockForecasts, fn($f) => $f['status'] === 'Peringatan (Mendekati Reorder Point)');

            if (!empty($criticalRestockItems)) {
                $names = implode(', ', array_map(fn($f) => $f['nama'], array_slice($criticalRestockItems, 0, 2)));
                $aiInsights[] = ['type' => 'danger', 'icon' => 'alert', 'title' => 'Restock Segera Diperlukan', 'message' => "Produk {$names} berada di bawah stok minimum. Segera buat Purchase Order untuk menghindari stockout."];
            }

            if (!empty($topSellingProducts)) {
                $topName = $topSellingProducts[0]['nama'];
                $topSold = $topSellingProducts[0]['total_sold'];
                $aiInsights[] = ['type' => 'success', 'icon' => 'trending', 'title' => 'Produk dengan Penjualan Tertinggi', 'message' => "{$topName} menjadi produk terlaris dengan {$topSold} unit terjual dalam 30 hari terakhir. Pertimbangkan untuk meningkatkan stok produk ini."];
            }

            if (!empty($slowMovingProducts)) {
                $slowName = $slowMovingProducts[0]['nama'];
                $slowStok = $slowMovingProducts[0]['stok_sisa'];
                $aiInsights[] = ['type' => 'warning', 'icon' => 'slow', 'title' => 'Produk Slow Moving Terdeteksi', 'message' => "{$slowName} memiliki {$slowStok} unit stok tersisa dengan penjualan sangat rendah. Pertimbangkan promosi atau pengurangan pembelian produk ini."];
            }

            if (!empty($expiredBatches) && count($expiredBatches) > 0) {
                $expiredCount = count($expiredBatches);
                $aiInsights[] = ['type' => 'danger', 'icon' => 'expired', 'title' => 'Risiko Kedaluwarsa Terdeteksi', 'message' => "Terdapat {$expiredCount} batch produk yang mendekati/melebihi tanggal kedaluwarsa. Prioritaskan pengeluaran barang berdasarkan metode FEFO."];
            }

            if (!empty($warningRestockItems) && empty($criticalRestockItems)) {
                $warnNames = implode(', ', array_map(fn($f) => $f['nama'], array_slice($warningRestockItems, 0, 2)));
                $aiInsights[] = ['type' => 'warning', 'icon' => 'alert', 'title' => 'Perhatian: Stok Mendekati Reorder Point', 'message' => "Produk {$warnNames} mendekati batas reorder point. Rencanakan pembelian dalam 1-2 minggu ke depan."];
            }

            if (empty($aiInsights)) {
                $aiInsights[] = ['type' => 'success', 'icon' => 'check', 'title' => 'Semua Indikator Normal', 'message' => 'Tidak ada anomali terdeteksi. Stok aman, tidak ada kedaluwarsa mendesak, dan rantai pasokan berjalan lancar.'];
            }
        }

        return view('dashboard', compact(
            'totalAsetValue',
            'supplierRankings',
            'restockForecasts',
            'expiredBatches',
            'errorsDetected',
            'stats',
            'poStats',
            'chartMonthLabels',
            'chartMonthData',
            'chartWeekLabels',
            'chartWeekData',
            'chartTodayLabels',
            'chartTodayData',
            'chartThirtyLabels',
            'chartThirtyData',
            'chartThreeMonthsLabels',
            'chartThreeMonthsData',
            'chartSixMonthsLabels',
            'chartSixMonthsData',
            'chartYearLabels',
            'chartYearData',
            'topSellingProducts',
            'slowMovingProducts',
            'aiInsights',
            'userRole'
        ));
    }

    public function restockFilter(Request $request)
    {
        $filter = $request->input('filter', 'semua');
        $search = $request->input('search', '');
        $thirtyDaysAgo = Carbon::today()->subDays(30);

        $products = Product::with('batchInbounds')->get();
        $restockForecasts = [];

        foreach ($products as $product) {
            $outboundQty = OutboundDetail::where('produk_id', $product->kode_produk)
                ->whereHas('outbound', function($q) use ($thirtyDaysAgo) {
                    $q->where('tanggal_keluar', '>=', $thirtyDaysAgo);
                })->sum('qty_keluar');

            $dailyDemand = $outboundQty / 30;
            $leadTime = 3;
            $rop = round(($dailyDemand * $leadTime) + $product->stok_minimum);
            $currentStock = $product->total_stok;

            $status = 'Aman';
            if ($currentStock <= $product->stok_minimum) {
                $status = 'Kritis';
            } elseif ($currentStock <= $rop) {
                $status = 'Peringatan';
            }

            $estDays = $dailyDemand > 0 ? round($currentStock / $dailyDemand) : 999;

            $restockForecasts[] = [
                'kode' => $product->kode_produk,
                'nama' => $product->nama_produk,
                'stok_sekarang' => $currentStock,
                'stok_min' => $product->stok_minimum,
                'daily_demand' => round($dailyDemand, 2),
                'rop' => $rop,
                'est_days_remaining' => $estDays == 999 ? 'N/A' : $estDays . ' Hari',
                'status' => $status,
            ];
        }

        // Apply filter
        if ($filter === 'kritis') {
            $restockForecasts = array_values(array_filter($restockForecasts, fn($f) => $f['status'] === 'Kritis'));
        } elseif ($filter === 'aman') {
            $restockForecasts = array_values(array_filter($restockForecasts, fn($f) => $f['status'] === 'Aman'));
        }

        // Apply search
        if (!empty($search)) {
            $restockForecasts = array_values(array_filter($restockForecasts, function($f) use ($search) {
                return stripos($f['nama'], $search) !== false || stripos($f['kode'], $search) !== false;
            }));
        }

        return response()->json($restockForecasts);
    }
}
