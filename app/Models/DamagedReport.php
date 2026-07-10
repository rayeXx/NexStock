<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DamagedReport extends Model
{
    protected $table = 't_damaged_reports';

    protected $fillable = [
        'produk_id',
        'batch_number',
        'rak_id',
        'qty_rusak',
        'foto_bukti',
        'alasan',
        'status',
        'created_by',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'produk_id', 'kode_produk');
    }

    public function batchInbound(): BelongsTo
    {
        return $this->belongsTo(BatchInbound::class, 'batch_number', 'batch_number');
    }

    public function rack(): BelongsTo
    {
        return $this->belongsTo(Rack::class, 'rak_id', 'kode_rak');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function destruction()
    {
        return $this->hasOne(Destruction::class, 'damaged_report_id', 'id');
    }
}
