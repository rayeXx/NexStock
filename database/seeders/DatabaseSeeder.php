<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Rack;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\BatchInbound;
use App\Models\Outbound;
use App\Models\OutboundDetail;
use App\Models\DamagedReport;
use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use App\Models\PoReceivingHistory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Clear existing data in correct dependency order (except Users)
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        } else {
            DB::statement('PRAGMA foreign_keys = OFF;');
        }
        
        StockOpnameDetail::truncate();
        StockOpname::truncate();
        DamagedReport::truncate();
        OutboundDetail::truncate();
        Outbound::truncate();
        PoReceivingHistory::truncate();
        BatchInbound::truncate();
        PurchaseOrderDetail::truncate();
        PurchaseOrder::truncate();
        Product::truncate();
        Supplier::truncate();
        Rack::truncate();
        Category::truncate();
        // Do NOT truncate Users!

        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } else {
            DB::statement('PRAGMA foreign_keys = ON;');
        }

        // 2. Ensure Users Exist
        if (User::count() === 0) {
            User::create([
                'name' => 'Owner NexStock',
                'email' => 'owner@nexstock.com',
                'password' => Hash::make('password'),
                'role' => 'owner',
            ]);
            User::create([
                'name' => 'Admin Gudang NexStock',
                'email' => 'admin@nexstock.com',
                'password' => Hash::make('password'),
                'role' => 'admin_gudang',
            ]);
            User::create([
                'name' => 'Staff Gudang NexStock',
                'email' => 'staff@nexstock.com',
                'password' => Hash::make('password'),
                'role' => 'staff_gudang',
            ]);
        }

        $allUsers = User::all();
        $adminUser = $allUsers->where('role', 'admin_gudang')->first();
        $staffUser = $allUsers->where('role', 'staff_gudang')->first();
        $ownerUser = $allUsers->where('role', 'owner')->first();

        // 3. Seed Categories
        $catMie = Category::create(['nama_kategori' => 'Mie Instan', 'catatan' => 'Penyimpanan Suhu Ruang Kering']);
        $catMinuman = Category::create(['nama_kategori' => 'Minuman', 'catatan' => 'Penyimpanan Suhu Ruang']);
        $catSnack = Category::create(['nama_kategori' => 'Makanan Ringan', 'catatan' => 'Kering, hindari sinar matahari langsung']);
        $catBumbu = Category::create(['nama_kategori' => 'Bumbu Dapur', 'catatan' => 'Kering, tutup rapat']);
        $catPokok = Category::create(['nama_kategori' => 'Kebutuhan Pokok', 'catatan' => 'Area Kering, hindari lembab']);

        // 4. Seed Suppliers
        $supIndofood = Supplier::create(['nama_supplier' => 'PT Indofood CBP', 'kontak' => '0811-1234-5678 (Budi)']);
        $supMayora = Supplier::create(['nama_supplier' => 'PT Mayora Indah', 'kontak' => '0821-8765-4321 (Siti)']);
        $supGaruda = Supplier::create(['nama_supplier' => 'PT GarudaFood', 'kontak' => '0852-3456-7890 (Agus)']);
        $supUnilever = Supplier::create(['nama_supplier' => 'PT Unilever Indonesia', 'kontak' => '0813-9876-5432 (Dewi)']);
        $supLainnya = Supplier::create(['nama_supplier' => 'CV Sumber Rejeki', 'kontak' => '0857-1122-3344 (Hendra)']);

        // 5. Seed Racks
        $racks = [];
        for ($i = 1; $i <= 5; $i++) {
            $kode = 'DRY-0' . $i;
            $racks[$kode] = Rack::create(['kode_rak' => $kode, 'kapasitas_maksimum_volume' => 5000, 'kapasitas_terpakai' => 0]);
        }
        $allRacks = Rack::all();

        // 6. Seed 15 Sembako Products
        $productsData = [
            ['kode' => 'PRD-S01', 'nama' => 'Indomie Goreng', 'kat' => $catMie->id, 'hb' => 2500, 'hj' => 3200, 'uom' => 'Pcs', 'sb' => 'Karton', 'sj' => 'Pcs', 'rasio' => 40],
            ['kode' => 'PRD-S02', 'nama' => 'Indomie Kuah Rasa Ayam Bawang', 'kat' => $catMie->id, 'hb' => 2400, 'hj' => 3000, 'uom' => 'Pcs', 'sb' => 'Karton', 'sj' => 'Pcs', 'rasio' => 40],
            ['kode' => 'PRD-S03', 'nama' => 'Teh Pucuk Harum 350ml', 'kat' => $catMinuman->id, 'hb' => 3000, 'hj' => 4000, 'uom' => 'Pack', 'sb' => 'Karton', 'sj' => 'Botol', 'rasio' => 24],
            ['kode' => 'PRD-S04', 'nama' => 'Le Minerale 600ml', 'kat' => $catMinuman->id, 'hb' => 2000, 'hj' => 3500, 'uom' => 'Pack', 'sb' => 'Karton', 'sj' => 'Botol', 'rasio' => 24],
            ['kode' => 'PRD-S05', 'nama' => 'Chitato Sapi Panggang 68g', 'kat' => $catSnack->id, 'hb' => 9000, 'hj' => 12000, 'uom' => 'Pcs', 'sb' => 'Karton', 'sj' => 'Pcs', 'rasio' => 30],
            ['kode' => 'PRD-S06', 'nama' => 'Taro Net Seaweed 65g', 'kat' => $catSnack->id, 'hb' => 4500, 'hj' => 6000, 'uom' => 'Pcs', 'sb' => 'Karton', 'sj' => 'Pcs', 'rasio' => 40],
            ['kode' => 'PRD-S07', 'nama' => 'Garuda Kacang Rosta 100g', 'kat' => $catSnack->id, 'hb' => 8000, 'hj' => 10500, 'uom' => 'Pcs', 'sb' => 'Karton', 'sj' => 'Pcs', 'rasio' => 20],
            ['kode' => 'PRD-S08', 'nama' => 'Beras Maknyuss 5kg', 'kat' => $catPokok->id, 'hb' => 65000, 'hj' => 75000, 'uom' => 'Pack', 'sb' => 'Karung', 'sj' => 'Sak', 'rasio' => 5],
            ['kode' => 'PRD-S09', 'nama' => 'Minyak Goreng Bimoli 2L', 'kat' => $catPokok->id, 'hb' => 35000, 'hj' => 42000, 'uom' => 'Pack', 'sb' => 'Karton', 'sj' => 'Pouch', 'rasio' => 6],
            ['kode' => 'PRD-S10', 'nama' => 'Gula Pasir Gulaku 1kg', 'kat' => $catPokok->id, 'hb' => 14000, 'hj' => 17000, 'uom' => 'Pcs', 'sb' => 'Karton', 'sj' => 'Pcs', 'rasio' => 24],
            ['kode' => 'PRD-S11', 'nama' => 'Kecap Manis Bango 520ml', 'kat' => $catBumbu->id, 'hb' => 22000, 'hj' => 28000, 'uom' => 'Pack', 'sb' => 'Karton', 'sj' => 'Pouch', 'rasio' => 12],
            ['kode' => 'PRD-S12', 'nama' => 'Saus Sambal ABC 340ml', 'kat' => $catBumbu->id, 'hb' => 15000, 'hj' => 19000, 'uom' => 'Pack', 'sb' => 'Karton', 'sj' => 'Botol', 'rasio' => 12],
            ['kode' => 'PRD-S13', 'nama' => 'Susu Kental Manis Frisian Flag 370g', 'kat' => $catMinuman->id, 'hb' => 11000, 'hj' => 14000, 'uom' => 'Pack', 'sb' => 'Karton', 'sj' => 'Kaleng', 'rasio' => 48],
            ['kode' => 'PRD-S14', 'nama' => 'Kopi Kapal Api Mix 10x25g', 'kat' => $catMinuman->id, 'hb' => 12000, 'hj' => 15500, 'uom' => 'Pack', 'sb' => 'Karton', 'sj' => 'Renceng', 'rasio' => 12],
            ['kode' => 'PRD-S15', 'nama' => 'Wafer Tango Coklat 130g', 'kat' => $catSnack->id, 'hb' => 6000, 'hj' => 8500, 'uom' => 'Pcs', 'sb' => 'Karton', 'sj' => 'Pcs', 'rasio' => 24],
        ];

        foreach ($productsData as $p) {
            Product::create([
                'kode_produk' => $p['kode'],
                'nama_produk' => $p['nama'],
                'kategori_id' => $p['kat'],
                'harga_beli' => $p['hb'],
                'harga_jual' => $p['hj'],
                'diskon_bawah_1_tahun' => 0,
                'diskon_bawah_6_bulan' => 5,
                'diskon_bawah_3_bulan' => 15,
                'stok_minimum' => rand(100, 200),
                'uom' => $p['uom'],
                'satuan_beli' => $p['sb'],
                'satuan_jual' => $p['sj'],
                'rasio_konversi' => $p['rasio'],
            ]);
        }

        // 7. Dynamic Seeder from August 2025 to Today
        $startDate = Carbon::create(2025, 8, 1)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        $allProducts = Product::all();
        $allSuppliers = Supplier::all();

        $poCounter = 1;
        $outCounter = 1;
        $batchCounter = 1;

        // Base Initial Inbound (August 1) - Stock the warehouse up
        $poNumber = 'PO-' . $startDate->format('Ymd') . '-INIT-1';
        $po = PurchaseOrder::create([
            'po_number' => $poNumber,
            'supplier_id' => $supLainnya->id,
            'status' => 'Completed',
            'total_harga' => 0,
            'created_by' => $adminUser->id,
            'created_at' => $startDate->copy()->subDays(2)->setHour(9),
            'updated_at' => $startDate->copy()->setHour(10),
        ]);
        
        $totalHargaPo = 0;
        foreach ($allProducts as $product) {
            $qtyPesan = rand(300, 600); // initial big stock
            PurchaseOrderDetail::create([
                'po_id' => $po->id,
                'produk_id' => $product->kode_produk,
                'qty_pesan' => $qtyPesan,
                'qty_diterima' => $qtyPesan,
                'harga_satuan' => (int)$product->harga_beli,
            ]);
            $totalHargaPo += $qtyPesan * (int)$product->harga_beli;

            $rack = $allRacks->sortBy(fn($r) => $r->kapasitas_terpakai)->first();
            $batchNumber = 'BTC-INIT-' . $startDate->format('ymd') . '-' . $product->kode_produk;
            $batchSupplier = 'SUP-' . rand(1000, 9999);
            
            $expiredDate = $startDate->copy()->addMonths(rand(12, 24)); // very long expiry initially

            PoReceivingHistory::create([
                'po_id' => $po->id,
                'produk_id' => $product->kode_produk,
                'qty_datang' => $qtyPesan,
                'qty_rusak' => 0,
                'qty_received' => $qtyPesan,
                'kondisi_barang' => 'Baik',
                'batch_number' => $batchNumber,
                'batch_supplier' => $batchSupplier,
                'expired_date' => $expiredDate,
                'rak_id' => $rack->kode_rak,
                'received_at' => $startDate->copy()->setHour(11),
                'received_by' => $staffUser->id,
            ]);

            BatchInbound::create([
                'batch_number' => $batchNumber,
                'batch_supplier' => $batchSupplier,
                'produk_id' => $product->kode_produk,
                'po_id' => $po->id,
                'rak_id' => $rack->kode_rak,
                'expired_date' => $expiredDate,
                'stok_awal_batch' => $qtyPesan,
                'stok_sisa_batch' => $qtyPesan,
                'created_at' => $startDate->copy()->setHour(12),
                'updated_at' => $startDate->copy()->setHour(12),
            ]);
            $rack->kapasitas_terpakai += $qtyPesan;
            $rack->save();
        }
        $po->total_harga = $totalHargaPo;
        $po->save();

        // Loop day by day for transactions
        for ($date = $startDate->copy()->addDay(); $date->lte($endDate); $date->addDay()) {
            
            // --- INBOUND (Every 3-4 days to restock some items) ---
            if (rand(1, 4) === 1) {
                $supplier = $allSuppliers->random();
                $poNumber = 'PO-' . $date->format('Ymd') . '-' . strtoupper(substr(str_replace(' ', '', $supplier->nama_supplier), 0, 4)) . '-' . $poCounter++;
                
                $poProducts = $allProducts->random(rand(2, 5));
                $poStatus = (rand(1, 100) <= 10) ? 'Partially Received' : 'Completed';
                
                $po = PurchaseOrder::create([
                    'po_number' => $poNumber,
                    'supplier_id' => $supplier->id,
                    'status' => $poStatus,
                    'total_harga' => 0,
                    'created_by' => $adminUser->id,
                    'created_at' => $date->copy()->subDays(2)->setHour(rand(8, 11)),
                    'updated_at' => $date->copy()->setHour(rand(9, 12)),
                ]);

                $totalHarga = 0;
                foreach ($poProducts as $product) {
                    $qtyPesan = rand(100, 300);
                    $qtyDiterima = $poStatus === 'Partially Received' ? rand(50, $qtyPesan - 10) : $qtyPesan;

                    PurchaseOrderDetail::create([
                        'po_id' => $po->id,
                        'produk_id' => $product->kode_produk,
                        'qty_pesan' => $qtyPesan,
                        'qty_diterima' => $qtyDiterima,
                        'harga_satuan' => (int)$product->harga_beli,
                    ]);

                    $totalHarga += $qtyPesan * (int)$product->harga_beli;
                    $hasDamaged = (rand(1, 100) <= 5); // 5% chance of damaged during receiving
                    $qtyRusak = 0;
                    if ($hasDamaged) {
                        $qtyRusak = rand(2, 10);
                        if ($qtyDiterima <= $qtyRusak) {
                            $qtyRusak = $qtyDiterima;
                        }
                    }
                    $qtyDiterimaJual = $qtyDiterima - $qtyRusak;

                    $expDice = rand(1, 100);
                    if ($expDice <= 5) {
                        $expiredDate = $date->copy()->addMonths(rand(1, 3));
                    } else {
                        $expiredDate = $date->copy()->addMonths(rand(6, 18));
                    }

                    $batchNumber = 'BTC-DYN-' . $date->format('ymd') . '-' . $product->kode_produk . '-' . $batchCounter++;
                    $batchSupplier = 'SUP-BATCH-' . rand(1000, 9999);

                    $rack = $allRacks->filter(fn($r) => ($r->kapasitas_maksimum_volume - $r->kapasitas_terpakai) >= $qtyDiterimaJual)->random(1)->first() ?? $allRacks->first();
                    
                    // Force cap if really full
                    if (($rack->kapasitas_maksimum_volume - $rack->kapasitas_terpakai) < $qtyDiterimaJual) {
                         $qtyDiterimaJual = max(0, $rack->kapasitas_maksimum_volume - $rack->kapasitas_terpakai);
                    }

                    PoReceivingHistory::create([
                        'po_id' => $po->id,
                        'produk_id' => $product->kode_produk,
                        'qty_datang' => $qtyDiterima,
                        'qty_rusak' => $qtyRusak,
                        'qty_received' => $qtyDiterimaJual,
                        'kondisi_barang' => $qtyRusak > 0 ? 'Rusak Sebagian' : 'Baik',
                        'alasan_kerusakan' => $qtyRusak > 0 ? 'Kemasan rusak / sobek' : null,
                        'batch_number' => $qtyDiterimaJual > 0 ? $batchNumber : null,
                        'batch_supplier' => $qtyDiterimaJual > 0 ? $batchSupplier : null,
                        'expired_date' => $qtyDiterimaJual > 0 ? $expiredDate : null,
                        'rak_id' => $qtyDiterimaJual > 0 ? $rack->kode_rak : null,
                        'received_at' => $date->copy()->setHour(rand(10, 15)),
                        'received_by' => $staffUser->id,
                    ]);

                    if ($qtyDiterimaJual > 0) {
                        BatchInbound::create([
                            'batch_number' => $batchNumber,
                            'batch_supplier' => $batchSupplier,
                            'produk_id' => $product->kode_produk,
                            'po_id' => $po->id,
                            'rak_id' => $rack->kode_rak,
                            'expired_date' => $expiredDate,
                            'stok_awal_batch' => $qtyDiterimaJual,
                            'stok_sisa_batch' => $qtyDiterimaJual,
                            'created_at' => $date->copy()->setHour(12),
                            'updated_at' => $date->copy()->setHour(12),
                        ]);
                        $rack->kapasitas_terpakai += $qtyDiterimaJual;
                        $rack->save();
                    }
                }
                $po->total_harga = $totalHarga;
                $po->save();
            }

            // --- OUTBOUND (Daily sales, stable) ---
            // Let's do 1-3 outbound transactions a day
            $numOutbounds = rand(1, 3);
            for ($o = 0; $o < $numOutbounds; $o++) {
                $availableBatches = BatchInbound::where('stok_sisa_batch', '>', 0)
                    ->orderBy('expired_date', 'asc') // FEFO
                    ->get()
                    ->groupBy('produk_id');
                
                if ($availableBatches->isEmpty()) continue;

                $outboundNumber = 'OUT-' . $date->format('Ymd') . '-' . $outCounter++;
                $outbound = Outbound::create([
                    'outbound_number' => $outboundNumber,
                    'tujuan' => 'Toko Retail ' . rand(1, 50),
                    'status' => 'Completed',
                    'tanggal_keluar' => $date->copy()->setHour(rand(9, 17)),
                    'created_at' => $date->copy()->setHour(8),
                    'updated_at' => $date->copy()->setHour(9),
                ]);

                // pick 1-4 random products
                $pickedProductIds = $availableBatches->keys()->random(min(4, $availableBatches->count()));
                $hasDetails = false;

                foreach ($pickedProductIds as $pid) {
                    $prodBatches = $availableBatches[$pid];
                    $qtyNeeded = rand(10, 50); // Daily sale qty per product
                    
                    $productModel = $allProducts->where('kode_produk', $pid)->first();
                    $basePrice = (int)$productModel->harga_jual;

                    foreach ($prodBatches as $batch) {
                        if ($qtyNeeded <= 0) break;
                        $qtyTaken = min($batch->stok_sisa_batch, $qtyNeeded);

                        // Calculate discount based on FEFO expiry (Simulation of FEFO algorithm)
                        $monthsToExpiry = $date->copy()->startOfDay()->diffInMonths(Carbon::parse($batch->expired_date)->startOfDay(), false);
                        $discountPct = 0;
                        if ($monthsToExpiry >= 0 && $monthsToExpiry < 3) {
                            $discountPct = $productModel->diskon_bawah_3_bulan ?? 0;
                        } elseif ($monthsToExpiry >= 0 && $monthsToExpiry < 6) {
                            $discountPct = $productModel->diskon_bawah_6_bulan ?? 0;
                        } elseif ($monthsToExpiry >= 0 && $monthsToExpiry < 12) {
                            $discountPct = $productModel->diskon_bawah_1_tahun ?? 0;
                        }

                        $finalPrice = $basePrice - ($basePrice * ($discountPct / 100));
                        $subtotal = $finalPrice * $qtyTaken;

                        OutboundDetail::create([
                            'outbound_id' => $outbound->id,
                            'produk_id' => $pid,
                            'batch_number' => $batch->batch_number,
                            'qty_keluar' => $qtyTaken,
                            'rak_id' => $batch->rak_id,
                            'batch_scanned' => true,
                            'harga_satuan_final' => $finalPrice,
                            'persentase_diskon' => $discountPct,
                            'subtotal' => $subtotal,
                        ]);

                        $batch->stok_sisa_batch -= $qtyTaken;
                        $batch->save();

                        // update rack
                        $rack = $allRacks->where('kode_rak', $batch->rak_id)->first();
                        if ($rack) {
                            $rack->kapasitas_terpakai = max(0, $rack->kapasitas_terpakai - $qtyTaken);
                            $rack->save();
                        }
                        
                        $qtyNeeded -= $qtyTaken;
                        $hasDetails = true;
                    }
                }

                if (!$hasDetails) {
                    $outbound->delete();
                }
            }

            // --- DAMAGED REPORTS (Occasional warehouse damage) ---
            if (rand(1, 30) === 1) {
                // Find a random active batch
                $batch = BatchInbound::where('stok_sisa_batch', '>', 5)->inRandomOrder()->first();
                if ($batch) {
                    $qtyRusak = rand(1, 3);
                    $report = DamagedReport::create([
                        'produk_id' => $batch->produk_id,
                        'batch_number' => $batch->batch_number,
                        'rak_id' => $batch->rak_id,
                        'qty_rusak' => $qtyRusak,
                        'alasan' => 'Bocor / Sobek digigit tikus',
                        'status' => 'Approved',
                        'created_by' => $staffUser->id,
                        'created_at' => $date->copy()->setHour(14),
                        'updated_at' => $date->copy()->setHour(15),
                    ]);
                    // Deduct from batch
                    $batch->stok_sisa_batch -= $qtyRusak;
                    $batch->save();
                    // update rack
                    $rack = $allRacks->where('kode_rak', $batch->rak_id)->first();
                    if ($rack) {
                        $rack->kapasitas_terpakai = max(0, $rack->kapasitas_terpakai - $qtyRusak);
                        $rack->save();
                    }
                }
            }
        }
    }
}
