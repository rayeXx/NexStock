<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Outbound extends Model
{
    protected $table = 't_outbounds';

    protected $fillable = [
        'outbound_number',
        'tujuan',
        'tanggal_keluar',
    ];

    protected $casts = [
        'tanggal_keluar' => 'date',
    ];

    public function details(): HasMany
    {
        return $this->hasMany(OutboundDetail::class, 'outbound_id', 'id');
    }
}
