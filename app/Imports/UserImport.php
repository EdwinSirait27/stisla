<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Employee;
use Maatwebsite\Excel\Concerns\ToModel;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Support\Facades\Hash;

class UserImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
         $employeeId = isset($row[1]) ? trim($row[1]) : null;

    if (empty($employeeId)) {
        \Log::warning("Skipped user import: empty employee_id");
        return null;
    }

    if (!Employee::where('id', $employeeId)->exists()) {
        \Log::warning("Skipped user import: employee_id '$employeeId' not found");
        return null;
    }

        $createdat = null;
        if (!empty($row[5])) {
            if (is_numeric($row[5])) {
                $createdat = Date::excelToDateTimeObject($row[5])->format('Y-m-d H:i:s');
            } else {
                $createdat = Carbon::parse($row[5])->format('Y-m-d H:i:s');
            }
        }
        return new User([
            'terms_id' => $row[0] ?? null,
            // 'employee_id' => trim($row[1]) ?? null,
             'employee_id' => $employeeId,
     'username' => $row[2] ?? null,
     'password'=> isset($row[3]) ? Hash::make($row[3]) : null,
     'remember_token' => $row[4] ?? null,
     'created_at' => $createdat ?? null,
     'updated_at' => $row[6] ?? null,
     'deleted_at' => $row[7] ?? null,
        ]);
    }
}
