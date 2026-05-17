<?php

namespace App\Services;

use App\Models\Documents;
use App\Models\Companydocumentconfigs;
use App\Models\Employee;
use App\Models\User;

class DocumentGeneratorService
{
    const MAHENDRADATA_COMPANY_ID  = '0196ba4f-5c58-7022-9eb2-ba407eaf4753';
    const TENJI_COMPANY_ID         = '0196ba57-c0ab-7339-b5f4-2bdfedfa14f2';
    const NUANSA_COMPANY_ID        = '0196ba59-1723-732d-bbe0-d6e245b7e67f';
    const ASIAN_BAY_COMPANY_ID     = '0199eb83-7351-729f-9def-c33ae7450447';
    const MAHENDRADATA_DW_CONFIG_ID = '019e15ad-3b96-70a2-ab05-485656c89fd3';

    public function generatePayrollIntroLetter(): void
{
    // Ambil HeadHR sekali saja di luar loop
    $headHR = User::role('HeadHR')
        ->whereHas('Employee', fn($q) => $q->where('status', 'Active'))
        ->first();

    if (!$headHR) {
        return;
    }

    $targetStatuses  = ['DW', 'PKWT', 'On Job Training'];
    $targetCompanies = [
        self::MAHENDRADATA_COMPANY_ID,
        self::TENJI_COMPANY_ID,
        self::NUANSA_COMPANY_ID,
        self::ASIAN_BAY_COMPANY_ID,
    ];

    $employees = Employee::with(['company'])
        ->whereIn('company_id', $targetCompanies)
        ->whereIn('status_employee', $targetStatuses)
        ->where('status', 'Active')
        ->get();

    foreach ($employees as $employee) {
        $config = $this->resolveConfig($employee);

        if (!$config) {
            continue;
        }

        $alreadyExists = Documents::where('company_document_config_id', $config->id)
            ->where('employee_id', $employee->id)
            ->whereYear('issued_date', now()->year)
            ->whereMonth('issued_date', now()->month)
            ->exists();

        if ($alreadyExists) {
            continue;
        }

        Documents::create([
            'company_document_config_id' => $config->id,
            'employee_id'                => $employee->id,
            'issued_by'                  => $headHR->employee_id,
            'issued_date'                => now()->toDateString(),
            'status'                     => 'draft',
        ]);
    }
}

    private function resolveConfig(Employee $employee): ?Companydocumentconfigs
    {
        // PT. MAHENDRADATA + DW → config OVictoria
        if (
            $employee->company_id === self::MAHENDRADATA_COMPANY_ID &&
            $employee->status_employee === 'DW'
        ) {
            return Companydocumentconfigs::find(self::MAHENDRADATA_DW_CONFIG_ID);
        }

        // Selain itu → config OCBC milik company masing-masing
        return Companydocumentconfigs::with(['documenttypes'])
            ->where('company_id', $employee->company_id)
            ->whereHas('documenttypes', fn($q) => $q->where('nickname', 'SPPRP'))
            ->where('is_active', true)
            ->where('id', '!=', self::MAHENDRADATA_DW_CONFIG_ID)
            ->first();
    }
}