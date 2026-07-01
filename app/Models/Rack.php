<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rack extends Model
{
    protected $table = 'm_racks';
    protected $primaryKey = 'kode_rak';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kode_rak',
        'kapasitas_maksimum_volume',
        'kapasitas_terpakai',
    ];

    public function batchInbounds(): HasMany
    {
        return $this->hasMany(BatchInbound::class, 'rak_id', 'kode_rak');
    }

    // Accessor for available capacity
    public function getSisaKapasitasAttribute(): int
    {
        return $this->kapasitas_maksimum_volume - $this->kapasitas_terpakai;
    }
}
