<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Complaint extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'complaint_date' => 'date',
        'resolved_date'  => 'date',
    ];

    // ─── Relationships ───────────────────────────────────────────

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function resolvedByUser()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function homeServices()
    {
        return $this->hasMany(ComplaintHomeService::class);
    }

    public function statusLogs()
    {
        return $this->hasMany(ComplaintStatusLog::class)->orderBy('created_at', 'desc');
    }

    // ─── Helpers ─────────────────────────────────────────────────

    public function getScenarioLabelAttribute(): string
    {
        return match($this->scenario_type) {
            'walk_in'      => 'Walk-in (Shop)',
            'remote'       => 'Phone/WhatsApp',
            'home_service' => 'Home Service',
            default        => 'Unknown',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'pending'     => '<span class="badge badge-warning">Pending</span>',
            'in_progress' => '<span class="badge badge-info">In Progress</span>',
            'resolved'    => '<span class="badge badge-success">Resolved</span>',
            'closed'      => '<span class="badge badge-secondary">Closed</span>',
            default       => '<span class="badge badge-light">Unknown</span>',
        };
    }

    public function getScenarioBadgeAttribute(): string
    {
        return match($this->scenario_type) {
            'walk_in'      => '<span class="badge badge-primary"><i class="fas fa-store"></i> Walk-in</span>',
            'remote'       => '<span class="badge badge-success"><i class="fab fa-whatsapp"></i> Remote</span>',
            'home_service' => '<span class="badge badge-danger"><i class="fas fa-home"></i> Home Service</span>',
            default        => '<span class="badge badge-light">-</span>',
        };
    }

    /**
     * Generate next complaint number for a branch
     */
    public static function generateComplaintNo(int $branchId): string
    {
        $branch   = Branch::find($branchId);
        $code     = $branch ? strtoupper(substr($branch->name, 0, 3)) : 'GEN';
        $year     = date('Y');
        $last     = static::where('branch_id', $branchId)
                          ->whereYear('created_at', $year)
                          ->count() + 1;
        return 'CMP-' . $year . '-' . $code . '-' . str_pad($last, 5, '0', STR_PAD_LEFT);
    }
}
