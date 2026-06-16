<?php
namespace App\Exports;

use App\Models\Roster;
use App\Models\Stores;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\DB;

use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class RosterHistoryExport implements WithEvents, ShouldAutoSize
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
         protected ?string $myDepartmentId = null, // ← tambah
    protected ?string $myCompanyId = null,    // ← tambah
    ) {}

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
               
$query = Roster::with([
    'employee:id,employee_name,status_employee,company_id',
    'employee.department:id,department_name',
    'employee.position:id,name',
    'shift:id,shift_name,start_time,end_time',
])
->whereBetween('date', [$this->startDate, $this->endDate])
->whereHas('employee', function ($q) {
    $q->whereNull('deleted_at')
    ->where('status', 'Active');

    if ($this->search) {
        $q->where('employee_name', 'like', '%' . $this->search . '%');
    }

    if ($this->employeeIdFilter) {
        // ViewRoster: hanya data diri sendiri
        $q->where('id', $this->employeeIdFilter);

    } elseif ($this->canManageAll) {
        // ManageRoster: bebas filter store
        if ($this->storeId) {
            $q->whereHas('store', fn($sq) =>
                $sq->where('stores_tables.id', $this->storeId)
            );
        }

    } else {
        // ManageRosterSPVManager: filter store + department + company
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

                $totalCols   = count($dates) + 1; // +1 kolom NAMA
                $lastColLetter = $this->colLetter($totalCols);

                // ── Baris 1: Kop judul ──
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

                // ── Baris 3: NAMA | tanggal ──
                $sheet->setCellValue('A3', 'NAMA');
                foreach ($dates as $i => $date) {
                    $col = $this->colLetter($i + 2);
                    $sheet->setCellValue("{$col}3", $date->day);
                }

                // ── Baris 4: HARI | S/M/K/R/J ──
                $hariMap = [0 => 'M', 1 => 'S', 2 => 'S', 3 => 'R', 4 => 'K', 5 => 'J', 6 => 'S'];
                $sheet->setCellValue('A4', 'HARI');
                foreach ($dates as $i => $date) {
                    $col = $this->colLetter($i + 2);
                    $sheet->setCellValue("{$col}4", $hariMap[$date->dayOfWeek]);
                }

                // Style header NAMA & HARI
                $sheet->getStyle("A3:A4")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FF000000']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFF00']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);


                foreach ($dates as $i => $date) {
                    $col   = $this->colLetter($i + 2);
                    $isSun = $date->isSunday();

                    $bgColor  = $isSun ? 'FFFFFF00' : null;
                    $txtColor = 'FF000000';

                    $style = [
                        'font' => [
                            'bold'  => true,
                            'color' => ['argb' => $txtColor]
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER
                        ],
                    ];

                    if ($isSun) {
                        $style['fill'] = [
                            'fillType'   => Fill::FILL_SOLID,
                            'startColor' => ['argb' => $bgColor],
                        ];
                    }

                    $sheet->getStyle("{$col}3:{$col}4")->applyFromArray($style);
                }

                // ── Baris 5+: Data employee ──
                $grouped = $query->groupBy('employee_id');
                $rowNum  = 5;

                foreach ($grouped as $empId => $items) {
                    $emp = $items->first()->employee;

                    // Map roster by date
                    $rosterMap = $items->keyBy(fn($r) => Carbon::parse($r->date)->toDateString());

                    // Kolom A: nama employee
                    $sheet->setCellValue("A{$rowNum}", $emp->employee_name);
                    $sheet->getStyle("A{$rowNum}")->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFF00']],
                    ]);


                    // Kolom tanggal
                    foreach ($dates as $i => $date) {
                        $col     = $this->colLetter($i + 2);
                        $dateStr = $date->toDateString();
                        $roster  = $rosterMap->get($dateStr);
                        $isSun   = $date->isSunday();

                        // Nilai cell
                        $value = '';
                        if ($roster) {
                            $value = match ($roster->day_type) {
                                'Off'             => 'Off',
                                'Public Holiday'  => 'PH',
                                'Leave'           => 'C',
                                'Cuti Melahirkan' => 'CM',
                                'Work'            => $roster->shift?->shift_name
                                    ? strtoupper(substr($roster->shift->shift_name, 0, 1))
                                    : 'M',
                                default           => '',
                            };
                        }

                        $sheet->setCellValue("{$col}{$rowNum}", $value);

                        // ── Background logic ──
                        $nonWorkTypes = ['Off', 'Public Holiday', 'Leave', 'Cuti Melahirkan'];
                        $isNonWork    = $roster && in_array($roster->day_type, $nonWorkTypes);

                        if ($isSun) {
                            // Minggu tetap kuning, apapun day_type-nya
                            $bg        = 'FFFFFF00';
                            $fontColor = 'FF000000';
                        } elseif ($isNonWork) {
                            // Off, PH, Leave, CM di hari biasa = merah
                            $bg        = 'FFEF4444';
                            $fontColor = 'FFFFFFFF'; // teks putih agar kontras
                        } elseif (!$roster) {
                            // Kosong = hitam
                            $bg        = 'FF000000';
                            $fontColor = 'FF000000';
                        } else {
                            // Work = putih
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
                $sheet->getColumnDimension('A')->setWidth(22);
                foreach ($dates as $i => $_) {
                    $sheet->getColumnDimension($this->colLetter($i + 2))->setWidth(4);
                }

                // ── Freeze pane ──
                $sheet->freezePane('B5');
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
