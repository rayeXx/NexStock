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
        // 1. Clear existing data in correct dependency order
        DB::statement('PRAGMA foreign_keys = OFF;'); // SQLite syntax
        
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
        User::truncate();

        DB::statement('PRAGMA foreign_keys = ON;');

        // 2. Seed Users
        $owner = User::create([
            'name' => 'Owner NexStock',
            'email' => 'owner@nexstock.com',
            'password' => Hash::make('password'),
            'role' => 'owner',
        ]);

        $admin = User::create([
            'name' => 'Admin Gudang NexStock',
            'email' => 'admin@nexstock.com',
            'password' => Hash::make('password'),
            'role' => 'admin_gudang',
        ]);

        $staff = User::create([
            'name' => 'Staff Gudang NexStock',
            'email' => 'staff@nexstock.com',
            'password' => Hash::make('password'),
            'role' => 'staff_gudang',
        ]);

        // 3. Seed Frozen Food Categories
        $catMeat = Category::create(['nama_kategori' => 'Daging & Seafood Beku', 'catatan' => 'Suhu Cold Storage -18C s/d -20C']);
        $catProcessed = Category::create(['nama_kategori' => 'Olahan Daging & Nugget', 'catatan' => 'Suhu Cold Storage -18C']);
        $catVeggies = Category::create(['nama_kategori' => 'Kentang & Sayuran Beku', 'catatan' => 'Suhu Cold Storage -18C']);
        $catDimsum = Category::create(['nama_kategori' => 'Cemilan & Dimsum', 'catatan' => 'Suhu Cold Storage -18C']);
        $catDessert = Category::create(['nama_kategori' => 'Es Krim & Dessert', 'catatan' => 'Suhu Cold Storage -25C']);

        // 4. Seed Suppliers
        $supFiesta = Supplier::create(['nama_supplier' => 'PT Fiesta Frozen Foods', 'kontak' => '0821-9876-5432 (Dewi)']);
        $supBelfoods = Supplier::create(['nama_supplier' => 'PT Belfoods Indonesia', 'kontak' => '0812-3456-7890 (Budi)']);
        $supSeafood = Supplier::create(['nama_supplier' => 'CV Ocean Fresh Seafood', 'kontak' => '0811-2233-4455 (Joko)']);
        $supCampina = Supplier::create(['nama_supplier' => 'PT Campina Ice Cream', 'kontak' => '0856-7788-9900 (Santi)']);

        // 5. Seed Racks
        $racks = [
            'FZ-01' => Rack::create(['kode_rak' => 'FZ-01', 'kapasitas_maksimum_volume' => 1500, 'kapasitas_terpakai' => 0]),
            'FZ-02' => Rack::create(['kode_rak' => 'FZ-02', 'kapasitas_maksimum_volume' => 1500, 'kapasitas_terpakai' => 0]),
            'FZ-03' => Rack::create(['kode_rak' => 'FZ-03', 'kapasitas_maksimum_volume' => 1500, 'kapasitas_terpakai' => 0]),
            'FZ-04' => Rack::create(['kode_rak' => 'FZ-04', 'kapasitas_maksimum_volume' => 1500, 'kapasitas_terpakai' => 0]),
            'CH-01' => Rack::create(['kode_rak' => 'CH-01', 'kapasitas_maksimum_volume' => 1000, 'kapasitas_terpakai' => 0]),
            'CH-02' => Rack::create(['kode_rak' => 'CH-02', 'kapasitas_maksimum_volume' => 1000, 'kapasitas_terpakai' => 0]),
        ];

        // 6. Seed Frozen Food Products
        $pSlice = Product::create(['kode_produk' => 'PRD-FZ01', 'nama_produk' => 'Daging Sapi Slice 500g', 'kategori_id' => $catMeat->id, 'harga_beli' => 55000, 'stok_minimum' => 20, 'uom' => 'Pcs']);
        $pSalmon = Product::create(['kode_produk' => 'PRD-FZ02', 'nama_produk' => 'Salmon Fillet Premium 1kg', 'kategori_id' => $catMeat->id, 'harga_beli' => 120000, 'stok_minimum' => 10, 'uom' => 'Pack']);
        $pNugget = Product::create(['kode_produk' => 'PRD-FZ03', 'nama_produk' => 'Chicken Nugget Premium 500g', 'kategori_id' => $catProcessed->id, 'harga_beli' => 35000, 'stok_minimum' => 30, 'uom' => 'Pcs']);
        $pSosis = Product::create(['kode_produk' => 'PRD-FZ04', 'nama_produk' => 'Sosis Sapi Bakar 1kg', 'kategori_id' => $catProcessed->id, 'harga_beli' => 65000, 'stok_minimum' => 15, 'uom' => 'Pack']);
        $pBakso = Product::create(['kode_produk' => 'PRD-FZ05', 'nama_produk' => 'Bakso Sapi Sumber Selera 500g', 'kategori_id' => $catProcessed->id, 'harga_beli' => 40000, 'stok_minimum' => 25, 'uom' => 'Pcs']);
        $pFrenchFries = Product::create(['kode_produk' => 'PRD-FZ06', 'nama_produk' => 'French Fries Shoestring 1kg', 'kategori_id' => $catVeggies->id, 'harga_beli' => 25000, 'stok_minimum' => 40, 'uom' => 'Pack']);
        $pEdamame = Product::create(['kode_produk' => 'PRD-FZ07', 'nama_produk' => 'Edamame Beku Organik 500g', 'kategori_id' => $catVeggies->id, 'harga_beli' => 18000, 'stok_minimum' => 20, 'uom' => 'Pcs']);
        $pSiomay = Product::create(['kode_produk' => 'PRD-FZ08', 'nama_produk' => 'Siomay Ayam Beku 30 Pcs', 'kategori_id' => $catDimsum->id, 'harga_beli' => 30000, 'stok_minimum' => 20, 'uom' => 'Pack']);
        $pIceCream = Product::create(['kode_produk' => 'PRD-FZ09', 'nama_produk' => 'Ice Cream Vanilla Bulky 8L', 'kategori_id' => $catDessert->id, 'harga_beli' => 150000, 'stok_minimum' => 5, 'uom' => 'Dus']);

        // 7. Sequential Simulation from January 2026 to July 2026 (Today is July 6, 2026)

        // ==========================================
        // JANUARI 2026
        // ==========================================
        $dateJan10 = Carbon::parse('2026-01-10 10:00:00');
        $poJan = PurchaseOrder::create([
            'po_number' => 'PO-20260110-FIESTA',
            'supplier_id' => $supFiesta->id,
            'status' => 'Completed',
            'total_harga' => (150 * $pNugget->harga_beli) + (80 * $pSosis->harga_beli),
            'created_by' => $admin->id,
            'created_at' => $dateJan10,
            'updated_at' => $dateJan10,
        ]);
        PurchaseOrderDetail::create(['po_id' => $poJan->id, 'produk_id' => $pNugget->kode_produk, 'qty_pesan' => 150, 'qty_diterima' => 150]);
        PurchaseOrderDetail::create(['po_id' => $poJan->id, 'produk_id' => $pSosis->kode_produk, 'qty_pesan' => 80, 'qty_diterima' => 80]);

        $bJanNugget = BatchInbound::create([
            'batch_number' => 'BTC-260110-NUGGET', 'batch_supplier' => 'FIE-JAN-01', 'produk_id' => $pNugget->kode_produk,
            'po_id' => $poJan->id, 'rak_id' => 'FZ-01', 'expired_date' => $dateJan10->copy()->addYear(),
            'stok_awal_batch' => 150, 'stok_sisa_batch' => 150, 'created_at' => $dateJan10,
        ]);
        $racks['FZ-01']->kapasitas_terpakai += 150; $racks['FZ-01']->save();

        $bJanSosis = BatchInbound::create([
            'batch_number' => 'BTC-260110-SOSIS', 'batch_supplier' => 'FIE-JAN-02', 'produk_id' => $pSosis->kode_produk,
            'po_id' => $poJan->id, 'rak_id' => 'FZ-02', 'expired_date' => $dateJan10->copy()->addYear(),
            'stok_awal_batch' => 80, 'stok_sisa_batch' => 80, 'created_at' => $dateJan10,
        ]);
        $racks['FZ-02']->kapasitas_terpakai += 80; $racks['FZ-02']->save();

        PoReceivingHistory::create([
            'po_id' => $poJan->id, 'produk_id' => $pNugget->kode_produk, 'qty_datang' => 150, 'qty_rusak' => 0, 'qty_received' => 150,
            'kondisi_barang' => 'Baik', 'batch_number' => $bJanNugget->batch_number, 'batch_supplier' => 'FIE-JAN-01',
            'expired_date' => $bJanNugget->expired_date, 'rak_id' => 'FZ-01', 'received_at' => $dateJan10, 'received_by' => $staff->id
        ]);
        PoReceivingHistory::create([
            'po_id' => $poJan->id, 'produk_id' => $pSosis->kode_produk, 'qty_datang' => 80, 'qty_rusak' => 0, 'qty_received' => 80,
            'kondisi_barang' => 'Baik', 'batch_number' => $bJanSosis->batch_number, 'batch_supplier' => 'FIE-JAN-02',
            'expired_date' => $bJanSosis->expired_date, 'rak_id' => 'FZ-02', 'received_at' => $dateJan10, 'received_by' => $staff->id
        ]);

        // Outbound Januari (20 Jan)
        $dateJan20 = Carbon::parse('2026-01-20 14:00:00');
        $outJan = Outbound::create(['outbound_number' => 'OUT-260120-01', 'tujuan' => 'Supermarket Prima', 'tanggal_keluar' => $dateJan20, 'created_at' => $dateJan20]);
        OutboundDetail::create(['outbound_id' => $outJan->id, 'produk_id' => $pNugget->kode_produk, 'batch_number' => $bJanNugget->batch_number, 'qty_keluar' => 40]);
        $bJanNugget->stok_sisa_batch -= 40; $bJanNugget->save();
        $racks['FZ-01']->kapasitas_terpakai -= 40; $racks['FZ-01']->save();

        OutboundDetail::create(['outbound_id' => $outJan->id, 'produk_id' => $pSosis->kode_produk, 'batch_number' => $bJanSosis->batch_number, 'qty_keluar' => 20]);
        $bJanSosis->stok_sisa_batch -= 20; $bJanSosis->save();
        $racks['FZ-02']->kapasitas_terpakai -= 20; $racks['FZ-02']->save();


        // ==========================================
        // FEBRUARI 2026
        // ==========================================
        $dateFeb05 = Carbon::parse('2026-02-05 11:00:00');
        $poFeb = PurchaseOrder::create([
            'po_number' => 'PO-20260205-BELFOODS',
            'supplier_id' => $supBelfoods->id,
            'status' => 'Completed',
            'total_harga' => (200 * $pBakso->harga_beli) + (150 * $pFrenchFries->harga_beli),
            'created_by' => $admin->id,
            'created_at' => $dateFeb05,
            'updated_at' => $dateFeb05,
        ]);
        PurchaseOrderDetail::create(['po_id' => $poFeb->id, 'produk_id' => $pBakso->kode_produk, 'qty_pesan' => 200, 'qty_diterima' => 200]);
        PurchaseOrderDetail::create(['po_id' => $poFeb->id, 'produk_id' => $pFrenchFries->kode_produk, 'qty_pesan' => 150, 'qty_diterima' => 150]);

        $bFebBakso = BatchInbound::create([
            'batch_number' => 'BTC-260205-BAKSO', 'batch_supplier' => 'BEL-FEB-01', 'produk_id' => $pBakso->kode_produk,
            'po_id' => $poFeb->id, 'rak_id' => 'FZ-03', 'expired_date' => $dateFeb05->copy()->addMonths(9),
            'stok_awal_batch' => 200, 'stok_sisa_batch' => 200, 'created_at' => $dateFeb05,
        ]);
        $racks['FZ-03']->kapasitas_terpakai += 200; $racks['FZ-03']->save();

        $bFebFries = BatchInbound::create([
            'batch_number' => 'BTC-260205-FRIES', 'batch_supplier' => 'BEL-FEB-02', 'produk_id' => $pFrenchFries->kode_produk,
            'po_id' => $poFeb->id, 'rak_id' => 'FZ-04', 'expired_date' => $dateFeb05->copy()->addYear(),
            'stok_awal_batch' => 150, 'stok_sisa_batch' => 150, 'created_at' => $dateFeb05,
        ]);
        $racks['FZ-04']->kapasitas_terpakai += 150; $racks['FZ-04']->save();

        PoReceivingHistory::create([
            'po_id' => $poFeb->id, 'produk_id' => $pBakso->kode_produk, 'qty_datang' => 200, 'qty_rusak' => 0, 'qty_received' => 200,
            'kondisi_barang' => 'Baik', 'batch_number' => $bFebBakso->batch_number, 'batch_supplier' => 'BEL-FEB-01',
            'expired_date' => $bFebBakso->expired_date, 'rak_id' => 'FZ-03', 'received_at' => $dateFeb05, 'received_by' => $staff->id
        ]);
        PoReceivingHistory::create([
            'po_id' => $poFeb->id, 'produk_id' => $pFrenchFries->kode_produk, 'qty_datang' => 150, 'qty_rusak' => 0, 'qty_received' => 150,
            'kondisi_barang' => 'Baik', 'batch_number' => $bFebFries->batch_number, 'batch_supplier' => 'BEL-FEB-02',
            'expired_date' => $bFebFries->expired_date, 'rak_id' => 'FZ-04', 'received_at' => $dateFeb05, 'received_by' => $staff->id
        ]);

        // Outbound Februari (18 Feb)
        $dateFeb18 = Carbon::parse('2026-02-18 15:30:00');
        $outFeb = Outbound::create(['outbound_number' => 'OUT-260218-01', 'tujuan' => 'Hypermart Lestari', 'tanggal_keluar' => $dateFeb18, 'created_at' => $dateFeb18]);
        OutboundDetail::create(['outbound_id' => $outFeb->id, 'produk_id' => $pNugget->kode_produk, 'batch_number' => $bJanNugget->batch_number, 'qty_keluar' => 50]);
        $bJanNugget->stok_sisa_batch -= 50; $bJanNugget->save(); // sisa 60
        $racks['FZ-01']->kapasitas_terpakai -= 50; $racks['FZ-01']->save();

        OutboundDetail::create(['outbound_id' => $outFeb->id, 'produk_id' => $pSosis->kode_produk, 'batch_number' => $bJanSosis->batch_number, 'qty_keluar' => 30]);
        $bJanSosis->stok_sisa_batch -= 30; $bJanSosis->save(); // sisa 30
        $racks['FZ-02']->kapasitas_terpakai -= 30; $racks['FZ-02']->save();

        OutboundDetail::create(['outbound_id' => $outFeb->id, 'produk_id' => $pBakso->kode_produk, 'batch_number' => $bFebBakso->batch_number, 'qty_keluar' => 60]);
        $bFebBakso->stok_sisa_batch -= 60; $bFebBakso->save(); // sisa 140
        $racks['FZ-03']->kapasitas_terpakai -= 60; $racks['FZ-03']->save();


        // ==========================================
        // MARET 2026 (Termasuk Kritis Expired)
        // ==========================================
        $dateMar05 = Carbon::parse('2026-03-05 09:00:00');
        $poMar = PurchaseOrder::create([
            'po_number' => 'PO-20260305-OCEAN',
            'supplier_id' => $supSeafood->id,
            'status' => 'Completed',
            'total_harga' => (50 * $pSalmon->harga_beli) + (80 * $pSlice->harga_beli),
            'created_by' => $admin->id,
            'created_at' => $dateMar05,
            'updated_at' => $dateMar05,
        ]);
        PurchaseOrderDetail::create(['po_id' => $poMar->id, 'produk_id' => $pSalmon->kode_produk, 'qty_pesan' => 50, 'qty_diterima' => 50]);
        PurchaseOrderDetail::create(['po_id' => $poMar->id, 'produk_id' => $pSlice->kode_produk, 'qty_pesan' => 80, 'qty_diterima' => 80]);

        $bMarSalmon = BatchInbound::create([
            'batch_number' => 'BTC-260305-SALMON', 'batch_supplier' => 'OCE-MAR-01', 'produk_id' => $pSalmon->kode_produk,
            'po_id' => $poMar->id, 'rak_id' => 'CH-02', 'expired_date' => $dateMar05->copy()->addMonths(9),
            'stok_awal_batch' => 50, 'stok_sisa_batch' => 50, 'created_at' => $dateMar05,
        ]);
        $racks['CH-02']->kapasitas_terpakai += 50; $racks['CH-02']->save();

        $bMarSlice = BatchInbound::create([
            'batch_number' => 'BTC-260305-SLICE', 'batch_supplier' => 'OCE-MAR-02', 'produk_id' => $pSlice->kode_produk,
            'po_id' => $poMar->id, 'rak_id' => 'CH-01', 'expired_date' => $dateMar05->copy()->addMonths(9),
            'stok_awal_batch' => 80, 'stok_sisa_batch' => 80, 'created_at' => $dateMar05,
        ]);
        $racks['CH-01']->kapasitas_terpakai += 80; $racks['CH-01']->save();

        PoReceivingHistory::create([
            'po_id' => $poMar->id, 'produk_id' => $pSalmon->kode_produk, 'qty_datang' => 50, 'qty_rusak' => 0, 'qty_received' => 50,
            'kondisi_barang' => 'Baik', 'batch_number' => $bMarSalmon->batch_number, 'batch_supplier' => 'OCE-MAR-01',
            'expired_date' => $bMarSalmon->expired_date, 'rak_id' => 'CH-02', 'received_at' => $dateMar05, 'received_by' => $staff->id
        ]);
        PoReceivingHistory::create([
            'po_id' => $poMar->id, 'produk_id' => $pSlice->kode_produk, 'qty_datang' => 80, 'qty_rusak' => 0, 'qty_received' => 80,
            'kondisi_barang' => 'Baik', 'batch_number' => $bMarSlice->batch_number, 'batch_supplier' => 'OCE-MAR-02',
            'expired_date' => $bMarSlice->expired_date, 'rak_id' => 'CH-01', 'received_at' => $dateMar05, 'received_by' => $staff->id
        ]);

        // CRITICAL EXPIRED RISK BATCHES (Dibuat pada Maret dengan tanggal kadaluwarsa mepet / terlampaui)
        $dateMar12 = Carbon::parse('2026-03-12 11:15:00');
        
        // 1. Batch A: Edamame - Kadaluwarsa pada Juni 28, 2026 (Sudah Lewat)
        $bExpEdamame = BatchInbound::create([
            'batch_number' => 'BTC-EXP-EDAMAME', 'batch_supplier' => 'SUP-EDM-SHORT', 'produk_id' => $pEdamame->kode_produk,
            'po_id' => null, 'rak_id' => 'FZ-04', 'expired_date' => Carbon::parse('2026-06-28 00:00:00'),
            'stok_awal_batch' => 40, 'stok_sisa_batch' => 12, 'created_at' => $dateMar12,
        ]);
        $racks['FZ-04']->kapasitas_terpakai += 12; $racks['FZ-04']->save();

        // 2. Batch B: Siomay - Kadaluwarsa pada Juli 12, 2026 (Sangat Mepet, 6 hari lagi dari Juli 6)
        $bExpSiomay = BatchInbound::create([
            'batch_number' => 'BTC-EXP-SIOMAY', 'batch_supplier' => 'SUP-SIO-SHORT', 'produk_id' => $pSiomay->kode_produk,
            'po_id' => null, 'rak_id' => 'FZ-01', 'expired_date' => Carbon::parse('2026-07-12 00:00:00'),
            'stok_awal_batch' => 30, 'stok_sisa_batch' => 8, 'created_at' => $dateMar12,
        ]);
        $racks['FZ-01']->kapasitas_terpakai += 8; $racks['FZ-01']->save();

        // 3. Batch C: Daging Slice - Kadaluwarsa pada Juli 28, 2026 (Risiko Tinggi, 22 hari lagi)
        $bExpSlice = BatchInbound::create([
            'batch_number' => 'BTC-EXP-SLICE', 'batch_supplier' => 'SUP-SLI-SHORT', 'produk_id' => $pSlice->kode_produk,
            'po_id' => null, 'rak_id' => 'CH-01', 'expired_date' => Carbon::parse('2026-07-28 00:00:00'),
            'stok_awal_batch' => 50, 'stok_sisa_batch' => 15, 'created_at' => $dateMar12,
        ]);
        $racks['CH-01']->kapasitas_terpakai += 15; $racks['CH-01']->save();

        // Outbound Maret (25 Mar)
        $dateMar25 = Carbon::parse('2026-03-25 14:00:00');
        $outMar = Outbound::create(['outbound_number' => 'OUT-260325-01', 'tujuan' => 'Mitra Catering Sejahtera', 'tanggal_keluar' => $dateMar25, 'created_at' => $dateMar25]);
        OutboundDetail::create(['outbound_id' => $outMar->id, 'produk_id' => $pNugget->kode_produk, 'batch_number' => $bJanNugget->batch_number, 'qty_keluar' => 40]);
        $bJanNugget->stok_sisa_batch -= 40; $bJanNugget->save(); // sisa 20
        $racks['FZ-01']->kapasitas_terpakai -= 40; $racks['FZ-01']->save();

        OutboundDetail::create(['outbound_id' => $outMar->id, 'produk_id' => $pBakso->kode_produk, 'batch_number' => $bFebBakso->batch_number, 'qty_keluar' => 40]);
        $bFebBakso->stok_sisa_batch -= 40; $bFebBakso->save(); // sisa 100
        $racks['FZ-03']->kapasitas_terpakai -= 40; $racks['FZ-03']->save();

        OutboundDetail::create(['outbound_id' => $outMar->id, 'produk_id' => $pSalmon->kode_produk, 'batch_number' => $bMarSalmon->batch_number, 'qty_keluar' => 20]);
        $bMarSalmon->stok_sisa_batch -= 20; $bMarSalmon->save(); // sisa 30
        $racks['CH-02']->kapasitas_terpakai -= 20; $racks['CH-02']->save();


        // ==========================================
        // APRIL 2026
        // ==========================================
        $dateApr08 = Carbon::parse('2026-04-08 10:00:00');
        $poApr = PurchaseOrder::create([
            'po_number' => 'PO-20260408-FIESTA',
            'supplier_id' => $supFiesta->id,
            'status' => 'Completed',
            'total_harga' => (100 * $pNugget->harga_beli) + (60 * $pSosis->harga_beli),
            'created_by' => $admin->id,
            'created_at' => $dateApr08,
            'updated_at' => $dateApr08,
        ]);
        PurchaseOrderDetail::create(['po_id' => $poApr->id, 'produk_id' => $pNugget->kode_produk, 'qty_pesan' => 100, 'qty_diterima' => 100]);
        PurchaseOrderDetail::create(['po_id' => $poApr->id, 'produk_id' => $pSosis->kode_produk, 'qty_pesan' => 60, 'qty_diterima' => 60]);

        $bAprNugget = BatchInbound::create([
            'batch_number' => 'BTC-260408-NUGGET', 'batch_supplier' => 'FIE-APR-01', 'produk_id' => $pNugget->kode_produk,
            'po_id' => $poApr->id, 'rak_id' => 'FZ-01', 'expired_date' => $dateApr08->copy()->addYear(),
            'stok_awal_batch' => 100, 'stok_sisa_batch' => 100, 'created_at' => $dateApr08,
        ]);
        $racks['FZ-01']->kapasitas_terpakai += 100; $racks['FZ-01']->save();

        $bAprSosis = BatchInbound::create([
            'batch_number' => 'BTC-260408-SOSIS', 'batch_supplier' => 'FIE-APR-02', 'produk_id' => $pSosis->kode_produk,
            'po_id' => $poApr->id, 'rak_id' => 'FZ-02', 'expired_date' => $dateApr08->copy()->addYear(),
            'stok_awal_batch' => 60, 'stok_sisa_batch' => 60, 'created_at' => $dateApr08,
        ]);
        $racks['FZ-02']->kapasitas_terpakai += 60; $racks['FZ-02']->save();

        PoReceivingHistory::create([
            'po_id' => $poApr->id, 'produk_id' => $pNugget->kode_produk, 'qty_datang' => 100, 'qty_rusak' => 0, 'qty_received' => 100,
            'kondisi_barang' => 'Baik', 'batch_number' => $bAprNugget->batch_number, 'batch_supplier' => 'FIE-APR-01',
            'expired_date' => $bAprNugget->expired_date, 'rak_id' => 'FZ-01', 'received_at' => $dateApr08, 'received_by' => $staff->id
        ]);
        PoReceivingHistory::create([
            'po_id' => $poApr->id, 'produk_id' => $pSosis->kode_produk, 'qty_datang' => 60, 'qty_rusak' => 0, 'qty_received' => 60,
            'kondisi_barang' => 'Baik', 'batch_number' => $bAprSosis->batch_number, 'batch_supplier' => 'FIE-APR-02',
            'expired_date' => $bAprSosis->expired_date, 'rak_id' => 'FZ-02', 'received_at' => $dateApr08, 'received_by' => $staff->id
        ]);

        // Outbound April (22 Apr)
        $dateApr22 = Carbon::parse('2026-04-22 13:00:00');
        $outApr = Outbound::create(['outbound_number' => 'OUT-260422-01', 'tujuan' => 'Supermarket Prima', 'tanggal_keluar' => $dateApr22, 'created_at' => $dateApr22]);
        OutboundDetail::create(['outbound_id' => $outApr->id, 'produk_id' => $pNugget->kode_produk, 'batch_number' => $bJanNugget->batch_number, 'qty_keluar' => 20]);
        $bJanNugget->stok_sisa_batch -= 20; $bJanNugget->save(); // sisa 0
        $racks['FZ-01']->kapasitas_terpakai -= 20; $racks['FZ-01']->save();

        OutboundDetail::create(['outbound_id' => $outApr->id, 'produk_id' => $pSosis->kode_produk, 'batch_number' => $bJanSosis->batch_number, 'qty_keluar' => 15]);
        $bJanSosis->stok_sisa_batch -= 15; $bJanSosis->save(); // sisa 15
        $racks['FZ-02']->kapasitas_terpakai -= 15; $racks['FZ-02']->save();

        OutboundDetail::create(['outbound_id' => $outApr->id, 'produk_id' => $pFrenchFries->kode_produk, 'batch_number' => $bFebFries->batch_number, 'qty_keluar' => 30]);
        $bFebFries->stok_sisa_batch -= 30; $bFebFries->save(); // sisa 120
        $racks['FZ-04']->kapasitas_terpakai -= 30; $racks['FZ-04']->save();


        // ==========================================
        // MEI 2026
        // ==========================================
        $dateMay06 = Carbon::parse('2026-05-06 09:45:00');
        $poMay = PurchaseOrder::create([
            'po_number' => 'PO-20260506-CAMPINA',
            'supplier_id' => $supCampina->id,
            'status' => 'Completed',
            'total_harga' => 30 * $pIceCream->harga_beli,
            'created_by' => $admin->id,
            'created_at' => $dateMay06,
            'updated_at' => $dateMay06,
        ]);
        PurchaseOrderDetail::create(['po_id' => $poMay->id, 'produk_id' => $pIceCream->kode_produk, 'qty_pesan' => 30, 'qty_diterima' => 30]);

        $bMayIce = BatchInbound::create([
            'batch_number' => 'BTC-260506-ICECREAM', 'batch_supplier' => 'CAM-MAY-01', 'produk_id' => $pIceCream->kode_produk,
            'po_id' => $poMay->id, 'rak_id' => 'CH-01', 'expired_date' => $dateMay06->copy()->addYear(),
            'stok_awal_batch' => 30, 'stok_sisa_batch' => 30, 'created_at' => $dateMay06,
        ]);
        $racks['CH-01']->kapasitas_terpakai += 30; $racks['CH-01']->save();

        PoReceivingHistory::create([
            'po_id' => $poMay->id, 'produk_id' => $pIceCream->kode_produk, 'qty_datang' => 30, 'qty_rusak' => 0, 'qty_received' => 30,
            'kondisi_barang' => 'Baik', 'batch_number' => $bMayIce->batch_number, 'batch_supplier' => 'CAM-MAY-01',
            'expired_date' => $bMayIce->expired_date, 'rak_id' => 'CH-01', 'received_at' => $dateMay06, 'received_by' => $staff->id
        ]);

        // Outbound Mei (20 May)
        $dateMay20 = Carbon::parse('2026-05-20 14:15:00');
        $outMay = Outbound::create(['outbound_number' => 'OUT-260520-01', 'tujuan' => 'Ramen & Sushi Hub', 'tanggal_keluar' => $dateMay20, 'created_at' => $dateMay20]);
        OutboundDetail::create(['outbound_id' => $outMay->id, 'produk_id' => $pIceCream->kode_produk, 'batch_number' => $bMayIce->batch_number, 'qty_keluar' => 10]);
        $bMayIce->stok_sisa_batch -= 10; $bMayIce->save(); // sisa 20
        $racks['CH-01']->kapasitas_terpakai -= 10; $racks['CH-01']->save();

        OutboundDetail::create(['outbound_id' => $outMay->id, 'produk_id' => $pNugget->kode_produk, 'batch_number' => $bAprNugget->batch_number, 'qty_keluar' => 30]);
        $bAprNugget->stok_sisa_batch -= 30; $bAprNugget->save(); // sisa 70
        $racks['FZ-01']->kapasitas_terpakai -= 30; $racks['FZ-01']->save();

        OutboundDetail::create(['outbound_id' => $outMay->id, 'produk_id' => $pBakso->kode_produk, 'batch_number' => $bFebBakso->batch_number, 'qty_keluar' => 20]);
        $bFebBakso->stok_sisa_batch -= 20; $bFebBakso->save(); // sisa 80
        $racks['FZ-03']->kapasitas_terpakai -= 20; $racks['FZ-03']->save();


        // ==========================================
        // JUNI 2026
        // ==========================================
        $dateJun03 = Carbon::parse('2026-06-03 10:30:00');
        $poJun = PurchaseOrder::create([
            'po_number' => 'PO-20260603-BELFOODS',
            'supplier_id' => $supBelfoods->id,
            'status' => 'Completed',
            'total_harga' => (120 * $pBakso->harga_beli) + (100 * $pFrenchFries->harga_beli),
            'created_by' => $admin->id,
            'created_at' => $dateJun03,
            'updated_at' => $dateJun03,
        ]);
        PurchaseOrderDetail::create(['po_id' => $poJun->id, 'produk_id' => $pBakso->kode_produk, 'qty_pesan' => 120, 'qty_diterima' => 120]);
        PurchaseOrderDetail::create(['po_id' => $poJun->id, 'produk_id' => $pFrenchFries->kode_produk, 'qty_pesan' => 100, 'qty_diterima' => 100]);

        $bJunBakso = BatchInbound::create([
            'batch_number' => 'BTC-260603-BAKSO', 'batch_supplier' => 'BEL-JUN-01', 'produk_id' => $pBakso->kode_produk,
            'po_id' => $poJun->id, 'rak_id' => 'FZ-03', 'expired_date' => $dateJun03->copy()->addMonths(9),
            'stok_awal_batch' => 120, 'stok_sisa_batch' => 120, 'created_at' => $dateJun03,
        ]);
        $racks['FZ-03']->kapasitas_terpakai += 120; $racks['FZ-03']->save();

        $bJunFries = BatchInbound::create([
            'batch_number' => 'BTC-260603-FRIES', 'batch_supplier' => 'BEL-JUN-02', 'produk_id' => $pFrenchFries->kode_produk,
            'po_id' => $poJun->id, 'rak_id' => 'FZ-04', 'expired_date' => $dateJun03->copy()->addYear(),
            'stok_awal_batch' => 100, 'stok_sisa_batch' => 100, 'created_at' => $dateJun03,
        ]);
        $racks['FZ-04']->kapasitas_terpakai += 100; $racks['FZ-04']->save();

        PoReceivingHistory::create([
            'po_id' => $poJun->id, 'produk_id' => $pBakso->kode_produk, 'qty_datang' => 120, 'qty_rusak' => 0, 'qty_received' => 120,
            'kondisi_barang' => 'Baik', 'batch_number' => $bJunBakso->batch_number, 'batch_supplier' => 'BEL-JUN-01',
            'expired_date' => $bJunBakso->expired_date, 'rak_id' => 'FZ-03', 'received_at' => $dateJun03, 'received_by' => $staff->id
        ]);
        PoReceivingHistory::create([
            'po_id' => $poJun->id, 'produk_id' => $pFrenchFries->kode_produk, 'qty_datang' => 100, 'qty_rusak' => 0, 'qty_received' => 100,
            'kondisi_barang' => 'Baik', 'batch_number' => $bJunFries->batch_number, 'batch_supplier' => 'BEL-JUN-02',
            'expired_date' => $bJunFries->expired_date, 'rak_id' => 'FZ-04', 'received_at' => $dateJun03, 'received_by' => $staff->id
        ]);

        // Outbound Juni 1 (18 Jun)
        $dateJun18 = Carbon::parse('2026-06-18 16:00:00');
        $outJun1 = Outbound::create(['outbound_number' => 'OUT-260618-01', 'tujuan' => 'Agen Frozen Mart Depok', 'tanggal_keluar' => $dateJun18, 'created_at' => $dateJun18]);
        OutboundDetail::create(['outbound_id' => $outJun1->id, 'produk_id' => $pIceCream->kode_produk, 'batch_number' => $bMayIce->batch_number, 'qty_keluar' => 15]);
        $bMayIce->stok_sisa_batch -= 15; $bMayIce->save(); // sisa 5
        $racks['CH-01']->kapasitas_terpakai -= 15; $racks['CH-01']->save();

        OutboundDetail::create(['outbound_id' => $outJun1->id, 'produk_id' => $pBakso->kode_produk, 'batch_number' => $bFebBakso->batch_number, 'qty_keluar' => 40]);
        $bFebBakso->stok_sisa_batch -= 40; $bFebBakso->save(); // sisa 40
        $racks['FZ-03']->kapasitas_terpakai -= 40; $racks['FZ-03']->save();

        // Outbound Juni 2 (28 Jun)
        $dateJun28 = Carbon::parse('2026-06-28 11:30:00');
        $outJun2 = Outbound::create(['outbound_number' => 'OUT-260628-01', 'tujuan' => 'Restoran Bebek Asap', 'tanggal_keluar' => $dateJun28, 'created_at' => $dateJun28]);
        OutboundDetail::create(['outbound_id' => $outJun2->id, 'produk_id' => $pFrenchFries->kode_produk, 'batch_number' => $bFebFries->batch_number, 'qty_keluar' => 50]);
        $bFebFries->stok_sisa_batch -= 50; $bFebFries->save(); // sisa 70
        $racks['FZ-04']->kapasitas_terpakai -= 50; $racks['FZ-04']->save();


        // ==========================================
        // JULI 2026 (Hari Ini: 6 Juli 2026)
        // ==========================================

        // --- 1. PO Ordered / OTW (1 Juli) ---
        $dateJul01 = Carbon::parse('2026-07-01 10:00:00');
        $poJul1 = PurchaseOrder::create([
            'po_number' => 'PO-20260701-OCEAN',
            'supplier_id' => $supSeafood->id,
            'status' => 'Ordered',
            'total_harga' => 15 * $pSalmon->harga_beli,
            'created_by' => $admin->id,
            'created_at' => $dateJul01,
            'updated_at' => $dateJul01,
        ]);
        PurchaseOrderDetail::create(['po_id' => $poJul1->id, 'produk_id' => $pSalmon->kode_produk, 'qty_pesan' => 15, 'qty_diterima' => 0]);

        // --- 2. Outbound Juli (3 Juli) ---
        $dateJul03Out = Carbon::parse('2026-07-03 14:00:00');
        $outJul = Outbound::create(['outbound_number' => 'OUT-260703-01', 'tujuan' => 'Agen Frozen Mart Depok', 'tanggal_keluar' => $dateJul03Out, 'created_at' => $dateJul03Out]);
        OutboundDetail::create(['outbound_id' => $outJul->id, 'produk_id' => $pBakso->kode_produk, 'batch_number' => $bFebBakso->batch_number, 'qty_keluar' => 20]);
        $bFebBakso->stok_sisa_batch -= 20; $bFebBakso->save(); // sisa 20
        $racks['FZ-03']->kapasitas_terpakai -= 20; $racks['FZ-03']->save();

        OutboundDetail::create(['outbound_id' => $outJul->id, 'produk_id' => $pFrenchFries->kode_produk, 'batch_number' => $bFebFries->batch_number, 'qty_keluar' => 30]);
        $bFebFries->stok_sisa_batch -= 30; $bFebFries->save(); // sisa 40
        $racks['FZ-04']->kapasitas_terpakai -= 30; $racks['FZ-04']->save();

        // --- 3. Damaged Report Disetujui (3 Juli) ---
        $dmgJul3 = DamagedReport::create([
            'produk_id' => $pBakso->kode_produk, 'batch_number' => $bFebBakso->batch_number, 'rak_id' => 'FZ-03',
            'qty_rusak' => 5, 'foto_bukti' => 'damaged_reports/proof_dummy_1.jpg',
            'alasan' => 'Kemasan luar sobek terkena silet saat penataan rak',
            'status' => 'Approved', 'created_by' => $staff->id, 'created_at' => $dateJul03Out,
        ]);
        $bFebBakso->stok_sisa_batch -= 5; $bFebBakso->save(); // sisa 15
        $racks['FZ-03']->kapasitas_terpakai -= 5; $racks['FZ-03']->save();

        // --- 4. PO Draft (5 Juli) ---
        $dateJul05 = Carbon::parse('2026-07-05 16:00:00');
        $poDraft = PurchaseOrder::create([
            'po_number' => 'PO-20260705-DRAFT-FIESTA',
            'supplier_id' => $supFiesta->id,
            'status' => 'Draft',
            'total_harga' => 50 * $pNugget->harga_beli,
            'created_by' => $admin->id,
            'created_at' => $dateJul05,
            'updated_at' => $dateJul05,
        ]);
        PurchaseOrderDetail::create(['po_id' => $poDraft->id, 'produk_id' => $pNugget->kode_produk, 'qty_pesan' => 50, 'qty_diterima' => 0]);

        // --- 5. Damaged Report Pending (Hari Ini) ---
        $dmgToday = DamagedReport::create([
            'produk_id' => $pFrenchFries->kode_produk, 'batch_number' => $bFebFries->batch_number, 'rak_id' => 'FZ-04',
            'qty_rusak' => 2, 'foto_bukti' => 'damaged_reports/proof_dummy_3.jpg',
            'alasan' => 'Bungkus bocor, isi mulai mencair di pinggir rak',
            'status' => 'Pending', 'created_by' => $staff->id, 'created_at' => Carbon::now(),
        ]);
        // Deduct 2 packs immediately for quarantine
        $bFebFries->stok_sisa_batch -= 2; $bFebFries->save(); // sisa 38
        $racks['FZ-04']->kapasitas_terpakai -= 2; $racks['FZ-04']->save();

        // --- 6. Stock Opname Audit (Hari Ini) ---
        $opname = StockOpname::create([
            'tanggal_opname' => Carbon::now(), 'created_by' => $staff->id, 'created_at' => Carbon::now(),
        ]);
        // Audit sisa sosis Januari: sistem = 15, fisik = 13 (selisih -2)
        StockOpnameDetail::create([
            'stock_opname_id' => $opname->id, 'produk_id' => $pSosis->kode_produk, 'batch_number' => $bJanSosis->batch_number,
            'qty_sistem' => 15, 'qty_fisik' => 13, 'selisih' => -2, 'catatan' => '2 pack kemasan luar rusak parah digigit hama di pojok rak',
        ]);
        $bJanSosis->stok_sisa_batch = 13; $bJanSosis->save();
        $racks['FZ-02']->kapasitas_terpakai -= 2; $racks['FZ-02']->save();
    }
}
