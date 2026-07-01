<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatchInbound extends Model
{
    protected $table = 't_batch_inbounds';
    protected $primaryKey = 'batch_number';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'batch_number',
        'produk_id',
        'po_id',
        'rak_id',
        'expired_date',
        'stok_awal_batch',
        'stok_sisa_batch',
    ];

    protected $casts = [
        'expired_date' => 'date',
        'stok_awal_batch' => 'integer',
        'stok_sisa_batch' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'produk_id', 'kode_produk');
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id', 'id');
    }

    public function rack(): BelongsTo
    {
        return $this->belongsTo(Rack::class, 'rak_id', 'kode_rak');
    }
}
