<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * ERP: User-Warehouse Responsibility Pivot
 * Tracks which users are assigned to which warehouses.
 * Cross-branch assignments are supported for Incharges.
 */
class UserWarehouse extends Pivot
{
    use HasFactory;

    protected $table = 'user_warehouses';

    protected $fillable = [
        'user_id',
        'warehouse_id',
        'branch_id',
        'is_incharge',
        'notes',
    ];

    protected $casts = [
        'is_incharge' => 'boolean',
        'branch_id'   => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
