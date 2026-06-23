<?php
namespace App\Exports;

use App\Models\Roster;
use App\Models\Stores;
use App\Models\Shifts;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Concerns\WithTitle;


// class RosterHistoryExport implements WithEvents, ShouldAutoSize
class RosterHistoryExport implements WithMultipleSheets
{
      public function __construct(
        protected string $startDate,
        protected string $endDate,
        protected ?string $storeId,
        protected ?string $search,
        protected bool $canManageAll,
        protected ?string $myStoreId,
        protected ?string $storeName = null,
        protected ?string $employeeIdFilter = null,
        protected ?string $myDepartmentId = null,
        protected ?string $myCompanyId = null,
    ) {}
 
    public function sheets(): array
    {
        return [
            new RosterHistorySheet(
                $this->startDate,
                $this->endDate,
                $this->storeId,
                $this->search,
                $this->canManageAll,
                $this->myStoreId,
                $this->storeName,
                $this->employeeIdFilter,
                $this->myDepartmentId,
                $this->myCompanyId,
            ),
            new RosterHistoryTutorialSheet(),
            new RosterHistoryShiftSheet($this->storeId ?? $this->myStoreId),
        ];
    }
}
 
// ═══════════════════════════════════════════════════════════════
//  SHEET 1 — History Roster
// ═══════════════════════════════════════════════════════════════
class RosterHistorySheet implements WithEvents, WithTitle, ShouldAutoSize
{
    public function __construct(
        protected string $startDate,
        protected string $endDate,
        protected ?string $storeId,
        protected ?string $search,
        protected bool $canManageAll,
        protected ?string $myStoreId,
        protected ?string $storeName = null,
        protected ?string $employeeIdFilter = null,
        protected ?string $myDepartmentId = null,
        protected ?string $myCompanyId = null,
    ) {}
 
    public function title(): string
    {
        return 'Roster';
    }
 
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
 
                $query = Roster::with([
                    'employee:id,employee_name,status_employee,company_id',
                    'employee.department:id,department_name',
                    'employee.position'  => fn($q) => $q->wherePivot('is_primary', true),
                    'employee.store'     => fn($q) => $q->wherePivot('is_primary', true),
                    'shift:id,shift_name,code,start_time,end_time',
                ])
                ->whereBetween('date', [$this->startDate, $this->endDate])
                ->whereHas('employee', function ($q) {
                    $q->whereNull('deleted_at')
                      ->where('status', 'Active');
 
                    if ($this->search) {
                        $q->where('employee_name', 'like', '%' . $this->search . '%');
                    }
 
                    if ($this->employeeIdFilter) {
                        $q->where('id', $this->employeeIdFilter);
 
                    } elseif ($this->canManageAll) {
                        if ($this->storeId) {
                            $q->whereHas('store', fn($sq) =>
                                $sq->where('stores_tables.id', $this->storeId)
                            );
                        }
 
                    } else {
                        if ($this->myStoreId) {
                            $q->whereExists(function ($sq) {
                                $sq->select(DB::raw(1))
                                    ->from('employee_stores')
                                    ->whereColumn('employee_stores.employee_id', 'employees_tables.id')
                                    ->where('employee_stores.store_id', $this->myStoreId);
                            });
                        }
 
                        if ($this->myDepartmentId) {
                            $q->whereExists(function ($sq) {
                                $sq->select(DB::raw(1))
                                    ->from('employee_departments')
                                    ->whereColumn('employee_departments.employee_id', 'employees_tables.id')
                                    ->where('employee_departments.department_id', $this->myDepartmentId);
                            });
                        }
 
                        if ($this->myCompanyId) {
                            $q->where('company_id', $this->myCompanyId);
                        }
                    }
                })
                ->orderBy('employee_id')
                ->orderBy('date')
                ->get();
 
                // ── Generate tanggal ──
                $dates   = [];
                $current = Carbon::parse($this->startDate);
                $end     = Carbon::parse($this->endDate);
                while ($current->lte($end)) {
                    $dates[] = $current->copy();
                    $current->addDay();
                }
 
                // A=NAMA, B=POSISI, C=STORE, D dst=tanggal
                $firstDateColIndex = 4; // D = index 4
                $totalCols         = 3 + count($dates);
                $lastColLetter     = $this->colLetter($totalCols);
 
                // ── Baris 1: Judul ──
                $storeName = $this->storeName ?? 'All Locations';
                $sheet->setCellValue('A1', 'Schedule');
                $sheet->mergeCells("A1:{$lastColLetter}1");
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0F172A']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
 
                // ── Baris 2: Store & Periode ──
                $sheet->setCellValue('A2', "Location: {$storeName}     |     Periode: {$this->startDate} s/d {$this->endDate}");
                $sheet->mergeCells("A2:{$lastColLetter}2");
                $sheet->getStyle('A2')->applyFromArray([
                    'font'      => ['size' => 10, 'italic' => true, 'color' => ['argb' => 'FF64748B']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF8FAFC']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
 
                // ── Baris 3: Header kolom ──
                $sheet->setCellValue('A3', 'Name');
                $sheet->setCellValue('B3', 'Position');
                $sheet->setCellValue('C3', 'Location');
                foreach ($dates as $i => $date) {
                    $col = $this->colLetter($firstDateColIndex + $i);
                    $sheet->setCellValue("{$col}3", $date->day);
                }
 
                // ── Baris 4: HARI ──
                $hariMap = [0 => 'M', 1 => 'S', 2 => 'S', 3 => 'R', 4 => 'K', 5 => 'J', 6 => 'S'];
                $sheet->setCellValue('A4', 'HARI');
                $sheet->setCellValue('B4', '');
                $sheet->setCellValue('C4', '');
                foreach ($dates as $i => $date) {
                    $col = $this->colLetter($firstDateColIndex + $i);
                    $sheet->setCellValue("{$col}4", $hariMap[$date->dayOfWeek]);
                }
 
                // Style header A-C
                $sheet->getStyle("A3:C4")->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['argb' => 'FF000000']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFF00']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
 
                // Style kolom tanggal
                foreach ($dates as $i => $date) {
                    $col   = $this->colLetter($firstDateColIndex + $i);
                    $isSun = $date->isSunday();
 
                    $style = [
                        'font'      => ['bold' => true, 'color' => ['argb' => 'FF000000']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ];
                    if ($isSun) {
                        $style['fill'] = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFF00']];
                    }
                    $sheet->getStyle("{$col}3:{$col}4")->applyFromArray($style);
                }
 
                // ── Baris 5+: Data employee ──
                $grouped = $query->groupBy('employee_id');
                $rowNum  = 5;
 
                foreach ($grouped as $empId => $items) {
                    $emp        = $items->first()->employee;
                    $rosterMap  = $items->keyBy(fn($r) => Carbon::parse($r->date)->toDateString());
 
                    $positionName = $emp->position?->first()?->name ?? '-';
                    $empStoreName = $emp->store?->first()?->name ?? '-';
 
                    // Kolom A: nama
                    $sheet->setCellValue("A{$rowNum}", $emp->employee_name);
                    $sheet->getStyle("A{$rowNum}")->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFF00']],
                    ]);
 
                    // Kolom B: posisi
                    $sheet->setCellValue("B{$rowNum}", $positionName);
                    $sheet->getStyle("B{$rowNum}")->applyFromArray([
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFF00']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                    ]);
 
                    // Kolom C: store
                    $sheet->setCellValue("C{$rowNum}", $empStoreName);
                    $sheet->getStyle("C{$rowNum}")->applyFromArray([
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFF00']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                    ]);
 
                    // Kolom tanggal
                    foreach ($dates as $i => $date) {
                        $col     = $this->colLetter($firstDateColIndex + $i);
                        $dateStr = $date->toDateString();
                        $roster  = $rosterMap->get($dateStr);
                        $isSun   = $date->isSunday();
 
                        // ── Nilai cell — Work pakai code bukan shift_name ──
                        $value = '';
                        if ($roster) {
                            $value = match ($roster->day_type) {
                                'Off'             => 'Off',
                                'Public Holiday'  => 'PH',
                                'Leave'           => 'C',
                                'Cuti Melahirkan' => 'CM',
                                'Sick'            => 'S',
                                'TOIL Off'        => 'TO',
                                'Work'            => strtoupper($roster->shift?->code ?? $roster->shift?->shift_name ?? 'M'),
                                default           => '',
                            };
                        }
 
                        $sheet->setCellValue("{$col}{$rowNum}", $value);
 
                        // ── Background logic ──
                        $nonWorkTypes = ['Off', 'Public Holiday', 'Leave', 'Cuti Melahirkan', 'Sick', 'TOIL Off'];
                        $isNonWork    = $roster && in_array($roster->day_type, $nonWorkTypes);
 
                        if ($isSun) {
                            $bg        = 'FFFFFF00';
                            $fontColor = 'FF000000';
                        } elseif ($isNonWork) {
                            $bg        = 'FFEF4444';
                            $fontColor = 'FFFFFFFF';
                        } elseif (!$roster) {
                            $bg        = 'FFFFFFFF';
                            $fontColor = 'FF000000';
                        } else {
                            $bg        = 'FFFFFFFF';
                            $fontColor = 'FF000000';
                        }
 
                        $sheet->getStyle("{$col}{$rowNum}")->applyFromArray([
                            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bg]],
                            'font'      => ['size' => 10, 'color' => ['argb' => $fontColor]],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        ]);
                    }
 
                    $rowNum++;
                }
 
                // ── Border semua data ──
                $sheet->getStyle("A3:{$lastColLetter}" . ($rowNum - 1))->applyFromArray([
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD1D5DB']],
                    ],
                ]);
 
                // ── Lebar kolom ──
                $sheet->getColumnDimension('A')->setWidth(22);
                $sheet->getColumnDimension('B')->setWidth(18);
                $sheet->getColumnDimension('C')->setWidth(16);
                foreach ($dates as $i => $_) {
                    $sheet->getColumnDimension($this->colLetter($firstDateColIndex + $i))->setWidth(4);
                }
 
                // ── Freeze pane ──
                $sheet->freezePane('D5');
            },
        ];
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
//  SHEET 2 — Tutorial / Legenda
// ═══════════════════════════════════════════════════════════════
class RosterHistoryTutorialSheet implements WithEvents, WithTitle
{
    public function title(): string
    {
        return 'Legenda';
    }
 
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
 
                $sheet->setCellValue('A1', 'LEGENDA KODE ROSTER');
                $sheet->mergeCells('A1:C1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 13, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0F172A']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(30);
 
                $sheet->setCellValue('A2', 'Kode');
                $sheet->setCellValue('B2', 'Keterangan');
                $sheet->setCellValue('C2', 'Warna');
                $sheet->getStyle('A2:C2')->applyFromArray([
                    'font'      => ['bold' => true],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFF00']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
 
                $rows = [
                    ['XX',  'Kode shift (2 huruf, lihat sheet "Shift")',  'Putih',  'FFFFFFFF', 'FF000000'],
                    ['PH',  'Public Holiday',                              'Merah',  'FFEF4444', 'FFFFFFFF'],
                    ['Off', 'Hari libur / day off',                       'Merah',  'FFEF4444', 'FFFFFFFF'],
                    ['C',   'Leave / Cuti',                               'Merah',  'FFEF4444', 'FFFFFFFF'],
                    ['CM',  'Cuti Melahirkan',                            'Merah',  'FFEF4444', 'FFFFFFFF'],
                    ['S',   'Sick / Sakit',                               'Merah',  'FFEF4444', 'FFFFFFFF'],
                    ['TO',  'TOIL Off',                                   'Merah',  'FFEF4444', 'FFFFFFFF'],
                    ['',    'Tidak ada jadwal',                           'Putih',  'FFFFFFFF', 'FF000000'],
                ];
 
                foreach ($rows as $idx => $row) {
                    $r = $idx + 3;
                    $sheet->setCellValue("A{$r}", $row[0]);
                    $sheet->setCellValue("B{$r}", $row[1]);
                    $sheet->setCellValue("C{$r}", $row[2]);
 
                    $sheet->getStyle("A{$r}")->applyFromArray([
                        'font'      => ['bold' => true, 'color' => ['argb' => $row[4]]],
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $row[3]]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);
                    $sheet->getStyle("B{$r}:C{$r}")->applyFromArray([
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $idx % 2 === 0 ? 'FFF8FAFC' : 'FFFFFFFF']],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                    $sheet->getStyle("C{$r}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $row[3]]],
                        'font' => ['color' => ['argb' => $row[4]]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);
                    $sheet->getRowDimension($r)->setRowHeight(22);
                }
 
                $lastRow = count($rows) + 2;
                $sheet->getStyle("A2:C{$lastRow}")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD1D5DB']]],
                ]);
 
                $sheet->getColumnDimension('A')->setWidth(10);
                $sheet->getColumnDimension('B')->setWidth(35);
                $sheet->getColumnDimension('C')->setWidth(12);
            },
        ];
    }
}
 
// ═══════════════════════════════════════════════════════════════
//  SHEET 3 — Daftar Shift
// ═══════════════════════════════════════════════════════════════
class RosterHistoryShiftSheet implements WithEvents, WithTitle
{
    public function __construct(private ?string $storeId) {}
 
    public function title(): string
    {
        return 'Shift';
    }
 
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet     = $event->sheet->getDelegate();
                $storeName = $this->storeId ? (Stores::find($this->storeId)?->name ?? '-') : 'All';
                $query     = Shifts::orderBy('shift_name');
                if ($this->storeId) {
                    $query->where('store_id', $this->storeId);
                }
                $shifts = $query->get(['shift_name', 'code', 'start_time', 'end_time']);
 
                $sheet->setCellValue('A1', "Daftar Shift — {$storeName}");
                $sheet->mergeCells('A1:D1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 13, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0F172A']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(30);
 
                $sheet->setCellValue('A2', 'Nama Shift');
                $sheet->setCellValue('B2', 'Kode');
                $sheet->setCellValue('C2', 'Jam Mulai');
                $sheet->setCellValue('D2', 'Jam Selesai');
                $sheet->getStyle('A2:D2')->applyFromArray([
                    'font'      => ['bold' => true],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFF00']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
 
                if ($shifts->isEmpty()) {
                    $sheet->setCellValue('A3', 'Tidak ada shift terdaftar.');
                    $sheet->mergeCells('A3:D3');
                    $sheet->getStyle('A3')->applyFromArray([
                        'font'      => ['italic' => true, 'color' => ['argb' => 'FF94A3B8']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);
                } else {
                    foreach ($shifts as $idx => $shift) {
                        $r = $idx + 3;
                        $sheet->setCellValue("A{$r}", $shift->shift_name);
                        $sheet->setCellValue("B{$r}", strtoupper($shift->code));
                        $sheet->setCellValue("C{$r}", substr($shift->start_time, 0, 5));
                        $sheet->setCellValue("D{$r}", substr($shift->end_time, 0, 5));
 
                        $sheet->getStyle("A{$r}:D{$r}")->applyFromArray([
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        ]);
                        $sheet->getStyle("B{$r}")->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['argb' => 'FF1E40AF']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFEFF6FF']],
                        ]);
                        if ($idx % 2 === 0) {
                            $sheet->getStyle("A{$r}:D{$r}")->applyFromArray([
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF8FAFC']],
                            ]);
                            $sheet->getStyle("B{$r}")->applyFromArray([
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFEFF6FF']],
                            ]);
                        }
                        $sheet->getRowDimension($r)->setRowHeight(22);
                    }
 
                    $lastRow = $shifts->count() + 2;
                    $sheet->getStyle("A2:D{$lastRow}")->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD1D5DB']]],
                    ]);
                }
 
                $sheet->getColumnDimension('A')->setWidth(20);
                $sheet->getColumnDimension('B')->setWidth(10);
                $sheet->getColumnDimension('C')->setWidth(12);
                $sheet->getColumnDimension('D')->setWidth(12);
            },
        ];
    }
//     public function __construct(
//         protected string $startDate,
//         protected string $endDate,
//         protected ?string $storeId,
//         protected ?string $search,
//         protected bool $canManageAll,
//         protected ?string $myStoreId,
//         protected ?string $storeName = null,
//         protected ?string $employeeIdFilter = null, 
//          protected ?string $myDepartmentId = null, // ← tambah
//     protected ?string $myCompanyId = null,    // ← tambah
//     ) {}

//     public function registerEvents(): array
//     {
//         return [
//             AfterSheet::class => function (AfterSheet $event) {
//                 $sheet = $event->sheet->getDelegate();
               
// $query = Roster::with([
//     'employee:id,employee_name,status_employee,company_id',
//     'employee.department:id,department_name',
//     'employee.position:id,name',
//     'shift:id,shift_name,start_time,end_time',
// ])
// ->whereBetween('date', [$this->startDate, $this->endDate])
// ->whereHas('employee', function ($q) {
//     $q->whereNull('deleted_at')
//     ->where('status', 'Active');

//     if ($this->search) {
//         $q->where('employee_name', 'like', '%' . $this->search . '%');
//     }

//     if ($this->employeeIdFilter) {
//         // ViewRoster: hanya data diri sendiri
//         $q->where('id', $this->employeeIdFilter);

//     } elseif ($this->canManageAll) {
//         // ManageRoster: bebas filter store
//         if ($this->storeId) {
//             $q->whereHas('store', fn($sq) =>
//                 $sq->where('stores_tables.id', $this->storeId)
//             );
//         }

//     } else {
//         // ManageRosterSPVManager: filter store + department + company
//         if ($this->myStoreId) {
//             $q->whereExists(function ($sq) {
//                 $sq->select(DB::raw(1))
//                     ->from('employee_stores')
//                     ->whereColumn('employee_stores.employee_id', 'employees_tables.id')
//                     ->where('employee_stores.store_id', $this->myStoreId);
//             });
//         }

//         if ($this->myDepartmentId) {
//             $q->whereExists(function ($sq) {
//                 $sq->select(DB::raw(1))
//                     ->from('employee_departments')
//                     ->whereColumn('employee_departments.employee_id', 'employees_tables.id')
//                     ->where('employee_departments.department_id', $this->myDepartmentId);
//             });
//         }

//         if ($this->myCompanyId) {
//             $q->where('company_id', $this->myCompanyId);
//         }
//     }
// })
// ->orderBy('employee_id')
// ->orderBy('date')
// ->get();

//                 // ── Generate tanggal ──
//                 $dates   = [];
//                 $current = Carbon::parse($this->startDate);
//                 $end     = Carbon::parse($this->endDate);
//                 while ($current->lte($end)) {
//                     $dates[] = $current->copy();
//                     $current->addDay();
//                 }

//                 $totalCols   = count($dates) + 2; // +2: kolom NAMA + POSISI
// $lastColLetter = $this->colLetter($totalCols);

//                 // ── Baris 1: Kop judul ──
//                 $storeName = $this->storeName ?? 'All Locations';
//                 $sheet->setCellValue('A1', 'Schedule');
//                 $sheet->mergeCells("A1:{$lastColLetter}1");
//                 $sheet->getStyle('A1')->applyFromArray([
//                     'font'      => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FFFFFFFF']],
//                     'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0F172A']],
//                     'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
//                 ]);

//                 // ── Baris 2: Store & Periode ──
//                 $sheet->setCellValue('A2', "Location: {$storeName}     |     Periode: {$this->startDate} s/d {$this->endDate}");
//                 $sheet->mergeCells("A2:{$lastColLetter}2");
//                 $sheet->getStyle('A2')->applyFromArray([
//                     'font'      => ['size' => 10, 'italic' => true, 'color' => ['argb' => 'FF64748B']],
//                     'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF8FAFC']],
//                     'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
//                 ]);

//                 // baris 3
//                 $sheet->setCellValue('A3', 'NAMA');
// $sheet->setCellValue('B3', 'POSISI'); // ← tambah
// foreach ($dates as $i => $date) {
//     $col = $this->colLetter($i + 3); // ← +3 bukan +2
//     $sheet->setCellValue("{$col}3", $date->day);
// }

//                 // // ── Baris 3: NAMA | tanggal ──
//                 // $sheet->setCellValue('A3', 'NAMA');
//                 // foreach ($dates as $i => $date) {
//                 //     $col = $this->colLetter($i + 2);
//                 //     $sheet->setCellValue("{$col}3", $date->day);
//                 // }

//                 // ── Baris 4: HARI | S/M/K/R/J ──
//                $hariMap = [0 => 'M', 1 => 'S', 2 => 'S', 3 => 'R', 4 => 'K', 5 => 'J', 6 => 'S'];
// $sheet->setCellValue('A4', 'HARI');
// $sheet->setCellValue('B4', '');     // ← kosong
// foreach ($dates as $i => $date) {
//     $col = $this->colLetter($i + 3); // ← +3
//     $sheet->setCellValue("{$col}4", $hariMap[$date->dayOfWeek]);
// }

//                 // Style header NAMA & HARI
//               $sheet->getStyle("A3:B4")->applyFromArray([
//     'font' => ['bold' => true, 'color' => ['argb' => 'FF000000']],
//     'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFF00']],
//     'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
// ]);


//                 foreach ($dates as $i => $date) {
//                     $col   = $this->colLetter($i + 3);
//                     $isSun = $date->isSunday();

//                     $bgColor  = $isSun ? 'FFFFFF00' : null;
//                     $txtColor = 'FF000000';

//                     $style = [
//                         'font' => [
//                             'bold'  => true,
//                             'color' => ['argb' => $txtColor]
//                         ],
//                         'alignment' => [
//                             'horizontal' => Alignment::HORIZONTAL_CENTER
//                         ],
//                     ];

//                     if ($isSun) {
//                         $style['fill'] = [
//                             'fillType'   => Fill::FILL_SOLID,
//                             'startColor' => ['argb' => $bgColor],
//                         ];
//                     }

//                     $sheet->getStyle("{$col}3:{$col}4")->applyFromArray($style);
//                 }

//                 // ── Baris 5+: Data employee ──
//                 $grouped = $query->groupBy('employee_id');
//                 $rowNum  = 5;

//                 foreach ($grouped as $empId => $items) {
//                     $emp = $items->first()->employee;

//                     // Map roster by date
//                     $rosterMap = $items->keyBy(fn($r) => Carbon::parse($r->date)->toDateString());

//                     // Kolom A: nama employee
//                     $sheet->setCellValue("A{$rowNum}", $emp->employee_name);
//                     $sheet->getStyle("A{$rowNum}")->applyFromArray([
//                         'font' => ['bold' => true],
//                         'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFF00']],
//                     ]);

//                     // Kolom B: posisi ← tambah
//     $positionName = $emp->position?->first()->name ?? '-';
//     $sheet->setCellValue("B{$rowNum}", $positionName);
//     $sheet->getStyle("B{$rowNum}")->applyFromArray([
//         'font' => ['bold' => false],
//         'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFF00']],
//         'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
//     ]);



//                     // Kolom tanggal
//                     foreach ($dates as $i => $date) {
//                         $col     = $this->colLetter($i + 3);
//                         $dateStr = $date->toDateString();
//                         $roster  = $rosterMap->get($dateStr);
//                         $isSun   = $date->isSunday();

//                         // Nilai cell
//                         $value = '';
//                         if ($roster) {
//                             $value = match ($roster->day_type) {
//                                 'Off'             => 'Off',
//                                 'Public Holiday'  => 'PH',
//                                 'Leave'           => 'C',
//                                 'Cuti Melahirkan' => 'CM',
//                                 'Work'            => $roster->shift?->shift_name
//                                     ? strtoupper(substr($roster->shift->shift_name, 0, 1))
//                                     : 'M',
//                                 default           => '',
//                             };
//                         }

//                         $sheet->setCellValue("{$col}{$rowNum}", $value);

//                         // ── Background logic ──
//                         $nonWorkTypes = ['Off', 'Public Holiday', 'Leave', 'Cuti Melahirkan'];
//                         $isNonWork    = $roster && in_array($roster->day_type, $nonWorkTypes);

//                         if ($isSun) {
//                             // Minggu tetap kuning, apapun day_type-nya
//                             $bg        = 'FFFFFF00';
//                             $fontColor = 'FF000000';
//                         } elseif ($isNonWork) {
//                             // Off, PH, Leave, CM di hari biasa = merah
//                             $bg        = 'FFEF4444';
//                             $fontColor = 'FFFFFFFF'; // teks putih agar kontras
//                         } elseif (!$roster) {
//                             // Kosong = hitam
//                             $bg        = 'FFFFFFFF';
//                             $fontColor = 'FF000000';
//                             // $bg        = 'FF000000';
//                             // $fontColor = 'FF000000';
//                         } else {
//                             // Work = putih
//                             $bg        = 'FFFFFFFF';
//                             $fontColor = 'FF000000';
//                         }

//                         $sheet->getStyle("{$col}{$rowNum}")->applyFromArray([
//                             'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bg]],
//                             'font'      => ['size' => 10, 'color' => ['argb' => $fontColor]],
//                             'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
//                         ]);
//                     }

//                     $rowNum++;
//                 }

//                 // ── Border semua data ──
//                 $dataRange = "A3:{$lastColLetter}" . ($rowNum - 1);
//                 $sheet->getStyle($dataRange)->applyFromArray([
//                     'borders' => [
//                         'allBorders' => [
//                             'borderStyle' => Border::BORDER_THIN,
//                             'color'       => ['argb' => 'FFD1D5DB'],
//                         ],
//                     ],
//                 ]);

//                 // ── Lebar kolom ──
//                 $sheet->getColumnDimension('A')->setWidth(22);
//                 $sheet->getColumnDimension('B')->setWidth(18); // ← tambah

//                 foreach ($dates as $i => $_) {
//                     $sheet->getColumnDimension($this->colLetter($i + 3))->setWidth(4);
//                 }

//                 // ── Freeze pane ──
//                 $sheet->freezePane('C5');
//             },
//         ];
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
}
// {
//     public function __construct(
//         protected string $startDate,
//         protected string $endDate,
//         protected ?string $storeId,
//         protected ?string $search,
//         protected bool $canManageAll,
//         protected ?string $myStoreId,
//         protected ?string $storeName = null,
//         protected ?string $employeeIdFilter = null, 
//          protected ?string $myDepartmentId = null, // ← tambah
//     protected ?string $myCompanyId = null,    // ← tambah
//     ) {}

//     public function registerEvents(): array
//     {
//         return [
//             AfterSheet::class => function (AfterSheet $event) {
//                 $sheet = $event->sheet->getDelegate();
               
// $query = Roster::with([
//     'employee:id,employee_name,status_employee,company_id',
//     'employee.department:id,department_name',
//     'employee.position:id,name',
//     'shift:id,shift_name,start_time,end_time',
// ])
// ->whereBetween('date', [$this->startDate, $this->endDate])
// ->whereHas('employee', function ($q) {
//     $q->whereNull('deleted_at')
//     ->where('status', 'Active');

//     if ($this->search) {
//         $q->where('employee_name', 'like', '%' . $this->search . '%');
//     }

//     if ($this->employeeIdFilter) {
//         // ViewRoster: hanya data diri sendiri
//         $q->where('id', $this->employeeIdFilter);

//     } elseif ($this->canManageAll) {
//         // ManageRoster: bebas filter store
//         if ($this->storeId) {
//             $q->whereHas('store', fn($sq) =>
//                 $sq->where('stores_tables.id', $this->storeId)
//             );
//         }

//     } else {
//         // ManageRosterSPVManager: filter store + department + company
//         if ($this->myStoreId) {
//             $q->whereExists(function ($sq) {
//                 $sq->select(DB::raw(1))
//                     ->from('employee_stores')
//                     ->whereColumn('employee_stores.employee_id', 'employees_tables.id')
//                     ->where('employee_stores.store_id', $this->myStoreId);
//             });
//         }

//         if ($this->myDepartmentId) {
//             $q->whereExists(function ($sq) {
//                 $sq->select(DB::raw(1))
//                     ->from('employee_departments')
//                     ->whereColumn('employee_departments.employee_id', 'employees_tables.id')
//                     ->where('employee_departments.department_id', $this->myDepartmentId);
//             });
//         }

//         if ($this->myCompanyId) {
//             $q->where('company_id', $this->myCompanyId);
//         }
//     }
// })
// ->orderBy('employee_id')
// ->orderBy('date')
// ->get();

//                 // ── Generate tanggal ──
//                 $dates   = [];
//                 $current = Carbon::parse($this->startDate);
//                 $end     = Carbon::parse($this->endDate);
//                 while ($current->lte($end)) {
//                     $dates[] = $current->copy();
//                     $current->addDay();
//                 }

//                 $totalCols   = count($dates) + 1; // +1 kolom NAMA
//                 $lastColLetter = $this->colLetter($totalCols);

//                 // ── Baris 1: Kop judul ──
//                 $storeName = $this->storeName ?? 'All Locations';
//                 $sheet->setCellValue('A1', 'Schedule');
//                 $sheet->mergeCells("A1:{$lastColLetter}1");
//                 $sheet->getStyle('A1')->applyFromArray([
//                     'font'      => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FFFFFFFF']],
//                     'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0F172A']],
//                     'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
//                 ]);

//                 // ── Baris 2: Store & Periode ──
//                 $sheet->setCellValue('A2', "Location: {$storeName}     |     Periode: {$this->startDate} s/d {$this->endDate}");
//                 $sheet->mergeCells("A2:{$lastColLetter}2");
//                 $sheet->getStyle('A2')->applyFromArray([
//                     'font'      => ['size' => 10, 'italic' => true, 'color' => ['argb' => 'FF64748B']],
//                     'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF8FAFC']],
//                     'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
//                 ]);

//                 // ── Baris 3: NAMA | tanggal ──
//                 $sheet->setCellValue('A3', 'NAMA');
//                 foreach ($dates as $i => $date) {
//                     $col = $this->colLetter($i + 2);
//                     $sheet->setCellValue("{$col}3", $date->day);
//                 }

//                 // ── Baris 4: HARI | S/M/K/R/J ──
//                 $hariMap = [0 => 'M', 1 => 'S', 2 => 'S', 3 => 'R', 4 => 'K', 5 => 'J', 6 => 'S'];
//                 $sheet->setCellValue('A4', 'HARI');
//                 foreach ($dates as $i => $date) {
//                     $col = $this->colLetter($i + 2);
//                     $sheet->setCellValue("{$col}4", $hariMap[$date->dayOfWeek]);
//                 }

//                 // Style header NAMA & HARI
//                 $sheet->getStyle("A3:A4")->applyFromArray([
//                     'font' => ['bold' => true, 'color' => ['argb' => 'FF000000']],
//                     'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFF00']],
//                     'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
//                 ]);


//                 foreach ($dates as $i => $date) {
//                     $col   = $this->colLetter($i + 2);
//                     $isSun = $date->isSunday();

//                     $bgColor  = $isSun ? 'FFFFFF00' : null;
//                     $txtColor = 'FF000000';

//                     $style = [
//                         'font' => [
//                             'bold'  => true,
//                             'color' => ['argb' => $txtColor]
//                         ],
//                         'alignment' => [
//                             'horizontal' => Alignment::HORIZONTAL_CENTER
//                         ],
//                     ];

//                     if ($isSun) {
//                         $style['fill'] = [
//                             'fillType'   => Fill::FILL_SOLID,
//                             'startColor' => ['argb' => $bgColor],
//                         ];
//                     }

//                     $sheet->getStyle("{$col}3:{$col}4")->applyFromArray($style);
//                 }

//                 // ── Baris 5+: Data employee ──
//                 $grouped = $query->groupBy('employee_id');
//                 $rowNum  = 5;

//                 foreach ($grouped as $empId => $items) {
//                     $emp = $items->first()->employee;

//                     // Map roster by date
//                     $rosterMap = $items->keyBy(fn($r) => Carbon::parse($r->date)->toDateString());

//                     // Kolom A: nama employee
//                     $sheet->setCellValue("A{$rowNum}", $emp->employee_name);
//                     $sheet->getStyle("A{$rowNum}")->applyFromArray([
//                         'font' => ['bold' => true],
//                         'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFF00']],
//                     ]);


//                     // Kolom tanggal
//                     foreach ($dates as $i => $date) {
//                         $col     = $this->colLetter($i + 2);
//                         $dateStr = $date->toDateString();
//                         $roster  = $rosterMap->get($dateStr);
//                         $isSun   = $date->isSunday();

//                         // Nilai cell
//                         $value = '';
//                         if ($roster) {
//                             $value = match ($roster->day_type) {
//                                 'Off'             => 'Off',
//                                 'Public Holiday'  => 'PH',
//                                 'Leave'           => 'C',
//                                 'Cuti Melahirkan' => 'CM',
//                                 'Work'            => $roster->shift?->shift_name
//                                     ? strtoupper(substr($roster->shift->shift_name, 0, 1))
//                                     : 'M',
//                                 default           => '',
//                             };
//                         }

//                         $sheet->setCellValue("{$col}{$rowNum}", $value);

//                         // ── Background logic ──
//                         $nonWorkTypes = ['Off', 'Public Holiday', 'Leave', 'Cuti Melahirkan'];
//                         $isNonWork    = $roster && in_array($roster->day_type, $nonWorkTypes);

//                         if ($isSun) {
//                             // Minggu tetap kuning, apapun day_type-nya
//                             $bg        = 'FFFFFF00';
//                             $fontColor = 'FF000000';
//                         } elseif ($isNonWork) {
//                             // Off, PH, Leave, CM di hari biasa = merah
//                             $bg        = 'FFEF4444';
//                             $fontColor = 'FFFFFFFF'; // teks putih agar kontras
//                         } elseif (!$roster) {
//                             // Kosong = hitam
//                             $bg        = 'FF000000';
//                             $fontColor = 'FF000000';
//                         } else {
//                             // Work = putih
//                             $bg        = 'FFFFFFFF';
//                             $fontColor = 'FF000000';
//                         }

//                         $sheet->getStyle("{$col}{$rowNum}")->applyFromArray([
//                             'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bg]],
//                             'font'      => ['size' => 10, 'color' => ['argb' => $fontColor]],
//                             'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
//                         ]);
//                     }

//                     $rowNum++;
//                 }

//                 // ── Border semua data ──
//                 $dataRange = "A3:{$lastColLetter}" . ($rowNum - 1);
//                 $sheet->getStyle($dataRange)->applyFromArray([
//                     'borders' => [
//                         'allBorders' => [
//                             'borderStyle' => Border::BORDER_THIN,
//                             'color'       => ['argb' => 'FFD1D5DB'],
//                         ],
//                     ],
//                 ]);

//                 // ── Lebar kolom ──
//                 $sheet->getColumnDimension('A')->setWidth(22);
//                 foreach ($dates as $i => $_) {
//                     $sheet->getColumnDimension($this->colLetter($i + 2))->setWidth(4);
//                 }

//                 // ── Freeze pane ──
//                 $sheet->freezePane('B5');
//             },
//         ];
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
