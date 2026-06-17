<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'department',
        'event_date',
        'description',
        'image',
        'user_id',
        'staff_id',
    ];

    protected $casts = [
        'event_date' => 'date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function likes()
    {
        return $this->hasMany(EventLike::class);
    }

    public function comments()
    {
        return $this->hasMany(EventComment::class)->with('user')->latest();
    }

    public function likedBy($userId)
    {
        return $this->likes()->where('user_id', $userId)->exists();
    }

    public function userReaction($userId)
    {
        $reaction = $this->likes()->where('user_id', $userId)->first();
        return $reaction ? $reaction->reaction_type : null;
    }
}
