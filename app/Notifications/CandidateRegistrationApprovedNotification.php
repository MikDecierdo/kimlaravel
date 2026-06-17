<?php

namespace App\Notifications;

use App\Models\CandidateRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CandidateRegistrationApprovedNotification extends Notification
{
    use Queueable;

    protected CandidateRegistration $registration;

    public function __construct(CandidateRegistration $registration)
    {
        $this->registration = $registration;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $election = $this->registration->election;

        return [
            'type' => 'candidate_registration_approved',
            'registration_id' => $this->registration->id,
            'election_id' => $this->registration->campus_election_id,
            'election_name' => $election?->election_name,
            'position' => $this->registration->position,
            'message' => 'Your candidate registration for ' . ($election?->election_name ?? 'this election') . ' has been approved.',
        ];
    }
}
