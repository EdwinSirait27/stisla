<?php

namespace App\Http\Controllers;

use App\Models\Fingerprints;
use App\Models\EditedFingerprint;
use App\Models\Employee;
use App\Models\Stores;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class FingerprintsController extends Controller
{
    public function index()
    {
        $stores = Stores::select('id', 'name')
            ->whereNotNull('name')
            ->distinct()
            ->pluck('name');
        return view('pages.Fingerprints.Fingerprints', compact('stores'));
    }

public function getFingerprints(Request $request)
{
    ini_set('memory_limit', '1024M');

    $storeName = $request->input('store_name');
    $startDate = Carbon::parse($request->input('start_date', now()->startOfMonth()))->startOfDay();
    $endDate = Carbon::parse($request->input('end_date', now()))->endOfDay();

    // Data edited
    $editedKeys = EditedFingerprint::pluck('scan_date', 'pin')
        ->map(fn($date, $pin) => $pin . '_' . Carbon::parse($date)->toDateString())
        ->values()
        ->toArray();

    // Ambil data Employees
    $employeesQuery = Employee::with(['position:id,name', 'store:id,name'])
        ->select('pin', 'employee_name', 'employee_pengenal', 'position_id', 'store_id', 'status_employee');

    if ($storeName) {
        $employeesQuery->whereHas('store', fn($q) => $q->where('name', $storeName));
    }

    $employees = $employeesQuery->get()->keyBy('pin');

    // Ambil fingerprint
    $fingerprints = Fingerprints::with('devicefingerprints:device_name,sn')
        ->select(['sn', 'scan_date', 'pin', 'inoutmode'])
        ->whereBetween('scan_date', [$startDate, $endDate])
        ->orderBy('scan_date')
        ->get();

    // Hitung total hari aktif
    $totalHariPerPin = $fingerprints
        ->groupBy('pin')
        ->map(fn($items) =>
            $items->pluck('scan_date')
                ->map(fn($d) => Carbon::parse($d)->toDateString())
                ->unique()
                ->count()
        );

    $grouped = $fingerprints
    ->groupBy(fn($f) => $f->pin . '_' . Carbon::parse($f->scan_date)->toDateString());


    $result = $grouped->map(function ($group, $key) use ($employees, $totalHariPerPin, $editedKeys) {

        $first = $group->first();
        $pin = $first->pin;
        $scanDate = Carbon::parse($first->scan_date)->toDateString();
        $employee = $employees->get($pin);

        if (!$employee) return null;

        $row = [
            'pin'               => $pin,
            'employee_name'     => $employee->employee_name ?? '-',
            'status_employee'   => $employee->status_employee ?? '-',
            'employee_pengenal' => $employee->employee_pengenal ?? '-',
            'name'              => $employee->store->name ?? '-',
            'position_name'     => $employee->position->name ?? '-',
            'device_name'       => optional($first->devicefingerprints)->device_name ?? '-',
            'scan_date'         => $scanDate,
            'total_hari'        => $totalHariPerPin[$pin] ?? 0,
        ];

        for ($i = 1; $i <= 10; $i++) {
            $row["in_$i"] = $row["device_$i"] = $row["combine_$i"] = null;
        }

        $group->groupBy('inoutmode')->each(function ($items, $mode) use (&$row) {
            if ($mode < 1 || $mode > 10) return;

            $sorted = $items->sortBy('scan_date');

            $times = $sorted->pluck('scan_date')
                ->map(fn($d) => Carbon::parse($d)->format('H:i:s'))
                ->implode(', ');

            $devices = $sorted
                ->map(fn($i) => optional($i->devicefingerprints)->device_name ?? '')
                ->implode(', ');

            $row["in_$mode"]      = $times;
            $row["device_$mode"]  = $devices;
            $row["combine_$mode"] = trim($times . ' ' . $devices);
        });

        $times = collect(range(1, 10))
            ->flatMap(function ($i) use ($row) {
                if (!$row["in_$i"]) return [];
                return explode(', ', $row["in_$i"]);
            })
            ->map(fn($t) => Carbon::parse($t))
            ->sort()
            ->values();

        if ($times->count() >= 2) {
            $start = $times->first();
            $end   = $times->last();
            $minutes = $start->diffInMinutes($end);

            $row['duration'] = sprintf(
                '%d hour%s %d minute%s',
                intdiv($minutes, 60),
                intdiv($minutes, 60) !== 1 ? 's' : '',
                $minutes % 60,
                $minutes % 60 !== 1 ? 's' : ''
            );
        } else {
            $row['duration'] = 'invalid';
        }

        $row['is_updated']     = in_array($key, $editedKeys);
        $row['updated_status'] = $row['is_updated'] ? 'Updated' : ' Original';

        return $row;
    })->filter()->values();

    // Return Datatables
    return DataTables::of($result)
        ->addColumn('action', function ($row) {
            if ($row['is_updated']) {
                return '<button class="btn btn-sm btn-secondary" disabled><i class="fas fa-edit"></i></button>';
            }
            $editUrl = route('pages.Fingerprints.edit', [
                'pin' => $row['pin'],
                'scan_date' => $row['scan_date'],
            ]);
            return '<a href="' . $editUrl . '" class="btn btn-sm btn-primary me-1">
                        <i class="fas fa-edit"></i>
                    </a>';
        })
        ->rawColumns(['action'])
        ->make(true);
}
    public function editFingerprint($pin, Request $request)
    {
        $scanDate = $request->input('scan_date');
        if (!$scanDate) {
            return response()->json(['message' => 'scan_date is required'], 400);
        }
        $scanDateCarbon = Carbon::parse($scanDate)->toDateString();
        Log::info('=== [EDIT FINGERPRINT] Akses halaman edit ===', [
            'pin' => $pin,
            'scan_date_input' => $scanDate,
            'scan_date_parsed' => $scanDateCarbon,
        ]);
        // 1️⃣ Coba cari di EditedFingerprint dulu
        $data = EditedFingerprint::with('devicefingerprints')
            ->where('pin', $pin)
            ->whereDate('scan_date', $scanDateCarbon)
            ->first();
        if ($data) {
            Log::info('Data ditemukan di EditedFingerprint', [
                'pin' => $pin,
                'scan_date' => $scanDateCarbon,
                'in_1' => $data->in_1,
                'in_2' => $data->in_2,
                'in_3' => $data->in_3,
                'in_4' => $data->in_4,
            ]);
            return view('pages.Fingerprints.edit', ['data' => $data, 'isEdited' => true]);
        }
        // 2️⃣ Kalau tidak ada, ambil semua fingerprint mentah untuk tanggal itu
        $fingerprints = Fingerprints::with('devicefingerprints')
            ->where('pin', $pin)
            ->whereDate('scan_date', $scanDateCarbon)
            ->orderBy('scan_date')
            ->get();

        Log::info('Data mentah fingerprint diambil', [
            'total' => $fingerprints->count(),
            'contoh_pertama' => $fingerprints->first()?->scan_date,
        ]);

        if ($fingerprints->isEmpty()) {
            Log::warning('Fingerprint kosong untuk pin & tanggal ini', compact('pin', 'scanDateCarbon'));
            return response()->json(['message' => 'Data not found'], 404);
        }

        // 3️⃣ Siapkan format data seperti di getFingerprints
        $first = $fingerprints->first();
        $employee = Employee::with(['store:id,name', 'position:id,name'])
            ->where('pin', $pin)
            ->first();

        $row = [
            'pin' => $pin,
            'employee_name' => $employee->employee_name ?? '-',
            'status_employee' => $employee->status_employee ?? '-',
            'employee_pengenal' => $employee->employee_pengenal ?? '-',
            'name' => $employee->store->name ?? '-',
            'position_name' => optional($employee->position)->name ?? '-',
            'device_name' => optional($first->devicefingerprints)->device_name ?? '-',
            'scan_date' => $scanDateCarbon,
        ];

        foreach (range(1, 10) as $i) {
            $row["in_$i"] = $row["device_$i"] = $row["combine_$i"] = null;
        }

        $fingerprints->groupBy('inoutmode')->each(function ($items, $mode) use (&$row) {
            if ($mode >= 1 && $mode <= 10) {
                $firstItem = $items->sortBy('scan_date')->first();

                $formatted = null;
                try {
                    $formatted = Carbon::parse($firstItem->scan_date)->format('H:i:s');
                } catch (\Exception $e) {
                    Log::error('Gagal parsing waktu', [
                        'mode' => $mode,
                        'raw_scan_date' => $firstItem->scan_date,
                        'error' => $e->getMessage(),
                    ]);
                }

                $deviceName = optional($firstItem->devicefingerprints)->device_name ?? '';

                $row["in_$mode"] = $formatted;
                $row["device_$mode"] = $deviceName;
                $row["combine_$mode"] = "{$formatted} {$deviceName}";

                Log::info('Fingerprint mode', [
                    'mode' => $mode,
                    'raw' => $firstItem->scan_date,
                    'formatted' => $formatted,
                    'device' => $deviceName,
                ]);
            }
        });

        Log::info('=== [HASIL AKHIR DATA EDIT] ===', [
            'pin' => $pin,
            'scan_date' => $scanDateCarbon,
            'in_1' => $row['in_1'],
            'in_2' => $row['in_2'],
            'in_3' => $row['in_3'],
            'in_4' => $row['in_4'],
        ]);

        return view('pages.Fingerprints.edit', [
            'data' => (object) $row,
            'isEdited' => false,
        ]);
    }
    public function updateFingerprint(Request $request)
    {
        //  dd($request->all());
        try {
            Log::info('Mulai updateFingerprint', $request->all());

            $validated = $request->validate([
                'pin' => 'required|string',
                'scan_date' => 'required|date',
                'employee_name' => 'nullable|string',
                'position_name' => 'nullable|string',
                'store_name' => 'nullable|string',
                'duration' => 'nullable|string',
                'attachment' => 'required|file|mimes:jpg,jpeg,png,pdf|max:512',
                ...collect(range(1, 10))->flatMap(function ($i) {
                    return [
                        "in_$i" => 'nullable|string',
                        "device_$i" => 'nullable|string',
                    ];
                })->toArray()
            ]);

            Log::info('Validasi berhasil', $validated);

            // Simpan file attachment jika ada
            $filename = null;
            if ($request->hasFile('attachment')) {
                try {
                    $filename = $request->file('attachment')->store('attachment', 'public');
                    Log::info('File berhasil diupload: ' . $filename);
                } catch (\Exception $fileException) {
                    Log::error('Gagal upload file attachment', [
                        'error' => $fileException->getMessage(),
                    ]);
                    // lanjut tanpa attachment
                }
            }

            $data = EditedFingerprint::updateOrCreate(
                [
                    'pin' => $validated['pin'],
                    'scan_date' => $validated['scan_date'],
                ],
                collect($validated)
                    ->except(['pin', 'scan_date'])
                    ->merge([
                        'attachment' => $filename,
                    ])
                    ->toArray()
            );

            Log::info('Data berhasil disimpan', $data->toArray());

            return redirect()
                ->route('pages.Fingerprints')
                ->with('success', 'Fingerprint berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Gagal updateFingerprint', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Terjadi kesalahan saat menyimpan data.');
        }
    }
    public function getTotalHariBekerja(Request $request)
    {
        $pin = $request->input('pin');
        // $startDate = $request->input('start_date', '2025-07-01');
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());

        $endDate = $request->input('end_date', now()->toDateString());

        $total = Fingerprints::where('pin', $pin)
            ->whereBetween('scan_date', [$startDate, $endDate])
            ->get()
            ->groupBy(function ($item) {
                return Carbon::parse($item->scan_date)->toDateString();
            })
            ->count();

        return response()->json(['total' => $total]);
    }
}
