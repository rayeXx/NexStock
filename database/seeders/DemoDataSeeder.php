<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Ensure Categories exist ──────────────────────────────────────
        $catMakanan = DB::table('m_categories')->insertOrIgnore([
            ['nama_kategori' => 'Makanan', 'created_at' => now(), 'updated_at' => now()],
            ['nama_kategori' => 'Minuman', 'created_at' => now(), 'updated_at' => now()],
            ['nama_kategori' => 'Snack', 'created_at' => now(), 'updated_at' => now()],
            ['nama_kategori' => 'Bumbu & Saus', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $catIds = DB::table('m_categories')->pluck('id', 'nama_kategori');

        // ── 2. Ensure Racks exist ───────────────────────────────────────────
        $racks = [
            ['kode_rak' => 'A1', 'kapasitas_maksimum_volume' => 1000, 'kapasitas_terpakai' => 0],
            ['kode_rak' => 'A2', 'kapasitas_maksimum_volume' => 1000, 'kapasitas_terpakai' => 0],
            ['kode_rak' => 'B1', 'kapasitas_maksimum_volume' => 800,  'kapasitas_terpakai' => 0],
            ['kode_rak' => 'B2', 'kapasitas_maksimum_volume' => 800,  'kapasitas_terpakai' => 0],
        ];
        foreach ($racks as $rack) {
            DB::table('m_racks')->updateOrInsert(
                ['kode_rak' => $rack['kode_rak']],
                array_merge($rack, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        // ── 3. Products ─────────────────────────────────────────────────────
        // 10 products: mix of fast-moving and slow-moving
        $products = [
            // Fast-moving (will have high outbound)
            ['kode' => 'PRD-001', 'nama' => 'Indomie Goreng',         'kat' => 'Makanan',     'harga' => 3200,  'min' => 50,  'uom' => 'Pcs'],
            ['kode' => 'PRD-002', 'nama' => 'Teh Botol Sosro 350ml',  'kat' => 'Minuman',     'harga' => 4500,  'min' => 40,  'uom' => 'Pcs'],
            ['kode' => 'PRD-003', 'nama' => 'Aqua Galon 19L',         'kat' => 'Minuman',     'harga' => 18000, 'min' => 20,  'uom' => 'Pcs'],
            ['kode' => 'PRD-004', 'nama' => 'Mie Sedaap Ayam Bawang', 'kat' => 'Makanan',     'harga' => 3000,  'min' => 50,  'uom' => 'Pcs'],
            ['kode' => 'PRD-005', 'nama' => 'Pocari Sweat 500ml',     'kat' => 'Minuman',     'harga' => 7500,  'min' => 30,  'uom' => 'Pcs'],
            // Medium-moving
            ['kode' => 'PRD-006', 'nama' => 'Chitato Rasa Sapi',      'kat' => 'Snack',       'harga' => 9500,  'min' => 20,  'uom' => 'Pack'],
            ['kode' => 'PRD-007', 'nama' => 'Oreo Original',          'kat' => 'Snack',       'harga' => 8000,  'min' => 20,  'uom' => 'Pack'],
            // Slow-moving (will have very low outbound)
            ['kode' => 'PRD-008', 'nama' => 'Kecap Manis ABC 600ml',  'kat' => 'Bumbu & Saus','harga' => 15000, 'min' => 10,  'uom' => 'Pcs'],
            ['kode' => 'PRD-009', 'nama' => 'Sarden ABC Kaleng 155gr','kat' => 'Makanan',     'harga' => 12000, 'min' => 10,  'uom' => 'Pcs'],
            ['kode' => 'PRD-010', 'nama' => 'Saos Sambal Indofood',   'kat' => 'Bumbu & Saus','harga' => 8500,  'min' => 10,  'uom' => 'Pcs'],
        ];

        foreach ($products as $p) {
            $katId = $catIds[$p['kat']] ?? 1;
            DB::table('m_products')->updateOrInsert(
                ['kode_produk' => $p['kode']],
                [
                    'nama_produk'   => $p['nama'],
                    'kategori_id'   => $katId,
                    'harga_beli'    => encrypt($p['harga']),
                    'stok_minimum'  => $p['min'],
                    'uom'           => $p['uom'],
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]
            );
        }

        // ── 4. Batch Inbounds ───────────────────────────────────────────────
        // Each product gets 1-2 batches with realistic stock
        $batches = [
            ['batch' => 'BTH-001-A', 'produk' => 'PRD-001', 'rak' => 'A1', 'stok' => 500, 'expired' => '+6 months'],
            ['batch' => 'BTH-002-A', 'produk' => 'PRD-002', 'rak' => 'A1', 'stok' => 300, 'expired' => '+8 months'],
            ['batch' => 'BTH-003-A', 'produk' => 'PRD-003', 'rak' => 'A2', 'stok' => 80,  'expired' => '+4 months'],
            ['batch' => 'BTH-004-A', 'produk' => 'PRD-004', 'rak' => 'A2', 'stok' => 450, 'expired' => '+6 months'],
            ['batch' => 'BTH-005-A', 'produk' => 'PRD-005', 'rak' => 'B1', 'stok' => 200, 'expired' => '+5 months'],
            ['batch' => 'BTH-005-B', 'produk' => 'PRD-005', 'rak' => 'B1', 'stok' => 150, 'expired' => '+3 months'],
            ['batch' => 'BTH-006-A', 'produk' => 'PRD-006', 'rak' => 'B1', 'stok' => 180, 'expired' => '+4 months'],
            ['batch' => 'BTH-007-A', 'produk' => 'PRD-007', 'rak' => 'B2', 'stok' => 160, 'expired' => '+5 months'],
            ['batch' => 'BTH-008-A', 'produk' => 'PRD-008', 'rak' => 'B2', 'stok' => 120, 'expired' => '+12 months'],
            ['batch' => 'BTH-009-A', 'produk' => 'PRD-009', 'rak' => 'A2', 'stok' => 90,  'expired' => '+10 months'],
            ['batch' => 'BTH-010-A', 'produk' => 'PRD-010', 'rak' => 'B2', 'stok' => 100, 'expired' => '+9 months'],
        ];

        foreach ($batches as $b) {
            // Skip if already exists
            if (DB::table('t_batch_inbounds')->where('batch_number', $b['batch'])->exists()) continue;
            DB::table('t_batch_inbounds')->insert([
                'batch_number'    => $b['batch'],
                'produk_id'       => $b['produk'],
                'po_id'           => null,
                'rak_id'          => $b['rak'],
                'expired_date'    => Carbon::now()->modify($b['expired'])->format('Y-m-d'),
                'stok_awal_batch' => $b['stok'],
                'stok_sisa_batch' => $b['stok'], // will be reduced by outbounds below
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }

        // ── 5. Outbound Transactions (last 30 days) ─────────────────────────
        // Simulate realistic daily sales for the past 30 days
        // Fast-movers sell a lot, slow-movers sell very little
        $salesProfile = [
            'PRD-001' => ['batch' => 'BTH-001-A', 'daily_min' => 12, 'daily_max' => 25], // Indomie - sangat laku
            'PRD-002' => ['batch' => 'BTH-002-A', 'daily_min' => 8,  'daily_max' => 18], // Teh Botol - laku
            'PRD-003' => ['batch' => 'BTH-003-A', 'daily_min' => 3,  'daily_max' => 8],  // Aqua - medium
            'PRD-004' => ['batch' => 'BTH-004-A', 'daily_min' => 10, 'daily_max' => 22], // Mie Sedaap - laku
            'PRD-005' => ['batch' => 'BTH-005-A', 'daily_min' => 5,  'daily_max' => 12], // Pocari - medium
            'PRD-006' => ['batch' => 'BTH-006-A', 'daily_min' => 3,  'daily_max' => 7],  // Chitato - medium
            'PRD-007' => ['batch' => 'BTH-007-A', 'daily_min' => 2,  'daily_max' => 6],  // Oreo - medium-low
            'PRD-008' => ['batch' => 'BTH-008-A', 'daily_min' => 0,  'daily_max' => 1],  // Kecap - slow
            'PRD-009' => ['batch' => 'BTH-009-A', 'daily_min' => 0,  'daily_max' => 1],  // Sarden - slow
            'PRD-010' => ['batch' => 'BTH-010-A', 'daily_min' => 0,  'daily_max' => 2],  // Saos - slow
        ];

        $outboundNum = 1;
        $totalSoldPerBatch = array_fill_keys(array_column(
            array_map(fn($k, $v) => ['k' => $v['batch']], array_keys($salesProfile), $salesProfile),
            'k'
        ), 0);

        // Generate transactions for last 30 days
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            // Skip some days randomly to simulate weekends/no-sales days
            if ($i % 7 === 0 && $i > 0) continue; // skip one day per week

            $outboundNumberStr = 'OUT-' . str_pad($outboundNum, 4, '0', STR_PAD_LEFT);

            // Only create outbound if at least one product has sales that day
            $lineItems = [];
            foreach ($salesProfile as $produkId => $profile) {
                $qty = rand($profile['daily_min'], $profile['daily_max']);
                if ($qty <= 0) continue;

                // Ensure we don't exceed available batch stock
                $currentBatchStok = DB::table('t_batch_inbounds')
                    ->where('batch_number', $profile['batch'])
                    ->value('stok_sisa_batch');

                if ($currentBatchStok === null || $currentBatchStok < $qty) continue;

                $lineItems[] = [
                    'produk_id'    => $produkId,
                    'batch_number' => $profile['batch'],
                    'qty_keluar'   => $qty,
                ];
            }

            if (empty($lineItems)) continue;

            // Insert outbound header
            $outboundId = DB::table('t_outbounds')->insertGetId([
                'outbound_number' => $outboundNumberStr,
                'tujuan'          => collect(['Toko Berkah', 'Minimarket Sejahtera', 'Warung Pak Budi', 'Alfamidi Cabang 3', 'Toko Makmur'])->random(),
                'tanggal_keluar'  => $date,
                'created_at'      => $date . ' 09:00:00',
                'updated_at'      => $date . ' 09:00:00',
            ]);

            // Insert outbound details & reduce batch stock
            foreach ($lineItems as $item) {
                DB::table('t_outbound_details')->insert([
                    'outbound_id'  => $outboundId,
                    'produk_id'    => $item['produk_id'],
                    'batch_number' => $item['batch_number'],
                    'qty_keluar'   => $item['qty_keluar'],
                    'created_at'   => $date . ' 09:00:00',
                    'updated_at'   => $date . ' 09:00:00',
                ]);

                // Reduce batch stock
                DB::table('t_batch_inbounds')
                    ->where('batch_number', $item['batch_number'])
                    ->decrement('stok_sisa_batch', $item['qty_keluar']);
            }

            $outboundNum++;
        }

        $this->command->info('✅ Demo data seeded: ' . ($outboundNum - 1) . ' outbound transactions created.');
        $this->command->info('   Products: ' . DB::table('m_products')->count());
        $this->command->info('   Batches : ' . DB::table('t_batch_inbounds')->count());
        $this->command->info('   Outbound Details: ' . DB::table('t_outbound_details')->count());
    }
}
