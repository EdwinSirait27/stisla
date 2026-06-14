<?php

namespace App\Exports;

use App\Models\Employee;
use App\Models\Stores;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class RosterTemplateExport implements WithEvents, WithTitle
{
    public function __construct(
        private string $storeId,
        private string $startDate,
        private string $endDate
    ) {}

    public function title(): string
    {
        return 'Template Roster';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $storeName = Stores::find($this->storeId)?->name ?? '-';

                // ── Generate tanggal ──
                $dates   = [];
                $current = Carbon::parse($this->startDate);
                $end     = Carbon::parse($this->endDate);
                while ($current->lte($end)) {
                    $dates[] = $current->copy();
                    $current->addDay();
                }

                // Kolom: A=pengenal, B=nama, C=store, D dst=tanggal
                // Tanggal dimulai di kolom ke-4 (D)
                $firstDateColIndex = 4;
                $totalCols    = 3 + count($dates);
                $lastColLetter = $this->colLetter($totalCols);

                // ── Baris 1: Judul ──
                $sheet->setCellValue('A1', 'Schedule');
                $sheet->mergeCells("A1:{$lastColLetter}1");
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0F172A']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // ── Baris 2: Location + Periode ──
                $sheet->setCellValue('A2', "Location: {$storeName}     |     Periode: {$this->startDate} s/d {$this->endDate}");
                $sheet->mergeCells("A2:{$lastColLetter}2");
                $sheet->getStyle('A2')->applyFromArray([
                    'font'      => ['size' => 10, 'italic' => true, 'color' => ['argb' => 'FF64748B']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF8FAFC']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // ── Baris 3: HEADER (A WAJIB 'employee_pengenal' agar import menemukannya) ──
                $sheet->setCellValue('A3', 'employee_pengenal');
                $sheet->setCellValue('B3', 'nama');
                $sheet->setCellValue('C3', 'store');
                foreach ($dates as $i => $date) {
                    $col = $this->colLetter($firstDateColIndex + $i);
                    $sheet->setCellValue("{$col}3", $date->day);
                }

                // ── Baris 4: HARI ──
                $hariMap = [0 => 'M', 1 => 'S', 2 => 'S', 3 => 'R', 4 => 'K', 5 => 'J', 6 => 'S'];
                // Kolom A,B,C dikosongkan supaya import melewati baris ini
                foreach ($dates as $i => $date) {
                    $col = $this->colLetter($firstDateColIndex + $i);
                    $sheet->setCellValue("{$col}4", $hariMap[$date->dayOfWeek]);
                }

                // Style header (baris 3) + HARI (baris 4): kuning
                $sheet->getStyle("A3:{$lastColLetter}4")->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['argb' => 'FF000000']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFF00']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                // Kolom Minggu di baris tanggal/hari: tetap kuning (sudah kuning), biarkan

                // ── Baris 5+: data karyawan ──
                $employees = Employee::where('store_id', $this->storeId)
                    ->whereNull('deleted_at')
                    ->orderBy('employee_name')
                    ->get(['employee_pengenal', 'employee_name']);

                $rowNum = 5;
                foreach ($employees as $emp) {
                    $sheet->setCellValue("A{$rowNum}", $emp->employee_pengenal ?? '');
                    $sheet->setCellValue("B{$rowNum}", $emp->employee_name ?? '');
                    $sheet->setCellValue("C{$rowNum}", $storeName);

                    // Kolom pengenal+nama+store: latar kuning muda biar beda dari sel isian
                    $sheet->getStyle("A{$rowNum}:C{$rowNum}")->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFEF9C3']],
                    ]);

                    // Sel tanggal: kosong, Minggu ditandai kuning agar HR sadar
                    foreach ($dates as $i => $date) {
                        $col = $this->colLetter($firstDateColIndex + $i);
                        $sheet->setCellValue("{$col}{$rowNum}", '');
                        if ($date->isSunday()) {
                            $sheet->getStyle("{$col}{$rowNum}")->applyFromArray([
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFF9C3']],
                            ]);
                        }
                        $sheet->getStyle("{$col}{$rowNum}")->applyFromArray([
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        ]);
                    }
                    $rowNum++;
                }

                // ── Border semua (baris 3 ke bawah) ──
                $dataRange = "A3:{$lastColLetter}" . ($rowNum - 1);
                $sheet->getStyle($dataRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['argb' => 'FFD1D5DB'],
                        ],
                    ],
                ]);

                // ── Lebar kolom ──
                $sheet->getColumnDimension('A')->setWidth(20);
                $sheet->getColumnDimension('B')->setWidth(26);
                $sheet->getColumnDimension('C')->setWidth(14);
                foreach ($dates as $i => $_) {
                    $sheet->getColumnDimension($this->colLetter($firstDateColIndex + $i))->setWidth(5);
                }

                // ── Freeze pane: kunci 3 kolom kiri + 4 baris atas ──
                $sheet->freezePane('D5');
            },
        ];
    }

    // Helper: nomor kolom → huruf (1=A, 27=AA, dst)
    private function colLetter(int $n): string
    {
        $letter = '';
        while ($n > 0) {
            $n--;
            $letter = chr(65 + ($n % 26)) . $letter;
            $n      = intdiv($n, 26);
        }
        return $letter;
    }
}