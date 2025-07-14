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
            $jamMasuk2 = isset($row['jam_masuk2']) ? Date::excelToDateTimeObject($row['jam_masuk2'])->format('H:i:s') : null;
            $jamKeluar2 = isset($row['jam_keluar2']) ? Date::excelToDateTimeObject($row['jam_keluar2'])->format('H:i:s') : null;
            $jamMasuk3 = isset($row['jam_masuk3']) ? Date::excelToDateTimeObject($row['jam_masuk3'])->format('H:i:s') : null;
            $jamKeluar3 = isset($row['jam_keluar3']) ? Date::excelToDateTimeObject($row['jam_keluar3'])->format('H:i:s') : null;
            $jamMasuk4 = isset($row['jam_masuk4']) ? Date::excelToDateTimeObject($row['jam_masuk4'])->format('H:i:s') : null;
            $jamKeluar4 = isset($row['jam_keluar4']) ? Date::excelToDateTimeObject($row['jam_keluar4'])->format('H:i:s') : null;
            $jamMasuk5 = isset($row['jam_masuk5']) ? Date::excelToDateTimeObject($row['jam_masuk5'])->format('H:i:s') : null;
            $jamKeluar5 = isset($row['jam_keluar5']) ? Date::excelToDateTimeObject($row['jam_keluar5'])->format('H:i:s') : null;
            $jamMasuk6 = isset($row['jam_masuk6']) ? Date::excelToDateTimeObject($row['jam_masuk6'])->format('H:i:s') : null;
            $jamKeluar6 = isset($row['jam_keluar6']) ? Date::excelToDateTimeObject($row['jam_keluar6'])->format('H:i:s') : null;
            $jamMasuk7 = isset($row['jam_masuk7']) ? Date::excelToDateTimeObject($row['jam_masuk7'])->format('H:i:s') : null;
            $jamKeluar7 = isset($row['jam_keluar7']) ? Date::excelToDateTimeObject($row['jam_keluar7'])->format('H:i:s') : null;
            $jamMasuk8 = isset($row['jam_masuk8']) ? Date::excelToDateTimeObject($row['jam_masuk8'])->format('H:i:s') : null;
            $jamKeluar8 = isset($row['jam_keluar8']) ? Date::excelToDateTimeObject($row['jam_keluar8'])->format('H:i:s') : null;
            $jamMasuk9 = isset($row['jam_masuk9']) ? Date::excelToDateTimeObject($row['jam_masuk9'])->format('H:i:s') : null;
            $jamKeluar9 = isset($row['jam_keluar9']) ? Date::excelToDateTimeObject($row['jam_keluar9'])->format('H:i:s') : null;
            $jamMasuk10 = isset($row['jam_masuk10']) ? Date::excelToDateTimeObject($row['jam_masuk10'])->format('H:i:s') : null;
            $jamKeluar10 = isset($row['jam_keluar10']) ? Date::excelToDateTimeObject($row['jam_keluar10'])->format('H:i:s') : null;

            $attendances = new Attendances();
            $attendances->employee_id = $employee->id;
            $attendances->tanggal       = $tanggal;
            $attendances->kantor        = $row['kantor'] ?? null;
            $attendances->jam_masuk     = $jamMasuk;
            $attendances->jam_keluar    = $jamKeluar;
            $attendances->jam_masuk2     = $jamMasuk2;
            $attendances->jam_keluar2    = $jamKeluar2;
            $attendances->jam_masuk3     = $jamMasuk3;
            $attendances->jam_keluar3    = $jamKeluar3;
            $attendances->jam_masuk4     = $jamMasuk4;
            $attendances->jam_keluar4    = $jamKeluar4;
            $attendances->jam_masuk5    = $jamMasuk5;
            $attendances->jam_keluar5    = $jamKeluar5;
            $attendances->jam_masuk6     = $jamMasuk6;
            $attendances->jam_keluar6    = $jamKeluar6;
            $attendances->jam_masuk7     = $jamMasuk7;
            $attendances->jam_keluar7    = $jamKeluar7;
            $attendances->jam_masuk8     = $jamMasuk8;
            $attendances->jam_keluar8    = $jamKeluar8;
            $attendances->jam_masuk9     = $jamMasuk9;
            $attendances->jam_keluar9    = $jamKeluar9;
            $attendances->jam_masuk10     = $jamMasuk10;
            $attendances->jam_keluar10    = $jamKeluar10;

            // for ($i = 2; $i <= 10; $i++) {
            //     $jm = "jam_masuk{$i}";
            //     $jk = "jam_keluar{$i}";
            //     $attendances->$jm = $row[$jm] ?? null;
            //     $attendances->$jk = $row[$jk] ?? null;
            // }
for ($i = 2; $i <= 10; $i++) {
    $jmKey = "jam_masuk{$i}";
    $jkKey = "jam_keluar{$i}";

    $jmValue = isset($row[$jmKey]) && is_numeric($row[$jmKey])
        ? Date::excelToDateTimeObject($row[$jmKey])->format('H:i:s')
        : null;

    $jkValue = isset($row[$jkKey]) && is_numeric($row[$jkKey])
        ? Date::excelToDateTimeObject($row[$jkKey])->format('H:i:s')
        : null;

    $attendances->$jmKey = $jmValue;
    $attendances->$jkKey = $jkValue;
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
