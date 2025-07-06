<?php
namespace App\Imports;
use App\Models\Payrolls;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\MessageBag;
class PayrollsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use SkipsFailures, Importable;
    protected $errors;
    public function __construct(&$errors)
    {
        $this->errors = &$errors;
    }
    public function model(array $row)
    {
//         if (empty($row['employee_id'])) {
//     throw ValidationException::withMessages([
//         'employee_id' => ['Employee ID kosong.']
//     ]);
// }

// if (Payrolls::where('employee_id', $row['employee_id'])->exists()) {
//     throw ValidationException::withMessages([
//         'employee_id' => ["Data dengan employee_id {$row['employee_id']} sudah ada."]
//     ]);
// }
 if (!isset($row['employee_id']) || trim($row['employee_id']) === '') {
        return null;
    }

if (empty($row['employee_id'])) {
    throw ValidationException::withMessages([
        'employee_id' => ['Employee ID kosong.']
    ]);
}

// if (Payrolls::where('employee_id', $row['employee_id'])->exists()->with('employee')) {
//     $failure = new Failure(
//         row: $row['__row'] ?? 0,
//         attribute: 'employee_id',
//         errors: ["Data dengan employee_id {$row['employee_id']} sudah ada."],
//         values: $row
//     );

//     $this->failures[] = $failure;
//     return null; // skip baris ini
// }
$payroll = Payrolls::with('employee')
    ->where('employee_id', $row['employee_id'])
    ->first();

if ($payroll) {
    $employee_name = optional($payroll->employee)->employee_name ?? 'Tidak ditemukan';
    
    $failure = new Failure(
        row: $row['__row'] ?? 0,
        attribute: 'employee_id',
        errors: ["Data dengan employee_id {$row['employee_id']} ($employee_name) sudah ada."],
        values: $row
    );

    $this->failures[] = $failure;
    return null;
}

      $monthYear = null;

if (!empty($row['month_year'])) {
    $monthYear = is_numeric($row['month_year'])
        ? Date::excelToDateTimeObject($row['month_year'])->format('Y-m-d')
        : Carbon::parse($row['month_year'])->format('Y-m-d');
}


        // ===== TANGANI CREATED_AT =====
        $createdAt = null;
        if (!empty($row['created_at'])) {
            $createdAt = $this->parseExcelDate($row['created_at'], 'Y-m-d H:i:s', 'created_at', $row['employee_id']);
            if (!$createdAt) return null;
        }

        // FLOAT CASTING
        $attendance         = $row['attendance'] ?? 0;
        $dailyAllowance     = isset($row['daily_allowance']) ? (float)$row['daily_allowance'] : 0.0;
        $houseAllowance     = isset($row['house_allowance']) ? (float)$row['house_allowance'] : 0.0;
        $mealAllowance      = isset($row['meal_allowance']) ? (float)$row['meal_allowance'] : 0.0;
        $transportAllowance = isset($row['transport_allowance']) ? (float)$row['transport_allowance'] : 0.0;
        $bonus              = isset($row['bonus']) ? (float)$row['bonus'] : 0.0;
        $overtime           = isset($row['overtime']) ? (float)$row['overtime'] : 0.0;
        $lateFine           = isset($row['late_fine']) ? (float)$row['late_fine'] : 0.0;
        $punishment         = isset($row['punishment']) ? (float)$row['punishment'] : 0.0;
        $bpjsKes            = isset($row['bpjs_kes']) ? (float)$row['bpjs_kes'] : 0.0;
        $bpjsKet            = isset($row['bpjs_ket']) ? (float)$row['bpjs_ket'] : 0.0;
        $tax                = isset($row['tax']) ? (float)$row['tax'] : 0.0;
        $debt               = isset($row['debt']) ? (float)$row['debt'] : 0.0;

        $deductions = $lateFine + $punishment + $bpjsKes + $bpjsKet + $tax + $debt;
        $salary = ($attendance * $dailyAllowance)
                + $houseAllowance
                + $mealAllowance
                + $transportAllowance
                + $bonus
                + $overtime;

        $takeHome = $salary - $deductions;
        return new Payrolls([
            'employee_id'         => $row['employee_id'],
            'attendance'          => $attendance,
            'daily_allowance'     => $dailyAllowance,
            'house_allowance'     => $houseAllowance,
            'meal_allowance'      => $mealAllowance,
            'transport_allowance' => $transportAllowance,
            'bonus'               => $bonus,
            'overtime'            => $overtime,
            'late_fine'           => $lateFine,
            'punishment'          => $punishment,
            'bpjs_kes'            => $bpjsKes,
            'bpjs_ket'            => $bpjsKet,
            'tax'                 => $tax,
            'debt'                => $debt,
            'deductions'          => $deductions,
            'salary'              => $salary,
            'take_home'           => $takeHome,
            'month_year'          => $monthYear,
            'created_at'          => $createdAt,
            'period'              => $row['period'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            '*.employee_id' => ['nullable'],
          
           
        ];
    }

    private function parseExcelDate($value, $format, $field, $employeeId)
    {
        try {
            if (is_numeric($value)) {
                return Date::excelToDateTimeObject($value)->format($format);
            }

            foreach (['d/m/Y', 'm/d/Y', 'Y-m-d'] as $fmt) {
                try {
                    return Carbon::createFromFormat($fmt, trim($value))->format($format);
                } catch (\Exception $e) {
                    // lanjut
                }
            }

            $this->errors[] = "Format tanggal tidak valid di kolom {$field} untuk employee_id {$employeeId} (isi: {$value}).";
            return null;

        } catch (\Exception $e) {
            $this->errors[] = "Gagal parsing tanggal di kolom {$field} untuk employee_id {$employeeId} (isi: {$value}).";
            return null;
        }
    }
      public function chunkSize(): int
    {
        return 500;
    }
}
