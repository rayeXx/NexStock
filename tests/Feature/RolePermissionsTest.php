<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Rack;
use App\Models\BatchInbound;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\DamagedReport;
use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionsTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private User $staffUser;
    private User $ownerUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Disable CSRF token verification for feature tests (web routes)
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        $this->adminUser = User::factory()->create(['role' => 'admin_gudang']);
        $this->staffUser = User::factory()->create(['role' => 'staff_gudang']);
        $this->ownerUser = User::factory()->create(['role' => 'owner']);
    }

    /**
     * 1. Admin cannot create or update a user with role 'owner'
     */
    public function test_admin_cannot_create_user_with_owner_role(): void
    {
        $response = $this->actingAs($this->adminUser)->post('/user', [
            'name' => 'New Owner User',
            'email' => 'newowner@nexstock.com',
            'role' => 'owner',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_cannot_update_user_to_owner_role(): void
    {
        $otherUser = User::factory()->create(['role' => 'staff_gudang']);

        $response = $this->actingAs($this->adminUser)->put("/user/{$otherUser->id}", [
            'name' => 'Updated Staff',
            'email' => $otherUser->email,
            'role' => 'owner',
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_cannot_view_owner_edit_page(): void
    {
        $response = $this->actingAs($this->adminUser)->get("/user/{$this->ownerUser->id}/edit");

        $response->assertStatus(403);
    }

    public function test_admin_cannot_delete_owner_account(): void
    {
        $response = $this->actingAs($this->adminUser)->delete("/user/{$this->ownerUser->id}");

        $response->assertStatus(403);
    }

    /**
     * 2. Admin cannot process receiving/inbound
     */
    public function test_admin_cannot_process_inbound_receiving(): void
    {
        $category = Category::create(['nama_kategori' => 'Gadgets']);
        $product = Product::create([
            'kode_produk' => 'PRD-001',
            'nama_produk' => 'Phone X',
            'kategori_id' => $category->id,
            'harga_beli' => 5000000,
            'stok_minimum' => 5,
            'uom' => 'Pcs',
        ]);

        $supplier = Supplier::create([
            'nama_supplier' => 'Global Corp',
            'kontak' => '0812345678',
        ]);

        $po = PurchaseOrder::create([
            'po_number' => 'PO-001',
            'supplier_id' => $supplier->id,
            'status' => 'Ordered',
            'total_harga' => 50000000,
            'created_by' => $this->adminUser->id,
        ]);

        PurchaseOrderDetail::create([
            'po_id' => $po->id,
            'produk_id' => $product->kode_produk,
            'qty_pesan' => 10,
            'qty_diterima' => 0,
        ]);

        $response = $this->actingAs($this->adminUser)->post('/inbound/store', [
            'po_id' => $po->id,
            'items' => [
                [
                    'produk_id' => $product->kode_produk,
                    'qty_datang' => 10,
                    'qty_rusak' => 0,
                ]
            ]
        ]);

        $response->assertStatus(403);
    }

    public function test_owner_cannot_process_inbound_receiving(): void
    {
        $responseCreate = $this->actingAs($this->ownerUser)->get('/inbound/create');
        $responseCreate->assertStatus(403);

        $responseStore = $this->actingAs($this->ownerUser)->post('/inbound/store', [
            'po_id' => 1,
            'items' => []
        ]);
        $responseStore->assertStatus(403);
    }

    public function test_admin_cannot_access_inbound_create(): void
    {
        $responseCreate = $this->actingAs($this->adminUser)->get('/inbound/create');
        $responseCreate->assertStatus(403);
    }

    /**
     * 3. Staff cannot create outbound request (store)
     */
    public function test_staff_cannot_create_outbound_request(): void
    {
        $response = $this->actingAs($this->staffUser)->post('/outbound/store', [
            'tujuan' => 'Toko Cabang',
            'items' => [
                [
                    'produk_id' => 'PRD-001',
                    'batch_number' => 'BATCH-001',
                    'qty_keluar' => 5,
                ]
            ]
        ]);

        $response->assertStatus(403);
    }

    /**
     * 3b. Admin cannot confirm outbound
     */
    public function test_admin_cannot_confirm_outbound(): void
    {
        $category = Category::create(['nama_kategori' => 'Cat-Blind-Admin']);
        $product  = Product::create([
            'kode_produk'  => 'PRD-BLIND-ADMIN',
            'nama_produk'  => 'Produk Anti-Blind Admin',
            'kategori_id'  => $category->id,
            'harga_beli'   => 5000,
            'stok_minimum' => 1,
            'uom'          => 'Pcs',
        ]);
        $rack = Rack::create([
            'kode_rak'                 => 'RAK-BLIND-ADMIN',
            'nama_rak'                 => 'Rak Blind Test Admin',
            'kapasitas_maksimum_volume' => 100,
        ]);
        $batch = \App\Models\BatchInbound::create([
            'batch_number'    => 'BATCH-ADMIN-001',
            'produk_id'       => $product->kode_produk,
            'rak_id'          => $rack->kode_rak,
            'stok_awal_batch' => 20,
            'stok_sisa_batch' => 20,
            'expired_date'    => now()->addMonths(6)->format('Y-m-d'),
        ]);

        $outbound = \App\Models\Outbound::create([
            'outbound_number' => 'OUT-ADMIN-001',
            'tujuan' => 'Toko Admin',
            'tanggal_keluar' => now()->format('Y-m-d'),
            'status' => 'Pending',
        ]);

        $outbound->details()->create([
            'produk_id' => $product->kode_produk,
            'batch_number' => 'BATCH-ADMIN-001',
            'qty_keluar' => 5,
            'rak_id' => $rack->kode_rak,
        ]);

        $response = $this->actingAs($this->adminUser)->post('/outbound/' . $outbound->id . '/confirm', [
            'batch_scanned' => ['BATCH-ADMIN-001']
        ]);

        $response->assertStatus(403);
    }

    /**
     * 4. Only staff can create a damaged report, and it defaults to Pending status
     */
    public function test_staff_can_create_damaged_report_as_pending(): void
    {
        $category = Category::create(['nama_kategori' => 'Gadgets']);
        $product = Product::create([
            'kode_produk' => 'PRD-002',
            'nama_produk' => 'Laptop Pro',
            'kategori_id' => $category->id,
            'harga_beli' => 15000000,
            'stok_minimum' => 2,
            'uom' => 'Pcs',
        ]);

        $rack = Rack::create([
            'kode_rak' => 'RAK-A1',
            'nama_rak' => 'Rak Utama',
            'kapasitas_maksimum_volume' => 100,
            'kapasitas_terpakai' => 20,
        ]);

        $batch = BatchInbound::create([
            'batch_number' => 'BTC-TEST-01',
            'produk_id' => $product->kode_produk,
            'po_id' => null,
            'rak_id' => $rack->kode_rak,
            'expired_date' => '2027-12-31',
            'stok_awal_batch' => 20,
            'stok_sisa_batch' => 20,
        ]);

        // Upload fake image (using create to avoid GD/imagejpeg dependency in some environments)
        $file = \Illuminate\Http\UploadedFile::fake()->create('proof.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($this->staffUser)->post('/damaged/store', [
            'batch_number' => $batch->batch_number,
            'qty_rusak' => 5,
            'alasan' => 'Pecah di sudut',
            'foto_bukti' => $file,
        ]);

        $response->assertRedirect('/damaged');
        
        $this->assertDatabaseHas('t_damaged_reports', [
            'batch_number' => $batch->batch_number,
            'qty_rusak' => 5,
            'status' => 'Pending',
        ]);

        // Stock should be immediately deducted, but rack capacity should remain unchanged (Pending status)
        $this->assertEquals(15, $batch->fresh()->stok_sisa_batch);
        $this->assertEquals(20, $rack->fresh()->kapasitas_terpakai);
    }

    /**
     * 5. Admin can approve or reject damaged reports
     */
    public function test_admin_can_approve_damaged_report(): void
    {
        $category = Category::create(['nama_kategori' => 'Gadgets']);
        $product = Product::create([
            'kode_produk' => 'PRD-002',
            'nama_produk' => 'Laptop Pro',
            'kategori_id' => $category->id,
            'harga_beli' => 15000000,
            'stok_minimum' => 2,
            'uom' => 'Pcs',
        ]);

        $rack = Rack::create([
            'kode_rak' => 'RAK-A1',
            'nama_rak' => 'Rak Utama',
            'kapasitas_maksimum_volume' => 100,
            'kapasitas_terpakai' => 20,
        ]);

        $batch = BatchInbound::create([
            'batch_number' => 'BTC-TEST-01',
            'produk_id' => $product->kode_produk,
            'po_id' => null,
            'rak_id' => $rack->kode_rak,
            'expired_date' => '2027-12-31',
            'stok_awal_batch' => 20,
            'stok_sisa_batch' => 15,
        ]);

        $report = DamagedReport::create([
            'produk_id' => $product->kode_produk,
            'batch_number' => $batch->batch_number,
            'rak_id' => $rack->kode_rak,
            'qty_rusak' => 5,
            'foto_bukti' => 'damaged_reports/proof.jpg',
            'alasan' => 'Cacat pabrik',
            'status' => 'Pending',
            'created_by' => $this->staffUser->id,
        ]);

        $response = $this->actingAs($this->adminUser)->post("/damaged/{$report->id}/approve");

        $response->assertRedirect('/damaged');
        $this->assertEquals('Approved', $report->fresh()->status);
        
        // Rack capacity should be reduced upon approval
        $this->assertEquals(15, $rack->fresh()->kapasitas_terpakai);
    }

    public function test_admin_can_reject_damaged_report_and_restore_stock(): void
    {
        $category = Category::create(['nama_kategori' => 'Gadgets']);
        $product = Product::create([
            'kode_produk' => 'PRD-002',
            'nama_produk' => 'Laptop Pro',
            'kategori_id' => $category->id,
            'harga_beli' => 15000000,
            'stok_minimum' => 2,
            'uom' => 'Pcs',
        ]);

        $rack = Rack::create([
            'kode_rak' => 'RAK-A1',
            'nama_rak' => 'Rak Utama',
            'kapasitas_maksimum_volume' => 100,
            'kapasitas_terpakai' => 20, // Under new rules, not deducted when Pending
        ]);

        $batch = BatchInbound::create([
            'batch_number' => 'BTC-TEST-01',
            'produk_id' => $product->kode_produk,
            'po_id' => null,
            'rak_id' => $rack->kode_rak,
            'expired_date' => '2027-12-31',
            'stok_awal_batch' => 20,
            'stok_sisa_batch' => 15, // Already deducted
        ]);

        $report = DamagedReport::create([
            'produk_id' => $product->kode_produk,
            'batch_number' => $batch->batch_number,
            'rak_id' => $rack->kode_rak,
            'qty_rusak' => 5,
            'foto_bukti' => 'damaged_reports/proof.jpg',
            'alasan' => 'Cacat pabrik',
            'status' => 'Pending',
            'created_by' => $this->staffUser->id,
        ]);

        $response = $this->actingAs($this->adminUser)->post("/damaged/{$report->id}/reject");

        $response->assertRedirect('/damaged');
        $this->assertEquals('Rejected', $report->fresh()->status);

        // Stock should be restored, rack capacity remains unchanged (never deducted)
        $this->assertEquals(20, $batch->fresh()->stok_sisa_batch);
        $this->assertEquals(20, $rack->fresh()->kapasitas_terpakai);
    }

    /**
     * Test separate destruction workflow: admin assigns, staff confirms with photo upload.
     */
    public function test_admin_can_assign_destruction_and_staff_can_confirm_destruction(): void
    {
        $category = Category::create(['nama_kategori' => 'Gadgets']);
        $product = Product::create([
            'kode_produk' => 'PRD-MUT-01',
            'nama_produk' => 'Test Product',
            'kategori_id' => $category->id,
            'harga_beli' => 10000,
            'stok_minimum' => 2,
            'uom' => 'Pcs',
        ]);
        $rack = Rack::create([
            'kode_rak' => 'RAK-MUT-01',
            'nama_rak' => 'Rak Test',
            'kapasitas_maksimum_volume' => 100,
            'kapasitas_terpakai' => 20,
        ]);
        $batch = BatchInbound::create([
            'batch_number' => 'BTC-MUT-01',
            'produk_id' => $product->kode_produk,
            'po_id' => null,
            'rak_id' => $rack->kode_rak,
            'expired_date' => '2027-12-31',
            'stok_awal_batch' => 20,
            'stok_sisa_batch' => 15,
        ]);
        $report = DamagedReport::create([
            'produk_id' => $product->kode_produk,
            'batch_number' => $batch->batch_number,
            'rak_id' => $rack->kode_rak,
            'qty_rusak' => 5,
            'foto_bukti' => 'damaged_reports/proof.jpg',
            'alasan' => 'Broken',
            'status' => 'Pending',
            'created_by' => $this->staffUser->id,
        ]);

        // Admin approves damaged report (which should automatically create a Belum Ditugaskan destruction)
        $responseApprove = $this->actingAs($this->adminUser)->post("/damaged/{$report->id}/approve");
        $responseApprove->assertRedirect('/damaged');
        $this->assertEquals('Approved', $report->fresh()->status);

        $this->assertDatabaseHas('t_destructions', [
            'damaged_report_id' => $report->id,
            'produk_id' => $product->kode_produk,
            'qty_dimusnahkan' => 5,
            'status' => 'Belum Ditugaskan',
        ]);

        $destruction = \App\Models\Destruction::where('damaged_report_id', $report->id)->firstOrFail();

        // Admin assigns destruction
        $responseAssign = $this->actingAs($this->adminUser)->post("/destruction/{$destruction->id}/assign", [
            'catatan_pemusnahan' => 'Please burn this item.',
        ]);

        $responseAssign->assertRedirect('/destruction');

        $this->assertDatabaseHas('t_destructions', [
            'id' => $destruction->id,
            'catatan_pemusnahan' => 'Please burn this item.',
            'status' => 'Menunggu Konfirmasi',
        ]);

        // Staff confirms destruction with photo proof
        $file = \Illuminate\Http\UploadedFile::fake()->create('destruction_proof.jpg', 150, 'image/jpeg');

        $responseStaff = $this->actingAs($this->staffUser)->post("/destruction/{$destruction->id}/confirm", [
            'foto_pemusnahan' => $file,
        ]);

        $responseStaff->assertRedirect('/destruction');
        $this->assertEquals('Selesai', $destruction->fresh()->status);
        $this->assertNotNull($destruction->fresh()->foto_pemusnahan);
        $this->assertEquals($this->staffUser->id, $destruction->fresh()->confirmed_by);
    }

    public function test_non_staff_cannot_confirm_destruction(): void
    {
        $category = Category::create(['nama_kategori' => 'Gadgets']);
        $product = Product::create([
            'kode_produk' => 'PRD-MUT-02',
            'nama_produk' => 'Test Product 2',
            'kategori_id' => $category->id,
            'harga_beli' => 10000,
            'stok_minimum' => 2,
            'uom' => 'Pcs',
        ]);
        $rack = Rack::create([
            'kode_rak' => 'RAK-MUT-02',
            'nama_rak' => 'Rak Test 2',
            'kapasitas_maksimum_volume' => 100,
            'kapasitas_terpakai' => 20,
        ]);
        $batch = BatchInbound::create([
            'batch_number' => 'BTC-MUT-02',
            'produk_id' => $product->kode_produk,
            'po_id' => null,
            'rak_id' => $rack->kode_rak,
            'expired_date' => '2027-12-31',
            'stok_awal_batch' => 20,
            'stok_sisa_batch' => 15,
        ]);
        $report = DamagedReport::create([
            'produk_id' => $product->kode_produk,
            'batch_number' => $batch->batch_number,
            'rak_id' => $rack->kode_rak,
            'qty_rusak' => 5,
            'foto_bukti' => 'damaged_reports/proof.jpg',
            'alasan' => 'Broken',
            'status' => 'Destruction Assigned',
            'created_by' => $this->staffUser->id,
        ]);

        $destruction = \App\Models\Destruction::create([
            'damaged_report_id' => $report->id,
            'produk_id' => $product->kode_produk,
            'batch_number' => $batch->batch_number,
            'rak_id' => $rack->kode_rak,
            'qty_dimusnahkan' => 5,
            'alasan' => 'Broken',
            'catatan_pemusnahan' => 'Burn',
            'assigned_by' => $this->adminUser->id,
            'assigned_at' => now(),
            'status' => 'Menunggu Konfirmasi',
        ]);

        $file = \Illuminate\Http\UploadedFile::fake()->create('destruction_proof.jpg', 150, 'image/jpeg');

        // Admin tries to confirm (should fail)
        $responseAdmin = $this->actingAs($this->adminUser)->post("/destruction/{$destruction->id}/confirm", [
            'foto_pemusnahan' => $file,
        ]);
        $responseAdmin->assertStatus(403);

        // Owner tries to confirm (should fail)
        $responseOwner = $this->actingAs($this->ownerUser)->post("/destruction/{$destruction->id}/confirm", [
            'foto_pemusnahan' => $file,
        ]);
        $responseOwner->assertStatus(403);
    }

    /**
     * Test automatic isolation of expired batches and staff confirmation.
     */
    public function test_auto_isolate_expired_batches_and_workflow(): void
    {
        $category = Category::create(['nama_kategori' => 'Electronics']);
        $product = Product::create([
            'kode_produk' => 'PRD-EXP-01',
            'nama_produk' => 'Expired Product',
            'kategori_id' => $category->id,
            'harga_beli' => 20000,
            'stok_minimum' => 2,
            'uom' => 'Pcs',
        ]);
        $rack = Rack::create([
            'kode_rak' => 'RAK-EXP-01',
            'nama_rak' => 'Rak Expired',
            'kapasitas_maksimum_volume' => 100,
            'kapasitas_terpakai' => 10,
        ]);
        $batch = BatchInbound::create([
            'batch_number' => 'BTC-EXP-01',
            'produk_id' => $product->kode_produk,
            'po_id' => null,
            'rak_id' => $rack->kode_rak,
            'expired_date' => \Carbon\Carbon::yesterday()->format('Y-m-d'),
            'stok_awal_batch' => 10,
            'stok_sisa_batch' => 10,
        ]);

        // Accessing the damaged reports index should trigger automatic isolation
        $this->actingAs($this->adminUser)->get('/damaged');

        $this->assertDatabaseHas('t_damaged_reports', [
            'batch_number' => $batch->batch_number,
            'qty_rusak' => 10,
            'status' => 'Expired Pending Check',
            'alasan' => 'Expired',
        ]);

        // Batch stock should be 0
        $this->assertEquals(0, $batch->fresh()->stok_sisa_batch);

        $report = DamagedReport::where('batch_number', $batch->batch_number)->firstOrFail();

        // Staff confirms by uploading photo
        $file = \Illuminate\Http\UploadedFile::fake()->create('expired_proof.jpg', 100, 'image/jpeg');
        $responseConfirm = $this->actingAs($this->staffUser)->post("/damaged/{$report->id}/confirm-expired", [
            'foto_bukti' => $file,
        ]);

        $responseConfirm->assertRedirect('/damaged');
        $this->assertEquals('Pending', $report->fresh()->status);
        $this->assertNotNull($report->fresh()->foto_bukti);

        // Admin approves the report
        $responseApprove = $this->actingAs($this->adminUser)->post("/damaged/{$report->id}/approve");
        $responseApprove->assertRedirect('/damaged');
        $this->assertEquals('Approved', $report->fresh()->status);

        // Rack capacity should be reduced from 10 to 0
        $this->assertEquals(0, $rack->fresh()->kapasitas_terpakai);
    }

    /**
     * 6. Stock Opname security and approval workflow
     */
    public function test_staff_can_create_stock_opname_as_pending_approval(): void
    {
        $category = Category::create(['nama_kategori' => 'Gadgets']);
        $product = Product::create([
            'kode_produk' => 'PRD-003',
            'nama_produk' => 'Tablet Pro',
            'kategori_id' => $category->id,
            'harga_beli' => 8000000,
            'stok_minimum' => 2,
            'uom' => 'Pcs',
        ]);

        $rack = Rack::create([
            'kode_rak' => 'RAK-B1',
            'nama_rak' => 'Rak B',
            'kapasitas_maksimum_volume' => 100,
            'kapasitas_terpakai' => 20,
        ]);

        $batch = BatchInbound::create([
            'batch_number' => 'BTC-TEST-OPNAME-01',
            'produk_id' => $product->kode_produk,
            'po_id' => null,
            'rak_id' => $rack->kode_rak,
            'expired_date' => '2027-12-31',
            'stok_awal_batch' => 20,
            'stok_sisa_batch' => 20,
        ]);

        $response = $this->actingAs($this->staffUser)->post('/opname/store', [
            'items' => [
                [
                    'batch_number' => $batch->batch_number,
                    'qty_fisik' => 15, // system has 20, physically we count 15
                    'catatan' => 'Lost some'
                ]
            ]
        ]);

        $response->assertRedirect('/opname');
        
        $this->assertDatabaseHas('t_stock_opnames', [
            'status' => 'Pending Approval',
            'created_by' => $this->staffUser->id,
        ]);

        // Stock and rack capacity MUST NOT be changed yet
        $this->assertEquals(20, $batch->fresh()->stok_sisa_batch);
        $this->assertEquals(20, $rack->fresh()->kapasitas_terpakai);
    }

    public function test_admin_can_approve_stock_opname(): void
    {
        $category = Category::create(['nama_kategori' => 'Gadgets']);
        $product = Product::create([
            'kode_produk' => 'PRD-003',
            'nama_produk' => 'Tablet Pro',
            'kategori_id' => $category->id,
            'harga_beli' => 8000000,
            'stok_minimum' => 2,
            'uom' => 'Pcs',
        ]);

        $rack = Rack::create([
            'kode_rak' => 'RAK-B1',
            'nama_rak' => 'Rak B',
            'kapasitas_maksimum_volume' => 100,
            'kapasitas_terpakai' => 20,
        ]);

        $batch = BatchInbound::create([
            'batch_number' => 'BTC-TEST-OPNAME-01',
            'produk_id' => $product->kode_produk,
            'po_id' => null,
            'rak_id' => $rack->kode_rak,
            'expired_date' => '2027-12-31',
            'stok_awal_batch' => 20,
            'stok_sisa_batch' => 20,
        ]);

        $opname = StockOpname::create([
            'tanggal_opname' => '2026-07-06',
            'created_by' => $this->staffUser->id,
            'status' => 'Pending Approval',
        ]);

        StockOpnameDetail::create([
            'stock_opname_id' => $opname->id,
            'produk_id' => $product->kode_produk,
            'batch_number' => $batch->batch_number,
            'qty_sistem' => 20,
            'qty_fisik' => 15,
            'selisih' => -5,
            'catatan' => 'Missing',
        ]);

        $response = $this->actingAs($this->adminUser)->post("/opname/{$opname->id}/approve");

        $response->assertRedirect("/opname/{$opname->id}");
        $this->assertEquals('Approved', $opname->fresh()->status);
        $this->assertEquals($this->adminUser->id, $opname->fresh()->approved_by);

        // Stock and rack capacity MUST be adjusted
        $this->assertEquals(15, $batch->fresh()->stok_sisa_batch);
        $this->assertEquals(15, $rack->fresh()->kapasitas_terpakai);
    }

    public function test_staff_cannot_approve_stock_opname(): void
    {
        $category = Category::create(['nama_kategori' => 'Gadgets']);
        $product = Product::create([
            'kode_produk' => 'PRD-003',
            'nama_produk' => 'Tablet Pro',
            'kategori_id' => $category->id,
            'harga_beli' => 8000000,
            'stok_minimum' => 2,
            'uom' => 'Pcs',
        ]);

        $rack = Rack::create([
            'kode_rak' => 'RAK-B1',
            'nama_rak' => 'Rak B',
            'kapasitas_maksimum_volume' => 100,
            'kapasitas_terpakai' => 20,
        ]);

        $batch = BatchInbound::create([
            'batch_number' => 'BTC-TEST-OPNAME-01',
            'produk_id' => $product->kode_produk,
            'po_id' => null,
            'rak_id' => $rack->kode_rak,
            'expired_date' => '2027-12-31',
            'stok_awal_batch' => 20,
            'stok_sisa_batch' => 20,
        ]);

        $opname = StockOpname::create([
            'tanggal_opname' => '2026-07-06',
            'created_by' => $this->staffUser->id,
            'status' => 'Pending Approval',
        ]);

        $response = $this->actingAs($this->staffUser)->post("/opname/{$opname->id}/approve");

        $response->assertStatus(403);
        $this->assertEquals('Pending Approval', $opname->fresh()->status);
    }

    public function test_staff_cannot_over_receive_inbound(): void
    {
        $category = Category::create(['nama_kategori' => 'Frozen Foods']);
        $product = Product::create([
            'kode_produk' => 'SKU-OVER-001',
            'nama_produk' => 'Frozen Nugget',
            'kategori_id' => $category->id,
            'harga_beli' => 20000,
            'stok_minimum' => 5,
            'uom' => 'Pcs',
        ]);

        $po = PurchaseOrder::create([
            'po_number' => 'PO-OVER-TEST',
            'supplier_id' => Supplier::create(['nama_supplier' => 'PT Nugget', 'kontak' => '123'])->id,
            'status' => 'Ordered',
            'total_harga' => 200000,
            'created_by' => $this->adminUser->id,
        ]);

        PurchaseOrderDetail::create([
            'po_id' => $po->id,
            'produk_id' => $product->kode_produk,
            'qty_pesan' => 10,
            'qty_diterima' => 4, // 6 remaining
        ]);

        // Attempting to post qty_datang = 8 (which is > remaining 6)
        $response = $this->actingAs($this->staffUser)->post('/inbound/store', [
            'po_id' => $po->id,
            'foto_bukti' => \Illuminate\Http\UploadedFile::fake()->create('bukti.jpg', 100, 'image/jpeg'),
            'items' => [
                [
                    'produk_id' => $product->kode_produk,
                    'qty_datang' => 8,
                    'qty_rusak' => 0,
                    'batch_supplier' => 'BATCH-SUPP-123',
                    'expired_date' => '2027-12-31',
                    'rak_id' => Rack::create(['kode_rak' => 'RAK-OVER', 'nama_rak' => 'Rak Over', 'kapasitas_maksimum_volume' => 100])->kode_rak,
                ]
            ]
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Jumlah barang datang tidak boleh melebihi sisa pesanan PO.');
    }

    // ===================================================================
    // TC-NEW-01: PO Custom Harga Satuan
    // ===================================================================
    /**
     * When Admin creates a PO with a custom harga_satuan, the system must
     * save that price to t_purchase_order_details and recalculate total_harga
     * using the custom price, NOT the master product price.
     */
    public function test_po_stores_custom_harga_satuan(): void
    {
        $category = Category::create(['nama_kategori' => 'Cat-PO-Price']);
        $product  = Product::create([
            'kode_produk'  => 'PRD-PRICE-01',
            'nama_produk'  => 'Produk Harga Test',
            'kategori_id'  => $category->id,
            'harga_beli'   => 10000, // master price Rp 10.000
            'stok_minimum' => 1,
            'uom'          => 'Pcs',
        ]);
        $supplier = Supplier::create(['nama_supplier' => 'Supplier Price Test', 'kontak' => '000']);

        // Admin creates PO with a custom price of Rp 12.500 (override)
        $response = $this->actingAs($this->adminUser)->post('/po/store', [
            'supplier_id'          => $supplier->id,
            'target_tanggal_kirim' => now()->addDays(7)->format('Y-m-d'),
            'items' => [
                [
                    'produk_id'    => $product->kode_produk,
                    'qty_pesan'    => 4,
                    'harga_satuan' => 12500, // custom price
                ]
            ]
        ]);

        $response->assertRedirect(route('po.index'));

        // Verify detail has the custom price
        $detail = \App\Models\PurchaseOrderDetail::where('produk_id', $product->kode_produk)->first();
        $this->assertNotNull($detail, 'PO detail should be created');
        $this->assertEquals(12500, $detail->harga_satuan, 'Custom harga_satuan must be stored as entered');

        // Verify total_harga = custom price * qty = 12500 * 4 = 50000
        $po = \App\Models\PurchaseOrder::find($detail->po_id);
        $this->assertEquals(50000, $po->total_harga, 'total_harga must reflect custom unit price');
    }

    // ===================================================================
    // TC-NEW-02: Anti-Blind Picking — Reject Wrong Batch
    // ===================================================================
    /**
     * When a staff submits the outbound confirmation with a batch_scanned
     * value that does NOT match the FEFO-allocated batch_number, the
     * system must redirect back with an error and NOT modify any stock.
     */
    public function test_outbound_confirm_rejects_wrong_batch_scan(): void
    {
        $category = Category::create(['nama_kategori' => 'Cat-Blind-01']);
        $product  = Product::create([
            'kode_produk'  => 'PRD-BLIND-01',
            'nama_produk'  => 'Produk Anti-Blind',
            'kategori_id'  => $category->id,
            'harga_beli'   => 5000,
            'stok_minimum' => 1,
            'uom'          => 'Pcs',
        ]);
        $rack = Rack::create([
            'kode_rak'                 => 'RAK-BLIND-01',
            'nama_rak'                 => 'Rak Blind Test',
            'kapasitas_maksimum_volume' => 100,
        ]);
        $batch = \App\Models\BatchInbound::create([
            'batch_number'    => 'BATCH-CORRECT-001',
            'produk_id'       => $product->kode_produk,
            'rak_id'          => $rack->kode_rak,
            'stok_awal_batch' => 20,
            'stok_sisa_batch' => 20,
            'expired_date'    => now()->addMonths(6)->format('Y-m-d'),
        ]);

        $outbound = \App\Models\Outbound::create([
            'outbound_number' => 'OUT-TEST-001',
            'tujuan' => 'Toko Test Blind',
            'tanggal_keluar' => now()->format('Y-m-d'),
            'status' => 'Pending',
        ]);

        $outbound->details()->create([
            'produk_id' => $product->kode_produk,
            'batch_number' => 'BATCH-CORRECT-001',
            'qty_keluar' => 5,
            'rak_id' => $rack->kode_rak,
        ]);

        \Illuminate\Support\Facades\Storage::fake('public');
        $file = \Illuminate\Http\UploadedFile::fake()->create('bukti.jpg', 100, 'image/jpeg');

        // Staff submits WRONG batch scan
        $response = $this->actingAs($this->staffUser)
            ->post('/outbound/' . $outbound->id . '/confirm', [
                'batch_scanned' => ['BATCH-WRONG-999'], // wrong!
                'bukti_foto' => $file,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Stock must NOT change
        $batch->refresh();
        $this->assertEquals(20, $batch->stok_sisa_batch, 'Stok harus tetap 20 karena batch scan salah');
    }

    // ===================================================================
    // TC-NEW-03: Anti-Blind Picking — Accept Correct Batch
    // ===================================================================
    /**
     * When a staff submits the confirmation with the EXACT correct batch_scanned,
     * the system must commit the outbound to DB, deduct the stock, and redirect
     * to the index with a success message.
     */
    public function test_outbound_confirm_succeeds_with_correct_batch_scan(): void
    {
        $category = Category::create(['nama_kategori' => 'Cat-Blind-02']);
        $product  = Product::create([
            'kode_produk'  => 'PRD-BLIND-02',
            'nama_produk'  => 'Produk Anti-Blind OK',
            'kategori_id'  => $category->id,
            'harga_beli'   => 5000,
            'stok_minimum' => 1,
            'uom'          => 'Pcs',
        ]);
        $rack = Rack::create([
            'kode_rak'                 => 'RAK-BLIND-02',
            'nama_rak'                 => 'Rak Blind Test OK',
            'kapasitas_maksimum_volume' => 100,
            'kapasitas_terpakai'       => 10,
        ]);
        $batch = \App\Models\BatchInbound::create([
            'batch_number'    => 'BATCH-MATCH-001',
            'produk_id'       => $product->kode_produk,
            'rak_id'          => $rack->kode_rak,
            'stok_awal_batch' => 10,
            'stok_sisa_batch' => 10,
            'expired_date'    => now()->addMonths(6)->format('Y-m-d'),
        ]);

        $outbound = \App\Models\Outbound::create([
            'outbound_number' => 'OUT-TEST-002',
            'tujuan' => 'Toko Test Confirmed',
            'tanggal_keluar' => now()->format('Y-m-d'),
            'status' => 'Pending',
        ]);

        $outbound->details()->create([
            'produk_id' => $product->kode_produk,
            'batch_number' => 'BATCH-MATCH-001',
            'qty_keluar' => 6,
            'rak_id' => $rack->kode_rak,
        ]);

        // Manually simulate the reservation deduction that happened in store()
        $batch->stok_sisa_batch -= 6;
        $batch->save();

        \Illuminate\Support\Facades\Storage::fake('public');
        $file = \Illuminate\Http\UploadedFile::fake()->create('bukti.jpg', 100, 'image/jpeg');

        // Staff submits CORRECT batch scan
        $response = $this->actingAs($this->staffUser)
            ->post('/outbound/' . $outbound->id . '/confirm', [
                'batch_scanned' => ['BATCH-MATCH-001'], // correct!
                'bukti_foto' => $file,
            ]);

        $response->assertRedirect(route('outbound.index'));
        $response->assertSessionHas('success');

        // Verify stock was deducted: 10 - 6 = 4
        $batch->refresh();
        $this->assertEquals(4, $batch->stok_sisa_batch, 'Stok harus berkurang 6 setelah konfirmasi berhasil');

        // Verify OutboundDetail was created with audit trail
        $detail = \App\Models\OutboundDetail::where('produk_id', $product->kode_produk)->first();
        $this->assertNotNull($detail, 'OutboundDetail harus dibuat');
        $this->assertEquals('BATCH-MATCH-001', $detail->batch_scanned, 'batch_scanned harus tersimpan sebagai audit trail');

        // Verify photo is stored
        $outbound->refresh();
        $this->assertNotNull($outbound->bukti_foto, 'Bukti foto harus tersimpan di database');
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($outbound->bukti_foto);
    }

    public function test_outbound_does_not_allow_quarantined_batches(): void
    {
        $category = Category::create(['nama_kategori' => 'Cat-Quarantine']);
        $product  = Product::create([
            'kode_produk'  => 'PRD-QUAR-01',
            'nama_produk'  => 'Produk Karantina',
            'kategori_id'  => $category->id,
            'harga_beli'   => 5000,
            'stok_minimum' => 1,
            'uom'          => 'Pcs',
        ]);
        $rack = Rack::create([
            'kode_rak'                 => 'RAK-QUAR-01',
            'nama_rak'                 => 'Rak Karantina Test',
            'kapasitas_maksimum_volume' => 100,
        ]);
        $batch = \App\Models\BatchInbound::create([
            'batch_number'    => 'BATCH-QUAR-001',
            'produk_id'       => $product->kode_produk,
            'rak_id'          => $rack->kode_rak,
            'stok_awal_batch' => 20,
            'stok_sisa_batch' => 20,
            'expired_date'    => now()->addMonths(6)->format('Y-m-d'),
        ]);

        // Create a Damaged Report for this batch (status Pending / quarantine)
        \App\Models\DamagedReport::create([
            'produk_id' => $product->kode_produk,
            'batch_number' => 'BATCH-QUAR-001',
            'rak_id' => $rack->kode_rak,
            'qty_rusak' => 5,
            'foto_bukti' => 'dummy.jpg',
            'alasan' => 'Pecah',
            'status' => 'Pending',
            'created_by' => $this->staffUser->id,
        ]);

        // Attempting to post outbound with this quarantined batch should redirect with error
        $response = $this->actingAs($this->adminUser)
            ->post('/outbound/store', [
                'tujuan' => 'Toko Cabang',
                'items' => [
                    [
                        'produk_id' => $product->kode_produk,
                        'batch_number' => 'BATCH-QUAR-001',
                        'qty_keluar' => 5,
                    ]
                ]
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_staff_gudang_must_provide_foto_bukti_for_inbound(): void
    {
        $category = Category::create(['nama_kategori' => 'Frozen Foods']);
        $product = Product::create([
            'kode_produk' => 'SKU-FOTO-001',
            'nama_produk' => 'Frozen Sausage',
            'kategori_id' => $category->id,
            'harga_beli' => 25000,
            'stok_minimum' => 5,
            'uom' => 'Pcs',
        ]);

        $po = PurchaseOrder::create([
            'po_number' => 'PO-FOTO-TEST',
            'supplier_id' => Supplier::create(['nama_supplier' => 'PT Sausage', 'kontak' => '123'])->id,
            'status' => 'Ordered',
            'total_harga' => 250000,
            'created_by' => $this->adminUser->id,
        ]);

        PurchaseOrderDetail::create([
            'po_id' => $po->id,
            'produk_id' => $product->kode_produk,
            'qty_pesan' => 10,
            'qty_diterima' => 0,
        ]);

        // Attempting to post without foto_bukti
        $response = $this->actingAs($this->staffUser)->post('/inbound/store', [
            'po_id' => $po->id,
            'items' => [
                [
                    'produk_id' => $product->kode_produk,
                    'qty_datang' => 5,
                    'qty_rusak' => 0,
                    'batch_supplier' => 'BATCH-SUPP-123',
                    'expired_date' => '2027-12-31',
                    'rak_id' => Rack::create(['kode_rak' => 'RAK-FOTO', 'nama_rak' => 'Rak Foto', 'kapasitas_maksimum_volume' => 100])->kode_rak,
                ]
            ]
        ]);

        $response->assertSessionHasErrors(['foto_bukti']);
    }

    public function test_staff_gudang_can_successfully_inbound_with_foto_bukti(): void
    {
        $category = Category::create(['nama_kategori' => 'Frozen Foods']);
        $product = Product::create([
            'kode_produk' => 'SKU-FOTO-002',
            'nama_produk' => 'Frozen Meatball',
            'kategori_id' => $category->id,
            'harga_beli' => 30000,
            'stok_minimum' => 5,
            'uom' => 'Pcs',
        ]);

        $po = PurchaseOrder::create([
            'po_number' => 'PO-FOTO-TEST-2',
            'supplier_id' => Supplier::create(['nama_supplier' => 'PT Meatball', 'kontak' => '123'])->id,
            'status' => 'Ordered',
            'total_harga' => 300000,
            'created_by' => $this->adminUser->id,
        ]);

        PurchaseOrderDetail::create([
            'po_id' => $po->id,
            'produk_id' => $product->kode_produk,
            'qty_pesan' => 10,
            'qty_diterima' => 0,
        ]);

        // Posting with valid foto_bukti
        $response = $this->actingAs($this->staffUser)->post('/inbound/store', [
            'po_id' => $po->id,
            'foto_bukti' => \Illuminate\Http\UploadedFile::fake()->create('bukti.jpg', 100, 'image/jpeg'),
            'items' => [
                [
                    'produk_id' => $product->kode_produk,
                    'qty_datang' => 5,
                    'qty_rusak' => 0,
                    'batch_supplier' => 'BATCH-SUPP-123',
                    'expired_date' => '2027-12-31',
                    'rak_id' => Rack::create(['kode_rak' => 'RAK-FOTO-2', 'nama_rak' => 'Rak Foto 2', 'kapasitas_maksimum_volume' => 100])->kode_rak,
                ]
            ]
        ]);

        $response->assertRedirect(route('inbound.index'));
        $response->assertSessionHasNoErrors();
        
        $this->assertDatabaseHas('t_po_receiving_history', [
            'po_id' => $po->id,
            'produk_id' => $product->kode_produk,
            'qty_received' => 5,
        ]);

        $history = \App\Models\PoReceivingHistory::where('po_id', $po->id)->first();
        $this->assertNotNull($history->foto_bukti);
        $this->assertStringContainsString('inbound_receipts/', $history->foto_bukti);
    }

    public function test_owner_cannot_create_stock_opname(): void
    {
        $responseCreate = $this->actingAs($this->ownerUser)->get('/opname/create');
        $responseCreate->assertStatus(403);

        $responseStore = $this->actingAs($this->ownerUser)->post('/opname/store', [
            'items' => [
                [
                    'batch_number' => 'BTC-TEST-OPNAME-01',
                    'qty_fisik' => 15,
                ]
            ]
        ]);
        $responseStore->assertStatus(403);
    }

    public function test_admin_can_create_stock_opname(): void
    {
        $responseCreate = $this->actingAs($this->adminUser)->get('/opname/create');
        $responseCreate->assertStatus(200);
    }
}
