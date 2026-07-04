<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $table = 'm_products';
    protected $primaryKey = 'kode_produk';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kode_produk',
        'nama_produk',
        'kategori_id',
        'harga_beli',
        'stok_minimum',
        'uom',
    ];

    protected $casts = [
        'harga_beli' => 'encrypted', // AES-256 encryption
        'stok_minimum' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'kategori_id', 'id');
    }

    public function batchInbounds(): HasMany
    {
        return $this->hasMany(BatchInbound::class, 'produk_id', 'kode_produk');
    }

    public function purchaseOrderDetails(): HasMany
    {
        return $this->hasMany(PurchaseOrderDetail::class, 'produk_id', 'kode_produk');
    }

    // Accessor for total stock
    public function getTotalStokAttribute(): int
    {
        return $this->batchInbounds()->sum('stok_sisa_batch');
    }

    // Accessor for stock status badge
    public function getStockStatusAttribute(): string
    {
        $stok = $this->total_stok;
        $min = $this->stok_minimum;

        if ($stok <= $min) {
            return 'Kritis';
        }

        if ($stok <= $min * 1.5) {
            return 'Menipis';
        }

        return 'Aman';
    }
}
