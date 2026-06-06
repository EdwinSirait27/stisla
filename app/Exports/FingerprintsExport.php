<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class FingerprintsExport implements FromCollection, WithHeadings, WithStyles, WithTitle, ShouldAutoSize, WithEvents
{
    protected Collection $data;
    protected string $storeName;

    public function __construct(Collection $data, string $storeName = '')
    {
        $this->data      = $data;
        $this->storeName = $storeName;
    }

    public function collection(): Collection
    {
        return $this->data->map(fn($row) => [
            $row['employee_name']     ?? '-',
            $row['employee_pengenal'] ?? '-',
            $row['name']              ?? '-',
            $row['pin']               ?? '-',
            trim(($row['roster_name'] ?? '-') . ' ' . ($row['roster_time'] ?? '')),
            $row['position_name']     ?? '-',
            $row['status_employee']   ?? '-',
            $row['scan_date']         ?? '-',
            $row['combine_1']         ?? '-',
            $row['combine_2']         ?? '-',
            $row['combine_3']         ?? '-',
            $row['combine_4']         ?? '-',
            $row['combine_5']         ?? '-',
            $row['combine_6']         ?? '-',
            $row['duration']          ?? '-',
            $row['updated_status']    ?? '-',
        ]);
    }

    public function headings(): array
    {
        return [
            'Employee',
            'NIP',
            'Location',
            'PIN',
            'Schedule',
            'Position',
            'Emp. Status',
            'Scan Date',
            'In',
            'Out',
            'Break In',
            'Break Out',
            'Ovt In',
            'Ovt Out',
            'Duration',
            'Record Status',
        ];
    }

    public function title(): string
    {
        return 'Fingerprints';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            2 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1e3a5f'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet   = $event->sheet->getDelegate();
                $lastRow = $this->data->count() + 2;
                $lastCol = 'P';

                // ── Title row ──
                $sheet->insertNewRowBefore(1, 1);
                $title = $this->storeName
                    ? "Fingerprints Attendance — {$this->storeName}"
                    : 'Fingerprints Attendance';
                $sheet->setCellValue('A1', $title);
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1e3a5f']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(28);

                // ── Border semua cell ──
                $sheet->getStyle("A1:{$lastCol}{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['rgb' => 'D1D5DB'],
                        ],
                    ],
                ]);

                // ── Zebra stripe ──
                for ($r = 3; $r <= $lastRow; $r++) {
                    if ($r % 2 === 0) {
                        $sheet->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
                            'fill' => [
                                'fillType'   => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'F1F5F9'],
                            ],
                        ]);
                    }
                }

                // ── Center align ──
                $sheet->getStyle("A1:{$lastCol}{$lastRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                // ── Freeze header ──
                $sheet->freezePane('A3');
            },
        ];
    }
}