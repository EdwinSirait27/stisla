<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\GeneratePayrollIntroLetterJob;
use App\Jobs\SendWhatsappReminder3Month;
;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('sessions:cleanup')->monthly();
        $schedule->command('payrolls:delete-old')->daily();
        $schedule->command('leave:anniversary')->dailyAt('23:10');
        $schedule->command('reminder:probation')->dailyAt('08:00');
        $schedule->job(new SendWhatsappReminder3Month)->dailyAt('08:00');
    $schedule->command('reminder:probation')->dailyAt('08:00');
 $schedule->job(new GeneratePayrollIntroLetterJob)
           ->everyFiveMinutes()
            ->name('generate-payroll-intro-letter')
            ->withoutOverlapping();
    }
    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
    protected $commands = [
        \App\Console\Commands\SendPayrollEmails::class,
        \App\Console\Commands\DeleteOldPayrolls::class,
        \App\Console\Commands\GiveAnnualLeave::class,
    ];
}