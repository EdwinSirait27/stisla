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
use Illuminate\Validation\Rule;

class PHImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, SkipsOnError
{
    use SkipsFailures, SkipsErrors, Importable;

    public function model(array $row)
    {
        $date = is_numeric($row['date'])
            ? Date::excelToDateTimeObject($row['date'])->format('Y-m-d')
            : Carbon::parse($row['date'])->format('Y-m-d');

        return new Ph([
            'type'   => $row['type'],
            'date'   => $date,
            'remark' => $row['remark'],
        ]);
    }

    public function rules(): array
    {
        return [
            '*.date' => [
                'required',
                function ($attribute, $value, $fail) {
                    $date = is_numeric($value)
                        ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d')
                        : \Carbon\Carbon::parse($value)->format('Y-m-d');

                    if (\App\Models\Ph::whereDate('date', $date)->exists()) {
                        $fail("The date {$date} has already been taken.");
                    }
                },
            ],

            '*.type'   => ['required', 'string'],

            '*.remark' => [
                'required',
                'string',
                Rule::unique('ph', 'remark'),
            ],
        ];
    }
}
