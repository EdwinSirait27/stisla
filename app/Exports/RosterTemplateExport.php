<?php

namespace App\Exports;

use App\Models\Employee;
use App\Models\Stores;
use App\Models\Ph;
use App\Models\Shifts;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class RosterTemplateExport implements WithMultipleSheets
// class RosterTemplateExport implements WithEvents, WithTitle
{
 
//        public function __construct(
//         private string $storeId,
//         private string $startDate,
//         private string $endDate
//     ) {}
 
//     public function sheets(): array
//     {
//         return [
//             new RosterTemplateSheet($this->storeId, $this->startDate, $this->endDate),
//             new RosterTutorialSheet(),
//             new RosterShiftSheet($this->storeId),
//         ];
//     }
 
//     /**
//      * Cek apakah tanggal adalah PH untuk agama karyawan tertentu.
//      * DW tidak perlu dikirim ke sini karena sudah di-skip di loop atas.
//      */
//     private function isPublicHolidayForEmployee(array $phMap, string $date, ?string $religion): bool
//     {
//         if (!isset($phMap[$date])) {
//             return false;
//         }
 
//         $phType = $phMap[$date]['type'];
 
//         if ($phType === 'All') {
//             return true;
//         }
 
//         if (empty($religion)) {
//             return false;
//         }
 
//         if ($phType === 'Hindu') {
//             return $religion === 'Hindu';
//         }
 
//         if ($phType === 'Non Hindu') {
//             return $religion !== 'Hindu';
//         }
 
//         return false;
//     }
 
//     // Helper: nomor kolom → huruf (1=A, 27=AA, dst)
//     private function colLetter(int $n): string
//     {
//         $letter = '';
//         while ($n > 0) {
//             $n--;
//             $letter = chr(65 + ($n % 26)) . $letter;
//             $n      = intdiv($n, 26);
//         }
//         return $letter;
//     }
// }
 
// // ═══════════════════════════════════════════════════════════════
// //  SHEET 1 — Template Roster
// // ═══════════════════════════════════════════════════════════════
// class RosterTemplateSheet implements WithEvents, WithTitle
// {
//     public function __construct(
//         private string $storeId,
//         private string $startDate,
//         private string $endDate
//     ) {}
 
//     public function title(): string
//     {
//         return 'Template Roster';
//     }
 
//     public function registerEvents(): array
//     {
//         return [
//             AfterSheet::class => function (AfterSheet $event) {
//                 $sheet = $event->sheet->getDelegate();
 
//                 $storeName = Stores::find($this->storeId)?->name ?? '-';
 
//                 // ── Generate tanggal ──
//                 $dates   = [];
//                 $current = Carbon::parse($this->startDate);
//                 $end     = Carbon::parse($this->endDate);
//                 while ($current->lte($end)) {
//                     $dates[] = $current->copy();
//                     $current->addDay();
//                 }
 
//                 // ── Ambil Public Holiday master dalam rentang ──
//                 $phMap    = [];
//                 $holidays = Ph::whereBetween('date', [$this->startDate, $this->endDate])
//                     ->get(['date', 'type', 'remark']);
//                 foreach ($holidays as $ph) {
//                     $key          = Carbon::parse($ph->date)->toDateString();
//                     $phMap[$key]  = ['type' => $ph->type, 'remark' => $ph->remark];
//                 }
 
//                 $firstDateColIndex = 4;
//                 $totalCols         = 3 + count($dates);
//                 $lastColLetter     = $this->colLetter($totalCols);
 
//                 // ── Baris 1: Judul ──
//                 $sheet->setCellValue('A1', 'Schedule');
//                 $sheet->mergeCells("A1:{$lastColLetter}1");
//                 $sheet->getStyle('A1')->applyFromArray([
//                     'font'      => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FFFFFFFF']],
//                     'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0F172A']],
//                     'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
//                 ]);
 
//                 // ── Baris 2: Location + Periode ──
//                 $sheet->setCellValue('A2', "Location: {$storeName}     |     Periode: {$this->startDate} s/d {$this->endDate}");
//                 $sheet->mergeCells("A2:{$lastColLetter}2");
//                 $sheet->getStyle('A2')->applyFromArray([
//                     'font'      => ['size' => 10, 'italic' => true, 'color' => ['argb' => 'FF64748B']],
//                     'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF8FAFC']],
//                     'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
//                 ]);
 
//                 // ── Baris 3: HEADER ──
//                 $sheet->setCellValue('A3', 'employee_pengenal');
//                 $sheet->setCellValue('B3', 'employee_name');
//                 $sheet->setCellValue('C3', 'store');
 
//                 foreach ($dates as $i => $date) {
//                     $col = $this->colLetter($firstDateColIndex + $i);
//                     $sheet->setCellValue("{$col}3", $date->day);
//                 }
 
//                 // ── Baris 4: HARI ──
//                 $hariMap = [0 => 'M', 1 => 'S', 2 => 'S', 3 => 'R', 4 => 'K', 5 => 'J', 6 => 'S'];
//                 foreach ($dates as $i => $date) {
//                     $col = $this->colLetter($firstDateColIndex + $i);
//                     $sheet->setCellValue("{$col}4", $hariMap[$date->dayOfWeek]);
//                 }
 
//                 $sheet->getStyle("A3:{$lastColLetter}4")->applyFromArray([
//                     'font'      => ['bold' => true, 'color' => ['argb' => 'FF000000']],
//                     'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFF00']],
//                     'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
//                 ]);
 
//                 // ── Ambil employee via pivot ──
//                 $employees = Employee::whereNull('deleted_at')
//                     ->whereIn('status', ['Active', 'On Leave', 'Pending'])
//                     ->whereHas('store', fn($q) => $q->where('stores_tables.id', $this->storeId))
//                     ->orderBy('employee_name')
//                     ->get(['employee_pengenal', 'employee_name', 'religion', 'status_employee']);
 
//                 $rowNum = 5;
//                 foreach ($employees as $emp) {
//                     $sheet->setCellValue("A{$rowNum}", $emp->employee_pengenal ?? '');
//                     $sheet->setCellValue("B{$rowNum}", $emp->employee_name ?? '');
//                     $sheet->setCellValue("C{$rowNum}", $storeName);
 
//                     $sheet->getStyle("A{$rowNum}:C{$rowNum}")->applyFromArray([
//                         'font' => ['bold' => true],
//                         'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFEF9C3']],
//                     ]);
 
//                     $isDW = $emp->status_employee === 'DW';
 
//                     foreach ($dates as $i => $date) {
//                         $col     = $this->colLetter($firstDateColIndex + $i);
//                         $dateStr = $date->toDateString();
 
//                         $isPH = $isDW
//                             ? false
//                             : $this->isPublicHolidayForEmployee($phMap, $dateStr, $emp->religion);
 
//                         $sheet->setCellValue("{$col}{$rowNum}", $isPH ? 'PH' : '');
 
//                         if ($isPH) {
//                             $sheet->getStyle("{$col}{$rowNum}")->applyFromArray([
//                                 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFE0B2']],
//                                 'font' => ['bold' => true, 'color' => ['argb' => 'FFB45309']],
//                             ]);
//                         } elseif ($date->isSunday()) {
//                             $sheet->getStyle("{$col}{$rowNum}")->applyFromArray([
//                                 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFF9C3']],
//                             ]);
//                         }
 
//                         $sheet->getStyle("{$col}{$rowNum}")->applyFromArray([
//                             'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
//                         ]);
//                     }
//                     $rowNum++;
//                 }
 
//                 // ── Border ──
//                 $sheet->getStyle("A3:{$lastColLetter}" . ($rowNum - 1))->applyFromArray([
//                     'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD1D5DB']]],
//                 ]);
 
//                 // ── Lebar kolom ──
//                 $sheet->getColumnDimension('A')->setWidth(25);
//                 $sheet->getColumnDimension('B')->setWidth(20);
//                 $sheet->getColumnDimension('C')->setWidth(14);
//                 foreach ($dates as $i => $_) {
//                     $sheet->getColumnDimension($this->colLetter($firstDateColIndex + $i))->setWidth(5);
//                 }
 
//                 $sheet->freezePane('D5');
//             },
//         ];
//     }
 
//     private function isPublicHolidayForEmployee(array $phMap, string $date, ?string $religion): bool
//     {
//         if (!isset($phMap[$date])) return false;
//         $phType = $phMap[$date]['type'];
//         if ($phType === 'All') return true;
//         if (empty($religion)) return false;
//         if ($phType === 'Hindu') return $religion === 'Hindu';
//         if ($phType === 'Non Hindu') return $religion !== 'Hindu';
//         return false;
//     }
 
//     private function colLetter(int $n): string
//     {
//         $letter = '';
//         while ($n > 0) {
//             $n--;
//             $letter = chr(65 + ($n % 26)) . $letter;
//             $n      = intdiv($n, 26);
//         }
//         return $letter;
//     }
// }
 
// // ═══════════════════════════════════════════════════════════════
// //  SHEET 2 — Tutorial Pengisian
// // ═══════════════════════════════════════════════════════════════
// class RosterTutorialSheet implements WithEvents, WithTitle
// {
//     public function title(): string
//     {
//         return 'Tutorial';
//     }
 
//     public function registerEvents(): array
//     {
//         return [
//             AfterSheet::class => function (AfterSheet $event) {
//                 $sheet = $event->sheet->getDelegate();
 
//                 // ── Judul ──
//                 $sheet->setCellValue('A1', 'PANDUAN PENGISIAN TEMPLATE ROSTER');
//                 $sheet->mergeCells('A1:D1');
//                 $sheet->getStyle('A1')->applyFromArray([
//                     'font'      => ['bold' => true, 'size' => 13, 'color' => ['argb' => 'FFFFFFFF']],
//                     'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0F172A']],
//                     'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
//                 ]);
//                 $sheet->getRowDimension(1)->setRowHeight(30);
 
//                 // ── Header tabel ──
//                 $sheet->setCellValue('A2', 'Kode');
//                 $sheet->setCellValue('B2', 'Keterangan');
//                 $sheet->setCellValue('C2', 'Contoh');
//                 $sheet->setCellValue('D2', 'Catatan');
//                 $sheet->getStyle('A2:D2')->applyFromArray([
//                     'font'      => ['bold' => true, 'color' => ['argb' => 'FF000000']],
//                     'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFF00']],
//                     'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
//                 ]);
//                 $sheet->getRowDimension(2)->setRowHeight(20);
 
//                 // ── Isi tutorial ──
//                 $rows = [
//                     ['NAMA SHIFT',      'Isi dengan nama shift yang tersedia (lihat sheet "Shift")', 'pagi / siang / malam', 'Tidak case-sensitive, harus sesuai nama shift di sistem'],
//                     ['PH',              'Public Holiday — hari libur nasional',                       'PH',                   'Otomatis terisi di template sesuai agama karyawan. Jika PH digeser, isi kode PH di tanggal pengganti'],
//                     ['OFF',             'Hari libur / day off',                                       'OFF',                  'Karyawan tidak bekerja, bukan PH'],
//                     ['LEAVE',           'Cuti tahunan',                                               'LEAVE',                'Pastikan kuota cuti karyawan mencukupi'],
//                     ['TOIL OFF',        'Time Off In Lieu — pengganti lembur',                        'TOIL OFF',             'Karyawan punya saldo TOIL'],
//                     ['CUTI MELAHIRKAN', 'Cuti melahirkan',                                            'CUTI MELAHIRKAN',      'Khusus karyawan yang sedang cuti melahirkan'],
//                     ['(kosong)',        'Sel kosong = tidak ada jadwal di tanggal tersebut',          '-',                    'Biarkan kosong jika tidak ada jadwal'],
//                 ];
 
//                 $styleNormal = [
//                     'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
//                 ];
//                 $styleCode = [
//                     'font'      => ['bold' => true, 'color' => ['argb' => 'FF1E40AF']],
//                     'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFEFF6FF']],
//                     'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
//                 ];
 
//                 foreach ($rows as $idx => $row) {
//                     $r = $idx + 3;
//                     $sheet->setCellValue("A{$r}", $row[0]);
//                     $sheet->setCellValue("B{$r}", $row[1]);
//                     $sheet->setCellValue("C{$r}", $row[2]);
//                     $sheet->setCellValue("D{$r}", $row[3]);
//                     $sheet->getStyle("A{$r}")->applyFromArray($styleCode);
//                     $sheet->getStyle("B{$r}:D{$r}")->applyFromArray($styleNormal);
//                     $sheet->getRowDimension($r)->setRowHeight(40);
 
//                     // Stripe baris
//                     if ($idx % 2 === 0) {
//                         $sheet->getStyle("B{$r}:D{$r}")->applyFromArray([
//                             'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF8FAFC']],
//                         ]);
//                     }
//                 }
 
//                 // ── Catatan khusus PH Tukar ──
//                 $noteRow = count($rows) + 3;
//                 $sheet->setCellValue("A{$noteRow}", '⚠ Catatan PH Tukar');
//                 $sheet->mergeCells("A{$noteRow}:D{$noteRow}");
//                 $sheet->getStyle("A{$noteRow}")->applyFromArray([
//                     'font'      => ['bold' => true, 'color' => ['argb' => 'FF92400E']],
//                     'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFEF3C7']],
//                     'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
//                 ]);
//                 $sheet->getRowDimension($noteRow)->setRowHeight(20);
 
//                 $noteRow++;
//                 $notes = [
//                     '1. Jika karyawan PKWT/OJT bekerja di tanggal PH, sistem akan menyimpan saldo PH tukar secara otomatis.',
//                     '2. Saldo PH tukar dapat digunakan di tanggal lain dalam periode yang sama (maks 2 bulan setelah periode berakhir).',
//                     '3. Karyawan DW (Daily Worker) TIDAK berhak mendapatkan PH. Jangan isi kode PH untuk karyawan DW.',
//                     '4. PH yang jatuh di hari Minggu untuk store Head Office / Holding / Distribution Center akan HANGUS (tidak bisa ditukar).',
//                     '5. Jumlah kode PH pengganti tidak boleh melebihi jumlah PH asli yang diperoleh karyawan di periode tersebut.',
//                 ];
//                 foreach ($notes as $note) {
//                     $sheet->setCellValue("A{$noteRow}", $note);
//                     $sheet->mergeCells("A{$noteRow}:D{$noteRow}");
//                     $sheet->getStyle("A{$noteRow}")->applyFromArray([
//                         'font'      => ['size' => 10, 'color' => ['argb' => 'FF78350F']],
//                         'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFBEB']],
//                         'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
//                     ]);
//                     $sheet->getRowDimension($noteRow)->setRowHeight(20);
//                     $noteRow++;
//                 }
 
//                 // ── Border seluruh tabel ──
//                 $lastDataRow = $noteRow - 1;
//                 $sheet->getStyle("A2:D{$lastDataRow}")->applyFromArray([
//                     'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD1D5DB']]],
//                 ]);
 
//                 // ── Lebar kolom ──
//                 $sheet->getColumnDimension('A')->setWidth(22);
//                 $sheet->getColumnDimension('B')->setWidth(45);
//                 $sheet->getColumnDimension('C')->setWidth(20);
//                 $sheet->getColumnDimension('D')->setWidth(50);
//             },
//         ];
//     }
// }
 
// // ═══════════════════════════════════════════════════════════════
// //  SHEET 3 — Daftar Shift
// // ═══════════════════════════════════════════════════════════════
// class RosterShiftSheet implements WithEvents, WithTitle
// {
//     public function __construct(private string $storeId) {}
 
//     public function title(): string
//     {
//         return 'Shift';
//     }
 
//     public function registerEvents(): array
//     {
//         return [
//             AfterSheet::class => function (AfterSheet $event) {
//                 $sheet     = $event->sheet->getDelegate();
//                 $storeName = Stores::find($this->storeId)?->name ?? '-';
//                 $shifts    = Shifts::where('store_id', $this->storeId)
//                     ->orderBy('shift_name')
//                     ->get(['shift_name', 'start_time', 'end_time']);
 
//                 // ── Judul ──
//                 $sheet->setCellValue('A1', "Daftar Shift — {$storeName}");
//                 $sheet->mergeCells('A1:D1');
//                 $sheet->getStyle('A1')->applyFromArray([
//                     'font'      => ['bold' => true, 'size' => 13, 'color' => ['argb' => 'FFFFFFFF']],
//                     'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0F172A']],
//                     'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
//                 ]);
//                 $sheet->getRowDimension(1)->setRowHeight(30);
 
//                 // ── Header ──
//                 $sheet->setCellValue('A2', 'Nama Shift');
//                 $sheet->setCellValue('B2', 'Jam Mulai');
//                 $sheet->setCellValue('C2', 'Jam Selesai');
//                 $sheet->setCellValue('D2', 'Kode di Template');
//                 $sheet->getStyle('A2:D2')->applyFromArray([
//                     'font'      => ['bold' => true],
//                     'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFF00']],
//                     'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
//                 ]);
//                 $sheet->getRowDimension(2)->setRowHeight(20);
 
//                 // ── Data shift ──
//                 if ($shifts->isEmpty()) {
//                     $sheet->setCellValue('A3', 'Tidak ada shift terdaftar untuk store ini.');
//                     $sheet->mergeCells('A3:D3');
//                     $sheet->getStyle('A3')->applyFromArray([
//                         'font'      => ['italic' => true, 'color' => ['argb' => 'FF94A3B8']],
//                         'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
//                     ]);
//                 } else {
//                     foreach ($shifts as $idx => $shift) {
//                         $r = $idx + 3;
//                         $sheet->setCellValue("A{$r}", $shift->shift_name);
//                         $sheet->setCellValue("B{$r}", substr($shift->start_time, 0, 5));
//                         $sheet->setCellValue("C{$r}", substr($shift->end_time, 0, 5));
//                         $sheet->setCellValue("D{$r}", strtoupper($shift->shift_name));
 
//                         $sheet->getStyle("A{$r}:D{$r}")->applyFromArray([
//                             'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
//                         ]);
 
//                         // Highlight kolom kode
//                         $sheet->getStyle("D{$r}")->applyFromArray([
//                             'font' => ['bold' => true, 'color' => ['argb' => 'FF1E40AF']],
//                             'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFEFF6FF']],
//                         ]);
 
//                         // Stripe baris
//                         if ($idx % 2 === 0) {
//                             $sheet->getStyle("A{$r}:C{$r}")->applyFromArray([
//                                 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF8FAFC']],
//                             ]);
//                         }
 
//                         $sheet->getRowDimension($r)->setRowHeight(22);
//                     }
 
//                     // ── Border ──
//                     $lastRow = $shifts->count() + 2;
//                     $sheet->getStyle("A2:D{$lastRow}")->applyFromArray([
//                         'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD1D5DB']]],
//                     ]);
//                 }
 
//                 // ── Lebar kolom ──
//                 $sheet->getColumnDimension('A')->setWidth(20);
//                 $sheet->getColumnDimension('B')->setWidth(12);
//                 $sheet->getColumnDimension('C')->setWidth(12);
//                 $sheet->getColumnDimension('D')->setWidth(20);
//             },
//         ];
//     }
//     public function __construct(
//         private string $storeId,
//         private string $startDate,
//         private string $endDate
//     ) {}
 
//     public function sheets(): array
//     {
//         return [
//             new RosterTemplateSheet($this->storeId, $this->startDate, $this->endDate),
//             new RosterTutorialSheet(),
//             new RosterShiftSheet($this->storeId),
//         ];
//     }
 
//     /**
//      * Cek apakah tanggal adalah PH untuk agama karyawan tertentu.
//      * DW tidak perlu dikirim ke sini karena sudah di-skip di loop atas.
//      */
//     private function isPublicHolidayForEmployee(array $phMap, string $date, ?string $religion): bool
//     {
//         if (!isset($phMap[$date])) {
//             return false;
//         }
 
//         $phType = $phMap[$date]['type'];
 
//         if ($phType === 'All') {
//             return true;
//         }
 
//         if (empty($religion)) {
//             return false;
//         }
 
//         if ($phType === 'Hindu') {
//             return $religion === 'Hindu';
//         }
 
//         if ($phType === 'Non Hindu') {
//             return $religion !== 'Hindu';
//         }
 
//         return false;
//     }
 
//     // Helper: nomor kolom → huruf (1=A, 27=AA, dst)
//     private function colLetter(int $n): string
//     {
//         $letter = '';
//         while ($n > 0) {
//             $n--;
//             $letter = chr(65 + ($n % 26)) . $letter;
//             $n      = intdiv($n, 26);
//         }
//         return $letter;
//     }
// }
 
// // ═══════════════════════════════════════════════════════════════
// //  SHEET 1 — Template Roster
// // ═══════════════════════════════════════════════════════════════
// class RosterTemplateSheet implements WithEvents, WithTitle
// {
//     public function __construct(
//         private string $storeId,
//         private string $startDate,
//         private string $endDate
//     ) {}
 
//     public function title(): string
//     {
//         return 'Template Roster';
//     }
 
//     public function registerEvents(): array
//     {
//         return [
//             AfterSheet::class => function (AfterSheet $event) {
//                 $sheet = $event->sheet->getDelegate();
 
//                 $storeName = Stores::find($this->storeId)?->name ?? '-';
 
//                 // ── Generate tanggal ──
//                 $dates   = [];
//                 $current = Carbon::parse($this->startDate);
//                 $end     = Carbon::parse($this->endDate);
//                 while ($current->lte($end)) {
//                     $dates[] = $current->copy();
//                     $current->addDay();
//                 }
 
//                 // ── Ambil Public Holiday master dalam rentang ──
//                 $phMap    = [];
//                 $holidays = Ph::whereBetween('date', [$this->startDate, $this->endDate])
//                     ->get(['date', 'type', 'remark']);
//                 foreach ($holidays as $ph) {
//                     $key          = Carbon::parse($ph->date)->toDateString();
//                     $phMap[$key]  = ['type' => $ph->type, 'remark' => $ph->remark];
//                 }
 
//                 $firstDateColIndex = 5; // ← geser dari 4 ke 5 karena tambah kolom position
//                 $totalCols         = 4 + count($dates); // ← 4 kolom info (A-D)
//                 $lastColLetter     = $this->colLetter($totalCols);
 
//                 // ── Baris 1: Judul ──
//                 $sheet->setCellValue('A1', 'Schedule');
//                 $sheet->mergeCells("A1:{$lastColLetter}1");
//                 $sheet->getStyle('A1')->applyFromArray([
//                     'font'      => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FFFFFFFF']],
//                     'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0F172A']],
//                     'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
//                 ]);
 
//                 // ── Baris 2: Location + Periode ──
//                 $sheet->setCellValue('A2', "Location: {$storeName}     |     Periode: {$this->startDate} s/d {$this->endDate}");
//                 $sheet->mergeCells("A2:{$lastColLetter}2");
//                 $sheet->getStyle('A2')->applyFromArray([
//                     'font'      => ['size' => 10, 'italic' => true, 'color' => ['argb' => 'FF64748B']],
//                     'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF8FAFC']],
//                     'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
//                 ]);
 
//                 // ── Baris 3: HEADER ──
//                 $sheet->setCellValue('A3', 'employee_pengenal');
//                 $sheet->setCellValue('B3', 'employee_name');
//                 $sheet->setCellValue('C3', 'position');  // ← baru
//                 $sheet->setCellValue('D3', 'store');     // ← geser dari C
 
//                 foreach ($dates as $i => $date) {
//                     $col = $this->colLetter($firstDateColIndex + $i);
//                     $sheet->setCellValue("{$col}3", $date->day);
//                 }
 
//                 // ── Baris 4: HARI ──
//                 $hariMap = [0 => 'M', 1 => 'S', 2 => 'S', 3 => 'R', 4 => 'K', 5 => 'J', 6 => 'S'];
//                 foreach ($dates as $i => $date) {
//                     $col = $this->colLetter($firstDateColIndex + $i);
//                     $sheet->setCellValue("{$col}4", $hariMap[$date->dayOfWeek]);
//                 }
 
//                 $sheet->getStyle("A3:{$lastColLetter}4")->applyFromArray([
//                     'font'      => ['bold' => true, 'color' => ['argb' => 'FF000000']],
//                     'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFF00']],
//                     'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
//                 ]);
 
//                 // ── Ambil employee via pivot + eager load position primary ──
//                 $employees = Employee::with([
//                         'position' => fn($q) => $q->wherePivot('is_primary', true),
//                     ])
//                     ->whereNull('deleted_at')
//                     ->whereIn('status', ['Active', 'On Leave', 'Pending'])
//                     ->whereHas('store', fn($q) => $q->where('stores_tables.id', $this->storeId))
//                     ->orderBy('employee_name')
//                     ->get(['id', 'employee_pengenal', 'employee_name', 'religion', 'status_employee']);
 
//                 $rowNum = 5;
//                 foreach ($employees as $emp) {
//                     $positionName = $emp->position->first()?->position_name ?? '-';
 
//                     $sheet->setCellValue("A{$rowNum}", $emp->employee_pengenal ?? '');
//                     $sheet->setCellValue("B{$rowNum}", $emp->employee_name ?? '');
//                     $sheet->setCellValue("C{$rowNum}", $positionName); // ← position
//                     $sheet->setCellValue("D{$rowNum}", $storeName);    // ← store
 
//                     $sheet->getStyle("A{$rowNum}:D{$rowNum}")->applyFromArray([
//                         'font' => ['bold' => true],
//                         'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFEF9C3']],
//                     ]);
 
//                     $isDW = $emp->status_employee === 'DW';
 
//                     foreach ($dates as $i => $date) {
//                         $col     = $this->colLetter($firstDateColIndex + $i);
//                         $dateStr = $date->toDateString();
 
//                         $isPH = $isDW
//                             ? false
//                             : $this->isPublicHolidayForEmployee($phMap, $dateStr, $emp->religion);
 
//                         $sheet->setCellValue("{$col}{$rowNum}", $isPH ? 'PH' : '');
 
//                         if ($isPH) {
//                             $sheet->getStyle("{$col}{$rowNum}")->applyFromArray([
//                                 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFE0B2']],
//                                 'font' => ['bold' => true, 'color' => ['argb' => 'FFB45309']],
//                             ]);
//                         } elseif ($date->isSunday()) {
//                             $sheet->getStyle("{$col}{$rowNum}")->applyFromArray([
//                                 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFF9C3']],
//                             ]);
//                         }
 
//                         $sheet->getStyle("{$col}{$rowNum}")->applyFromArray([
//                             'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
//                         ]);
//                     }
//                     $rowNum++;
//                 }
 
//                 // ── Border ──
//                 $sheet->getStyle("A3:{$lastColLetter}" . ($rowNum - 1))->applyFromArray([
//                     'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD1D5DB']]],
//                 ]);
 
//                 // ── Lebar kolom ──
//                 $sheet->getColumnDimension('A')->setWidth(25);
//                 $sheet->getColumnDimension('B')->setWidth(22);
//                 $sheet->getColumnDimension('C')->setWidth(20); // position
//                 $sheet->getColumnDimension('D')->setWidth(14); // store
//                 foreach ($dates as $i => $_) {
//                     $sheet->getColumnDimension($this->colLetter($firstDateColIndex + $i))->setWidth(5);
//                 }
 
//                 $sheet->freezePane('E5'); // ← geser dari D5 ke E5
//             },
//         ];
//     }
 
//     private function isPublicHolidayForEmployee(array $phMap, string $date, ?string $religion): bool
//     {
//         if (!isset($phMap[$date])) return false;
//         $phType = $phMap[$date]['type'];
//         if ($phType === 'All') return true;
//         if (empty($religion)) return false;
//         if ($phType === 'Hindu') return $religion === 'Hindu';
//         if ($phType === 'Non Hindu') return $religion !== 'Hindu';
//         return false;
//     }
 
//     private function colLetter(int $n): string
//     {
//         $letter = '';
//         while ($n > 0) {
//             $n--;
//             $letter = chr(65 + ($n % 26)) . $letter;
//             $n      = intdiv($n, 26);
//         }
//         return $letter;
//     }
// }
 
// // ═══════════════════════════════════════════════════════════════
// //  SHEET 2 — Tutorial Pengisian
// // ═══════════════════════════════════════════════════════════════
// class RosterTutorialSheet implements WithEvents, WithTitle
// {
//     public function title(): string
//     {
//         return 'Tutorial';
//     }
 
//     public function registerEvents(): array
//     {
//         return [
//             AfterSheet::class => function (AfterSheet $event) {
//                 $sheet = $event->sheet->getDelegate();
 
//                 // ── Judul ──
//                 $sheet->setCellValue('A1', 'PANDUAN PENGISIAN TEMPLATE ROSTER');
//                 $sheet->mergeCells('A1:D1');
//                 $sheet->getStyle('A1')->applyFromArray([
//                     'font'      => ['bold' => true, 'size' => 13, 'color' => ['argb' => 'FFFFFFFF']],
//                     'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0F172A']],
//                     'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
//                 ]);
//                 $sheet->getRowDimension(1)->setRowHeight(30);
 
//                 // ── Header tabel ──
//                 $sheet->setCellValue('A2', 'Kode');
//                 $sheet->setCellValue('B2', 'Keterangan');
//                 $sheet->setCellValue('C2', 'Contoh');
//                 $sheet->setCellValue('D2', 'Catatan');
//                 $sheet->getStyle('A2:D2')->applyFromArray([
//                     'font'      => ['bold' => true, 'color' => ['argb' => 'FF000000']],
//                     'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFF00']],
//                     'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
//                 ]);
//                 $sheet->getRowDimension(2)->setRowHeight(20);
 
//                 // ── Isi tutorial ──
//                 $rows = [
//                     ['NAMA SHIFT',      'Isi dengan nama shift yang tersedia (lihat sheet "Shift")', 'pagi / siang / malam', 'Tidak case-sensitive, harus sesuai nama shift di sistem'],
//                     ['PH',              'Public Holiday — hari libur nasional',                       'PH',                   'Otomatis terisi di template sesuai agama karyawan. Jika PH digeser, isi kode PH di tanggal pengganti'],
//                     ['OFF',             'Hari libur / day off',                                       'OFF',                  'Karyawan tidak bekerja, bukan PH'],
//                     // ['LEAVE',           'Cuti tahunan',                                               'LEAVE',                'Pastikan kuota cuti karyawan mencukupi'],
//                     // ['TOIL OFF',        'Time Off In Lieu — pengganti lembur',                        'TOIL OFF',             'Karyawan punya saldo TOIL'],
//                     // ['CUTI MELAHIRKAN', 'Cuti melahirkan',                                            'CUTI MELAHIRKAN',      'Khusus karyawan yang sedang cuti melahirkan'],
//                     // ['(kosong)',        'Sel kosong = tidak ada jadwal di tanggal tersebut',          '-',                    'Biarkan kosong jika tidak ada jadwal'],
//                 ];
 
//                 $styleNormal = [
//                     'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
//                 ];
//                 $styleCode = [
//                     'font'      => ['bold' => true, 'color' => ['argb' => 'FF1E40AF']],
//                     'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFEFF6FF']],
//                     'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
//                 ];
 
//                 foreach ($rows as $idx => $row) {
//                     $r = $idx + 3;
//                     $sheet->setCellValue("A{$r}", $row[0]);
//                     $sheet->setCellValue("B{$r}", $row[1]);
//                     $sheet->setCellValue("C{$r}", $row[2]);
//                     $sheet->setCellValue("D{$r}", $row[3]);
//                     $sheet->getStyle("A{$r}")->applyFromArray($styleCode);
//                     $sheet->getStyle("B{$r}:D{$r}")->applyFromArray($styleNormal);
//                     $sheet->getRowDimension($r)->setRowHeight(40);
 
//                     // Stripe baris
//                     if ($idx % 2 === 0) {
//                         $sheet->getStyle("B{$r}:D{$r}")->applyFromArray([
//                             'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF8FAFC']],
//                         ]);
//                     }
//                 }
 
//                 // ── Catatan khusus PH Tukar ──
//                 $noteRow = count($rows) + 3;
//                 $sheet->setCellValue("A{$noteRow}", '⚠ Catatan PH Tukar');
//                 $sheet->mergeCells("A{$noteRow}:D{$noteRow}");
//                 $sheet->getStyle("A{$noteRow}")->applyFromArray([
//                     'font'      => ['bold' => true, 'color' => ['argb' => 'FF92400E']],
//                     'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFEF3C7']],
//                     'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
//                 ]);
//                 $sheet->getRowDimension($noteRow)->setRowHeight(20);
 
//                 $noteRow++;
//                 $notes = [
//                     '1. Jika karyawan PKWT/OJT bekerja di tanggal PH, sistem akan menyimpan saldo PH tukar secara otomatis.',
//                     '2. Saldo PH tukar dapat digunakan di tanggal lain dalam periode yang sama (maks 2 bulan setelah periode berakhir).',
//                     '3. Karyawan DW (Daily Worker) TIDAK berhak mendapatkan PH. Jangan isi kode PH untuk karyawan DW.',
//                     '4. PH yang jatuh di hari Minggu untuk store Head Office / Holding / Distribution Center akan HANGUS (tidak bisa ditukar).',
//                     '5. Jumlah kode PH pengganti tidak boleh melebihi jumlah PH asli yang diperoleh karyawan di periode tersebut.',
//                 ];
//                 foreach ($notes as $note) {
//                     $sheet->setCellValue("A{$noteRow}", $note);
//                     $sheet->mergeCells("A{$noteRow}:D{$noteRow}");
//                     $sheet->getStyle("A{$noteRow}")->applyFromArray([
//                         'font'      => ['size' => 10, 'color' => ['argb' => 'FF78350F']],
//                         'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFBEB']],
//                         'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
//                     ]);
//                     $sheet->getRowDimension($noteRow)->setRowHeight(20);
//                     $noteRow++;
//                 }
 
//                 // ── Border seluruh tabel ──
//                 $lastDataRow = $noteRow - 1;
//                 $sheet->getStyle("A2:D{$lastDataRow}")->applyFromArray([
//                     'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD1D5DB']]],
//                 ]);
 
//                 // ── Lebar kolom ──
//                 $sheet->getColumnDimension('A')->setWidth(22);
//                 $sheet->getColumnDimension('B')->setWidth(45);
//                 $sheet->getColumnDimension('C')->setWidth(20);
//                 $sheet->getColumnDimension('D')->setWidth(50);
//             },
//         ];
//     }
// }
 
// // ═══════════════════════════════════════════════════════════════
// //  SHEET 3 — Daftar Shift
// // ═══════════════════════════════════════════════════════════════
// class RosterShiftSheet implements WithEvents, WithTitle
// {
//     public function __construct(private string $storeId) {}
 
//     public function title(): string
//     {
//         return 'Shift';
//     }
 
//     public function registerEvents(): array
//     {
//         return [
//             AfterSheet::class => function (AfterSheet $event) {
//                 $sheet     = $event->sheet->getDelegate();
//                 $storeName = Stores::find($this->storeId)?->name ?? '-';
//                 $shifts    = Shifts::where('store_id', $this->storeId)
//                     ->orderBy('shift_name')
//                     ->get(['shift_name', 'start_time', 'end_time']);
 
//                 // ── Judul ──
//                 $sheet->setCellValue('A1', "Daftar Shift — {$storeName}");
//                 $sheet->mergeCells('A1:D1');
//                 $sheet->getStyle('A1')->applyFromArray([
//                     'font'      => ['bold' => true, 'size' => 13, 'color' => ['argb' => 'FFFFFFFF']],
//                     'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0F172A']],
//                     'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
//                 ]);
//                 $sheet->getRowDimension(1)->setRowHeight(30);
 
//                 // ── Header ──
//                 $sheet->setCellValue('A2', 'Nama Shift');
//                 $sheet->setCellValue('B2', 'Jam Mulai');
//                 $sheet->setCellValue('C2', 'Jam Selesai');
//                 $sheet->setCellValue('D2', 'Kode di Template');
//                 $sheet->getStyle('A2:D2')->applyFromArray([
//                     'font'      => ['bold' => true],
//                     'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFF00']],
//                     'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
//                 ]);
//                 $sheet->getRowDimension(2)->setRowHeight(20);
 
//                 // ── Data shift ──
//                 if ($shifts->isEmpty()) {
//                     $sheet->setCellValue('A3', 'Tidak ada shift terdaftar untuk store ini.');
//                     $sheet->mergeCells('A3:D3');
//                     $sheet->getStyle('A3')->applyFromArray([
//                         'font'      => ['italic' => true, 'color' => ['argb' => 'FF94A3B8']],
//                         'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
//                     ]);
//                 } else {
//                     foreach ($shifts as $idx => $shift) {
//                         $r = $idx + 3;
//                         $sheet->setCellValue("A{$r}", $shift->shift_name);
//                         $sheet->setCellValue("B{$r}", substr($shift->start_time, 0, 5));
//                         $sheet->setCellValue("C{$r}", substr($shift->end_time, 0, 5));
//                         $sheet->setCellValue("D{$r}", strtoupper($shift->shift_name));
 
//                         $sheet->getStyle("A{$r}:D{$r}")->applyFromArray([
//                             'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
//                         ]);
 
//                         // Highlight kolom kode
//                         $sheet->getStyle("D{$r}")->applyFromArray([
//                             'font' => ['bold' => true, 'color' => ['argb' => 'FF1E40AF']],
//                             'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFEFF6FF']],
//                         ]);
 
//                         // Stripe baris
//                         if ($idx % 2 === 0) {
//                             $sheet->getStyle("A{$r}:C{$r}")->applyFromArray([
//                                 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF8FAFC']],
//                             ]);
//                         }
 
//                         $sheet->getRowDimension($r)->setRowHeight(22);
//                     }
 
//                     // ── Border ──
//                     $lastRow = $shifts->count() + 2;
//                     $sheet->getStyle("A2:D{$lastRow}")->applyFromArray([
//                         'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD1D5DB']]],
//                     ]);
//                 }
 
//                 // ── Lebar kolom ──
//                 $sheet->getColumnDimension('A')->setWidth(20);
//                 $sheet->getColumnDimension('B')->setWidth(12);
//                 $sheet->getColumnDimension('C')->setWidth(12);
//                 $sheet->getColumnDimension('D')->setWidth(20);
//             },
//         ];
//     }

 public function __construct(
        private string $storeId,
        private string $startDate,
        private string $endDate,
        private bool   $canManageAll = true,  // ← tambah
        private array  $myDeptIds   = [],     // ← tambah
        private array  $bawahanIds  = [],     // ← tambah
    ) {}
 
    public function sheets(): array
    {
        return [
            new RosterTemplateSheet(
                $this->storeId, 
                $this->startDate, 
                $this->endDate,
                $this->canManageAll,  
                $this->myDeptIds,     
                $this->bawahanIds, ),
            new RosterTutorialSheet(),
            new RosterShiftSheet($this->storeId),
        ];
    }
 
    /**
     * Cek apakah tanggal adalah PH untuk agama karyawan tertentu.
     * DW tidak perlu dikirim ke sini karena sudah di-skip di loop atas.
     */
    private function isPublicHolidayForEmployee(array $phMap, string $date, ?string $religion): bool
    {
        if (!isset($phMap[$date])) {
            return false;
        }
 
        $phType = $phMap[$date]['type'];
 
        if ($phType === 'All') {
            return true;
        }
 
        if (empty($religion)) {
            return false;
        }
 
        if ($phType === 'Hindu') {
            return $religion === 'Hindu';
        }
 
        if ($phType === 'Non Hindu') {
            return $religion !== 'Hindu';
        }
 
        return false;
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
 
// ═══════════════════════════════════════════════════════════════
//  SHEET 1 — Template Roster
// ═══════════════════════════════════════════════════════════════
class RosterTemplateSheet implements WithEvents, WithTitle
{
    public function __construct(
        private string $storeId,
        private string $startDate,
        private string $endDate,
        private bool   $canManageAll = true,  // ← tambah
        private array  $myDeptIds   = [],     // ← tambah
        private array  $bawahanIds  = [],     // ← tambah
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
 
                // ── Ambil Public Holiday master dalam rentang ──
                $phMap    = [];
                $holidays = Ph::whereBetween('date', [$this->startDate, $this->endDate])
                    ->get(['date', 'type', 'remark']);
                foreach ($holidays as $ph) {
                    $key          = Carbon::parse($ph->date)->toDateString();
                    $phMap[$key]  = ['type' => $ph->type, 'remark' => $ph->remark];
                }
 
                $firstDateColIndex = 5; // ← geser dari 4 ke 5 karena tambah kolom position
                $totalCols         = 4 + count($dates); // ← 4 kolom info (A-D)
                $lastColLetter     = $this->colLetter($totalCols);
 
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
 
                // ── Baris 3: HEADER ──
                $sheet->setCellValue('A3', 'employee_pengenal');
                $sheet->setCellValue('B3', 'Name');
                $sheet->setCellValue('C3', 'Position');  // ← baru
                $sheet->setCellValue('D3', 'Location');     // ← geser dari C
 
                foreach ($dates as $i => $date) {
                    $col = $this->colLetter($firstDateColIndex + $i);
                    $sheet->setCellValue("{$col}3", $date->day);
                }
 
                // ── Baris 4: HARI ──
                $hariMap = [0 => 'M', 1 => 'S', 2 => 'S', 3 => 'R', 4 => 'K', 5 => 'J', 6 => 'S'];
                foreach ($dates as $i => $date) {
                    $col = $this->colLetter($firstDateColIndex + $i);
                    $sheet->setCellValue("{$col}4", $hariMap[$date->dayOfWeek]);
                }
 
                $sheet->getStyle("A3:{$lastColLetter}4")->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['argb' => 'FF000000']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFF00']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
 
                // ── Ambil employee via pivot + eager load position primary ──
                // $employees = Employee::with([
                //         'position' => fn($q) => $q->wherePivot('is_primary', true),
                //     ])
                //     ->whereNull('deleted_at')
                //     ->whereIn('status', ['Active', 'On Leave', 'Pending'])
                //     ->whereHas('store', fn($q) => $q->where('stores_tables.id', $this->storeId))
                //     ->orderBy('employee_name')
                //     ->get(['id', 'employee_pengenal', 'employee_name', 'religion', 'status_employee']);
    //             $employees = Employee::with([
    //     'position' => fn($q) => $q->wherePivot('is_primary', true),
    // ])
    // ->whereNull('deleted_at')
    // ->whereIn('status', ['Active', 'On Leave', 'Pending'])
    // ->whereHas('store', fn($q) => $q->where('stores_tables.id', $this->storeId))
    // ->get(['id', 'employee_pengenal', 'employee_name', 'religion', 'status_employee'])
    // // ← Sort by position name A-Z setelah get() karena relasi pivot
    // ->sortBy(fn($emp) => $emp->position->first()?->name ?? 'edw')
    // ->values();
    $employeeQuery = Employee::with([
        'position' => fn($q) => $q->wherePivot('is_primary', true),
    ])
    ->whereNull('deleted_at')
    ->whereIn('status', ['Active', 'On Leave', 'Pending'])
    ->whereHas('store', fn($q) => $q->where('stores_tables.id', $this->storeId));

if (!$this->canManageAll) {
    // SPV: filter department + bawahan
    $employeeQuery->where(function ($q) {
        // Karyawan reguler — department sama
        $q->whereHas('department', fn($dq) =>
            $dq->whereIn('departments_tables.id', $this->myDeptIds)
        );
        // Atau bawahan langsung
        if (!empty($this->bawahanIds)) {
            $q->orWhereIn('id', $this->bawahanIds);
        }
    });
}

$employees = $employeeQuery
    ->get(['id', 'employee_pengenal', 'employee_name', 'religion', 'status_employee'])
    ->sortBy(fn($emp) => $emp->position->first()?->name ?? 'ZZZZ')
    ->values();
 
                $rowNum = 5;
                foreach ($employees as $emp) {
                    $positionName = $emp->position->first()?->name ?? '-';
 
                    $sheet->setCellValue("A{$rowNum}", $emp->employee_pengenal ?? '');
                    $sheet->setCellValue("B{$rowNum}", $emp->employee_name ?? '');
                    $sheet->setCellValue("C{$rowNum}", $positionName); // ← position
                    $sheet->setCellValue("D{$rowNum}", $storeName);    // ← store
 
                    $sheet->getStyle("A{$rowNum}:D{$rowNum}")->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFEF9C3']],
                    ]);
 
                    $isDW = $emp->status_employee === 'DW';
 
                    foreach ($dates as $i => $date) {
                        $col     = $this->colLetter($firstDateColIndex + $i);
                        $dateStr = $date->toDateString();
 
                        $isPH = $isDW
                            ? false
                            : $this->isPublicHolidayForEmployee($phMap, $dateStr, $emp->religion);
 
                        $sheet->setCellValue("{$col}{$rowNum}", $isPH ? 'PH' : '');
 
                        if ($isPH) {
                            $sheet->getStyle("{$col}{$rowNum}")->applyFromArray([
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFE0B2']],
                                'font' => ['bold' => true, 'color' => ['argb' => 'FFB45309']],
                            ]);
                        } elseif ($date->isSunday()) {
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
 
                // ── Border ──
                $sheet->getStyle("A3:{$lastColLetter}" . ($rowNum - 1))->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD1D5DB']]],
                ]);
 
                // ── Lebar kolom ──
                $sheet->getColumnDimension('A')->setWidth(25);
                $sheet->getColumnDimension('B')->setWidth(22);
                $sheet->getColumnDimension('C')->setWidth(20); // position
                $sheet->getColumnDimension('D')->setWidth(14); // store
                foreach ($dates as $i => $_) {
                    $sheet->getColumnDimension($this->colLetter($firstDateColIndex + $i))->setWidth(5);
                }
 
                $sheet->freezePane('E5'); // ← geser dari D5 ke E5
            },
        ];
    }
 
    private function isPublicHolidayForEmployee(array $phMap, string $date, ?string $religion): bool
    {
        if (!isset($phMap[$date])) return false;
        $phType = $phMap[$date]['type'];
        if ($phType === 'All') return true;
        if (empty($religion)) return false;
        if ($phType === 'Hindu') return $religion === 'Hindu';
        if ($phType === 'Non Hindu') return $religion !== 'Hindu';
        return false;
    }
 
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
 
// ═══════════════════════════════════════════════════════════════
//  SHEET 2 — Tutorial Pengisian
// ═══════════════════════════════════════════════════════════════
class RosterTutorialSheet implements WithEvents, WithTitle
{
    public function title(): string
    {
        return 'Tutorial';
    }
 
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
 
                // ── Judul ──
                $sheet->setCellValue('A1', 'PANDUAN PENGISIAN TEMPLATE ROSTER');
                $sheet->mergeCells('A1:D1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 13, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0F172A']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(30);
 
                // ── Header tabel ──
                $sheet->setCellValue('A2', 'Kode');
                $sheet->setCellValue('B2', 'Keterangan');
                $sheet->setCellValue('C2', 'Contoh');
                $sheet->setCellValue('D2', 'Catatan');
                $sheet->getStyle('A2:D2')->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['argb' => 'FF000000']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFF00']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(20);
 
                // ── Isi tutorial ──
                $rows = [
                    ['NAMA SHIFT',      'Isi dengan kode shift 2 huruf (lihat sheet "Shift" kolom Kode)', 'PA / SI / MA', 'Lihat kolom "Kode di Template" di sheet Shift. Tidak case-sensitive'],
                    ['PH',              'Public Holiday — hari libur nasional',                       'PH',                   'Otomatis terisi di template sesuai agama karyawan. Jika PH digeser, isi kode PH di tanggal pengganti'],
                    ['OFF',             'Hari libur / day off',                                       'OFF',                  'Karyawan tidak bekerja, bukan PH'],
                    // ['LEAVE',           'Cuti tahunan',                                               'LEAVE',                'Pastikan kuota cuti karyawan mencukupi'],
                    // ['TOIL OFF',        'Time Off In Lieu — pengganti lembur',                        'TOIL OFF',             'Karyawan punya saldo TOIL'],
                    // ['CUTI MELAHIRKAN', 'Cuti melahirkan',                                            'CUTI MELAHIRKAN',      'Khusus karyawan yang sedang cuti melahirkan'],
                    // ['(kosong)',        'Sel kosong = tidak ada jadwal di tanggal tersebut',          '-',                    'Biarkan kosong jika tidak ada jadwal'],
                ];
 
                $styleNormal = [
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                ];
                $styleCode = [
                    'font'      => ['bold' => true, 'color' => ['argb' => 'FF1E40AF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFEFF6FF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ];
 
                foreach ($rows as $idx => $row) {
                    $r = $idx + 3;
                    $sheet->setCellValue("A{$r}", $row[0]);
                    $sheet->setCellValue("B{$r}", $row[1]);
                    $sheet->setCellValue("C{$r}", $row[2]);
                    $sheet->setCellValue("D{$r}", $row[3]);
                    $sheet->getStyle("A{$r}")->applyFromArray($styleCode);
                    $sheet->getStyle("B{$r}:D{$r}")->applyFromArray($styleNormal);
                    $sheet->getRowDimension($r)->setRowHeight(40);
 
                    // Stripe baris
                    if ($idx % 2 === 0) {
                        $sheet->getStyle("B{$r}:D{$r}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF8FAFC']],
                        ]);
                    }
                }
 
                // ── Catatan khusus PH Tukar ──
                $noteRow = count($rows) + 3;
                $sheet->setCellValue("A{$noteRow}", '⚠ Catatan PH Tukar');
                $sheet->mergeCells("A{$noteRow}:D{$noteRow}");
                $sheet->getStyle("A{$noteRow}")->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['argb' => 'FF92400E']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFEF3C7']],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension($noteRow)->setRowHeight(20);
 
                $noteRow++;
                $notes = [
                    '1. Jika karyawan PKWT/OJT bekerja di tanggal PH, sistem akan menyimpan saldo PH tukar secara otomatis.',
                    '2. Saldo PH tukar dapat digunakan di tanggal lain dalam periode yang sama (maks 2 bulan setelah periode berakhir).',
                    '3. Karyawan DW (Daily Worker) TIDAK berhak mendapatkan PH. Jangan isi kode PH untuk karyawan DW.',
                    '4. PH yang jatuh di hari Minggu untuk store Head Office / Holding / Distribution Center akan HANGUS (tidak bisa ditukar).',
                    '5. Jumlah kode PH pengganti tidak boleh melebihi jumlah PH asli yang diperoleh karyawan di periode tersebut.',
                ];
                foreach ($notes as $note) {
                    $sheet->setCellValue("A{$noteRow}", $note);
                    $sheet->mergeCells("A{$noteRow}:D{$noteRow}");
                    $sheet->getStyle("A{$noteRow}")->applyFromArray([
                        'font'      => ['size' => 10, 'color' => ['argb' => 'FF78350F']],
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFBEB']],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    ]);
                    $sheet->getRowDimension($noteRow)->setRowHeight(20);
                    $noteRow++;
                }
 
                // ── Border seluruh tabel ──
                $lastDataRow = $noteRow - 1;
                $sheet->getStyle("A2:D{$lastDataRow}")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD1D5DB']]],
                ]);
 
                // ── Lebar kolom ──
                $sheet->getColumnDimension('A')->setWidth(22);
                $sheet->getColumnDimension('B')->setWidth(45);
                $sheet->getColumnDimension('C')->setWidth(20);
                $sheet->getColumnDimension('D')->setWidth(50);
            },
        ];
    }
}
 
// ═══════════════════════════════════════════════════════════════
//  SHEET 3 — Daftar Shift
// ═══════════════════════════════════════════════════════════════
class RosterShiftSheet implements WithEvents, WithTitle
{
    public function __construct(private string $storeId) {}
 
    public function title(): string
    {
        return 'Shift';
    }
 
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet     = $event->sheet->getDelegate();
                $storeName = Stores::find($this->storeId)?->name ?? '-';
                $shifts    = Shifts::where('store_id', $this->storeId)
                    ->orderBy('shift_name')
                    ->get(['shift_name', 'start_time', 'end_time','code']);
 
                // ── Judul ──
                $sheet->setCellValue('A1', "Daftar Shift — {$storeName}");
                $sheet->mergeCells('A1:D1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 13, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0F172A']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(30);
 
                // ── Header ──
                $sheet->setCellValue('A2', 'Nama Shift');
                $sheet->setCellValue('B2', 'Jam Mulai');
                $sheet->setCellValue('C2', 'Jam Selesai');
                $sheet->setCellValue('D2', 'Kode di Template');
                $sheet->getStyle('A2:D2')->applyFromArray([
                    'font'      => ['bold' => true],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFF00']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(20);
 
                // ── Data shift ──
                if ($shifts->isEmpty()) {
                    $sheet->setCellValue('A3', 'Tidak ada shift terdaftar untuk store ini.');
                    $sheet->mergeCells('A3:D3');
                    $sheet->getStyle('A3')->applyFromArray([
                        'font'      => ['italic' => true, 'color' => ['argb' => 'FF94A3B8']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);
                } else {
                    foreach ($shifts as $idx => $shift) {
                        $r = $idx + 3;
                        $sheet->setCellValue("A{$r}", $shift->shift_name);
                        $sheet->setCellValue("B{$r}", substr($shift->start_time, 0, 5));
                        $sheet->setCellValue("C{$r}", substr($shift->end_time, 0, 5));
                        $sheet->setCellValue("D{$r}", strtoupper($shift->code)); // ← pakai code
 
                        $sheet->getStyle("A{$r}:D{$r}")->applyFromArray([
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                        ]);
 
                        // Highlight kolom kode
                        $sheet->getStyle("D{$r}")->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['argb' => 'FF1E40AF']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFEFF6FF']],
                        ]);
 
                        // Stripe baris
                        if ($idx % 2 === 0) {
                            $sheet->getStyle("A{$r}:C{$r}")->applyFromArray([
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF8FAFC']],
                            ]);
                        }
 
                        $sheet->getRowDimension($r)->setRowHeight(22);
                    }
 
                    // ── Border ──
                    $lastRow = $shifts->count() + 2;
                    $sheet->getStyle("A2:D{$lastRow}")->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD1D5DB']]],
                    ]);
                }
 
                // ── Lebar kolom ──
                $sheet->getColumnDimension('A')->setWidth(20);
                $sheet->getColumnDimension('B')->setWidth(12);
                $sheet->getColumnDimension('C')->setWidth(12);
                $sheet->getColumnDimension('D')->setWidth(20);
            },
        ];
    }
}
