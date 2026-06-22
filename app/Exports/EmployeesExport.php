<?php

namespace App\Exports;

use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
class EmployeesExport extends DefaultValueBinder implements FromQuery, WithHeadings, WithMapping, WithStyles, WithCustomValueBinder

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
        ->leftJoin('employee_positions', function ($join) {
            $join->on('employee_positions.employee_id', '=', 'employees_tables.id')
                 ->where('employee_positions.is_primary', true);
        })
        ->leftJoin('position_tables', 'position_tables.id', '=', 'employee_positions.position_id')
        
        ->leftJoin('employee_stores', function ($join) {
            $join->on('employee_stores.employee_id', '=', 'employees_tables.id')
                 ->where('employee_stores.is_primary', true);
        })
        ->leftJoin('stores_tables', 'stores_tables.id', '=', 'employee_stores.store_id')

        // ← Department lewat pivot
        ->leftJoin('employee_departments', function ($join) {
            $join->on('employee_departments.employee_id', '=', 'employees_tables.id')
                 ->where('employee_departments.is_primary', true);
        })
        ->leftJoin('departments_tables', 'departments_tables.id', '=', 'employee_departments.department_id')

        
        
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
            'employees_tables.end_date',
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
                'filter_join_date_from' => $query->where('employees_tables.join_date', '>=', Carbon::parse($value)->startOfDay()),
'filter_join_date_to'   => $query->where('employees_tables.join_date', '<=', Carbon::parse($value)->endOfDay()),
'filter_end_date_from'  => $query->where('employees_tables.end_date', '>=', Carbon::parse($value)->startOfDay()),
'filter_end_date_to'    => $query->where('employees_tables.end_date', '<=', Carbon::parse($value)->endOfDay()),
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

    private $rowNumber = 0;

    
    public function map($e): array
{
    $this->rowNumber++;

    $los = 'EMPTY';
    if ($e->join_date) {
        $diff = Carbon::parse($e->join_date)->diff(Carbon::now());
        $los  = strtoupper(sprintf('%d year %d month %d days', $diff->y, $diff->m, $diff->d));
    }

    return [
        $this->rowNumber,
        strtoupper($e->employee_name   ?? 'EMPTY'),
        strtoupper($e->employee_pengenal ?? 'EMPTY'),
        strtoupper($e->name_company    ?? 'EMPTY'),
        strtoupper($e->department_name ?? 'EMPTY'),
        strtoupper($e->store_name      ?? 'EMPTY'),
        strtoupper($e->position_name   ?? 'EMPTY'),
        strtoupper($e->grading_name    ?? 'EMPTY'),
        strtoupper($e->remark          ?? 'EMPTY'),
        strtoupper($e->status_employee ?? 'EMPTY'),
        $los,
        strtoupper($e->status          ?? 'EMPTY'),
    ];
}

    public function styles(Worksheet $sheet)
    {
        return [
            // Bold header row
            1 => ['font' => ['bold' => true]],
        ];
    }
    public function bindValue(Cell $cell, $value): bool
{
    // Kolom C = NIP, paksa sebagai string
    if ($cell->getColumn() === 'C') {
        $cell->setValueExplicit($value, DataType::TYPE_STRING);
        return true;
    }

    return parent::bindValue($cell, $value);
}
}
// public function map($e): array
    // {
    //     $this->rowNumber++;

    //     $los = 'Empty';
    //     if ($e->join_date) {
    //         $diff = Carbon::parse($e->join_date)->diff(Carbon::now());
    //         $los  = sprintf('%d year %d month %d days', $diff->y, $diff->m, $diff->d);
    //     }
    //     return [
    //         $this->rowNumber,
    //         $e->employee_name  ?? 'Empty',
    //         $e->employee_pengenal  ?? 'Empty',
    //         $e->name_company   ?? 'Empty',
    //         $e->department_name ?? 'Empty',
    //         $e->store_name     ?? 'Empty',
    //         $e->position_name  ?? 'Empty',
    //         $e->grading_name   ?? 'Empty',
    //         $e->remark         ?? 'Empty',
    //         $e->status_employee ?? 'Empty',
    //         $los,
    //         $e->status         ?? 'Empty',
    //     ];
    // }