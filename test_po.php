<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$po = App\Models\PurchaseOrder::with('details')->latest()->first();
echo "PO Number: " . $po->po_number . "\n";
echo "Status: " . $po->status . "\n";
foreach($po->details as $d) {
    echo "Item: " . $d->produk_id . " | Pesan: " . $d->qty_pesan . " | Terima: " . $d->qty_diterima . "\n";
}
