<?php
// app/Exports/EmployeesExport.php

namespace App\Exports;

use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeesExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }
   public function query()
{
    $query = User::query()
        ->leftJoin('employees_tables', 'users.employee_id', '=', 'employees_tables.id')
        ->leftJoin('position_tables', 'position_tables.id', '=', 'employees_tables.position_id')
        ->leftJoin('stores_tables', 'stores_tables.id', '=', 'employees_tables.store_id')
        ->leftJoin('departments_tables', 'departments_tables.id', '=', 'employees_tables.department_id')
        ->leftJoin('grading', 'grading.id', '=', 'employees_tables.grading_id')
        ->leftJoin('groups_tables', 'groups_tables.id', '=', 'employees_tables.group_id')
        ->leftJoin('company_tables', 'company_tables.id', '=', 'employees_tables.company_id')
        ->select([
            'users.*',
            'employees_tables.employee_name',
            'employees_tables.employee_pengenal',
            'employees_tables.status_employee',
            'employees_tables.status',
            'employees_tables.join_date',
            'position_tables.name as position_name',
            'groups_tables.remark as remark',
            'stores_tables.name as store_name',
            'departments_tables.department_name',
            'grading.grading_name',
            'company_tables.name as name_company',
        ]);
    // ✅ Pakai filled() — ignore null dan string kosong sekaligus
    collect($this->filters)->each(function ($value, $key) use ($query) {
        if (filled($value)) {
            //   dd('Filter dijalankan:', $key, $value);
            match ($key) {
                'filter_company'    => $query->where('company_tables.name', $value),
                'filter_department' => $query->where('departments_tables.department_name', $value),
                'filter_group'      => $query->where('groups_tables.remark', $value),
                'filter_grading'    => $query->where('grading.grading_name', $value),
                'filter_store'      => $query->where('stores_tables.name', $value),
                'filter_emp_status' => $query->where('employees_tables.status_employee', $value),
                'filter_status'     => $query->where('employees_tables.status', $value),
                'filter_los'        => $this->applyLosFilter($query, $value),
                default             => null,
            };
        }
    });

    return $query;
}

// Pisah LOS filter ke method sendiri biar rapi
private function applyLosFilter($query, $los)
{
    if ($los === 'under3months') {
        $query->where('employees_tables.join_date', '>=', Carbon::now()->subMonths(3));
    } else {
        $date = match ($los) {
            '1year'  => Carbon::now()->subYear(),
            '3years' => Carbon::now()->subYears(3),
            '5years' => Carbon::now()->subYears(5),
            default  => null,
        };
        if ($date) $query->where('employees_tables.join_date', '<=', $date);
    }
}

    public function headings(): array
    {
        return [
            'No',
            'Employee Name',
            'NIP',
            'Company',
            'Department',
            'Location',
            'Position',
            'Grade',
            'Group',
            'Emp. Status',
            'Length of Service',
            'Status',
        ];
    }

    // Counter untuk nomor urut
    private $rowNumber = 0;

    public function map($e): array
    {
        $this->rowNumber++;

        $los = 'Empty';
        if ($e->join_date) {
            $diff = Carbon::parse($e->join_date)->diff(Carbon::now());
            $los  = sprintf('%d year %d month %d days', $diff->y, $diff->m, $diff->d);
        }
        return [
            $this->rowNumber,
            $e->employee_name  ?? 'Empty',
            // $e->employee_pengenal  ?? 'Empty',
        $e->employee_pengenal ? "'" . $e->employee_pengenal : 'Empty',

            $e->name_company   ?? 'Empty',
            $e->department_name ?? 'Empty',
            $e->store_name     ?? 'Empty',
            $e->position_name  ?? 'Empty',
            $e->grading_name   ?? 'Empty',
            $e->remark         ?? 'Empty',
            $e->status_employee ?? 'Empty',
            $los,
            $e->status         ?? 'Empty',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Bold header row
            1 => ['font' => ['bold' => true]],
        ];
    }
}