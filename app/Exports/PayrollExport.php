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

class PayrollExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    public function __construct(
        protected PayrollPeriod $period,
        protected ?string $status         = null,
        protected ?string $statusEmployee = null,
        protected ?string $storeName      = null,
    ) {}

    public function query()
    {
        $query = Payroll::with([
            'employee:id,employee_name,employee_pengenal,status_employee,store_id,banks_id,bank_account_number',
            'employee.store:id,name','employee.bank:id,name',
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
            'Store',
            'Status',
            'Bank',
            'No Rekening',
            'Working Days',
            'Attendance Days',
            'Absent Days',
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

    public function map($row): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $row->employee->employee_pengenal   ?? '-',
            $row->employee->employee_name        ?? '-',
            $row->employee->store->name          ?? '-',
            $row->employee->status_employee      ?? '-',
            $row->employee->bank->name            ?? '-',
            $row->employee->bank_account_number         ?? '-',
            $row->working_days,
            $row->attendance_days,
            $row->absent_days,
            $row->is_prorate
                ? 'Ya (' . ($row->prorate_ratio * 100) . '%)'
                : 'Tidak',
            $row->basic_salary,
            $row->position_allowance,
            $row->daily_rate,
            $row->gross_salary,
            $row->total_income,
            $row->total_deduction,
            $row->net_salary,
            ucfirst($row->status),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Header row
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