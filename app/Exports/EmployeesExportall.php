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

class EmployeesExportall extends DefaultValueBinder implements FromQuery, WithHeadings, WithMapping, WithStyles, WithCustomValueBinder
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

            // Position via pivot
            ->leftJoin('employee_positions', function ($join) {
                $join->on('employee_positions.employee_id', '=', 'employees_tables.id')
                    ->where('employee_positions.is_primary', true);
            })
            ->leftJoin('position_tables', 'position_tables.id', '=', 'employee_positions.position_id')

            // Store via pivot
            ->leftJoin('employee_stores', function ($join) {
                $join->on('employee_stores.employee_id', '=', 'employees_tables.id')
                    ->where('employee_stores.is_primary', true);
            })
            ->leftJoin('stores_tables', 'stores_tables.id', '=', 'employee_stores.store_id')

            // Department via pivot
            ->leftJoin('employee_departments', function ($join) {
                $join->on('employee_departments.employee_id', '=', 'employees_tables.id')
                    ->where('employee_departments.is_primary', true);
            })
            ->leftJoin('departments_tables', 'departments_tables.id', '=', 'employee_departments.department_id')

            ->leftJoin('grading', 'grading.id', '=', 'employees_tables.grading_id')
            ->leftJoin('groups_tables', 'groups_tables.id', '=', 'employees_tables.group_id')
            ->leftJoin('company_tables', 'company_tables.id', '=', 'employees_tables.company_id')
            ->leftJoin('banks_tables', 'banks_tables.id', '=', 'employees_tables.banks_id')

            ->select([
                'users.*',
                'employees_tables.employee_name',
                'employees_tables.employee_pengenal',
                'employees_tables.bank_account_number',
                'employees_tables.join_date',
                'employees_tables.end_date',
                'employees_tables.created_at',
                'employees_tables.marriage',
                'employees_tables.child',
                'employees_tables.blood_type',
                'employees_tables.telp_number',
                'employees_tables.nik',
                'employees_tables.kk_number',
                'employees_tables.gender',
                'employees_tables.date_of_birth',
                'employees_tables.place_of_birth',
                'employees_tables.biological_mother_name',
                'employees_tables.religion',
                'employees_tables.current_address',
                'employees_tables.id_card_address',
                'employees_tables.last_education',
                'employees_tables.institution',
                'employees_tables.npwp',
                'employees_tables.bpjs_kes',
                'employees_tables.bpjs_ket',
                'employees_tables.email',
                'employees_tables.company_email',
                'employees_tables.emergency_contact_name',
                'employees_tables.pin',
                'employees_tables.can_approve',
                'employees_tables.pending_email',
                'employees_tables.pending_telp_number',
                'employees_tables.status_employee',
                'employees_tables.status',
                'position_tables.name as position_name',
                'groups_tables.remark as remark',
                'stores_tables.name as store_name',
                'banks_tables.name as bank_name',
                'departments_tables.department_name',
                'grading.grading_name',
                'company_tables.name as name_company',
            ]);

        collect($this->filters)->each(function ($value, $key) use ($query) {
            if (filled($value)) {
                match ($key) {
                    'filter_company'        => $query->where('company_tables.name', $value),
                    'filter_department'     => $query->where('departments_tables.department_name', $value),
                    'filter_group'          => $query->where('groups_tables.remark', $value),
                    'filter_grading'        => $query->where('grading.grading_name', $value),
                    'filter_store'          => $query->where('stores_tables.name', $value),
                    'filter_blood_type'     => $query->where('employees_tables.blood_type', $value),
                    'filter_emp_status'     => $query->where('employees_tables.status_employee', $value),
                    'filter_status'         => $query->where('employees_tables.status', $value),
                    'filter_bank'           => $query->where('banks_tables.name', $value),
                    'filter_gender'         => $query->where('employees_tables.gender', $value),
                    'filter_marriage'       => $query->where('employees_tables.marriage', $value),
                    'filter_religion'       => $query->where('employees_tables.religion', $value),
                    'filter_last_education' => $query->where('employees_tables.last_education', $value),
                    'filter_los'            => $this->applyLosFilter($query, $value),
                    default                 => null,
                };
            }
        });

        return $query;
    }

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
            'No', 'Status', 'Employee', 'NIP', 'P.Finger', 'Can Approve',
            'NIK', 'KK Number', 'Religion', 'Gender', 'Date of Birth', 'Plc. of Birth',
            'Moth. Name', 'Crnt. Address', 'ID Card Address', 'Lst. Education',
            'Institution', 'Marriage', 'Child', 'Blood Type', 'Emer. Contact',
            'Email', 'Pend. Email', 'Comp. Email', 'Phone', 'Pend. Phone',
            'BPJS Kes.', 'BPJS Ket.', 'NPWP', 'Bank', 'Bank Acc. Number',
            'Company', 'Department', 'Location', 'Position', 'Grade', 'Group',
            'Emp. Status', 'LOS', 'Join Date', 'End Date', 'Acc. Created',
        ];
    }

    private $rowNumber = 0;

    private function formatDate($date): string
    {
        return $date ? Carbon::parse($date)->format('Y-m-d') : 'EMPTY';
    }

    private function up(?string $value, string $default = 'EMPTY'): string
    {
        return strtoupper($value ?? $default);
    }

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
            $this->up($e->status),
            $this->up($e->employee_name),
            $e->employee_pengenal           ?? 'EMPTY',
            $e->pin                         ?? 'EMPTY',
            $e->can_approve                 ?? 0,
            $e->nik                         ?? 'EMPTY',
            $e->kk_number                         ?? 'EMPTY',
            $this->up($e->religion),
            $this->up($e->gender),
            $this->formatDate($e->date_of_birth),
            $this->up($e->place_of_birth),
            $this->up($e->biological_mother_name),
            $this->up($e->current_address),
            $this->up($e->id_card_address),
            $this->up($e->last_education),
            $this->up($e->institution),
            $this->up($e->marriage),
            $e->child                       ?? '0',
            $this->up($e->blood_type),
            $this->up($e->emergency_contact_name),
            $e->email                       ?? 'EMPTY',
            $e->pending_email               ?? 'EMPTY',
            $e->company_email               ?? 'EMPTY',
            $e->telp_number                 ?? 'EMPTY',
            $e->pending_telp_number         ?? 'EMPTY',
            $e->bpjs_kes                    ?? 'EMPTY',
            $e->bpjs_ket                    ?? 'EMPTY',
            $e->npwp                        ?? 'EMPTY',
            $this->up($e->bank_name),
            $e->bank_account_number         ?? 'EMPTY',
            $this->up($e->name_company),
            $this->up($e->department_name),
            $this->up($e->store_name),      // ← nama alias diganti dari 'name' ke 'store_name'
            $this->up($e->position_name),
            $this->up($e->grading_name),
            $this->up($e->remark),
            $this->up($e->status_employee),
            $los,
            $this->formatDate($e->join_date),
            $this->formatDate($e->end_date),
            $this->formatDate($e->created_at),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function bindValue(Cell $cell, $value): bool
    {
        if (is_string($value) && preg_match('/^\d{10,}$/', $value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }
}