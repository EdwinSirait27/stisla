<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payrolls;
use Carbon\Carbon;
class DeleteOldPayrolls extends Command
{
    protected $signature = 'payrolls:delete-old';
    protected $description = 'Hapus data payroll yang berumur lebih dari 1 hari';

    public function handle()
    {
        $deleted = Payrolls::where('created_at', '<', Carbon::now()->subDay())->delete();
        $this->info("Data payroll lama yang dihapus: $deleted");
    }
}
