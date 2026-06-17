<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Candidate;

class CandidateAddedNotification extends Notification
{
    use Queueable;

    protected $candidate;
    protected $addedBy;

    /**
     * Create a new notification instance.
     */
    public function __construct(Candidate $candidate, $addedBy)
    {
        $this->candidate = $candidate;
        $this->addedBy = $addedBy;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('New Candidate Added to ' . $this->candidate->department)
                    ->line('A new candidate has been added to the ' . $this->candidate->department . ' department.')
                    ->line('Name: ' . $this->candidate->first_name . ' ' . $this->candidate->last_name)
                    ->line('Position: ' . $this->candidate->position)
                    ->line('Added by: ' . $this->addedBy)
                    ->action('View Candidates', url('/department-head/candidates'))
                    ->line('Thank you for using our election system!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'candidate_id' => $this->candidate->id,
            'candidate_name' => $this->candidate->first_name . ' ' . $this->candidate->last_name,
            'position' => $this->candidate->position,
            'department' => $this->candidate->department,
            'added_by' => $this->addedBy,
            'message' => 'New candidate ' . $this->candidate->first_name . ' ' . $this->candidate->last_name . ' has been added for position ' . $this->candidate->position . ' in ' . $this->candidate->department . ' department.'
        ];
    }
}
