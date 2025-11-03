<?php

// namespace App\Imports;

// use App\Models\Payrolls;
// use Carbon\Carbon;
// use PhpOffice\PhpSpreadsheet\Shared\Date;
// use Maatwebsite\Excel\Concerns\ToModel;
// use Maatwebsite\Excel\Concerns\WithHeadingRow;
// use Maatwebsite\Excel\Concerns\WithValidation;
// use Maatwebsite\Excel\Concerns\SkipsOnFailure;
// use Maatwebsite\Excel\Concerns\SkipsFailures;
// use Maatwebsite\Excel\Concerns\Importable;
// use Illuminate\Support\Facades\Log;
// use Maatwebsite\Excel\Validators\Failure;
// use Illuminate\Validation\ValidationException;
// use Illuminate\Support\MessageBag;
// use Illuminate\Support\Facades\Crypt;

// class PayrollsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
// {
//     use SkipsFailures, Importable;
//     protected $errors;
//     public function __construct(&$errors)
//     {
//         $this->errors = &$errors;
//     }
//     public function model(array $row)
//     {
//         if (!isset($row['employee_id']) || trim($row['employee_id']) === '') {
//             return null;
//         }
//         if (empty($row['employee_id'])) {
//             throw ValidationException::withMessages([
//                 'employee_id' => ['Employee ID kosong.']
//             ]);
//         }
//         $payroll = Payrolls::with('employee')
//             ->where('employee_id', $row['employee_id'])
//             ->first();
//         if ($payroll) {
//             $employee_name = optional($payroll->employee)->employee_name ?? 'Tidak ditemukan';
//             $failure = new Failure(
//                 row: $row['__row'] ?? 0,
//                 attribute: 'employee_id',
//                 errors: ["Data dengan employee_id {$row['employee_id']} ($employee_name) sudah ada."],
//                 values: $row
//             );
//             $this->failures[] = $failure;
//             return null;
//         }
//         $monthYear = null;
//         if (!empty($row['month_year'])) {
//             $monthYear = is_numeric($row['month_year'])
//                 ? Date::excelToDateTimeObject($row['month_year'])->format('Y-m-d')
//                 : Carbon::parse($row['month_year'])->format('Y-m-d');
//         }
//         $createdAt = null;
//         if (!empty($row['created_at'])) {
//             $createdAt = $this->parseExcelDate($row['created_at'], 'Y-m-d H:i:s', 'created_at', $row['employee_id']);
//             if (!$createdAt) return null;
//         }
//         $attendance         = $row['attendance'] ?? 0;
//         $dailyAllowance     = isset($row['daily_allowance']) ? (float)$row['daily_allowance'] : 0.0;
//         $houseAllowance     = isset($row['house_allowance']) ? (float)$row['house_allowance'] : 0.0;
//         $mealAllowance      = isset($row['meal_allowance']) ? (float)$row['meal_allowance'] : 0.0;
//         $transportAllowance = isset($row['transport_allowance']) ? (float)$row['transport_allowance'] : 0.0;
//         $bonus              = isset($row['bonus']) ? (float)$row['bonus'] : 0.0;
//         $overtime           = isset($row['overtime']) ? (float)$row['overtime'] : 0.0;
//         $lateFine           = isset($row['late_fine']) ? (float)$row['late_fine'] : 0.0;
//         $punishment         = isset($row['punishment']) ? (float)$row['punishment'] : 0.0;
//         $bpjsKes            = isset($row['bpjs_kes']) ? (float)$row['bpjs_kes'] : 0.0;
//         $bpjsKet            = isset($row['bpjs_ket']) ? (float)$row['bpjs_ket'] : 0.0;
//         $tax                = isset($row['tax']) ? (float)$row['tax'] : 0.0;
//         $debt               = isset($row['debt']) ? (float)$row['debt'] : 0.0;
//         $deductions = $lateFine + $punishment + $bpjsKes + $bpjsKet + $tax + $debt;
//         $salary = ($attendance * $dailyAllowance)
//             + $houseAllowance
//             + $mealAllowance
//             + $transportAllowance
//             + $bonus
//             + $overtime;
//         $takeHome = $salary - $deductions;
//         return new Payrolls([
//             'employee_id'         => $row['employee_id'],
//             'attendance'          => $attendance,
//             'daily_allowance'     => Crypt::encryptString($dailyAllowance),
//             'house_allowance'     => Crypt::encryptString($houseAllowance),
//             'meal_allowance'      => Crypt::encryptString($mealAllowance),
//             'transport_allowance' => Crypt::encryptString($transportAllowance),
//             'bonus'               => Crypt::encryptString($bonus),
//             'overtime'            => Crypt::encryptString($overtime),
//             'late_fine'           => Crypt::encryptString($lateFine),
//             'punishment'          => Crypt::encryptString($punishment),
//             'bpjs_kes'            => Crypt::encryptString($bpjsKes),
//             'bpjs_ket'            => Crypt::encryptString($bpjsKet),
//             'tax'                 => Crypt::encryptString($tax),
//             'debt'                => Crypt::encryptString($debt),
//             'deductions'          => Crypt::encryptString($deductions),
//             'salary'              => Crypt::encryptString($salary),
//             'take_home'           => Crypt::encryptString($takeHome),
//             'month_year'          => $monthYear,
//             'created_at'          => $createdAt,
//             'period'              => $row['period'] ?? null,
//         ]);
//     }
//     public function rules(): array
//     {
//         return [
//             '*.employee_id' => ['nullable'],
//         ];
//     }
//     private function parseExcelDate($value, $format, $field, $employeeId)
//     {
//         try {
//             if (is_numeric($value)) {
//                 return Date::excelToDateTimeObject($value)->format($format);
//             }
//             foreach (['d/m/Y', 'm/d/Y', 'Y-m-d'] as $fmt) {
//                 try {
//                     return Carbon::createFromFormat($fmt, trim($value))->format($format);
//                 } catch (\Exception $e) {
//                 }
//             }
//             $this->errors[] = "Format tanggal tidak valid di kolom {$field} untuk employee_id {$employeeId} (isi: {$value}).";
//             return null;
//         } catch (\Exception $e) {
//             $this->errors[] = "Gagal parsing tanggal di kolom {$field} untuk employee_id {$employeeId} (isi: {$value}).";
//             return null;
//         }
//     }
//     public function chunkSize(): int
//     {
//         return 500;
//     }
// }

// namespace App\Imports;

// use App\Models\Payrolls;
// use App\Models\Employee;
// use Carbon\Carbon;
// use PhpOffice\PhpSpreadsheet\Shared\Date;
// use Maatwebsite\Excel\Concerns\ToModel;
// use Maatwebsite\Excel\Concerns\WithHeadingRow;
// use Maatwebsite\Excel\Concerns\WithValidation;
// use Maatwebsite\Excel\Concerns\SkipsOnFailure;
// use Maatwebsite\Excel\Concerns\SkipsFailures;
// use Maatwebsite\Excel\Concerns\Importable;
// use Illuminate\Support\Facades\Crypt;
// use Maatwebsite\Excel\Validators\Failure;
// use Illuminate\Validation\ValidationException;

// class PayrollsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
// {
//     use SkipsFailures, Importable;

//     protected $errors;

//     public function __construct(&$errors)
//     {
//         $this->errors = &$errors;
//     }
//     public function model(array $row)
// {
//     if (collect($row)->filter(fn($v) => trim((string)$v) !== '')->isEmpty()) {
//         return null;
//     }

//     // 2️⃣ Pastikan kolom employee_name diisi
//     if (!isset($row['employee_name']) || trim($row['employee_name']) === '') {
//         return null; 
//     }

//     $employeeName = trim($row['employee_name']);

//     // 3️⃣ Cari employee_id berdasarkan nama (case-insensitive, trim)
//     $employee = Employee::whereRaw('LOWER(TRIM(employee_name)) = ?', [strtolower($employeeName)])->first();

//     if (!$employee) {
//         $this->errors[] = "Employee dengan nama '{$employeeName}' tidak ditemukan di database.";
//         return null;
//     }

//     // 4️⃣ Cek apakah payroll untuk employee ini sudah ada (optional: berdasarkan bulan-tahun)
//     $existing = Payrolls::where('employee_id', $employee->id)
//         ->where('month_year', $row['month_year'] ?? null)
//         ->first();

//     if ($existing) {
//         $failure = new Failure(
//             row: $row['__row'] ?? 0,
//             attribute: 'employee_name',
//             errors: ["Payroll untuk {$employeeName} sudah ada."],
//             values: $row
//         );
//         $this->failures[] = $failure;
//         return null;
//     }

//     // 5️⃣ Parsing kolom tanggal month_year
//     $monthYear = null;
//     if (!empty($row['month_year'])) {
//         try {
//             $monthYear = is_numeric($row['month_year'])
//                 ? Date::excelToDateTimeObject($row['month_year'])->format('Y-m-d')
//                 : Carbon::parse($row['month_year'])->format('Y-m-d');
//         } catch (\Exception $e) {
//             $this->errors[] = "Format tanggal tidak valid untuk kolom month_year di {$employeeName}.";
//             return null;
//         }
//     }

//     // 6️⃣ Parsing kolom created_at
//     $createdAt = null;
//     if (!empty($row['created_at'])) {
//         $createdAt = $this->parseExcelDate($row['created_at'], 'Y-m-d H:i:s', 'created_at', $employeeName);
//         if (!$createdAt) return null;
//     }

//     // 7️⃣ Hitung semua komponen payroll
//     $attendance         = $row['attendance'] ?? 0;
//     $dailyAllowance     = (float)($row['daily_allowance'] ?? 0);
//     $houseAllowance     = (float)($row['house_allowance'] ?? 0);
//     $mealAllowance      = (float)($row['meal_allowance'] ?? 0);
//     $transportAllowance = (float)($row['transport_allowance'] ?? 0);
//     $bonus              = (float)($row['bonus'] ?? 0);
//     $overtime           = (float)($row['overtime'] ?? 0);
//     $lateFine           = (float)($row['late_fine'] ?? 0);
//     $punishment         = (float)($row['punishment'] ?? 0);
//     $bpjsKes            = (float)($row['bpjs_kes'] ?? 0);
//     $bpjsKet            = (float)($row['bpjs_ket'] ?? 0);
//     $tax                = (float)($row['tax'] ?? 0);
//     $debt               = (float)($row['debt'] ?? 0);

//     $deductions = $lateFine + $punishment + $bpjsKes + $bpjsKet + $tax + $debt;
//     $salary = ($attendance * $dailyAllowance)
//         + $houseAllowance
//         + $mealAllowance
//         + $transportAllowance
//         + $bonus
//         + $overtime;

//     $takeHome = $salary - $deductions;

//     // 8️⃣ Simpan ke tabel payrolls
//     return new Payrolls([
//         'employee_id'         => $employee->id,
//         'attendance'          => $attendance,
//         'daily_allowance'     => Crypt::encryptString($dailyAllowance),
//         'house_allowance'     => Crypt::encryptString($houseAllowance),
//         'meal_allowance'      => Crypt::encryptString($mealAllowance),
//         'transport_allowance' => Crypt::encryptString($transportAllowance),
//         'bonus'               => Crypt::encryptString($bonus),
//         'overtime'            => Crypt::encryptString($overtime),
//         'late_fine'           => Crypt::encryptString($lateFine),
//         'punishment'          => Crypt::encryptString($punishment),
//         'bpjs_kes'            => Crypt::encryptString($bpjsKes),
//         'bpjs_ket'            => Crypt::encryptString($bpjsKet),
//         'tax'                 => Crypt::encryptString($tax),
//         'debt'                => Crypt::encryptString($debt),
//         'deductions'          => Crypt::encryptString($deductions),
//         'salary'              => Crypt::encryptString($salary),
//         'take_home'           => Crypt::encryptString($takeHome),
//         'month_year'          => $monthYear,
//         'created_at'          => $createdAt,
//         'period'              => $row['period'] ?? null,
//     ]);
// }


//     public function rules(): array
//     {
//         return [
//             '*.employee_name' => ['nullable', 'string','distinct'],
//         ];
//     }

//     private function parseExcelDate($value, $format, $field, $employeeName)
//     {
//         try {
//             if (is_numeric($value)) {
//                 return Date::excelToDateTimeObject($value)->format($format);
//             }
//             foreach (['d/m/Y', 'm/d/Y', 'Y-m-d'] as $fmt) {
//                 try {
//                     return Carbon::createFromFormat($fmt, trim($value))->format($format);
//                 } catch (\Exception $e) {
//                     // coba format lain
//                 }
//             }
//             $this->errors[] = "Format tanggal tidak valid di kolom {$field} untuk {$employeeName} (isi: {$value}).";
//             return null;
//         } catch (\Exception $e) {
//             $this->errors[] = "Gagal parsing tanggal di kolom {$field} untuk {$employeeName} (isi: {$value}).";
//             return null;
//         }
//     }

//     public function chunkSize(): int
//     {
//         return 500;
//     }
// }
// namespace App\Imports;

// use App\Models\Payrolls;
// use App\Models\Employee;
// use Carbon\Carbon;
// use Illuminate\Support\Facades\Crypt;
// use Maatwebsite\Excel\Concerns\ToModel;
// use Maatwebsite\Excel\Concerns\WithHeadingRow;
// use Maatwebsite\Excel\Concerns\WithValidation;
// use Maatwebsite\Excel\Validators\Failure;
// use PhpOffice\PhpSpreadsheet\Shared\Date;
// use Maatwebsite\Excel\Concerns\WithChunkReading;

// class PayrollsImport implements ToModel, WithHeadingRow, WithValidation, WithChunkReading
// {
//     private array $errors = [];
//     private array $failures = [];
//     private array $importedNames = []; // untuk deteksi duplikat di Excel

//     public function model(array $row)
//     {
//         // 1️⃣ Skip baris kosong
//         if (collect($row)->filter(fn($v) => trim((string)$v) !== '')->isEmpty()) {
//             return null;
//         }

//         // 2️⃣ Pastikan kolom employee_name diisi
//         if (!isset($row['employee_name']) || trim($row['employee_name']) === '') {
//             return null;
//         }

//         $employeeName = trim($row['employee_name']);
//         $normalizedName = strtolower($employeeName);

//         // 3️⃣ Cegah duplikat antar baris Excel
//         if (in_array($normalizedName, $this->importedNames)) {
//             $failure = new Failure(
//                 row: $row['__row'] ?? 0,
//                 attribute: 'employee_name',
//                 errors: ["Employee '{$employeeName}' duplikat di file Excel."],
//                 values: $row
//             );
//             $this->failures[] = $failure;
//             return null;
//         }
//         $this->importedNames[] = $normalizedName;

//         // 4️⃣ Cari employee_id dari database
//         $employee = Employee::whereRaw('LOWER(TRIM(employee_name)) = ?', [$normalizedName])->first();

//         if (!$employee) {
//             $this->errors[] = "Employee '{$employeeName}' tidak ditemukan di database.";
//             return null;
//         }

//         // 5️⃣ Parsing month_year untuk validasi DB
//         $monthYear = null;
//         if (!empty($row['month_year'])) {
//             try {
//                 $monthYear = is_numeric($row['month_year'])
//                     ? Date::excelToDateTimeObject($row['month_year'])->format('Y-m-d')
//                     : Carbon::parse($row['month_year'])->format('Y-m-d');
//             } catch (\Exception $e) {
//                 $this->errors[] = "Format tanggal tidak valid untuk kolom month_year di {$employeeName}.";
//                 return null;
//             }
//         }

//         // 6️⃣ Cegah duplikat di database (employee_id + month_year)
//         $exists = Payrolls::where('employee_id', $employee->id)
//             ->when($monthYear, fn($q) => $q->where('month_year', $monthYear))
//             ->exists();

//         if ($exists) {
//             $failure = new Failure(
//                 row: $row['__row'] ?? 0,
//                 attribute: 'employee_name',
//                 errors: ["Payroll untuk '{$employeeName}' sudah ada di database."],
//                 values: $row
//             );
//             $this->failures[] = $failure;
//             return null;
//         }

//         // 7️⃣ Parsing created_at jika ada
//         $createdAt = null;
//         if (!empty($row['created_at'])) {
//             $createdAt = $this->parseExcelDate($row['created_at'], 'Y-m-d H:i:s', 'created_at', $employeeName);
//             if (!$createdAt) return null;
//         }

//         // 8️⃣ Hitung komponen payroll
//         $attendance         = $row['attendance'] ?? 0;
//         $dailyAllowance     = (float)($row['daily_allowance'] ?? 0);
//         $houseAllowance     = (float)($row['house_allowance'] ?? 0);
//         $mealAllowance      = (float)($row['meal_allowance'] ?? 0);
//         $transportAllowance = (float)($row['transport_allowance'] ?? 0);
//         $bonus              = (float)($row['bonus'] ?? 0);
//         $overtime           = (float)($row['overtime'] ?? 0);
//         $lateFine           = (float)($row['late_fine'] ?? 0);
//         $punishment         = (float)($row['punishment'] ?? 0);
//         $bpjsKes            = (float)($row['bpjs_kes'] ?? 0);
//         $bpjsKet            = (float)($row['bpjs_ket'] ?? 0);
//         $tax                = (float)($row['tax'] ?? 0);
//         $debt               = (float)($row['debt'] ?? 0);

//         $deductions = $lateFine + $punishment + $bpjsKes + $bpjsKet + $tax + $debt;
//         $salary = ($attendance * $dailyAllowance)
//             + $houseAllowance
//             + $mealAllowance
//             + $transportAllowance
//             + $bonus
//             + $overtime;

//         $takeHome = $salary - $deductions;

//         // 9️⃣ Simpan data payroll baru
//         return new Payrolls([
//             'employee_id'         => $employee->id,
//             'attendance'          => $attendance,
//             'daily_allowance'     => Crypt::encryptString($dailyAllowance),
//             'house_allowance'     => Crypt::encryptString($houseAllowance),
//             'meal_allowance'      => Crypt::encryptString($mealAllowance),
//             'transport_allowance' => Crypt::encryptString($transportAllowance),
//             'bonus'               => Crypt::encryptString($bonus),
//             'overtime'            => Crypt::encryptString($overtime),
//             'late_fine'           => Crypt::encryptString($lateFine),
//             'punishment'          => Crypt::encryptString($punishment),
//             'bpjs_kes'            => Crypt::encryptString($bpjsKes),
//             'bpjs_ket'            => Crypt::encryptString($bpjsKet),
//             'tax'                 => Crypt::encryptString($tax),
//             'debt'                => Crypt::encryptString($debt),
//             'deductions'          => Crypt::encryptString($deductions),
//             'salary'              => Crypt::encryptString($salary),
//             'take_home'           => Crypt::encryptString($takeHome),
//             'month_year'          => $monthYear,
//             'created_at'          => $createdAt,
//             'period'              => $row['period'] ?? null,
//         ]);
//     }

//     public function rules(): array
//     {
//         return [
//             '*.employee_name' => ['nullable', 'string', 'distinct'],
//             '*.month_year' => ['nullable'],
//         ];
//     }

//     private function parseExcelDate($value, $format, $field, $employeeName)
//     {
//         try {
//             if (is_numeric($value)) {
//                 return Date::excelToDateTimeObject($value)->format($format);
//             }

//             foreach (['d/m/Y', 'm/d/Y', 'Y-m-d'] as $fmt) {
//                 try {
//                     return Carbon::createFromFormat($fmt, trim($value))->format($format);
//                 } catch (\Exception) {}
//             }

//             $this->errors[] = "Format tanggal tidak valid di kolom {$field} untuk {$employeeName} (isi: {$value}).";
//             return null;
//         } catch (\Exception) {
//             $this->errors[] = "Gagal parsing tanggal di kolom {$field} untuk {$employeeName} (isi: {$value}).";
//             return null;
//         }
//     }

//     public function chunkSize(): int
//     {
//         return 500;
//     }
// }

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
        // 1️⃣ Skip baris kosong
        if (collect($row)->filter(fn($v) => trim((string)$v) !== '')->isEmpty()) {
            return null;
        }

        $employeePengenal = trim((string)($row['employee_pengenal'] ?? ''));
        if ($employeePengenal === '') {
            return null;
        }

        // 2️⃣ Cegah duplikat antar baris di file Excel
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

        // 3️⃣ Cari employee di database berdasarkan employee_pengenal
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

        // 4️⃣ Parsing month_year
        $monthYear = $this->parseExcelDate($row['month_year'] ?? null, 'Y-m-d', 'month_year', $employeePengenal);
        if ($monthYear === false) {
            return null;
        }

        // 5️⃣ Cegah duplikat di database (employee_id + month_year)
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

        // 6️⃣ Parsing created_at opsional
        $createdAt = $this->parseExcelDate($row['created_at'] ?? null, 'Y-m-d H:i:s', 'created_at', $employeePengenal);
        if ($createdAt === false) {
            return null;
        }

        // 7️⃣ Komponen payroll
        $attendance         = $row['attendance'] ?? 0;
        $dailyAllowance     = (float)($row['daily_allowance'] ?? 0);
        $houseAllowance     = (float)($row['house_allowance'] ?? 0);
        $mealAllowance      = (float)($row['meal_allowance'] ?? 0);
        $transportAllowance = (float)($row['transport_allowance'] ?? 0);
        $bonus              = (float)($row['bonus'] ?? 0);
        $overtime           = (float)($row['overtime'] ?? 0);
        $lateFine           = (float)($row['late_fine'] ?? 0);
        $punishment         = (float)($row['punishment'] ?? 0);
        $bpjsKes            = (float)($row['bpjs_kes'] ?? 0);
        $bpjsKet            = (float)($row['bpjs_ket'] ?? 0);
        $tax                = (float)($row['tax'] ?? 0);
        $debt               = (float)($row['debt'] ?? 0);

        $deductions = $lateFine + $punishment + $bpjsKes + $bpjsKet + $tax + $debt;
        $salary = ($attendance * $dailyAllowance)
            + $houseAllowance
            + $mealAllowance
            + $transportAllowance
            + $bonus
            + $overtime;

        $takeHome = $salary - $deductions;

        // 8️⃣ Simpan data baru
        return new Payrolls([
            'employee_id'         => $employee->id,
            'attendance'          => $attendance,
            'daily_allowance'     => Crypt::encryptString($dailyAllowance),
            'house_allowance'     => Crypt::encryptString($houseAllowance),
            'meal_allowance'      => Crypt::encryptString($mealAllowance),
            'transport_allowance' => Crypt::encryptString($transportAllowance),
            'bonus'               => Crypt::encryptString($bonus),
            'overtime'            => Crypt::encryptString($overtime),
            'late_fine'           => Crypt::encryptString($lateFine),
            'punishment'          => Crypt::encryptString($punishment),
            'bpjs_kes'            => Crypt::encryptString($bpjsKes),
            'bpjs_ket'            => Crypt::encryptString($bpjsKet),
            'tax'                 => Crypt::encryptString($tax),
            'debt'                => Crypt::encryptString($debt),
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

  // $employeePengenal = trim($row['employee_pengenal'] ?? '');
        // if ($employeePengenal === '') {
        //     return null;
        // }

        // $normalizedName = strtolower($employeePengenal);

        // if (in_array($normalizedName, $this->importedNames)) {
        //     $this->onFailure(new Failure(
        //         $row['__row'] ?? 0,
        //         'employee_pengenal',
        //         ["Employee '{$employeePengenal}' duplikat di file Excel."],
        //         $row
        //     ));
        //     return null;
        // }
        // $this->importedNames[] = $normalizedName;