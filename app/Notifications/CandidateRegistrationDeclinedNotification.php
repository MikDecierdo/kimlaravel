<?php

namespace App\Notifications;

use App\Models\CandidateRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CandidateRegistrationDeclinedNotification extends Notification
{
    use Queueable;

    protected CandidateRegistration $registration;
    protected string $reason;

    public function __construct(CandidateRegistration $registration, string $reason)
    {
        $this->registration = $registration;
        $this->reason = $reason;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $election = $this->registration->election;

        return [
            'type' => 'candidate_registration_declined',
            'registration_id' => $this->registration->id,
            'election_id' => $this->registration->campus_election_id,
            'election_name' => $election?->election_name,
            'position' => $this->registration->position,
            'reason' => $this->reason,
            'message' => 'Your candidate registration for ' . ($election?->election_name ?? 'this election') . ' was declined: ' . $this->reason,
        ];
    }
}