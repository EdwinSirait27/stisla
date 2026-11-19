<?php

namespace App\Console\Commands;

use App\Models\Toilbalances;
use Illuminate\Console\Command;

class ExpireToilBalances extends Command
{
    protected $signature = 'toil:expire-balances';

    protected $description = 'Tandai saldo TOIL yang sudah lewat masa berlaku sebagai expired';

    /**
     * Hanya tipe Toil: saldo Cash yang lewat tanggal tetap dibayar lewat payroll.
     */
    public function handle(): int
    {
        $balances = Toilbalances::ofType('Toil')
            ->where('status', 'active')
            ->where('expires_at', '<', today())
            ->get();

        $balances->each->refreshStatus();

        $this->info("{$balances->count()} saldo TOIL diperiksa.");

        return self::SUCCESS;
    }
}
