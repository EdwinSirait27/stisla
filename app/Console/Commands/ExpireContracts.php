<?php

namespace App\Console\Commands;

use App\Models\Contract;
use Illuminate\Console\Command;

/** Kontrak berlaku yang tanggal berakhirnya sudah lewat → Expired (tanggal berakhir masih termasuk masa kontrak). */
class ExpireContracts extends Command
{
    protected $signature = 'contracts:expire';
    protected $description = 'Ubah kontrak yang tanggal berakhirnya sudah lewat menjadi Expired';

    public function handle()
    {
        $count = 0;
        Contract::where('contract_status', 'Active')->whereNotNull('end_date')->whereDate('end_date', '<', today())
            ->get()->each(function (Contract $c) use (&$count) {
                $c->update(['contract_status' => 'Expired']);
                $count++;
            });

        $this->info("Kontrak berakhir: {$count}");
    }
}
