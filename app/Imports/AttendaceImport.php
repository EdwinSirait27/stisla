<?php

namespace App\Imports;

use App\Models\Attendances;
use App\Models\Employee;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class AttendaceImport implements ToCollection, WithHeadingRow
{
    public array $failures = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $row = array_change_key_case($row->toArray(), CASE_LOWER);
$pin = trim((string) $row['pin']);

            
$employee = Employee::where('pin', $pin)->first();
            
            if (!$employee) {
    $this->failures[] = "PIN $pin not found in employees table.";
    continue;
}
            $tanggal = isset($row['tanggal']) ? Date::excelToDateTimeObject($row['tanggal'])->format('Y-m-d') : null;
            $jamMasuk = isset($row['jam_masuk']) ? Date::excelToDateTimeObject($row['jam_masuk'])->format('H:i:s') : null;
            $jamKeluar = isset($row['jam_keluar']) ? Date::excelToDateTimeObject($row['jam_keluar'])->format('H:i:s') : null;

            $attendances = new Attendances();
            $attendances->employee_id = $employee->id;
            $attendances->tanggal       = $tanggal;
            $attendances->kantor        = $row['kantor'] ?? null;
            $attendances->jam_masuk     = $jamMasuk;
            $attendances->jam_keluar    = $jamKeluar;

            for ($i = 2; $i <= 10; $i++) {
                $jm = "jam_masuk{$i}";
                $jk = "jam_keluar{$i}";
                $attendances->$jm = $row[$jm] ?? null;
                $attendances->$jk = $row[$jk] ?? null;
            }

            $attendances->save();
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
