<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandidateRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'campus_election_id',
        'user_id',
        'position',
        'description',
        'decline_reason',
        'status',
        'submitted_by_staff_id',
        'reviewed_by_staff_id',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function election()
    {
        return $this->belongsTo(CampusElection::class, 'campus_election_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function submittedBy()
    {
        return $this->belongsTo(Staff::class, 'submitted_by_staff_id');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(Staff::class, 'reviewed_by_staff_id');
    }
}
