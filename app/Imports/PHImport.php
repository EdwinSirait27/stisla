<?php

namespace App\Imports;

use App\Models\Ph;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;

class PHImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, SkipsOnError
{
    use SkipsFailures, SkipsErrors, Importable;

    private function parseDate(mixed $value): ?string
    {
        try {
            return is_numeric($value)
                ? Date::excelToDateTimeObject($value)->format('Y-m-d')
                : Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception) {
            return null;
        }
    }

    public function model(array $row): ?Ph
    {
        $date   = $this->parseDate($row['date']);
        $type   = trim($row['type']);
        $remark = trim($row['remark']);

        if (!$date) return null;

        // Skip jika kombinasi type + date sudah ada (bypass unique, tidak error)
        if (Ph::where('type', $type)->whereDate('date', $date)->exists()) {
            return null;
        }

        return new Ph([
            'type'   => $type,
            'date'   => $date,
            'remark' => $remark,
        ]);
    }

    public function rules(): array
    {
        return [
            '*.type'   => ['required', 'string', 'max:50'],
            '*.date'   => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!$this->parseDate($value)) {
                        $fail('Format tanggal tidak valid.');
                    }
                },
            ],
            '*.remark' => ['required', 'string', 'max:255'],
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            '*.type.required'   => 'Kolom type wajib diisi.',
            '*.type.max'        => 'Kolom type maksimal 50 karakter.',
            '*.date.required'   => 'Kolom date wajib diisi.',
            '*.remark.required' => 'Kolom remark wajib diisi.',
            '*.remark.max'      => 'Kolom remark maksimal 255 karakter.',
        ];
    }
}