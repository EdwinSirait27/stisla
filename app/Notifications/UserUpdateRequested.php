<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserUpdateRequested extends Notification
{
    use Queueable;

    protected $user;
    protected $field;

    public function __construct($user, $field)
    {
        $this->user = $user;
        $this->field = $field;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(ucfirst($this->field) . ' Update Request')
            ->greeting('Hello HR Department PT. Mahendradata Jaya Mandiri,')
            ->line("User {$this->user->employee->employee_name} has proposed changes to {$this->field}.")
            ->line('Please login to the HR dashboard to approve/reject.')
            ->action('Dashboard System', 'https://hr.unclejo.xyz')
            ->line('Thank you.');
    }
}
