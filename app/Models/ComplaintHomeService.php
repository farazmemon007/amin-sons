<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComplaintHomeService extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'visit_date'    => 'date',
        'charges_paid'  => 'boolean',
    ];

    public function complaint()
    {
        return $this->belongsTo(Complaint::class);
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getVisitStatusBadgeAttribute(): string
    {
        return match($this->visit_status) {
            'scheduled'  => '<span class="badge badge-warning">Scheduled</span>',
            'visited'    => '<span class="badge badge-info">Visited</span>',
            'resolved'   => '<span class="badge badge-success">Resolved</span>',
            'follow_up'  => '<span class="badge badge-danger">Follow Up</span>',
            default      => '<span class="badge badge-light">-</span>',
        };
    }
}
