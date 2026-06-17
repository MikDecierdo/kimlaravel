<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    use HasFactory;

    protected $fillable = [
        'campus_election_id',
        'first_name',
        'middle_name',
        'last_name',
        'student_id',
        'position',
        'partylist',
        'department',
        'description',
        'advocacy',
        'image',
        'votes'
    ];

    // Accessor to get full name
    public function getFullNameAttribute()
    {
        $name = $this->first_name;
        if ($this->middle_name) {
            $name .= ' ' . $this->middle_name;
        }
        $name .= ' ' . $this->last_name;
        return $name;
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    public function campusElection()
    {
        return $this->belongsTo(CampusElection::class);
    }

    public function getVotePercentageAttribute()
    {
        $samePosition = Candidate::where('position', $this->position)->get();
        $totalVotes = $samePosition->sum('votes');
        
        return $totalVotes > 0 ? round(($this->votes / $totalVotes) * 100) : 0;
    }
}
