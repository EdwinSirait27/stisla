<?php

namespace App\Imports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\ToModel;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class EmployeeImport implements ToModel
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $joinDate = null;
        if (!empty($row[9])) {
            if (is_numeric($row[9])) {
                $joinDate = Date::excelToDateTimeObject($row[9])->format('Y-m-d');
            } else {
                $joinDate = Carbon::parse($row[9])->format('Y-m-d');
            }
        }
        $datebirth = null;
        if (!empty($row[15])) {
            if (is_numeric($row[15])) {
                $datebirth = Date::excelToDateTimeObject($row[15])->format('Y-m-d');
            } else {
                $datebirth = Carbon::parse($row[15])->format('Y-m-d');
            }
        }
        if (empty($row[1]) || trim($row[1]) === '') {
            return null; // Lewati baris kalau pengenal kosong
        }
        
        
        return new Employee([
       'employee_name' => $row[0] ?? null,
       'employee_pengenal' => trim($row[1]) ?? null,
'position_id' => $row[2] ?? null,
'company_id' => $row[3] ?? null,
'store_id' => $row[4] ?? null,
'banks_id' => $row[5] ?? null,
'department_id' => $row[6] ?? null,
'fingerprint_id' => $row[7] ?? null,
'status_employee' => $row[8] ?? null,
'join_date' => $joinDate ?? null,
'marriage' => $row[10] ?? null,
'child' => $row[11] ?? null,
'telp_number' => $row[12] ?? null,
'nik' => $row[13] ?? null,
'gender' => $row[14] ?? null,
'date_of_birth' => $datebirth ?? null,
'place_of_birth' => $row[16] ?? null,
'biological_mother_name' => $row[17] ?? null,
'religion' => $row[18] ?? null,
'current_address' => $row[19] ?? null,
'id_card_address' => $row[20] ?? null,
'last_education' => $row[21] ?? null,
'institution' => $row[22] ?? null,
'npwp' => $row[23] ?? null,
'bpjs_kes' => $row[24] ?? null,
'bpjs_ket' => $row[25] ?? null,
'email' => $row[26] ?? null,
'emergency_contact_name' => $row[27] ?? null,
'status' => $row[28] ?? null,
'notes' => $row[29] ?? null,

        ]);
    }
}
