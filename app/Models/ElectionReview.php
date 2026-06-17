<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ElectionReview extends Model
{
    protected $fillable = ['user_id', 'campus_election_id', 'rating', 'review'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function campusElection()
    {
        return $this->belongsTo(CampusElection::class);
    }
}
