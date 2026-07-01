<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutboundDetail extends Model
{
    protected $table = 't_outbound_details';

    protected $fillable = [
        'outbound_id',
        'produk_id',
        'batch_number',
        'qty_keluar',
    ];

    public function outbound(): BelongsTo
    {
        return $this->belongsTo(Outbound::class, 'outbound_id', 'id');
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
