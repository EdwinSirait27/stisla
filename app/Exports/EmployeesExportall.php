<?php

namespace App\Exports;

use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeesExportall implements FromQuery, WithHeadings, WithMapping, WithStyles
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
                'employees_tables.telp_number',
                'employees_tables.nik',
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
                'employees_tables.pending_email',
                'employees_tables.pending_telp_number',
                'employees_tables.status_employee',
                'employees_tables.status',
                'employees_tables.join_date',
                'position_tables.name as position_name',
                'groups_tables.remark as remark',
                'stores_tables.name as name',
                'banks_tables.name as bank_name',
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
                    'filter_bank'     => $query->where('banks_tables.name', $value),
                    'filter_gender'     => $query->where('employees_tables.gender', $value),
                    'filter_marriage'     => $query->where('employees_tables.marriage', $value),
                    'filter_religion'     => $query->where('employees_tables.religion', $value),
                    'filter_last_education'     => $query->where('employees_tables.last_education', $value),
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
            'Status',
            'Employee',
            'NIP',
            'P.Finger',
            'NIK',
            'Religion',
            'Gender',
            'Date of Birth',
            'Plc. of Birth',
            'Moth. Name',
            'Crnt. Address',
            'ID Card Address',
            'Lst. Education',
            'Institution',
            'Marriage',
            'Child',
            'Emer. Contact',
            'Email',
            'Pend. Email',
            'Comp. Email',
            'Phone',
            'Pend. Phone',
            'BPJS Kes.',
            'BPJS Ket.',
            'NPWP',
            'Bank',
            'Bank Acc. Number',
            'Company',
            'Department',
            'Location',
            'Position',
            'Grade',
            'Group',
            'Emp. status',
            'LOS',
            'Join Date',
            'End Date',
            'Acc. Created',
        ];
    }
    // Counter untuk nomor urut
    private $rowNumber = 0;

    private function formatDate($date)
{
    return $date ? Carbon::parse($date)->format('Y-m-d') : 'Empty';
}
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
        $e->status ?? 'Empty',
        $e->employee_name ?? 'Empty',
        // $e->employee_pengenal ?? 'Empty',
        $e->employee_pengenal ? "'" . $e->employee_pengenal : 'Empty',
        $e->pin ?? 'Empty',
        // $e->nik ?? 'Empty',
        $e->nik ? "''" . $e->nik : 'Empty',
        $e->religion ?? 'Empty',
        $e->gender ?? 'Empty',
        $this->formatDate($e->date_of_birth),
        $e->place_of_birth ?? 'Empty',
        $e->biological_mother_name ?? 'Empty',
        $e->current_address ?? 'Empty',
        $e->id_card_address ?? 'Empty',
        $e->last_education ?? 'Empty',
        $e->institution ?? 'Empty',
        $e->marriage ?? 'Empty',
        $e->child ?? '0',
        $e->emergency_contact_name ?? 'Empty',
        $e->email ?? 'Empty',
        $e->pending_email ?? 'Empty',
        $e->company_email ?? 'Empty',
        // $e->telp_number ?? 'Empty',
                $e->telp_number ? "'" . $e->telp_number : 'Empty',
                
                // $e->pending_telp_number ?? 'Empty',
                $e->pending_telp_number ? "'" . $e->pending_telp_number : 'Empty',
                $e->bpjs_kes ? "'" . $e->bpjs_kes : 'Empty',
                $e->bpjs_ket ? "'" . $e->bpjs_ket : 'Empty',
                $e->npwp ? "'" . $e->npwp : 'Empty',
        // $e->bpjs_kes ?? 'Empty',
        // $e->bpjs_ket ?? 'Empty',
        // $e->npwp ?? 'Empty',
        
        $e->bank_name ?? 'Empty',
        // $e->bank_account_number ?? 'Empty',
        $e->bank_account_number ? "'" . $e->bank_account_number : 'Empty',

        $e->name_company ?? 'Empty',
        $e->department_name ?? 'Empty',
        $e->name ?? 'Empty', // store
        $e->position_name ?? 'Empty',
        $e->grading_name ?? 'Empty',
        $e->remark ?? 'Empty',
        $e->status_employee ?? 'Empty',
        $los,
        $this->formatDate($e->join_date),
        $this->formatDate($e->end_date),
        $this->formatDate($e->created_at),
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
