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
        $basicSalary     = (float)($row['basic_salary']);
        $dailyAllowance     = (float)($row['daily_allowance']);
        $houseAllowance     = (float)($row['house_allowance'] ?? 0);
        $mealAllowance      = (float)($row['meal_allowance'] ?? 0);
        $transportAllowance = (float)($row['transport_allowance'] ?? 0);
        $bonus              = (float)($row['bonus'] ?? 0);
        $allowance           = (float)($row['allowance']);
        $reamburse           = (float)($row['reamburse'] ?? 0);
        $overtime           = (float)($row['overtime'] ?? 0);
        $lateFine           = (float)($row['late_fine'] ?? 0);
        $punishment         = (float)($row['punishment'] ?? 0);
        $bpjsKes            = (float)($row['bpjs_kes'] ?? 0);
        $bpjsKet            = (float)($row['bpjs_ket'] ?? 0);
        $tax                = (float)($row['tax'] ?? 0);
        $debt               = (float)($row['debt'] ?? 0);

        $deductions = $lateFine + $punishment + $bpjsKes + $bpjsKet + $tax + $debt;
        $salary = ($basicSalary + $dailyAllowance) * $attendance  
            + $houseAllowance
            + $allowance
            + $reamburse
            + $mealAllowance
            + $transportAllowance
            + $bonus
            + $overtime;
        $takeHome = $salary - $deductions;
        // 8️⃣ Simpan data baru
        return new Payrolls([
            'employee_id'         => $employee->id,
            'attendance'          => $attendance,
            'basic_salary'     => Crypt::encryptString($basicSalary),
            'daily_allowance'     => Crypt::encryptString($dailyAllowance),
            'house_allowance'     => Crypt::encryptString($houseAllowance),
            'meal_allowance'      => Crypt::encryptString($mealAllowance),
            'transport_allowance' => Crypt::encryptString($transportAllowance),
            'bonus'               => Crypt::encryptString($bonus),
            'allowance'            => Crypt::encryptString($allowance),
            'reamburse'            => Crypt::encryptString($reamburse),
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