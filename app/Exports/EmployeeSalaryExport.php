<?php

namespace App\Exports;

use App\Models\EmployeeSalary;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;


class EmployeeSalaryExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        protected ?string $effectiveDate,
        protected ?string $status,
        protected ?string $storeName,
        protected ?string $companyName,
        protected ?string $gradingName,
        protected ?string $departmentName,
    ) {}

    public function query()
    {
        $query = EmployeeSalary::with([
            'employee:id,employee_name,employee_pengenal,status_employee,store_id,grading_id,company_id,department_id,position_id', // ← tambah store_id
            'employee.store:id,name',
            'employee.grading:id,grading_name',
            'employee.company:id,name',
            'employee.department:id,department_name',
            'employee.position:id,name' 
        ]);

        if ($this->effectiveDate) {
            $query->where('effective_date', $this->effectiveDate);
        }

        if ($this->status) {
            $query->whereHas('employee', fn($q) =>
                $q->where('status_employee', $this->status)
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

        return $query;
    }
    public function headings(): array
    {
        return [
            'Nama',
            'NIP',
            'Company',
            'Department',
            'Location',
            'Grading',
            'Position',
            'Status',
            'Basic Salary',
            'Position Allowance',
            'Daily Rate',
            'Meal Allowance',
            'House Allowance',
            'Transport Allowance',
            'BPJS Ketenagakerjaan',
            'BPJS Kesehatan',
            'Effective Date',
        ];
    }
    public function map($row): array
    {
        return [
            $row->employee->employee_name     ?? '-',
            $row->employee->employee_pengenal ?? '-',
            $row->employee->company->name       ?? '-',
            $row->employee->department->first()?->department_name       ?? '-',
            $row->employee->store->first()?->name       ?? '-',
            $row->employee->grading->grading_name       ?? '-',
            $row->employee->position->first()?->name       ?? '-',
            $row->employee->status_employee   ?? '-',
            $row->basic_salary,
            $row->position_allowance,
            $row->daily_rate,
            $row->meal_allowance,
            $row->house_allowance,
            $row->transport_allowance,
            $row->bpjs_ketenagakerjaan,
            $row->bpjs_kesehatan,
            $row->effective_date?->format('d/m/Y'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1e293b'],
                ],
            ],
        ];
    }
}