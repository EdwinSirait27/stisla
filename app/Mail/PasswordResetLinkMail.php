<?php

namespace App\Mail;

use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $resetUrl,
        public CarbonInterface $expiresAt
    ) {}

    public function build()
    {
        return $this->subject('Reset password akun HRX')
            ->view('mail.PasswordResetLinkMail')
            ->with([
                'employeeName' => $this->user->Employee->employee_name ?? $this->user->username,
                'username'     => $this->user->username,
                'companyName'  => $this->user->Employee->company->name ?? 'HRX',
                'expiresText'  => $this->expiresAt->copy()->setTimezone(config('app.timezone'))
                    ->locale('id')->translatedFormat('d M Y, H:i'),
                'expireMinutes' => \App\Services\PasswordResetLinkService::EXPIRE_MINUTES,
            ]);
    }
}
