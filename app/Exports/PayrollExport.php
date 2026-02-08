<?php

namespace App\Exports;

use App\Models\Payrolls;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PayrollExport implements FromCollection, WithHeadings, WithMapping
{
  protected $monthYear;

    public function __construct($monthYear = null)
    {
        $this->monthYear = $monthYear;
    }

    public function collection()
    {
        $query = Payrolls::with('employee');

        if ($this->monthYear) {
            $query->where('month_year', $this->monthYear);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Employee Name',
            'NIP',
            'Attendance',
            'Daily Allowance',
            'House Allowance',
            'Meal Allowance',
            'Transport Allowance',
            'Reimburse',
            'Bonus',
            'Overtime',
            'Late Fine',
            'BPJS Kes',
            'BPJS Ket',
            'Tax',
            'Debt',
            'Total Salary',
            'Take Home Pay',
            'Month Year',
            'Period'
        ];
    }

    public function map($row): array
    {
        return [
            $row->employee->employee_name ?? '-',
            $row->employee->employee_pengenal ?? '-',
            $row->attendance,
            $row->daily_allowance,
            $row->house_allowance,
            $row->meal_allowance,
            $row->transport_allowance,
            $row->reamburse,
            $row->bonus,
            $row->overtime,
            $row->late_fine,
            $row->bpjs_kes,
            $row->bpjs_ket,
            $row->tax,
            $row->debt,
            $row->salary,
            $row->take_home,
            $row->month_year,
            $row->period,
        ];
    }
}
