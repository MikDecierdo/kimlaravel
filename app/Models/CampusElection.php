<?php

namespace App\Models;

use App\Models\CandidateApplication;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampusElection extends Model
{
    use HasFactory;

    protected $fillable = [
        'department',
        'election_name',
        'description',
        'positions',
        'candidate_registration_schema',
        'partylist_teams',
        'banner_image',
        'registration_start_date',
        'registration_end_date',
        'start_date',
        'end_date',
        'voting_start_time',
        'voting_end_time',
        'is_active'
    ];

    protected $casts = [
        'registration_start_date' => 'date',
        'registration_end_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'positions' => 'array',
        'candidate_registration_schema' => 'array',
        'partylist_teams' => 'array'
    ];

    public static function defaultCandidateRegistrationSchema(): array
    {
        return [
            [
                'key' => 'full_name',
                'label' => 'Full Name',
                'type' => 'text',
                'required' => true,
                'readonly' => true,
            ],
            [
                'key' => 'student_id',
                'label' => 'Student ID',
                'type' => 'text',
                'required' => true,
                'readonly' => true,
            ],
            [
                'key' => 'year_level',
                'label' => 'Year Level',
                'type' => 'text',
                'required' => true,
                'readonly' => true,
            ],
            [
                'key' => 'department',
                'label' => 'Department',
                'type' => 'text',
                'required' => true,
                'readonly' => true,
            ],
            [
                'key' => 'position',
                'label' => 'Applying Position',
                'type' => 'select',
                'required' => true,
                'source' => 'election_positions',
            ],
            [
                'key' => 'platform_statement',
                'label' => 'Platform / Advocacy',
                'type' => 'textarea',
                'required' => true,
                'max' => 500,
            ],
        ];
    }

    public static function normalizePartylistTeams($teams): array
    {
        if (!is_array($teams)) {
            return [];
        }

        $normalized = [];

        foreach ($teams as $team) {
            if (!is_array($team)) {
                continue;
            }

            $name = trim((string) ($team['name'] ?? ''));
            $tagline = trim((string) ($team['tagline'] ?? ''));

            if ($name === '') {
                continue;
            }

            $normalized[] = [
                'name' => $name,
                'tagline' => $tagline,
            ];
        }

        return $normalized;
    }

    public function candidates()
    {
        return $this->hasMany(Candidate::class);
    }

    public function candidateApplications()
    {
        return $this->hasMany(CandidateApplication::class, 'election_id');
    }
}
