<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'user_id',
        'role',
        'browser_fingerprint',
        'ip_address',
        'login_timestamp',
        'last_activity',
        'is_active'
    ];

    protected $casts = [
        'login_timestamp' => 'datetime',
        'last_activity' => 'datetime',
        'is_active' => 'boolean'
    ];

    /**
     * Get the authenticated user — resolves to the correct model
     * depending on role (Admin, Staff, or User).
     */
    public function user()
    {
        return match($this->role) {
            'admin'           => $this->belongsTo(Admin::class, 'user_id'),
            'department_head' => $this->belongsTo(Staff::class, 'user_id'),
            default           => $this->belongsTo(User::class,  'user_id'),
        };
    }

    /**
     * Check if session is expired (30 minutes of inactivity)
     */
    public function isExpired()
    {
        if (!$this->last_activity) {
            return false;
        }
        
        return $this->last_activity->diffInMinutes(now()) > 30;
    }

    /**
     * Update last activity timestamp
     */
    public function updateActivity()
    {
        $this->update(['last_activity' => now()]);
    }

    /**
     * Invalidate this session
     */
    public function invalidate()
    {
        $this->update(['is_active' => false]);
    }
}
