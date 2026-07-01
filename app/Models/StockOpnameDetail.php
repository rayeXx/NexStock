<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockOpnameDetail extends Model
{
    protected $table = 't_stock_opname_details';

    protected $fillable = [
        'stock_opname_id',
        'produk_id',
        'batch_number',
        'qty_sistem',
        'qty_fisik',
        'selisih',
        'catatan',
    ];

    public function stockOpname(): BelongsTo
    {
        return $this->belongsTo(StockOpname::class, 'stock_opname_id', 'id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'produk_id', 'kode_produk');
    }

    public function batchInbound(): BelongsTo
    {
        return $this->belongsTo(BatchInbound::class, 'batch_number', 'batch_number');
    }
}
