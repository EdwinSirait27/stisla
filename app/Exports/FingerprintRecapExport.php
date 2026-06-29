<?php

namespace App\Exports;

use App\Models\Employee;
use App\Models\Fingerprintrecaparchive;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class FingerprintRecapExport implements  FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithTitle,
    ShouldAutoSize
{
    protected string $startDate;
    protected string $endDate;
    protected ?string $storeName;
    protected ?string $statusName;

    public function __construct(string $startDate, string $endDate, ?string $storeName = null, ?string $statusName = null)
    {
        $this->startDate = $startDate;
        $this->endDate   = $endDate;
        $this->storeName = $storeName;
        $this->statusName = $statusName;
    }

    public function collection()
    {
        $employeesQuery = Employee::with([
            'store' => fn($q) => $q->wherePivot('is_primary', true),
        ])
            ->select('id', 'employee_name', 'employee_pengenal','status_employee','status')
            ->whereNotNull('pin')
            // ->whereIn('status', ['Active', 'Mutation', 'Pending', 'On Leave','Resign'])
            ->whereIn('status', ['Active', 'Inactive', 'Mutation', 'Pending', 'On Leave'])
            ->whereNull('deleted_at');
            if ($this->storeName) {
    $employeesQuery->whereHas('store', fn($q) =>
        $q->where('stores_tables.name', $this->storeName)
    );
}

// ← tambah filter status
if ($this->statusName) {
    $employeesQuery->where('status', $this->statusName);
}

        if ($this->storeName) {
            $employeesQuery->whereHas('store', fn($q) => $q->where('stores_tables.name', $this->storeName));
        }

        $employees = $employeesQuery->get();

        $archives = Fingerprintrecaparchive::where('period_start', $this->startDate)
            ->where('period_end', $this->endDate)
            ->get()
            ->keyBy('employee_id');

        $periodIn  = Carbon::parse($this->startDate)->format('d-m-Y');
        $periodOut = Carbon::parse($this->endDate)->format('d-m-Y');

        return $employees->map(function ($employee) use ($archives, $periodIn, $periodOut) {
            $archive = $archives->get($employee->id);

            return (object) [
                'employee_name'    => $employee->employee_name ?? '-',
                'employee_pengenal'    => $employee->employee_pengenal ?? '-',
                'store_name'       => $employee->store->first()?->name ?? '-',
                'status_name'           => $employee->status ?? '-', // ← tambah
                'total_hari'       => $archive->total_hari_kerja ?? 0,
                'total_hari_telat' => $archive->total_hari_telat ?? 0,
                'remarks'          => $archive->remarks ?? '-',
                'period_in'        => $periodIn,
                'period_out'       => $periodOut,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Karyawan',
            'NIP',
            'Location',
            'Status',
            'Total Working Days',
            'Total Days Late',
            'Remarks',
            'Periode Start',
            'Periode End',
        ];
    }

    // $rowNumber dimulai dari 2 (baris 1 = heading)
    public function map($row): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $row->employee_name,
            $row->employee_pengenal,
            $row->store_name,
            $row->status_name,
            $row->total_hari . ' days',
            $row->total_hari_telat . ' days',
            $row->remarks,
            $row->period_in,
            $row->period_out,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $sheet->getHighestRow();
        $lastCol = $sheet->getHighestColumn();

        // Border seluruh tabel
        $sheet->getStyle("A1:{$lastCol}{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        // Center kolom No, Total Hari, Total Telat, Periode
       
        // Center kolom No, Total Hari, Total Telat, Periode
// $sheet->getStyle("A2:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
// $sheet->getStyle("E2:F{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // E=Total Working Days, F=Total Days Late
// $sheet->getStyle("H2:I{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // H=Periode Start, I=Periode End
$sheet->getStyle("A2:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("F2:G{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // E=Total Working Days, F=Total Days Late
$sheet->getStyle("I2:J{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // H=Periode Start, I=Periode End

        return [
            // Header styling
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1F4E79'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Fingerprint Recap';
    }
}