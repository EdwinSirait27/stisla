<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class SyncEmployeePositions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-employee-positions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
  public function handle()
{
    DB::table('employees_tables')
        ->whereNotNull('position_id')
        ->orderBy('id')
        ->chunk(100, function ($rows) {

            // foreach ($rows as $row) {

            //     // cek apakah position ini sudah ada
            //     $exists = DB::table('employee_positions')
            //         ->where('employee_id', $row->id)
            //         ->where('position_id', $row->position_id)
            //         ->exists();

            //     // matikan primary lama
            //     DB::table('employee_positions')
            //         ->where('employee_id', $row->id)
            //         ->update([
            //             'is_primary' => 0
            //         ]);

            //     if (! $exists) {

            //         DB::table('employee_positions')->insert([
            //             'id' => Uuid::uuid7()->toString(),
            //             'employee_id' => $row->id,
            //             'position_id' => $row->position_id,
            //             'is_primary' => 1,
            //             'created_at' => now(),
            //             'updated_at' => now(),
            //         ]);

            //     } else {

            //         // kalau sudah ada, jadikan primary
            //         DB::table('employee_positions')
            //             ->where('employee_id', $row->id)
            //             ->where('position_id', $row->position_id)
            //             ->update([
            //                 'is_primary' => 1
            //             ]);
            //     }
            // }
            foreach ($rows as $row) {

    $positionExists = DB::table('position_tables')
        ->where('id', $row->position_id)
        ->exists();

    if (! $positionExists) {
        $this->warn("Skip employee {$row->id}, position tidak ditemukan");
        continue;
    }


    $exists = DB::table('employee_positions')
        ->where('employee_id', $row->id)
        ->where('position_id', $row->position_id)
        ->exists();


    DB::table('employee_positions')
        ->where('employee_id', $row->id)
        ->update([
            'is_primary' => 0
        ]);


    if (! $exists) {

        DB::table('employee_positions')->insert([
            'id' => Uuid::uuid7()->toString(),
            'employee_id' => $row->id,
            'position_id' => $row->position_id,
            'is_primary' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

    } else {

        DB::table('employee_positions')
            ->where('employee_id', $row->id)
            ->where('position_id', $row->position_id)
            ->update([
                'is_primary' => 1
            ]);
    }
}

        });

    $this->info('Employee positions synced successfully.');
}
}
