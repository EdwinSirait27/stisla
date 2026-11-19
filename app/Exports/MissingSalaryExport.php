<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Karyawan aktif yang belum punya data gaji, dalam format template import gaji.
 * Kolom A–I sama persis dengan template import (dibaca per judul kolom), kolom J ke kanan
 * hanya info dan diabaikan importer. Baris yang kolom gajinya dibiarkan kosong dilewati saat import.
 */
class MissingSalaryExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithMapping, WithEvents, WithCustomValueBinder
{
    /** NIP (kolom A) selalu teks, supaya nol di depan dan digit panjang tidak rusak di Excel. */
    public function bindValue(Cell $cell, $value)
    {
        if ($cell->getColumn() === 'A' && $cell->getRow() > 1) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
            return true;
        }
        return parent::bindValue($cell, $value);
    }

    private const MONEY = ['basic_salary', 'position_allowance', 'daily_rate', 'meal_allowance', 'house_allowance', 'transport_allowance', 'bpjs_kesehatan', 'bpjs_ketenagakerjaan'];

    public function __construct(private Collection $employees)
    {
    }

    public function collection()
    {
        return $this->employees;
    }

    public function headings(): array
    {
        return array_merge(['employee_pengenal'], self::MONEY, [
            'info_nama', 'info_tipe', 'info_status', 'info_lokasi', 'info_departemen', 'info_jabatan', 'info_tanggal_bergabung',
        ]);
    }

    public function map($e): array
    {
        $primary = fn($rel) => $rel->firstWhere('pivot.is_primary', true) ?? $rel->first();
        $join = $e->getRawOriginal('join_date');

        return array_merge(
            [(string) $e->employee_pengenal],
            array_fill(0, count(self::MONEY), null),
            [
                $e->employee_name,
                $e->status_employee,
                $e->status,
                $primary($e->store)?->name,
                $primary($e->department)?->department_name,
                $primary($e->position)?->name,
                $join ? \Carbon\Carbon::parse($join)->format('Y-m-d') : null,
            ]
        );
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $last  = $this->employees->count() + 1;

                $sheet->freezePane('B2');
                $sheet->getStyle('A1:I1')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
                ]);
                $sheet->getStyle('J1:P1')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '475569']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
                ]);
                // NIP sebagai teks supaya nol di depan tidak hilang
                $sheet->getStyle("A2:A{$last}")->getNumberFormat()->setFormatCode('@');
                $sheet->getStyle("B2:I{$last}")->getNumberFormat()->setFormatCode('#,##0');

                // Kolom yang tidak dipakai untuk tipe karyawan itu diberi warna abu
                foreach ($this->employees->values() as $i => $e) {
                    $row = $i + 2;
                    $grey = strtoupper($e->status_employee ?? '') === 'DW' ? ["B{$row}:C{$row}"] : ["D{$row}"];
                    foreach ($grey as $range) {
                        $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F1F5F9');
                    }
                }

                foreach (range('A', 'P') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}
