<?php

namespace App\Http\Controllers;

use App\Models\Fingerprints;
use App\Models\EditedFingerprint;
use App\Models\Employee;
use App\Models\Stores;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
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
       
        $startDate = Carbon::parse($request->input('start_date', now()->startOfMonth()->toDateString()))
            ->startOfDay();

        $endDate = Carbon::parse($request->input('end_date', now()->toDateString()))
            ->endOfDay();


        $edited = EditedFingerprint::select('pin', 'scan_date')->get()
            ->map(fn($item) => $item->pin . '_' . Carbon::parse($item->scan_date)->toDateString())
            ->toArray();

        $employeesQuery = Employee::with('position', 'store')
            ->select('pin', 'employee_name', 'employee_pengenal', 'position_id', 'store_id');

        if ($storeName) {
            $employeesQuery->whereHas('store', function ($q) use ($storeName) {
                $q->where('name', $storeName);
            });
        }

        $employees = $employeesQuery->get()->keyBy('pin');

        $fingerprints = Fingerprints::with('devicefingerprints')
            ->select(['sn', 'scan_date', 'pin', 'inoutmode'])
            ->whereBetween('scan_date', [$startDate, $endDate])
            ->orderBy('scan_date')
            ->get();

        $grouped = $fingerprints->groupBy(function ($item) {
            return $item->pin . '_' . Carbon::parse($item->scan_date)->toDateString();
        });

        $result = [];
        $totalHariPerPin = $fingerprints
            ->groupBy(function ($item) {
                return $item->pin . '_' . Carbon::parse($item->scan_date)->toDateString();
            })
            ->map(function ($items) {
                $first = $items->first();
                return [
                    'pin' => $first->pin,
                    'date' => Carbon::parse($first->scan_date)->toDateString()
                ];
            })
            ->groupBy('pin')
            ->map(fn($items) => collect($items)->pluck('date')->unique()->count());

        foreach ($grouped as $group) {
            $first = $group->first();
            $pin = $first->pin;
            $scanDate = Carbon::parse($first->scan_date)->toDateString();
            $employee = $employees->get($pin);
            if (!$employee) {
                continue;
            }
            $totalHari = $totalHariPerPin[$pin] ?? 0;
            $row = [
                'pin' => $pin,
                'employee_name' => $employee->employee_name ?? 'No Data',
                'employee_pengenal' => $employee->employee_pengenal ?? 'No Data',
                'name' => $employee->store->name ?? 'No Data',
                'position_name' => $employee ? optional($employee->position)->name : '-',
                'device_name' => optional($first->devicefingerprints)->device_name ?? '-',
                'scan_date' => $scanDate,
            ];
            for ($i = 1; $i <= 10; $i++) {
                $row['in_' . $i] = null;
            }
            $byMode = $group->groupBy('inoutmode');
            foreach ($byMode as $mode => $items) {
                if ($mode >= 1 && $mode <= 10) {
                    $earliest = $items->sortBy('scan_date')->first();
                    $row['in_' . $mode] = $earliest && $earliest->scan_date
                        ? Carbon::parse($earliest->scan_date)->format('H:i:s')
                        : '';
                    $row['device_' . $mode] = $earliest && $earliest->devicefingerprints
                        ? $earliest->devicefingerprints->device_name
                        : '';
                }
            }

            for ($i = 1; $i <= 10; $i++) {
                $jam = $row['in_' . $i] ?? '';
                $device = $row['device_' . $i] ?? '';
                $row['combine_' . $i] = $jam . ' ' . $device;
            }
            $scanTimes = collect(range(1, 10))
                ->map(fn($i) => $row['in_' . $i])
                ->filter()
                ->sort()
                ->values();
            if ($scanTimes->count() >= 2) {
                $start = Carbon::parse($scanTimes->first());
                $end = Carbon::parse($scanTimes->last());
                $diffInMinutes = $start->diffInMinutes($end);
                $hours = floor($diffInMinutes / 60);
                $minutes = $diffInMinutes % 60;

                $row['duration'] = ($hours > 0 ? $hours . ' hour' . ($hours > 1 ? 's' : '') : '') .
                    ($minutes > 0 ? ' ' . $minutes . ' minute' . ($minutes > 1 ? 's' : '') : '');
                $row['duration'] = trim($row['duration']) ?: '0 minutes';
            } else {
                $row['duration'] = 'invalid';
            }

            // ✅ Penanda apakah data sudah diedit
            $isUpdated = in_array($pin . '_' . $scanDate, $edited);
            $row['updated'] = $isUpdated ? '✔️ Updated' : '❌ Original';
            $row['is_updated'] = $isUpdated;

            $result[] = $row;
        }

        $result = collect($result)->sortBy('scan_date')->values();

        return DataTables::of($result)


            // ->addColumn('action', function ($row) {
            //     $editBtn = '';
            //     if ($row['is_updated']) {
            //         $editBtn = '<button class="btn btn-sm btn-secondary" disabled>Edited</button>';
            //     } else {
            //         $editUrl = route('pages.Fingerprints.edit', [
            //             'pin' => $row['pin'],
            //             // 'employee_name' => $row['employee_name'],
            //             'scan_date' => $row['scan_date'],
            //         ]);
            //         $editBtn = '<a href="' . $editUrl . '" class="btn btn-sm btn-primary me-1">Edit</a>';
            //     }


            //     $lihatBtn = '<button class="btn btn-sm btn-info lihat-total"
            //     data-pin="' . $row['pin'] . '"
            //     data-employee="' . e($row['employee_name']) . '">
            //     Lihat Total
            // </button>';

            //     return $editBtn . $lihatBtn;
            // })
            ->addColumn('action', function ($row) {
    $editBtn = '';
    if ($row['is_updated']) {
        $editBtn = '<button class="btn btn-sm btn-secondary" disabled>
                        <i class="fas fa-edit"></i>
                    </button>';
    } else {
        $editUrl = route('pages.Fingerprints.edit', [
            'pin' => $row['pin'],
            'scan_date' => $row['scan_date'],
        ]);
        $editBtn = '<a href="' . $editUrl . '" class="btn btn-sm btn-primary me-1">
                        <i class="fas fa-edit"></i>
                    </a>';
    }

    $lihatBtn = '<button class="btn btn-sm btn-info lihat-total"
        data-pin="' . $row['pin'] . '"
        data-employee="' . e($row['employee_name']) . '">
        <i class="fas fa-eye"></i>
    </button>';

    return $editBtn . $lihatBtn;
})

            ->addColumn('updated_status', function ($row) {
                return $row['updated'];
            })
            ->rawColumns(['action', 'updated_status'])
            ->make(true);
    }
    public function editFingerprint($pin)
    {
        Log::info('Akses editFingerprint', compact('pin'));

        // Ambil data terakhir dari EditedFingerprint berdasarkan pin
        $data = EditedFingerprint::where('pin', $pin)
            ->orderByDesc('scan_date')
            ->first();

        if (!$data) {
            Log::info('Tidak ditemukan di EditedFingerprint, ambil dari source fingerprint', compact('pin'));

            // Ambil dari getFingerprints
            $response = $this->getFingerprints(request());
            $result = $response->getData()->data;

            Log::info('Data dari getFingerprints total:', ['count' => count($result)]);

            // Ambil data paling terakhir dari sumber berdasarkan pin
            $data = collect($result)
                ->where('pin', $pin)
                ->sortByDesc('scan_date')
                ->first();

            if (!$data) {
                Log::warning('Data fingerprint tidak ditemukan sama sekali!', compact('pin'));
                return response()->json(['message' => 'Data not found'], 404);
            }

            Log::info('Data ditemukan di sumber fingerprint.', (array) $data);
        } else {
            Log::info('Data ditemukan di EditedFingerprint.', $data->toArray());
        }

        return view('pages.Fingerprints.edit', ['data' => $data]);
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
