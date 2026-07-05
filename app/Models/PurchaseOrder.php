<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    protected $table = 't_purchase_orders';

    protected $fillable = [
        'po_number',
        'supplier_id',
        'status',
        'total_harga',
        'target_tanggal_kirim',
        'created_by',
    ];

    protected $casts = [
        'target_tanggal_kirim' => 'date',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(PurchaseOrderDetail::class, 'po_id', 'id');
    }

    public function receivingHistory(): HasMany
    {
        return $this->hasMany(PoReceivingHistory::class, 'po_id', 'id');
    }
}
