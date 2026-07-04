<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PoReceivingHistory extends Model
{
    protected $table = 't_po_receiving_history';

    protected $fillable = [
        'po_id',
        'produk_id',
        'qty_received',
        'batch_number',
        'received_at',
        'received_by',
    ];

    protected $casts = [
        'received_at' => 'datetime',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id', 'id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'produk_id', 'kode_produk');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by', 'id');
    }
}
