<?php

namespace App\Imports;

use App\Models\Payrolls;
use Maatwebsite\Excel\Concerns\ToModel;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Support\Facades\Crypt;

class PayrollsImport implements ToModel
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    protected $errors;

    public function __construct(&$errors)
    {
        $this->errors = &$errors;
    }
    public function model(array $row)
    {
        $createdat = null;
        if (!empty($row[16])) {
            if (is_numeric($row[16])) {
                $createdat = Date::excelToDateTimeObject($row[16])->format('Y-m-d H:i:s');
            } else {
                $createdat = Carbon::parse($row[16])->format('Y-m-d H:i:s');
            }
        }

        $monthyear = null;
        if (!empty($row[15])) {
            if (is_numeric($row[15])) {
                $monthyear = Date::excelToDateTimeObject($row[15])->format('Y-m-d');
            } else {
                $monthyear = Carbon::parse($row[15])->format('Y-m-d');
            }
        }

        // Cek unik employee_id
        if ($row[0] !== null && !Payrolls::where('employee_id', $row[0])->exists()) {
            return new Payrolls([
                'employee_id' => $row[0],
                'attendance' => $row[1] ?? null,
                'daily_allowance' => isset($row[2]) ? Crypt::encrypt($row[2]) : null,
                'house_allowance' => isset($row[3]) ? Crypt::encrypt($row[3]) : null,
                'meal_allowance' => isset($row[4]) ? Crypt::encrypt($row[4]) : null,
                'transport_allowance' => isset($row[5]) ? Crypt::encrypt($row[5]) : null,
                'bonus' => isset($row[6]) ? Crypt::encrypt($row[6]) : null,
                'overtime' => isset($row[7]) ? Crypt::encrypt($row[7]) : null,
                'late_fine' => isset($row[8]) ? Crypt::encrypt($row[8]) : null,
                'punishment' => isset($row[9]) ? Crypt::encrypt($row[9]) : null,
                'bpjs_kes' => isset($row[10]) ? Crypt::encrypt($row[10]) : null,
                'bpjs_ket' => isset($row[11]) ? Crypt::encrypt($row[11]) : null,
                'tax' => isset($row[12]) ? Crypt::encrypt($row[12]) : null,
                // 'deductions' => isset($row[13]) ? Crypt::encrypt($row[13]) : null,
                // 'salary' => isset($row[14]) ? Crypt::encrypt($row[14]) : null,
                'deductions' => isset($row[13]) && $row[13] !== null
    ? Crypt::encrypt($row[13])
    : Crypt::encrypt(($row[8] ?? 0) + ($row[9] ?? 0) + ($row[10] ?? 0) + ($row[11] ?? 0) + ($row[12] ?? 0)),
               'salary' => isset($row[14]) && $row[14] !== null
    ? Crypt::encrypt($row[14])
    : Crypt::encrypt(
        (($row[1] ?? 0) * ($row[2] ?? 0)) +
        ($row[3] ?? 0) +
        ($row[4] ?? 0) +
        ($row[5] ?? 0) +
        ($row[6] ?? 0) +
        ($row[7] ?? 0)
    ),

                'month_year' => $monthyear ?? null,
                'created_at' => $createdat ?? null,
                'period' => $row[17] ?? null,
               
            ]);
        }

        // Jika employee_id sudah ada, skip baris tersebut
        return null;
    }
}
