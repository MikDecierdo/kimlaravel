<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Staff extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The table associated with the model.
     */
    protected $table = 'staff';

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
        'employee_id',
        'department',
        'csg_original_department',
        'position',
        'is_department_head',
        'can_access_department_portal',
        'can_access_faculty_system',
        'department_portal_permissions',
        'office_location',
        'phone_number',
        'hire_date',
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
        'hire_date'         => 'date',
        'is_department_head'=> 'boolean',
        'can_access_department_portal' => 'boolean',
        'can_access_faculty_system' => 'boolean',
        'department_portal_permissions' => 'array',
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    public const DEPARTMENT_PORTAL_PERMISSIONS = [
        'create_election',
        'add_candidates',
        'post_events',
        'approve_students',
    ];

    /**
     * Check if staff is from a specific department.
     */
    public function isFromDepartment(string $department): bool
    {
        return strcasecmp($this->department, $department) === 0;
    }

    /**
     * Get full name.
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->name} {$this->middle_name} {$this->last_name}");
    }

    public function hasDepartmentPortalPermission(string $permission): bool
    {
        if ((bool) ($this->is_department_head ?? false)) {
            return true;
        }

        if (!(bool) ($this->can_access_department_portal ?? false)) {
            return false;
        }

        $permissions = is_array($this->department_portal_permissions)
            ? $this->department_portal_permissions
            : [];

        return in_array($permission, $permissions, true);
    }
}
