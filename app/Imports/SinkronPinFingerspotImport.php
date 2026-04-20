<?php

namespace App\Imports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;


class SinkronPinFingerspotImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    public array $failures = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $pin = trim($row['pin']); // dari header Excel
            $namaExcel = strtolower(trim($row['employee_name'])); // dari header Excel

            $employee = Employee::whereRaw('LOWER(employee_name) = ?', [$namaExcel])->first();

            if (!$employee) {
                $this->failures[] = "Name not found: $namaExcel";
                continue;
            }

            $pinSudahDipakai = Employee::where('pin', $pin)->where('id', '!=', $employee->id)->exists();

            if ($pinSudahDipakai) {
                $this->failures[] = "pin $pin already taken.";
                continue;
            }

            $employee->pin = $pin;
            $employee->save();
        }
    }

    public function failures()
    {
        return $this->failures;
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
