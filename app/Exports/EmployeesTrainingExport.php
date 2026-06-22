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

class EmployeesTrainingExport extends DefaultValueBinder implements FromQuery, WithHeadings, WithMapping, WithStyles, WithCustomValueBinder
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

        
        
        ->leftJoin('company_tables', 'company_tables.id', '=', 'employees_tables.company_id')
        ->select([
            'users.*',
            'employees_tables.employee_name',
            'employees_tables.employee_pengenal',
            'employees_tables.status',
            'employees_tables.telp_number',
            'position_tables.name as position_name',
            'stores_tables.name as store_name',
            'departments_tables.department_name',
            'company_tables.name as name_company',
        ]);
    // ✅ Pakai filled() — ignore null dan string kosong sekaligus
    collect($this->filters)->each(function ($value, $key) use ($query) {
        if (filled($value)) {
            //   dd('Filter dijalankan:', $key, $value);
            match ($key) {
                'filter_company'    => $query->where('company_tables.name', $value),
                'filter_department' => $query->where('departments_tables.department_name', $value),
                'filter_store'      => $query->where('stores_tables.name', $value),
                'filter_status'     => $query->where('employees_tables.status', $value),
                 default             => null,
            };
        }
    });

    return $query;
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
            'Telp Number',
            'Status',
        ];
    }

    private $rowNumber = 0;

    public function map($e): array
    {
        $this->rowNumber++;
        return [
            $this->rowNumber,
            $e->employee_name  ?? 'Empty',
            $e->employee_pengenal  ?? 'Empty',
            $e->name_company   ?? 'Empty',
            $e->department_name ?? 'Empty',
            $e->store_name     ?? 'Empty',
            $e->position_name  ?? 'Empty',
            $e->telp_number         ?? 'Empty',
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
