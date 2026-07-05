<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderDetail extends Model
{
    protected $table = 't_purchase_order_details';

    protected $fillable = [
        'po_id',
        'produk_id',
        'qty_pesan',
        'qty_diterima',
        'harga_satuan',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id', 'id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'produk_id', 'kode_produk');
    }
}
