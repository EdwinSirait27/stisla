<?php
namespace Database\Seeders;
use App\Models\EmployeeStore;
use App\Models\EmployeeStoreAttendance;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;

class EmployeeStoreAttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding employee_store_attendances from employee_stores...');

        $employeeStores = EmployeeStore::all();

        if ($employeeStores->isEmpty()) {
            $this->command->warn('Tidak ada data di employee_stores, seeder dibatalkan.');
            return;
        }

        $inserted = 0;
        $skipped  = 0;

        foreach ($employeeStores as $employeeStore) {
            // Skip kalau sudah ada (hindari duplicate)
            $exists = EmployeeStoreAttendance::where('employee_id', $employeeStore->employee_id)
                ->where('employee_store_id', $employeeStore->id)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            try {
                EmployeeStoreAttendance::create([
                    'id'                => Uuid::uuid7()->toString(),
                    'employee_id'       => $employeeStore->employee_id,
                    'employee_store_id' => $employeeStore->id,
                ]);
                $inserted++;
            } catch (\Throwable $e) {
                Log::error('EmployeeStoreAttendanceSeeder failed', [
                    'employee_store_id' => $employeeStore->id,
                    'employee_id'       => $employeeStore->employee_id,
                    'error'             => $e->getMessage(),
                ]);
                $this->command->error("Failed: employee_store_id {$employeeStore->id} — {$e->getMessage()}");
            }
        }

        $this->command->info("Done. Inserted: {$inserted}, Skipped (already exists): {$skipped}");
    }
}