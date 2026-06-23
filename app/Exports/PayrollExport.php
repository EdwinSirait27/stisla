<?php

namespace App\Exports;

use App\Models\Payroll;
use App\Models\PayrollPeriod;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting; 
// class PayrollExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize

class PayrollExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize, WithColumnFormatting
{
    public function __construct(
        protected PayrollPeriod $period,
        protected ?string $status         = null,
        protected ?string $statusEmployee = null,
        protected ?string $storeName      = null,
        protected ?string $positionName      = null,
        protected ?string $companyName      = null,
        protected ?string $gradingName      = null,
        protected ?string $departmentName      = null,
    ) {}

    public function query()
    {
        $query = Payroll::with([
            'employee:id,employee_name,employee_pengenal,status_employee,grading_id,company_id,banks_id,bank_account_number',
            'employee.store:id,name','employee.company:id,name','employee.grading:id,grading_name','employee.department:id,department_name','employee.bank:id,name',
        ])->where('payroll_period_id', $this->period->id);

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->statusEmployee) {
            $query->whereHas('employee', fn($q) =>
                $q->where('status_employee', $this->statusEmployee)
            );
        }

        if ($this->storeName) {
            $query->whereHas('employee.store', fn($q) =>
                $q->where('name', $this->storeName)
            );
        }
        if ($this->companyName) {
            $query->whereHas('employee.company', fn($q) =>
                $q->where('name', $this->companyName)
            );
        }
        if ($this->departmentName) {
            $query->whereHas('employee.department', fn($q) =>
                $q->where('department_name', $this->departmentName)
            );
        }
        if ($this->gradingName) {
            $query->whereHas('employee.grading', fn($q) =>
                $q->where('grading_name', $this->gradingName)
            );
        }

        return $query->orderBy('created_at');
    }

    public function title(): string
    {
        return $this->period->period_label;
    }

    public function headings(): array
    {
        return [
            'No',
            'NIP',
            'Nama Karyawan',
            'Company',
            'Department',
            'Grading',
            'Location',
            'Position',
            'Status',
            'Bank',
            'No Rekening',
            // 'Working Days',
            'Attendance Days',
            // 'Absent Days',
            'Prorate',
            'Basic Salary',
            'Position Allowance',
            'Daily Rate',
            'Gross Salary',
            'Total Income',
            'Total Deduction',
            'Net Salary',
            'Status Payroll',
        ];
    }

    // public function map($row): array
    // {
    //     static $no = 0;
    //     $no++;

    //     return [
    //         $no,
    //         $row->employee->employee_pengenal   ?? '-',
    //         $row->employee->employee_name        ?? '-',
    //         $row->employee->company->name          ?? '-',
    //         $row->employee->department?->first()->department_name          ?? '-',
    //         $row->employee->grading->grading_name          ?? '-',
    //         $row->employee->store?->first()->name          ?? '-',
    //         $row->employee->position?->first()->name          ?? '-',
    //         $row->employee->status_employee      ?? '-',
    //         $row->employee->bank->name            ?? '-',
    //         $row->employee->bank_account_number         ?? '-',
    //         // $row->working_days,
    //         $row->attendance_days,
    //         // $row->absent_days,
    //         $row->is_prorate
    //             ? 'Ya (' . ($row->prorate_ratio * 100) . '%)'
    //             : 'Tidak',
    //         $row->basic_salary,
    //         $row->position_allowance,
    //         $row->daily_rate,
            
    //         $row->gross_salary,
    //         $row->total_income,
    //         $row->total_deduction,
    //         $row->net_salary,
    //         ucfirst($row->status),
    //     ];
    // }

    // public function styles(Worksheet $sheet): array
    // {
    //     return [
    //         // Header row
    //         1 => [
    //             'font' => [
    //                 'bold'  => true,
    //                 'color' => ['rgb' => 'FFFFFF'],
    //             ],
    //             'fill' => [
    //                 'fillType'   => Fill::FILL_SOLID,
    //                 'startColor' => ['rgb' => '1d4ed8'],
    //             ],
    //             'alignment' => [
    //                 'horizontal' => Alignment::HORIZONTAL_CENTER,
    //             ],
    //         ],
    //     ];
    // }
    public function map($row): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $row->employee->employee_pengenal   ?? '-',
            $row->employee->employee_name        ?? '-',
            $row->employee->company->name          ?? '-',
            $row->employee->department?->first()->department_name          ?? '-',
            $row->employee->grading->grading_name          ?? '-',
            $row->employee->store?->first()->name          ?? '-',
            $row->employee->position?->first()->name          ?? '-',
            $row->employee->status_employee      ?? '-',
            $row->employee->bank->name            ?? '-',
            // ↓ Cast ke string agar tidak jadi scientific notation
            (string) ($row->employee->bank_account_number ?? '-'),
            $row->attendance_days,
            $row->is_prorate
                ? 'Ya (' . ($row->prorate_ratio * 100) . '%)'
                : 'Tidak',
            // ↓ Cast ke int/float agar column formatting bekerja
            (int) $row->basic_salary,
            (int) $row->position_allowance,
            (int) $row->daily_rate,
            (int) $row->gross_salary,
            (int) $row->total_income,
            (int) $row->total_deduction,
            (int) $row->net_salary,
            ucfirst($row->status),
        ];
    }

    /**
     * Format kolom: nomor rekening → @text, salary → number dengan separator ribuan
     * Kolom dihitung dari index 1 (A=1, B=2, dst)
     */
    public function columnFormats(): array
    {
        return [
            'K' => NumberFormat::FORMAT_TEXT,           // No Rekening → plain text
            'N' => '#,##0',                             // Basic Salary
            'O' => '#,##0',                             // Position Allowance
            'P' => '#,##0',                             // Daily Rate
            'Q' => '#,##0',                             // Gross Salary
            'R' => '#,##0',                             // Total Income
            'S' => '#,##0',                             // Total Deduction
            'T' => '#,##0',                             // Net Salary
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold'  => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1d4ed8'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }
}
// namespace App\Exports;

// use App\Models\Payrolls;
// use Maatwebsite\Excel\Concerns\FromCollection;
// use Maatwebsite\Excel\Concerns\WithHeadings;
// use Maatwebsite\Excel\Concerns\WithMapping;

// class PayrollExport implements FromCollection, WithHeadings, WithMapping
// {
//   protected $monthYear;

//     public function __construct($monthYear = null)
//     {
//         $this->monthYear = $monthYear;
//     }

//     public function collection()
//     {
//         $query = Payrolls::with('employee');

//         if ($this->monthYear) {
//             $query->where('month_year', $this->monthYear);
//         }

//         return $query->get();
//     }

//     public function headings(): array
//     {
//         return [
//             'Employee Name',
//             'NIP',
//             'Attendance',
//             'Daily Allowance',
//             'House Allowance',
//             'Meal Allowance',
//             'Transport Allowance',
//             'Reimburse',
//             'Bonus',
//             'Overtime',
//             'Late Fine',
//             'BPJS Kes',
//             'BPJS Ket',
//             'Tax',
//             'Debt',
//             'Total Salary',
//             'Take Home Pay',
//             'Month Year',
//             'Period'
//         ];
//     }

//     public function map($row): array
//     {
//         return [
//             $row->employee->employee_name ?? '-',
//             $row->employee->employee_pengenal ?? '-',
//             $row->attendance,
//             $row->daily_allowance,
//             $row->house_allowance,
//             $row->meal_allowance,
//             $row->transport_allowance,
//             $row->reamburse,
//             $row->bonus,
//             $row->overtime,
//             $row->late_fine,
//             $row->bpjs_kes,
//             $row->bpjs_ket,
//             $row->tax,
//             $row->debt,
//             $row->salary,
//             $row->take_home,
//             $row->month_year,
//             $row->period,
//         ];
//     }
// }