<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\EmployeeSalary;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class EmployeeSalaryImport implements ToCollection, WithHeadingRow, WithValidation
{
    protected string $effectiveDate;
    protected string $createdBy;
    public array $skipped = []; // ← tampung yang diskip

    public function __construct(string $effectiveDate, string $createdBy)
    {
        $this->effectiveDate = $effectiveDate;
        $this->createdBy     = $createdBy;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $employee = Employee::where('employee_pengenal', $row['employee_pengenal'])
                ->whereIn('status', ['Active', 'Mutation', 'Pending', 'On Leave', 'Resign'])
                ->first();

            if (!$employee) {
                Log::warning('EmployeeSalaryImport: NIP tidak ditemukan', [
                    'employee_pengenal' => $row['employee_pengenal'],
                ]);
                $this->skipped[] = "NIP {$row['employee_pengenal']} tidak ditemukan di database.";
                continue;
            }

            $status = strtoupper($employee->status_employee);

            $existing = EmployeeSalary::where('employee_id', $employee->id)
                ->where('effective_date', $this->effectiveDate)
                ->first();

            if ($existing) {
                // Skip & catat
                $this->skipped[] = "NIP {$row['employee_pengenal']} ({$employee->employee_name}) sudah memiliki data salary untuk tanggal {$this->effectiveDate}.";
                continue;
            }

            EmployeeSalary::create([
                'employee_id'        => $employee->id,
                'effective_date'     => $this->effectiveDate,
                'basic_salary'       => $status === 'DW' ? 0 : (float) ($row['basic_salary'] ?? 0),
                'position_allowance' => $status === 'DW' ? 0 : (float) ($row['position_allowance'] ?? 0),
                'daily_rate'         => $status === 'DW' ? (float) ($row['daily_rate'] ?? 0) : 0,
                'meal_allowance' => (float) ($row['meal_allowance'] ?? 0),
                'house_allowance' => (float) ($row['house_allowance'] ?? 0),
                'transport_allowance' => (float) ($row['transport_allowance'] ?? 0),
                'bpjs_kesehatan' => (float) ($row['bpjs_kesehatan'] ?? 0),
                'bpjs_ketenagakerjaan' => (float) ($row['bpjs_ketenagakerjaan'] ?? 0),
    //              'bpjs_kesehatan'       => $status === 'DW' ? 0 : (float) ($row['bpjs_kesehatan']       ?? 0), // ← DW = 0
    // 'bpjs_ketenagakerjaan' => $status === 'DW' ? 0 : (float) ($row['bpjs_ketenagakerjaan'] ?? 0), // ← DW = 0
                'created_by'         => $this->createdBy,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'employee_pengenal' => 'required',
        ];
    }
}