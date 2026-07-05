<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockOpname extends Model
{
    protected $table = 't_stock_opnames';

    protected $fillable = [
        'tanggal_opname',
        'created_by',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'tanggal_opname' => 'date',
        'approved_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by', 'id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(StockOpnameDetail::class, 'stock_opname_id', 'id');
    }
}
