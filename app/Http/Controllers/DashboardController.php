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
        abort_if(auth()->user()->role === 'staff_gudang', 403, 'Akses Ditolak: Staff tidak diizinkan mengakses dashboard.');

        \App\Http\Controllers\DamagedReportController::autoIsolateExpired();

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


        // 4. Smart Feature: Expired Risk Detection & Already Expired Detection
        // Find batches with remaining stock that expire soon or are already expired
        $activeBatches = BatchInbound::with('product')
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
                    'expired_date' => $batch->expired_date,
                    'days_remaining' => $daysToExpiry,
                    'status' => $status,
                ];
            });

        // Add batches that are already isolated but not yet confirmed by staff
        $pendingExpiredReports = DamagedReport::with(['product', 'batchInbound'])
            ->where('status', 'Expired Pending Check')
            ->get()
            ->map(function($report) use ($today) {
                $expDate = $report->batchInbound ? $report->batchInbound->expired_date : Carbon::yesterday();
                $daysToExpiry = $today->diffInDays($expDate, false);

                return [
                    'batch_number' => $report->batch_number,
                    'nama_produk' => $report->product->nama_produk,
                    'rak' => $report->rak_id,
                    'stok_sisa' => $report->qty_rusak, // original qty before isolation
                    'expired_date' => $expDate,
                    'days_remaining' => $daysToExpiry,
                    'status' => 'Kedaluwarsa', // since it's already isolated, it must be expired
                ];
            });

        // Merge active batches and pending expired checks
        $allExpiryStatusBatches = $activeBatches->concat($pendingExpiredReports)
            ->filter(function($item) {
                return $item['status'] !== 'Aman';
            })
            ->map(function($item) {
                // Format expired_date to string for output
                $item['expired_date'] = $item['expired_date'] instanceof Carbon ? $item['expired_date']->format('d M Y') : Carbon::parse($item['expired_date'])->format('d M Y');
                return $item;
            });

        // Filter 1: Hanya yang mendekati tanggal expired (days_remaining > 0)
        $expiredBatches = $allExpiryStatusBatches->filter(function($item) {
            return $item['days_remaining'] > 0;
        })->sortBy('days_remaining')->values();

        // Filter 2: Yang sudah expired (days_remaining <= 0)
        $alreadyExpiredBatches = $allExpiryStatusBatches->filter(function($item) {
            return $item['days_remaining'] <= 0;
        })->sortBy('days_remaining')->values();


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

        // Calculate total loss in Rupiah from damaged reports (excluding rejected ones)
        $totalLoss = 0;
        $damagedReports = DamagedReport::where('status', '!=', 'Rejected')->with('product')->get();
        foreach ($damagedReports as $report) {
            if ($report->product) {
                $totalLoss += $report->qty_rusak * (int)$report->product->harga_beli;
            }
        }

        // Calculate total revenue from Completed outbound shipments
        $totalRevenue = (int) OutboundDetail::whereHas('outbound', function ($q) {
            $q->where('status', 'Completed');
        })->sum('subtotal');

        // Overall stats counters
        $stats = [
            'total_produk' => Product::count(),
            'total_stok' => BatchInbound::sum('stok_sisa_batch'),
            'total_rak' => Rack::count(),
            'total_supplier' => Supplier::count(),
            'total_kerugian' => $totalLoss,
            'total_pemasukan' => $totalRevenue,
            'karantina_hari_ini' => DamagedReport::whereDate('created_at', $today)->count(),
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
            'damaged_today' => DamagedReport::where('status', '!=', 'Rejected')->whereDate('created_at', Carbon::today())->sum('qty_rusak'),
            'damaged_week' => DamagedReport::where('status', '!=', 'Rejected')->where('created_at', '>=', Carbon::now()->subDays(6)->startOfDay())->sum('qty_rusak'),
            'damaged_month' => DamagedReport::where('status', '!=', 'Rejected')->where('created_at', '>=', Carbon::now()->subDays(29)->startOfDay())->sum('qty_rusak'),
        ];
        // 6. Chart Data: Sales/Outbound (for Owner only)
        $userRole = auth()->user()->role;
        $chartMonthLabels = [];
        $chartMonthData = [];
        $chartWeekLabels = [];
        $chartWeekData = [];
        $chartWeekDates = [];
        $chartTodayLabels = [];
        $chartTodayData = [];
        $chartTodayDates = [];
        $chartThirtyLabels = [];
        $chartThirtyData = [];
        $chartThirtyDates = [];
        $chartThreeMonthsLabels = [];
        $chartThreeMonthsData = [];
        $chartThreeMonthsDates = [];
        $chartSixMonthsLabels = [];
        $chartSixMonthsData = [];
        $chartSixMonthsDates = [];
        $chartYearLabels = [];
        $chartYearData = [];
        $chartYearDates = [];
        $topSellingProducts = [];
        $slowMovingProducts = [];
        $chartDmgTodayLabels = [];
        $chartDmgTodayData = [];
        $chartDmgTodayDates = [];
        $chartDmgWeekLabels = [];
        $chartDmgWeekData = [];
        $chartDmgWeekDates = [];
        $chartDmgThirtyLabels = [];
        $chartDmgThirtyData = [];
        $chartDmgThirtyDates = [];
        $chartDmgThreeMonthsLabels = [];
        $chartDmgThreeMonthsData = [];
        $chartDmgThreeMonthsDates = [];
        $chartDmgSixMonthsLabels = [];
        $chartDmgSixMonthsData = [];
        $chartDmgSixMonthsDates = [];
        $chartDmgYearLabels = [];
        $chartDmgYearData = [];
        $chartDmgYearDates = [];
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
            $chartWeekDates = [];
            for ($i = 6; $i >= 0; $i--) {
                $dateStr = Carbon::now()->subDays($i)->format('Y-m-d');
                $labelStr = Carbon::now()->subDays($i)->format('D, d M');
                $weekGroups[$labelStr] = 0;
                $chartWeekDates[] = $dateStr;
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
            $chartTodayDates = [];
            for ($h = 8; $h <= 18; $h++) {
                $todayGroups[sprintf('%02d:00', $h)] = 0;
                $chartTodayDates[] = Carbon::today()->format('Y-m-d');
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
            $chartThirtyDates = [];
            for ($i = 29; $i >= 0; $i--) {
                $dateStr = Carbon::now()->subDays($i)->format('Y-m-d');
                $labelStr = Carbon::now()->subDays($i)->format('d M');
                $monthGroups[$labelStr] = 0;
                $chartThirtyDates[] = $dateStr;
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
            $chartThreeMonthsDates = [];
            for ($i = 12; $i >= 0; $i--) {
                $wStart = Carbon::now()->subWeeks($i)->startOfWeek();
                $label = 'W-' . $wStart->format('d M');
                $groups3M[$label] = 0;
                $chartThreeMonthsDates[] = $wStart->format('Y-m-d');
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
            $chartSixMonthsDates = [];
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
                $chartSixMonthsDates[] = $mStart->format('Y-m');
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
            $chartYearDates = [];
            for ($i = 11; $i >= 0; $i--) {
                $mStart = Carbon::now()->subMonths($i)->startOfMonth();
                $engMonth = $mStart->format('F');
                $indoMonth = $indonesianMonths[$engMonth] ?? $engMonth;
                $label = $indoMonth . ' ' . $mStart->format('Y');
                $groups1Y[$label] = 0;
                $chartYearDates[] = $mStart->format('Y-m');
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

            // Calculate DATEDIFF / days diff expression compatible with SQLite and MySQL
            $daysDiffRaw = DB::getDriverName() === 'mysql'
                ? 'DATEDIFF(t_outbounds.tanggal_keluar, t_batch_inbounds.created_at)'
                : 'julianday(t_outbounds.tanggal_keluar) - julianday(date(t_batch_inbounds.created_at))';

            // Query sales and average storage days in the last 30 days
            $salesSpeedQuery = DB::table('t_outbound_details')
                ->join('t_outbounds', 't_outbounds.id', '=', 't_outbound_details.outbound_id')
                ->join('t_batch_inbounds', function ($join) {
                    $join->on('t_batch_inbounds.batch_number', '=', 't_outbound_details.batch_number')
                         ->on('t_batch_inbounds.produk_id', '=', 't_outbound_details.produk_id');
                })
                ->join('m_products', 'm_products.kode_produk', '=', 't_outbound_details.produk_id')
                ->select(
                    'm_products.kode_produk',
                    'm_products.nama_produk',
                    DB::raw('SUM(t_outbound_details.qty_keluar) as total_sold'),
                    DB::raw('SUM(t_outbound_details.qty_keluar * (CASE WHEN (' . $daysDiffRaw . ') < 0 THEN 0 ELSE (' . $daysDiffRaw . ') END)) as total_weighted_days')
                )
                ->where('t_outbounds.status', 'Completed')
                ->where('t_outbounds.tanggal_keluar', '>=', Carbon::now()->subDays(30))
                ->groupBy('m_products.kode_produk', 'm_products.nama_produk')
                ->get();

            // Store product sales details mapping by kode_produk
            $salesMap = [];
            foreach ($salesSpeedQuery as $item) {
                $avgDays = $item->total_sold > 0 ? ($item->total_weighted_days / $item->total_sold) : 0;
                $salesMap[$item->kode_produk] = [
                    'kode' => $item->kode_produk,
                    'nama' => $item->nama_produk,
                    'total_sold' => (int)$item->total_sold,
                    'avg_days' => round($avgDays, 1),
                ];
            }

            // Query active stock and current age of unsold stock
            $currentDateRaw = DB::getDriverName() === 'mysql'
                ? 'DATEDIFF(NOW(), t_batch_inbounds.created_at)'
                : 'julianday(\'now\') - julianday(date(t_batch_inbounds.created_at))';

            $stockAgeQuery = DB::table('t_batch_inbounds')
                ->join('m_products', 'm_products.kode_produk', '=', 't_batch_inbounds.produk_id')
                ->select(
                    'm_products.kode_produk',
                    'm_products.nama_produk',
                    DB::raw('SUM(t_batch_inbounds.stok_sisa_batch) as stok_sisa'),
                    DB::raw('SUM(t_batch_inbounds.stok_sisa_batch * (CASE WHEN (' . $currentDateRaw . ') < 0 THEN 0 ELSE (' . $currentDateRaw . ') END)) as total_weighted_age_days')
                )
                ->where('t_batch_inbounds.stok_sisa_batch', '>', 0)
                ->groupBy('m_products.kode_produk', 'm_products.nama_produk')
                ->get();

            // Inflows: total received in the last 30 days per product (barang masuk)
            $inbound30Query = DB::table('t_batch_inbounds')
                ->select('produk_id', DB::raw('SUM(stok_awal_batch) as total_received'))
                ->where('created_at', '>=', Carbon::now()->subDays(30))
                ->groupBy('produk_id')
                ->pluck('total_received', 'produk_id')
                ->toArray();

            // Compile Fast Moving
            $fastMovingCandidates = [];
            foreach ($salesMap as $kode => $saleInfo) {
                // Velocity score = sold / (avg_days_to_sell + 1)
                $score = $saleInfo['total_sold'] / ($saleInfo['avg_days'] + 1);
                $totalReceived = $inbound30Query[$kode] ?? 0;

                $fastMovingCandidates[] = [
                    'kode' => $kode,
                    'nama' => $saleInfo['nama'],
                    'total_sold' => $saleInfo['total_sold'],
                    'total_received' => (int)$totalReceived,
                    'avg_days' => $saleInfo['avg_days'],
                    'score' => $score,
                ];
            }

            usort($fastMovingCandidates, fn($a, $b) => $b['score'] <=> $a['score']);
            $fastMovingCandidates = array_slice($fastMovingCandidates, 0, 5);

            $maxScore = !empty($fastMovingCandidates) ? max(array_column($fastMovingCandidates, 'score')) : 1;
            if ($maxScore <= 0) $maxScore = 1;

            $topSellingProducts = [];
            foreach ($fastMovingCandidates as $item) {
                $topSellingProducts[] = [
                    'kode' => $item['kode'],
                    'nama' => $item['nama'],
                    'total_sold' => $item['total_sold'],
                    'total_received' => $item['total_received'],
                    'avg_days' => $item['avg_days'],
                    'percentage' => round(($item['score'] / $maxScore) * 100),
                ];
            }

            // Compile Slow Moving
            $slowMovingCandidates = [];
            foreach ($stockAgeQuery as $stockInfo) {
                $kode = $stockInfo->kode_produk;
                $stokSisa = (int)$stockInfo->stok_sisa;

                $hasSales = isset($salesMap[$kode]);
                $totalSold = $hasSales ? $salesMap[$kode]['total_sold'] : 0;

                if ($hasSales) {
                    $ageDays = $salesMap[$kode]['avg_days'];
                    $statusLabel = "Rata-rata Terjual: {$ageDays} Hari";
                    $sortAge = $ageDays;
                } else {
                    $avgAge = $stockInfo->stok_sisa > 0 ? ($stockInfo->total_weighted_age_days / $stockInfo->stok_sisa) : 0;
                    $ageDays = round($avgAge, 1);
                    $statusLabel = "Belum Terjual > {$ageDays} Hari";
                    $sortAge = $ageDays + 100; // Rank unsold stock higher
                }

                $slowMovingCandidates[] = [
                    'kode' => $kode,
                    'nama' => $stockInfo->nama_produk,
                    'total_sold' => $totalSold,
                    'stok_sisa' => $stokSisa,
                    'age_days' => $ageDays,
                    'status_label' => $statusLabel,
                    'sort_age' => $sortAge,
                ];
            }

            usort($slowMovingCandidates, fn($a, $b) => $b['sort_age'] <=> $a['sort_age']);
            $slowMovingCandidates = array_slice($slowMovingCandidates, 0, 5);

            $maxAge = !empty($slowMovingCandidates) ? max(array_column($slowMovingCandidates, 'sort_age')) : 1;
            if ($maxAge <= 0) $maxAge = 1;

            $slowMovingProducts = [];
            foreach ($slowMovingCandidates as $item) {
                $slowMovingProducts[] = [
                    'kode' => $item['kode'],
                    'nama' => $item['nama'],
                    'total_sold' => $item['total_sold'],
                    'stok_sisa' => $item['stok_sisa'],
                    'age_days' => $item['age_days'],
                    'status_label' => $item['status_label'],
                    'percentage' => round(($item['sort_age'] / $maxAge) * 100),
                ];
            }

            // --- Damaged Items Chart Data ---
            // 1. Today (per hour)
            $dmgHourSelect = DB::getDriverName() === 'mysql'
                ? 'DATE_FORMAT(created_at, "%H") as hour'
                : 'STRFTIME(\'%H\', created_at) as hour';

            $damagedToday = DB::table('t_damaged_reports')
                ->select(DB::raw($dmgHourSelect), DB::raw('SUM(qty_rusak) as total_qty'))
                ->whereIn('status', ['Approved', 'Destruction Assigned'])
                ->whereDate('created_at', Carbon::today())
                ->groupBy('hour')
                ->orderBy('hour')
                ->get();

            $dmgTodayGroups = [];
            $chartDmgTodayDates = [];
            for ($h = 8; $h <= 18; $h++) {
                $dmgTodayGroups[sprintf('%02d:00', $h)] = 0;
                $chartDmgTodayDates[] = Carbon::today()->format('Y-m-d');
            }
            foreach ($damagedToday as $item) {
                $hour = $item->hour . ':00';
                if (isset($dmgTodayGroups[$hour])) {
                    $dmgTodayGroups[$hour] = (int)$item->total_qty;
                }
            }
            $chartDmgTodayLabels = array_keys($dmgTodayGroups);
            $chartDmgTodayData = array_values($dmgTodayGroups);

            // 2. Weekly (7 hari terakhir)
            $dmgWeekStart = Carbon::now()->subDays(6)->startOfDay();
            $damagedWeekly = DB::table('t_damaged_reports')
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(qty_rusak) as total_qty'))
                ->whereIn('status', ['Approved', 'Destruction Assigned'])
                ->where('created_at', '>=', $dmgWeekStart)
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            $dmgWeekGroups = [];
            $chartDmgWeekDates = [];
            for ($i = 6; $i >= 0; $i--) {
                $dateStr = Carbon::now()->subDays($i)->format('Y-m-d');
                $labelStr = Carbon::now()->subDays($i)->format('D, d M');
                $dmgWeekGroups[$labelStr] = 0;
                $chartDmgWeekDates[] = $dateStr;
                $found = $damagedWeekly->firstWhere('date', $dateStr);
                if ($found) {
                    $dmgWeekGroups[$labelStr] = (int)$found->total_qty;
                }
            }
            $chartDmgWeekLabels = array_keys($dmgWeekGroups);
            $chartDmgWeekData = array_values($dmgWeekGroups);

            // 3. 30 Hari Terakhir (1 Bulan)
            $dmgThirtyDaysStart = Carbon::now()->subDays(29)->startOfDay();
            $damagedThirty = DB::table('t_damaged_reports')
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(qty_rusak) as total_qty'))
                ->whereIn('status', ['Approved', 'Destruction Assigned'])
                ->where('created_at', '>=', $dmgThirtyDaysStart)
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            $dmgMonthGroups = [];
            $chartDmgThirtyDates = [];
            for ($i = 29; $i >= 0; $i--) {
                $dateStr = Carbon::now()->subDays($i)->format('Y-m-d');
                $labelStr = Carbon::now()->subDays($i)->format('d M');
                $dmgMonthGroups[$labelStr] = 0;
                $chartDmgThirtyDates[] = $dateStr;
                $found = $damagedThirty->firstWhere('date', $dateStr);
                if ($found) {
                    $dmgMonthGroups[$labelStr] = (int)$found->total_qty;
                }
            }
            $chartDmgThirtyLabels = array_keys($dmgMonthGroups);
            $chartDmgThirtyData = array_values($dmgMonthGroups);

            // 4. 3 Bulan (90 hari terakhir, grouped by week)
            $dmgThreeMonthsStart = Carbon::now()->subDays(89)->startOfDay();
            $damaged3M = DB::table('t_damaged_reports')
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(qty_rusak) as total_qty'))
                ->whereIn('status', ['Approved', 'Destruction Assigned'])
                ->where('created_at', '>=', $dmgThreeMonthsStart)
                ->groupBy('date')
                ->get();

            $dmgGroups3M = [];
            $chartDmgThreeMonthsDates = [];
            for ($i = 12; $i >= 0; $i--) {
                $wStart = Carbon::now()->subWeeks($i)->startOfWeek();
                $label = 'W-' . $wStart->format('d M');
                $dmgGroups3M[$label] = 0;
                $chartDmgThreeMonthsDates[] = $wStart->format('Y-m-d');
            }
            foreach ($damaged3M as $item) {
                $itemWStart = Carbon::parse($item->date)->startOfWeek();
                $label = 'W-' . $itemWStart->format('d M');
                if (isset($dmgGroups3M[$label])) {
                    $dmgGroups3M[$label] += (int)$item->total_qty;
                }
            }
            $chartDmgThreeMonthsLabels = array_keys($dmgGroups3M);
            $chartDmgThreeMonthsData = array_values($dmgGroups3M);

            // 5. 6 Bulan (180 hari terakhir, grouped by month)
            $dmgSixMonthsStart = Carbon::now()->subMonths(5)->startOfMonth();
            $damaged6M = DB::table('t_damaged_reports')
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(qty_rusak) as total_qty'))
                ->whereIn('status', ['Approved', 'Destruction Assigned'])
                ->where('created_at', '>=', $dmgSixMonthsStart)
                ->groupBy('date')
                ->get();

            $dmgGroups6M = [];
            $chartDmgSixMonthsDates = [];
            for ($i = 5; $i >= 0; $i--) {
                $mStart = Carbon::now()->subMonths($i)->startOfMonth();
                $engMonth = $mStart->format('F');
                $indoMonth = $indonesianMonths[$engMonth] ?? $engMonth;
                $label = $indoMonth . ' ' . $mStart->format('Y');
                $dmgGroups6M[$label] = 0;
                $chartDmgSixMonthsDates[] = $mStart->format('Y-m');
            }
            foreach ($damaged6M as $item) {
                $itemMonth = Carbon::parse($item->date)->startOfMonth();
                $engMonth = $itemMonth->format('F');
                $indoMonth = $indonesianMonths[$engMonth] ?? $engMonth;
                $label = $indoMonth . ' ' . $itemMonth->format('Y');
                if (isset($dmgGroups6M[$label])) {
                    $dmgGroups6M[$label] += (int)$item->total_qty;
                }
            }
            $chartDmgSixMonthsLabels = array_keys($dmgGroups6M);
            $chartDmgSixMonthsData = array_values($dmgGroups6M);

            // 6. 1 Tahun (365 hari terakhir, grouped by month)
            $dmgOneYearStart = Carbon::now()->subMonths(11)->startOfMonth();
            $damaged1Y = DB::table('t_damaged_reports')
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(qty_rusak) as total_qty'))
                ->whereIn('status', ['Approved', 'Destruction Assigned'])
                ->where('created_at', '>=', $dmgOneYearStart)
                ->groupBy('date')
                ->get();

            $dmgGroups1Y = [];
            $chartDmgYearDates = [];
            for ($i = 11; $i >= 0; $i--) {
                $mStart = Carbon::now()->subMonths($i)->startOfMonth();
                $engMonth = $mStart->format('F');
                $indoMonth = $indonesianMonths[$engMonth] ?? $engMonth;
                $label = $indoMonth . ' ' . $mStart->format('Y');
                $dmgGroups1Y[$label] = 0;
                $chartDmgYearDates[] = $mStart->format('Y-m');
            }
            foreach ($damaged1Y as $item) {
                $itemMonth = Carbon::parse($item->date)->startOfMonth();
                $engMonth = $itemMonth->format('F');
                $indoMonth = $indonesianMonths[$engMonth] ?? $engMonth;
                $label = $indoMonth . ' ' . $itemMonth->format('Y');
                if (isset($dmgGroups1Y[$label])) {
                    $dmgGroups1Y[$label] += (int)$item->total_qty;
                }
            }
            $chartDmgYearLabels = array_keys($dmgGroups1Y);
            $chartDmgYearData = array_values($dmgGroups1Y);

            // AI Insights Generation
            $criticalRestockItems = array_filter($restockForecasts, fn($f) => $f['status'] === 'Kritis');
            $warningRestockItems = array_filter($restockForecasts, fn($f) => $f['status'] === 'Peringatan');

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
            'alreadyExpiredBatches',
            'errorsDetected',
            'stats',
            'poStats',
            'chartMonthLabels',
            'chartMonthData',
            'chartWeekLabels',
            'chartWeekData',
            'chartWeekDates',
            'chartTodayLabels',
            'chartTodayData',
            'chartTodayDates',
            'chartThirtyLabels',
            'chartThirtyData',
            'chartThirtyDates',
            'chartThreeMonthsLabels',
            'chartThreeMonthsData',
            'chartThreeMonthsDates',
            'chartSixMonthsLabels',
            'chartSixMonthsData',
            'chartSixMonthsDates',
            'chartYearLabels',
            'chartYearData',
            'chartYearDates',
            'chartDmgTodayLabels',
            'chartDmgTodayData',
            'chartDmgTodayDates',
            'chartDmgWeekLabels',
            'chartDmgWeekData',
            'chartDmgWeekDates',
            'chartDmgThirtyLabels',
            'chartDmgThirtyData',
            'chartDmgThirtyDates',
            'chartDmgThreeMonthsLabels',
            'chartDmgThreeMonthsData',
            'chartDmgThreeMonthsDates',
            'chartDmgSixMonthsLabels',
            'chartDmgSixMonthsData',
            'chartDmgSixMonthsDates',
            'chartDmgYearLabels',
            'chartDmgYearData',
            'chartDmgYearDates',
            'topSellingProducts',
            'slowMovingProducts',
            'aiInsights',
            'userRole'
        ));
    }

    public function restockFilter(Request $request)
    {
        abort_if(auth()->user()->role === 'staff_gudang', 403, 'Akses Ditolak.');

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
