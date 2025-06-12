<?php
namespace App\Imports;
use App\Models\Employee;
use Maatwebsite\Excel\Concerns\ToModel;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
class EmployeeImport implements ToModel
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
public function model(array $row)
{
    try {
        // Hitung total row yang diproses (buat log manual jika diperlukan)
        static $importedCount = 0;

        // Parsing tanggal join
        $joinDate = null;
        if (!empty($row[10])) {
            if (is_numeric($row[10])) {
                $joinDate = Date::excelToDateTimeObject($row[10])->format('Y-m-d');
            } else {
                $joinDate = Carbon::parse($row[10])->format('Y-m-d');
            }
        }

        // Parsing tanggal lahir
        $datebirth = null;
        if (!empty($row[16])) {
            if (is_numeric($row[16])) {
                $datebirth = Date::excelToDateTimeObject($row[16])->format('Y-m-d');
            } else {
                $datebirth = Carbon::parse($row[16])->format('Y-m-d');
            }
        }

        // Lewati jika kolom pengenal kosong
        if (empty($row[1]) || trim($row[1]) === '') {
            Log::info('Baris dilewati karena kolom pengenal kosong.', ['row' => $row]);
            return null;
        }

        // Validasi department_id
        $departmentId = $row[7] ?? null;
        $departmentExists = $departmentId && DB::table('departments_tables')->where('id', $departmentId)->exists();

        if (!$departmentExists) {
            Log::warning('Import gagal: department_id tidak ditemukan.', [
                'department_id' => $departmentId,
                'row' => $row
            ]);
            return null;
        }

        // Naikkan counter dan log
        $importedCount++;
        Log::info("Data karyawan berhasil di-import ke-{$importedCount}", [
            'pengenal' => $row[1],
            'department_id' => $departmentId
        ]);

        // Return Employee model
        return new Employee([
            'employee_name' => $row[0] ?? null,
            'employee_pengenal' => trim($row[1]) ?? null,
            'position_id' => $row[2] ?? null,
            'company_id' => $row[3] ?? null,
            'store_id' => $row[4] ?? null,
            'banks_id' => $row[5] ?? null,
            'bank_account_number' => $row[6] ?? null,
            'department_id' => $departmentId,
            'fingerprint_id' => $row[8] ?? null,
            'status_employee' => $row[9] ?? null,
            'join_date' => $joinDate ?? null,
            'marriage' => $row[11] ?? null,
            'child' => $row[12] ?? null,
            'telp_number' => $row[13] ?? null,
            'nik' => $row[14] ?? null,
            'gender' => $row[15] ?? null,
            'date_of_birth' => $datebirth ?? null,
            'place_of_birth' => $row[17] ?? null,
            'biological_mother_name' => $row[18] ?? null,
            'religion' => $row[19] ?? null,
            'current_address' => $row[20] ?? null,
            'id_card_address' => $row[21] ?? null,
            'last_education' => $row[22] ?? null,
            'institution' => $row[23] ?? null,
            'npwp' => $row[24] ?? null,
            'bpjs_kes' => $row[25] ?? null,
            'bpjs_ket' => $row[26] ?? null,
            'email' => $row[27] ?? null,
            'emergency_contact_name' => $row[28] ?? null,
            'status' => $row[29] ?? null,
            'notes' => $row[30] ?? null,
        ]);
    } catch (\Exception $e) {
        Log::error('❌ Error saat import karyawan di baris: ' . json_encode($row));
        Log::error('Pesan error: ' . $e->getMessage());
        Log::error('Trace: ' . $e->getTraceAsString());
        return null;
    }
}

}
