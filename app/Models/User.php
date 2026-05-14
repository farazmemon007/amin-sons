<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
// use Spatie\Permission\Models\Role;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];
    // protected $fillable = [
    //     'name',
    //     'email',
    //     'password',
    // ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Branch relationship
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    // Warehouse assignments (for role-based access)
    public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class, 'user_warehouses')
                    ->withPivot('is_incharge', 'branch_id', 'notes')
                    ->withTimestamps();
    }

    /**
     * Returns the warehouse IDs this user is explicitly assigned to.
     * Used for data-level security when the user is NOT a super admin or branch admin.
     */
    public function assignedWarehouseIds(): array
    {
        return $this->warehouses()->pluck('warehouses.id')->toArray();
    }

//    public function roles()
//     {
//         return $this->belongsToMany(Role::class, 'model_has_roles', 'model_id', 'role_id')
//                     ->where('model_type', User::class);
//     }
    
}
