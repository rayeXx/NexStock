<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $table = 'm_categories';

    protected $fillable = [
        'nama_kategori',
        'catatan',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'kategori_id', 'id');
    }
}
