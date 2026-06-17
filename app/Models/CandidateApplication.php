<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandidateApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'student_id',
        'election_id',
        'form_responses',
        'status',
        'submitted_at',
        'decision_description',
    ];

    protected $casts = [
        'form_responses' => 'array',
        'submitted_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function election()
    {
        return $this->belongsTo(CampusElection::class, 'election_id');
    }
}
