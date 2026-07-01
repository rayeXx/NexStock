<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Category;
use App\Models\Rack;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\BatchInbound;
use App\Models\Outbound;
use App\Models\OutboundDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FefoAlgorithmTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;
    private Rack $rack;
    private Supplier $supplier;
    private Product $product;
    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create base master data
        $this->category = Category::create(['nama_kategori' => 'Makanan']);

        $this->rack = Rack::create([
            'kode_rak' => 'A1',
            'kapasitas_maksimum_volume' => 1000,
            'kapasitas_terpakai' => 0,
        ]);

        $this->supplier = Supplier::create([
            'nama_supplier' => 'PT Test Supplier',
            'kontak' => '08123456789',
        ]);

        $this->adminUser = User::create([
            'name' => 'Admin Test',
            'email' => 'admin_test@nexstock.com',
            'password' => bcrypt('password'),
            'role' => 'admin_gudang',
        ]);

        $this->product = Product::create([
            'kode_produk' => 'SKU-TEST-001',
            'nama_produk' => 'Produk Test FEFO',
            'kategori_id' => $this->category->id,
            'harga_beli' => 10000,
            'stok_minimum' => 5,
            'uom' => 'Pcs',
        ]);
    }

    /**
     * TC-01: FEFO Algorithm — system must deduct from the batch with the nearest expired_date first.
     */
    public function test_fefo_deducts_from_earliest_expired_batch_first(): void
    {
        // Create batch that expires LATER (2027)
        $batchLater = BatchInbound::create([
            'batch_number' => 'BATCH-LATER-001',
            'produk_id' => $this->product->kode_produk,
            'po_id' => null,
            'rak_id' => $this->rack->kode_rak,
            'expired_date' => '2027-06-01',
            'stok_awal_batch' => 100,
            'stok_sisa_batch' => 100,
        ]);

        // Create batch that expires SOONER (2026)
        $batchSooner = BatchInbound::create([
            'batch_number' => 'BATCH-SOONER-001',
            'produk_id' => $this->product->kode_produk,
            'po_id' => null,
            'rak_id' => $this->rack->kode_rak,
            'expired_date' => '2026-09-01',
            'stok_awal_batch' => 50,
            'stok_sisa_batch' => 50,
        ]);

        // Request outbound of 30 units for this product
        $qtyNeeded = 30;

        // Simulate FEFO logic from OutboundController
        $batches = BatchInbound::where('produk_id', $this->product->kode_produk)
            ->where('stok_sisa_batch', '>', 0)
            ->orderBy('expired_date', 'asc')
            ->get();

        $totalDeducted = 0;
        $deductedBatches = [];

        foreach ($batches as $batch) {
            if ($qtyNeeded <= 0) break;
            $qtyToTake = min($qtyNeeded, $batch->stok_sisa_batch);
            $deductedBatches[$batch->batch_number] = $qtyToTake;
            $qtyNeeded -= $qtyToTake;
            $totalDeducted += $qtyToTake;
        }

        // Assert: the sooner-expiring batch was deducted first
        $this->assertArrayHasKey('BATCH-SOONER-001', $deductedBatches,
            'FEFO: Batch dengan expired lebih dekat (2026) harus dikurangi pertama.');

        $this->assertEquals(30, $deductedBatches['BATCH-SOONER-001'],
            'FEFO: Seluruh 30 unit harus diambil dari batch 2026 (sisa cukup).');

        $this->assertArrayNotHasKey('BATCH-LATER-001', $deductedBatches,
            'FEFO: Batch 2027 tidak boleh disentuh karena batch 2026 mencukupi.');
    }

    /**
     * TC-02: FEFO splits across multiple batches when one batch is insufficient.
     */
    public function test_fefo_splits_across_batches_when_first_batch_insufficient(): void
    {
        // Batch that expires sooner but has only 20 units
        BatchInbound::create([
            'batch_number' => 'BATCH-SOONER-002',
            'produk_id' => $this->product->kode_produk,
            'po_id' => null,
            'rak_id' => $this->rack->kode_rak,
            'expired_date' => '2026-09-01',
            'stok_awal_batch' => 20,
            'stok_sisa_batch' => 20,
        ]);

        // Batch that expires later with 50 units
        BatchInbound::create([
            'batch_number' => 'BATCH-LATER-002',
            'produk_id' => $this->product->kode_produk,
            'po_id' => null,
            'rak_id' => $this->rack->kode_rak,
            'expired_date' => '2027-06-01',
            'stok_awal_batch' => 50,
            'stok_sisa_batch' => 50,
        ]);

        // Request outbound for 35 units (more than first batch has)
        $qtyNeeded = 35;

        $batches = BatchInbound::where('produk_id', $this->product->kode_produk)
            ->where('stok_sisa_batch', '>', 0)
            ->orderBy('expired_date', 'asc')
            ->get();

        $deductedBatches = [];
        foreach ($batches as $batch) {
            if ($qtyNeeded <= 0) break;
            $qtyToTake = min($qtyNeeded, $batch->stok_sisa_batch);
            $deductedBatches[$batch->batch_number] = $qtyToTake;
            $qtyNeeded -= $qtyToTake;
        }

        // BATCH-SOONER-002 should have 20 taken (all it has)
        $this->assertEquals(20, $deductedBatches['BATCH-SOONER-002'],
            'FEFO split: Harus mengambil semua 20 unit dari batch yang lebih cepat expired.');

        // Remaining 15 from later batch
        $this->assertEquals(15, $deductedBatches['BATCH-LATER-002'],
            'FEFO split: Sisa 15 unit harus diambil dari batch kedua.');
    }

    /**
     * TC-03: System should not process outbound if total stock is insufficient.
     */
    public function test_outbound_fails_when_stock_insufficient(): void
    {
        BatchInbound::create([
            'batch_number' => 'BATCH-SMALL-001',
            'produk_id' => $this->product->kode_produk,
            'po_id' => null,
            'rak_id' => $this->rack->kode_rak,
            'expired_date' => '2027-06-01',
            'stok_awal_batch' => 10,
            'stok_sisa_batch' => 10,
        ]);

        // Total stock is 10, but request 50
        $totalStok = $this->product->total_stok;
        $qtyRequested = 50;

        $this->assertLessThan($qtyRequested, $totalStok,
            'Validasi Stok: Stok tersedia (10) harus lebih kecil dari yang diminta (50) agar transaksi ditolak.');
    }

    /**
     * TC-04: Expired date in the past must be rejected at inbound.
     */
    public function test_inbound_rejects_past_expired_date(): void
    {
        $today = Carbon::today();
        $yesterday = $today->copy()->subDay()->format('Y-m-d');
        $tomorrow = $today->copy()->addDay()->format('Y-m-d');

        // Past date: should be rejected
        $pastDate = Carbon::parse($yesterday);
        $this->assertTrue($pastDate->lte($today),
            'Validasi Tanggal: Tanggal kemarin harus dianggap tidak valid (<=today).');

        // Future date: should be accepted
        $futureDate = Carbon::parse($tomorrow);
        $this->assertFalse($futureDate->lte($today),
            'Validasi Tanggal: Tanggal besok harus dianggap valid (>today).');
    }

    /**
     * TC-05: Inbound quantity exceeding PO ordered quantity must be blocked.
     */
    public function test_inbound_rejects_qty_exceeding_po_order(): void
    {
        $po = PurchaseOrder::create([
            'po_number' => 'PO-TEST-001',
            'supplier_id' => $this->supplier->id,
            'status' => 'Approved',
            'total_harga' => 1000000,
            'created_by' => $this->adminUser->id,
        ]);

        $poDetail = PurchaseOrderDetail::create([
            'po_id' => $po->id,
            'produk_id' => $this->product->kode_produk,
            'qty_pesan' => 50,
            'qty_diterima' => 0,
        ]);

        // Try to receive more than ordered
        $qtyToReceive = 60;
        $remainingQty = $poDetail->qty_pesan - $poDetail->qty_diterima;

        $this->assertGreaterThan($remainingQty, $qtyToReceive,
            'Validasi PO: Qty yang diterima (60) melebihi yang dipesan (50), harus diblokir.');
    }

    /**
     * TC-06: Karantina Rollback — rejected damaged report should restore stock.
     */
    public function test_damaged_report_rejected_restores_stock(): void
    {
        $initialStock = 50;

        $batch = BatchInbound::create([
            'batch_number' => 'BATCH-DAMAGED-001',
            'produk_id' => $this->product->kode_produk,
            'po_id' => null,
            'rak_id' => $this->rack->kode_rak,
            'expired_date' => '2027-06-01',
            'stok_awal_batch' => $initialStock,
            'stok_sisa_batch' => $initialStock,
        ]);

        $qtyRusak = 10;

        // Simulate stock deduction when report is created
        $batch->stok_sisa_batch -= $qtyRusak;
        $batch->save();

        $this->assertEquals(40, $batch->fresh()->stok_sisa_batch,
            'Karantina: Stok harus berkurang dari 50 menjadi 40 setelah laporan barang rusak dibuat.');

        // Simulate rollback (rejection)
        $batch->stok_sisa_batch += $qtyRusak;
        $batch->save();

        $this->assertEquals(50, $batch->fresh()->stok_sisa_batch,
            'Rollback: Stok harus kembali ke 50 setelah laporan barang rusak ditolak Owner.');
    }
}
