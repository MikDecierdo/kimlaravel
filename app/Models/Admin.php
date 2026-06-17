<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The table associated with the model.
     */
    protected $table = 'admins';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'middle_name',
        'last_name',
        'email',
        'email_verified_at',
        'password',
        'profile_picture',
        'admin_level',
        'permissions',
        'assigned_at',
    ];

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
        'permissions'       => 'array',
        'assigned_at'       => 'datetime',
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    /**
     * Check if admin has specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->admin_level === 'super') {
            return true;
        }

        $permissions = $this->permissions ?? [];
        return in_array($permission, $permissions);
    }

    /**
     * Get full name.
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->name} {$this->middle_name} {$this->last_name}");
    }
}
