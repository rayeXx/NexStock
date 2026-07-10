<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Destruction extends Model
{
    protected $table = 't_destructions';

    protected $fillable = [
        'damaged_report_id',
        'produk_id',
        'batch_number',
        'rak_id',
        'qty_dimusnahkan',
        'alasan',
        'catatan_pemusnahan',
        'assigned_by',
        'assigned_at',
        'foto_pemusnahan',
        'confirmed_by',
        'confirmed_at',
        'status',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'confirmed_at' => 'datetime',
    ];

    public function damagedReport(): BelongsTo
    {
        return $this->belongsTo(DamagedReport::class, 'damaged_report_id', 'id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'produk_id', 'kode_produk');
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by', 'id');
    }

    public function confirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by', 'id');
    }
}
