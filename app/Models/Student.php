<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'student_id',
        'department',
        'year_level',
        'program',
        'section',
        'enrollment_date',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'enrollment_date' => 'date',
    ];

    /**
     * Get the user that owns the student profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get student's votes.
     */
    public function votes()
    {
        return $this->hasMany(Vote::class, 'user_id', 'user_id');
    }

    /**
     * Check if student is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if student is from a specific year level.
     */
    public function isYearLevel(string $yearLevel): bool
    {
        return $this->year_level === $yearLevel;
    }
}
