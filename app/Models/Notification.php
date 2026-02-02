<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'sale_id',
        'customer_id',
        'product_id',
        'warehouse_id',
        'type',
        'title',
        'description',
        'notification_date',
        'sent_at',
        'status',
        'is_read',
        'created_by',
    ];

    protected $casts = [
        'notification_date' => 'date',
        'sent_at' => 'datetime',
        'is_read' => 'boolean',
    ];

    // Relationships
    public function booking()
    {
        return $this->belongsTo(Productbooking::class, 'booking_id');
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeForToday($query)
    {
        return $query->whereDate('notification_date', today());
    }

    public function scopeOverdue($query)
    {
        return $query->whereDate('notification_date', '<', today())->where('status', '!=', 'sent');
    }
}

