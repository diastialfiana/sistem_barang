<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'nip',
        'email',
        'password',
        'branch_id',
        'job_title',
        'company',
        'last_login_at',
        'last_logout_at',
        'previous_login_at',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

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
        'password' => 'hashed',
        'last_login_at' => 'datetime',
        'last_logout_at' => 'datetime',
        'previous_login_at' => 'datetime',
    ];

    public function getRoleColorAttribute()
    {
        $role = $this->getRoleNames()->first();

        return match ($role) {
            'super_admin' => 'bg-red-100 text-red-800',
            'admin_1' => 'bg-blue-100 text-blue-800',
            'admin_2' => 'bg-indigo-100 text-indigo-800',
            'user' => 'bg-green-100 text-green-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
