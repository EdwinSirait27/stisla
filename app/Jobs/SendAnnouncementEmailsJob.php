<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Employee;
use App\Mail\Announcement as AnnouncementMail;
use Illuminate\Support\Facades\Mail;


class SendAnnouncementEmailsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $announcement;

    public function __construct($announcement)
    {
        $this->announcement = $announcement;
    }

    public function handle()
    {
        $employees = Employee::whereIn('status', ['Active', 'Pending', 'Mutation'])
            ->whereNotNull('email')
            ->get();

        foreach ($employees as $emp) {
            Mail::to($emp->email)->send(new AnnouncementMail($this->announcement, $emp));
        }
    }
}