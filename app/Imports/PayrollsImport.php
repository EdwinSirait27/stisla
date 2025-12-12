<?php
namespace App\Imports;
use App\Models\Payrolls;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Validators\Failure;
use PhpOffice\PhpSpreadsheet\Shared\Date;
class PayrollsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, WithChunkReading
{
    use \Maatwebsite\Excel\Concerns\SkipsFailures;

    private array $importedIds = [];

    public function model(array $row)
    {
        if (collect($row)->filter(fn($v) => trim((string)$v) !== '')->isEmpty()) {
            return null;
        }
        $employeePengenal = trim((string)($row['employee_pengenal'] ?? ''));
        if ($employeePengenal === '') {
            return null;
        }
        if (in_array($employeePengenal, $this->importedIds)) {
            $this->onFailure(new Failure(
                $row['__row'] ?? 0,
                'employee_pengenal',
                ["Employee dengan pengenal {$employeePengenal} duplikat di file Excel."],
                $row
            ));
            return null;
        }
        $this->importedIds[] = $employeePengenal;
        $employee = Employee::where('employee_pengenal', $employeePengenal)->first();
        if (!$employee) {
            $this->onFailure(new Failure(
                $row['__row'] ?? 0,
                'employee_pengenal',
                ["Employee dengan pengenal {$employeePengenal} tidak ditemukan di database."],
                $row
            ));
            return null;
        }
        $monthYear = $this->parseExcelDate($row['month_year'] ?? null, 'Y-m-d', 'month_year', $employeePengenal);
        if ($monthYear === false) {
            return null;
        }
        $exists = Payrolls::where('employee_id', $employee->id)
            ->when($monthYear, fn($q) => $q->where('month_year', $monthYear))
            ->exists();
        if ($exists) {
            $this->onFailure(new Failure(
                $row['__row'] ?? 0,
                'employee_pengenal',
                ["Payroll untuk pengenal {$employeePengenal} dengan tanggal {$monthYear} sudah ada di database."],
                $row
            ));
            return null;
        }
        $createdAt = $this->parseExcelDate($row['created_at'] ?? null, 'Y-m-d H:i:s', 'created_at', $employeePengenal);
        if ($createdAt === false) {
            return null;
        }

$attendance         = $row['attendance'] ?? 0;
$basicSalary        = (float)($row['basic_salary']);
$dailyAllowance     = (float)($row['daily_allowance']);
$houseAllowance     = (float)($row['house_allowance']);
$mealAllowance      = (float)($row['meal_allowance']);
$transportAllowance = (float)($row['transport_allowance']);
$bonus              = (float)($row['bonus']);
$positionalAllowance= (float)($row['allowance']);
$reamburse          = (float)($row['reamburse']);
$overtime           = (float)($row['overtime']);

$lateFine           = (float)($row['late_fine']);
$punishment         = (float)($row['punishment']);
$bpjsKes            = (float)($row['bpjs_kes']);
$bpjsKet            = (float)($row['bpjs_ket']);
$tax                = (float)($row['tax']);
$debt               = (float)($row['debt']);

$deductions = $lateFine + $punishment + $bpjsKes + $bpjsKet + $tax + $debt;
$status = $employee->status_employee ?? 'DW';

// Default rumus: DW
// if ($status === 'PKWT') {
if (in_array($status, ['PKWT', 'On Job Training'])) {

$totalHariKerja = 26;

// JANGAN round di sini
$prorata = ($basicSalary + $positionalAllowance) / $totalHariKerja;

// hitung salary full dulu
$salary =
    ($prorata * $attendance)
    + $houseAllowance
    + $mealAllowance
    + $transportAllowance
    + $bonus
    + $overtime
    + $reamburse;

// ROUND DI AKHIR SAJA
$salary = round($salary, 0);
$grossSalary = $salary;



} else {
    $totalHariKerja = 26;

    // DW (daily worker)
$grossSalary        =  $dailyAllowance * $attendance;

    $salary =
        $basicSalary
        + ($dailyAllowance * $attendance)
        + $houseAllowance
        + $positionalAllowance
        + $mealAllowance
        + $transportAllowance
        + $bonus
        + $overtime
        + $reamburse;
}
$takeHome = $salary - $deductions;

        return new Payrolls([
            'employee_id'         => $employee->id,
            'attendance'          => $attendance,
            'basic_salary'     => Crypt::encryptString($basicSalary),
            'daily_allowance'     => Crypt::encryptString($dailyAllowance),
            'house_allowance'     => Crypt::encryptString($houseAllowance),
            'meal_allowance'      => Crypt::encryptString($mealAllowance),
            'transport_allowance' => Crypt::encryptString($transportAllowance),
            'bonus'               => Crypt::encryptString($bonus),
            'allowance'            => Crypt::encryptString($positionalAllowance),
            'reamburse'            => Crypt::encryptString($reamburse),
            'overtime'            => Crypt::encryptString($overtime),
            'late_fine'           => Crypt::encryptString($lateFine),
            'punishment'          => Crypt::encryptString($punishment),
            'bpjs_kes'            => Crypt::encryptString($bpjsKes),
            'bpjs_ket'            => Crypt::encryptString($bpjsKet),
            'tax'                 => Crypt::encryptString($tax),
            'debt'                => Crypt::encryptString($debt),
            'gross_salary'                => Crypt::encryptString($grossSalary),
            'deductions'          => Crypt::encryptString($deductions),
            'salary'              => Crypt::encryptString($salary),
            'take_home'           => Crypt::encryptString($takeHome),
            'month_year'          => $monthYear,
            'created_at'          => $createdAt,
            'period'              => $row['period'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            '*.employee_pengenal' => ['required', 'numeric', 'distinct'],
            '*.month_year' => ['nullable'],
        ];
    }

    private function parseExcelDate($value, $format, $field, $identifier)
    {
        if (empty($value)) return null;

        try {
            if (is_numeric($value)) {
                return Date::excelToDateTimeObject($value)->format($format);
            }

            foreach (['d/m/Y', 'm/d/Y', 'Y-m-d'] as $fmt) {
                try {
                    return Carbon::createFromFormat($fmt, trim($value))->format($format);
                } catch (\Exception) {
                    continue;
                }
            }

            $this->onFailure(new Failure(
                0,
                $field,
                ["Format tanggal tidak valid di kolom {$field} untuk employee pengenal {$identifier} (isi: {$value})."],
                []
            ));
            return false;
        } catch (\Exception) {
            $this->onFailure(new Failure(
                0,
                $field,
                ["Gagal parsing tanggal di kolom {$field} untuk employee pengenal {$identifier} (isi: {$value})."],
                []
            ));
            return false;
        }
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
//         $attendance         = $row['attendance'] ?? 0;
//         $basicSalary     = (float)($row['basic_salary']);
//         $dailyAllowance     = (float)($row['daily_allowance']);
//         $houseAllowance     = (float)($row['house_allowance']);
//         $mealAllowance      = (float)($row['meal_allowance']);
//         $transportAllowance = (float)($row['transport_allowance']);
//         $bonus              = (float)($row['bonus']);
//         $allowance           = (float)($row['allowance']);
//         $reamburse           = (float)($row['reamburse']);
//         $overtime           = (float)($row['overtime']);
//         $lateFine           = (float)($row['late_fine']);
//         $punishment         = (float)($row['punishment']);
//         $bpjsKes            = (float)($row['bpjs_kes']);
//         $bpjsKet            = (float)($row['bpjs_ket']);
//         $tax                = (float)($row['tax']);
//         $debt               = (float)($row['debt']);

//         $deductions = $lateFine + $punishment + $bpjsKes + $bpjsKet + $tax + $debt;
       
//         $salary = 
//     $basicSalary
//     + ($dailyAllowance * $attendance)
//     + $houseAllowance
//     + $allowance
//     + $reamburse
//     + $mealAllowance
//     + $transportAllowance
//     + $bonus
//     + $overtime;
// $takeHome = $salary - $deductions;
 // $salary = ($basicSalary + $dailyAllowance) * $attendance  
        //     + $houseAllowance
        //     + $allowance
        //     + $reamburse
        //     + $mealAllowance
        //     + $transportAllowance
        //     + $bonus
        //     + $overtime;
        // $takeHome = $salary - $deductions;


        
// status employee (PKWT atau DW)
// $status = $row['status_employee'];

// // Default: DW
// if ($status === 'PKWT') {

//     // PKWT: (basic + allowance) dibagi 26 hari kerja lalu dikali hadir
//     $totalHariKerja = 26;

//     $prorata = ($basicSalary + $allowance) / $totalHariKerja;
//     $salary = 
//         ($prorata * $attendance)
//         + $houseAllowance
//         + $mealAllowance
//         + $transportAllowance
//         + $bonus
//         + $overtime
//         + $reamburse;

// } else {

//     // DW (daily worker)
//     $salary = 
//         $basicSalary
//         + ($dailyAllowance * $attendance)
//         + $houseAllowance
//         + $allowance
//         + $mealAllowance
//         + $transportAllowance
//         + $bonus
//         + $overtime
//         + $reamburse;
// }

// $takeHome = $salary - $deductions;
// status employee (PKWT atau DW diambil dari database)
// $status = $employee->status_employee ?? 'DW';

// // Default rumus: DW
// if ($status === 'PKWT') {

//     // PKWT: (basic + allowance) dibagi 26 hari kerja lalu dikali hadir
//     $totalHariKerja = 26;

//     $prorata = ($basicSalary + $allowance) / $totalHariKerja;

//     $salary =
//         ($prorata * $attendance)
//         + $houseAllowance
//         + $mealAllowance
//         + $transportAllowance
//         + $bonus
//         + $overtime
//         + $reamburse;

// } else {

//     // DW (daily worker)
//     $salary =
//         $basicSalary
//         + ($dailyAllowance * $attendance)
//         + $houseAllowance
//         + $allowance
//         + $mealAllowance
//         + $transportAllowance
//         + $bonus
//         + $overtime
//         + $reamburse;
// }
// $takeHome = $salary - $deductions;

    // $totalHariKerja = 26;
    
    // $prorata = ($basicSalary + $positionalAllowance) / $totalHariKerja;
    // $grossSalary        = $basicSalary + $positionalAllowance;

    // $salary =
    //     ($prorata * $attendance)
    //     + $houseAllowance
    //     + $mealAllowance
    //     + $transportAllowance
    //     + $bonus
    //     + $overtime
    //     + $reamburse;
//     $totalHariKerja = 26;

// // prorata gaji tetap
// $prorata = round(($basicSalary + $positionalAllowance) / $totalHariKerja, 2);

// // total salary sebelum potongan
// $salary =
//     round($prorata * $attendance, 2)
//     + $houseAllowance
//     + $mealAllowance
//     + $transportAllowance
//     + $bonus
//     + $overtime
//     + $reamburse;

// // gross = salary sebelum potongan
// $grossSalary = $salary;