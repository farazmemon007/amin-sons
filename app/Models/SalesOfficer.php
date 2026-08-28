<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesOfficer extends Model
{
     use HasFactory;

    protected $fillable = [
        'branch_id', 'name', 'name_urdu', 'mobile',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
}
