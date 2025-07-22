<?php

namespace App\Http\Controllers;
use App\Models\Fingerprints;
use App\Models\EditedFingerprint;
use App\Models\Employee;
use App\Models\Stores;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
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
   
 
    // public function editFingerprint($pin, $scanDate)
    // {
    //     $data = EditedFingerprint::where('pin', $pin)
    //         ->whereDate('scan_date', $scanDate)
    //         ->first();

    //     if (!$data) {
    //         // ambil ulang data dari source fingerprint
    //         $result = $this->getFingerprints(request())->getData()->data;

    //         $data = collect($result)->first(function ($item) use ($pin, $scanDate) {
    //             return $item->pin == $pin && $item->scan_date == $scanDate;
    //         });

    //         if (!$data) {
    //             return response()->json(['message' => 'Data not found'], 404);
    //         }
    //     }

    //     return view('Pages.Fingerprints.edit', ['data' => $data]);
    // }
    // public function updateFingerprint(Request $request)
    // {
    //     $validated = $request->validate([
    //         'pin' => 'required',
    //         'scan_date' => 'required|date',
    //         'employee_name' => 'required',
    //         'position_name' => 'nullable',
    //         'device_name' => 'nullable',
    //         'duration' => 'nullable',
    //         'in_1' => 'nullable|date',
    //         'in_2' => 'nullable|date',
    //         'in_3' => 'nullable|date',
    //         'in_4' => 'nullable|date',
    //         'in_5' => 'nullable|date',
    //         'in_6' => 'nullable|date',
    //         'in_7' => 'nullable|date',
    //         'in_8' => 'nullable|date',
    //         'in_9' => 'nullable|date',
    //         'in_10' => 'nullable|date',
    //     ]);

    //     // Simpan atau update ke tabel edited_fingerprints
    //     EditedFingerprint::updateOrCreate(
    //         ['pin' => $validated['pin'], 'scan_date' => $validated['scan_date']],
    //         $validated
    //     );

    //     return redirect()->back()->with('success', 'Fingerprint berhasil disimpan.');
    // }

    public function getFingerprints(Request $request)
    {
        ini_set('memory_limit', '1024M');

        $storeName = $request->input('store_name');
        $startDate = $request->input('start_date', '2025-07-01');
        $endDate = $request->input('end_date', now()->toDateString());

        // Bangun query employee + relasi position dan store
        $employeesQuery = Employee::with('position', 'store')
            ->select('pin', 'employee_name', 'position_id', 'store_id');

        // Jika ada filter store_name, tambahkan kondisi whereHas
        if ($storeName) {
            $employeesQuery->whereHas('store', function ($q) use ($storeName) {
                $q->where('name', $storeName); // Cocok karena kolomnya 'name'
            });
        }

        // Eksekusi query dan keyBy pin
        $employees = $employeesQuery->get()->keyBy('pin');

        // Ambil fingerprint + relasi devicefingerprints
        $fingerprints = Fingerprints::with('devicefingerprints')
            ->select(['sn', 'scan_date', 'pin', 'inoutmode'])
            ->whereBetween('scan_date', [$startDate, $endDate])
            ->orderBy('scan_date')
            ->get();

        // Kelompokkan berdasarkan PIN + tanggal (tanpa jam)
        $grouped = $fingerprints->groupBy(function ($item) {
            return $item->pin . '_' . Carbon::parse($item->scan_date)->toDateString();
        });

        $result = [];

        foreach ($grouped as $group) {
            $first = $group->first();
            $pin = $first->pin;
            $scanDate = Carbon::parse($first->scan_date)->toDateString();

            $employee = $employees->get($pin);

            $row = [
                'pin' => $pin,
                'employee_name' => $employee->employee_name ?? 'No Data',
                'name' => $employee->store->name ?? 'No Data',
                'position_name' => $employee ? optional($employee->position)->name : '-',
                'device_name' => optional($first->devicefingerprints)->device_name ?? '-',
                'scan_date' => $scanDate,
            ];

            // Inisialisasi in_1 sampai in_10
            for ($i = 1; $i <= 10; $i++) {
                $row['in_' . $i] = null;
            }

            // Kelompokkan berdasarkan inoutmode dan ambil waktu scan paling awal
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

            // gabungkan jam + device jadi 1 kolom untuk datatable
            for ($i = 1; $i <= 10; $i++) {
                $jam = $row['in_' . $i] ?? '';
                $device = $row['device_' . $i] ?? '';
                $row['combine_' . $i] = $jam . ' ' . $device . '';
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

            $result[] = $row;

        }
        // Urutkan berdasarkan scan_date
        $result = collect($result)->sortBy('scan_date')->values();

        return DataTables::of($result)
            ->addColumn('action', function ($row) {
                $editUrl = route('pages.Fingerprints.edit', [
                    'pin' => $row['pin'],
                    'scan_date' => $row['scan_date'],
                ]);

                return '<a href="' . $editUrl . '" class="btn btn-sm btn-primary">Edit</a>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

public function editFingerprint($pin, $scanDate)
{
    Log::info('Akses editFingerprint', compact('pin', 'scanDate'));

    $data = EditedFingerprint::where('pin', $pin)
        ->whereDate('scan_date', $scanDate)
        ->first();

    if (!$data) {
        Log::info('Tidak ditemukan di EditedFingerprint, ambil dari source fingerprint', compact('pin', 'scanDate'));

        // Ambil dari getFingerprints
        $response = $this->getFingerprints(request());
        $result = $response->getData()->data;

        Log::info('Data dari getFingerprints total:', ['count' => count($result)]);

        $data = collect($result)->first(function ($item) use ($pin, $scanDate) {
            return $item->pin == $pin && $item->scan_date == $scanDate;
        });

        if (!$data) {
            Log::warning('Data fingerprint tidak ditemukan sama sekali!', compact('pin', 'scanDate'));
            return response()->json(['message' => 'Data not found'], 404);
        }

        Log::info('Data ditemukan di sumber fingerprint.', (array)$data);
    } else {
        Log::info('Data ditemukan di EditedFingerprint.', $data->toArray());
    }
    return view('pages.Fingerprints.edit', ['data' => $data]);
}
// public function updateFingerprint(Request $request)
// {
//     $validated = $request->validate([
//         'pin' => 'required',
//         'scan_date' => 'required|date',
//         'employee_name' => 'required',
//         'position_name' => 'nullable',
//         'device_name' => 'nullable',
//         'duration' => 'nullable',
//         'in_1' => 'nullable|date',
//         'in_2' => 'nullable|date',
//         'in_3' => 'nullable|date',
//         'in_4' => 'nullable|date',
//         'in_5' => 'nullable|date',
//         'in_6' => 'nullable|date',
//         'in_7' => 'nullable|date',
//         'in_8' => 'nullable|date',
//         'in_9' => 'nullable|date',
//         'in_10' => 'nullable|date',
//         'device_1' => 'nullable|string',
//         'device_2' => 'nullable|string',
//         'device_3' => 'nullable|string',
//         'device_4' => 'nullable|string',
//         'device_5' => 'nullable|string',
//         'device_6' => 'nullable|string',
//         'device_7' => 'nullable|string',
//         'device_8' => 'nullable|string',
//         'device_9' => 'nullable|string',
//         'device_10' => 'nullable|string',
//     ]);

//     Log::info('Proses updateFingerprint dimulai', [
//         'pin' => $validated['pin'],
//         'scan_date' => $validated['scan_date'],
//     ]);

//     try {
//         $record = EditedFingerprint::updateOrCreate(
//             ['pin' => $validated['pin'], 'scan_date' => $validated['scan_date']],
//             $validated
//         );

//         Log::info('Fingerprint berhasil disimpan atau diupdate.', [
//             'pin' => $record->pin,
//             'scan_date' => $record->scan_date,
//         ]);

//         return redirect()->back()->with('success', 'Fingerprint berhasil disimpan.');
//     } catch (\Exception $e) {
//         Log::error('Gagal menyimpan fingerprint', [
//             'error' => $e->getMessage(),
//             'data' => $validated,
//         ]);

//         return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan fingerprint.');
//     }
// }
public function updateFingerprint(Request $request)
{
    try {
        Log::info('Mulai updateFingerprint', $request->all());

        $validated = $request->validate([
            'pin' => 'required|string',
            'scan_date' => 'required|date',
            'employee_name' => 'nullable|string',
            'position_name' => 'nullable|string',
            'device_name' => 'nullable|string',
            'duration' => 'nullable|string',
            ...collect(range(1, 10))->flatMap(function ($i) {
                return [
                    "in_$i" => 'nullable|string',
                    "device_$i" => 'nullable|string',
                ];
            })->toArray()
        ]);

        Log::info('Validasi berhasil', $validated);

        $data = EditedFingerprint::updateOrCreate(
            [
                'pin' => $validated['pin'],
                'scan_date' => $validated['scan_date'],
            ],
            collect($validated)->except(['pin', 'scan_date'])->toArray()
        );

        Log::info('Data berhasil disimpan', $data->toArray());

        return redirect()
            ->route('Fingerprints.index')
            ->with('success', 'Fingerprint berhasil diperbarui.');
    } catch (\Exception $e) {
        Log::error('Gagal updateFingerprint', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return back()->with('error', 'Terjadi kesalahan saat menyimpan data.');
    }
}


















   //   public function getFingerprints(Request $request)
    // {
    //     ini_set('memory_limit', '1024M');

    //     // Ambil semua employee + relasi position
    //      $storeFilter = $request->input('store');
    //     $employees = Employee::with('position', 'store')
    //         ->select('pin', 'employee_name', 'position_id', 'store_id')
    //         ->get()
    //         ->keyBy('pin');
    //          if ($storeFilter) {
    //     $filteredPins = $employees->filter(function ($employee) use ($storeFilter) {
    //         return $employee->store && $employee->store->name === $storeFilter;
    //     })->keys();
    // } else {
    //     $filteredPins = $employees->keys();
    // }
    //     $startDate = $request->input('start_date', '2025-07-01');
    //     $endDate = $request->input('end_date', now()->toDateString());

    //     // Ambil fingerprint + relasi devicefingerprints
    //     $fingerprints = Fingerprints::with('devicefingerprints')
    //         ->select(['sn', 'scan_date', 'pin', 'inoutmode'])
    //         ->whereBetween('scan_date', [$startDate, $endDate])
    //          ->whereIn('pin', $filteredPins) 
    //         ->orderBy('scan_date')
    //         ->get();

    //     // Kelompokkan berdasarkan PIN + tanggal (tanpa jam)
    //     $grouped = $fingerprints->groupBy(function ($item) {
    //         return $item->pin . '_' . Carbon::parse($item->scan_date)->toDateString();
    //     });

    //     $result = [];

    //     foreach ($grouped as $group) {
    //         $first = $group->first();
    //         $pin = $first->pin;
    //         $scanDate = Carbon::parse($first->scan_date)->toDateString();

    //         $employee = $employees->get($pin);

    //         $row = [
    //             'pin' => $pin,
    //             'employee_name' => $employee->employee_name ?? 'No Data',
    //             'name' => $employee->store->name ?? 'No Data',
    //             'position_name' => $employee ? optional($employee->position)->name : '-',
    //             'device_name' => optional($first->devicefingerprints)->device_name ?? '-',
    //             'scan_date' => $scanDate,
    //         ];

    //         // Inisialisasi in_1 sampai in_10
    //         for ($i = 1; $i <= 10; $i++) {
    //             $row['in_' . $i] = null;
    //         }

    //         // Kelompokkan berdasarkan inoutmode dan ambil waktu scan paling awal
    //         $byMode = $group->groupBy('inoutmode');

    //         foreach ($byMode as $mode => $items) {
    //             if ($mode >= 1 && $mode <= 10) {
    //                 $earliest = $items->sortBy('scan_date')->first();

    //                 $row['in_' . $mode] = $earliest && $earliest->scan_date
    //                     ? Carbon::parse($earliest->scan_date)->format('H:i:s')
    //                     : '';

    //                 $row['device_' . $mode] = $earliest && $earliest->devicefingerprints
    //                     ? $earliest->devicefingerprints->device_name
    //                     : '';

    //             }
    //         }



    //         // Hitung durasi dari scan pertama ke scan terakhir
    //         $scanTimes = collect(range(1, 10))
    //             ->map(fn($i) => $row['in_' . $i])
    //             ->filter()
    //             ->sort()
    //             ->values();

    //         $row['duration'] = $scanTimes->count() >= 2
    //             ? Carbon::parse($scanTimes->first())->diffForHumans(Carbon::parse($scanTimes->last()), true)
    //             : 'invalid';

    //         $result[] = $row;
    //     }

    //     // Urutkan berdasarkan scan_date
    //     $result = collect($result)->sortBy('scan_date')->values();

    //     return DataTables::of($result)
    //         ->addColumn('action', function ($row) {
    //             $editUrl = route('Fingerprints.edit', [
    //                 'pin' => $row['pin'],
    //                 'scan_date' => $row['scan_date'],
    //             ]);

    //             return '<a href="' . $editUrl . '" class="btn btn-sm btn-primary">Edit</a>';
    //         })
    //         ->rawColumns(['action'])
    //         ->make(true);
    // }
   
    //       public function getFingerprints(Request $request)
//     {
//         ini_set('memory_limit', '1024M');

    // //         // Ambil semua employee + relasi position
// //         $storeName = $request->input('store_name'); 
// //         $employees = Employee::with('position', 'store')
// //             ->select('pin', 'employee_name', 'position_id', 'store_id')
// //             ->get()
// //             ->keyBy('pin');
// //         $startDate = $request->input('start_date', '2025-07-01');
// //         $endDate = $request->input('end_date', now()->toDateString());
// // if ($storeName) {
// //     $employees->whereHas('store', function ($q) use ($storeName) {
// //         $q->where('name', $storeName); // ← Cocok karena nama kolomnya 'name'
// //     });
// // Ambil filter dari request
// $storeName = $request->input('store_name');
// $startDate = $request->input('start_date', '2025-07-01');
// $endDate = $request->input('end_date', now()->toDateString());

    // // Bangun query employee + relasi position dan store
// $employeesQuery = Employee::with('position', 'store')
//     ->select('pin', 'employee_name', 'position_id', 'store_id');

    // // Jika ada filter store_name, tambahkan kondisi whereHas
// if ($storeName) {
//     $employeesQuery->whereHas('store', function ($q) use ($storeName) {
//         $q->where('name', $storeName); // Cocok karena kolomnya 'name'
//     });
// }

    // // Eksekusi query dan keyBy pin
// $employees = $employeesQuery->get()->keyBy('pin');

    //         // Ambil fingerprint + relasi devicefingerprints
//         $fingerprints = Fingerprints::with('devicefingerprints')
//             ->select(['sn', 'scan_date', 'pin', 'inoutmode'])
//             ->whereBetween('scan_date', [$startDate, $endDate])
//             ->orderBy('scan_date')
//             ->get();

    //         // Kelompokkan berdasarkan PIN + tanggal (tanpa jam)
//         $grouped = $fingerprints->groupBy(function ($item) {
//             return $item->pin . '_' . Carbon::parse($item->scan_date)->toDateString();
//         });

    //         $result = [];

    //         foreach ($grouped as $group) {
//             $first = $group->first();
//             $pin = $first->pin;
//             $scanDate = Carbon::parse($first->scan_date)->toDateString();

    //             $employee = $employees->get($pin);

    //             $row = [
//                 'pin' => $pin,
//                 'employee_name' => $employee->employee_name ?? 'No Data',
//                 'name' => $employee->store->name ?? 'No Data',
//                 'position_name' => $employee ? optional($employee->position)->name : '-',
//                 'device_name' => optional($first->devicefingerprints)->device_name ?? '-',
//                 'scan_date' => $scanDate,
//             ];

    //             // Inisialisasi in_1 sampai in_10
//             for ($i = 1; $i <= 10; $i++) {
//                 $row['in_' . $i] = null;
//             }

    //             // Kelompokkan berdasarkan inoutmode dan ambil waktu scan paling awal
//             $byMode = $group->groupBy('inoutmode');


    //             foreach ($byMode as $mode => $items) {
//     if ($mode >= 1 && $mode <= 10) {
//         $earliest = $items->sortBy('scan_date')->first();

    //         $row['in_' . $mode] = $earliest && $earliest->scan_date
//             ? Carbon::parse($earliest->scan_date)->format('H:i:s')
//             : '';

    //         $row['device_' . $mode] = $earliest && $earliest->devicefingerprints
//             ? $earliest->devicefingerprints->device_name
//             : '';
//     }
// }

    // // gabungkan jam + device jadi 1 kolom untuk datatable
// for ($i = 1; $i <= 10; $i++) {
//     $jam = $row['in_' . $i] ?? '';
//     $device = $row['device_' . $i] ?? '';
//     $row['combine_' . $i] = $jam . ' ' . $device . '';
// }

    //             // // Hitung durasi dari scan pertama ke scan terakhir
//             // $scanTimes = collect(range(1, 10))
//             //     ->map(fn($i) => $row['in_' . $i])
//             //     ->filter()
//             //     ->sort()
//             //     ->values();

    //             // $row['duration'] = $scanTimes->count() >= 2
//             //     ? Carbon::parse($scanTimes->first())->diffForHumans(Carbon::parse($scanTimes->last()), true)
//             //     : 'invalid';

    //             // $result[] = $row;
//             $scanTimes = collect(range(1, 10))
//     ->map(fn($i) => $row['in_' . $i])
//     ->filter()
//     ->sort()
//     ->values();

    // if ($scanTimes->count() >= 2) {
//     $start = Carbon::parse($scanTimes->first());
//     $end = Carbon::parse($scanTimes->last());
//     $diffInMinutes = $start->diffInMinutes($end);
//     $hours = floor($diffInMinutes / 60);
//     $minutes = $diffInMinutes % 60;

    //     $row['duration'] = ($hours > 0 ? $hours . ' hour' . ($hours > 1 ? 's' : '') : '') .
//                        ($minutes > 0 ? ' ' . $minutes . ' minute' . ($minutes > 1 ? 's' : '') : '');
//     $row['duration'] = trim($row['duration']) ?: '0 minutes';
// } else {
//     $row['duration'] = 'invalid';
// }

    // $result[] = $row;

    //         }
//         // Urutkan berdasarkan scan_date
//         $result = collect($result)->sortBy('scan_date')->values();

    //         return DataTables::of($result)
//             ->addColumn('action', function ($row) {
//                 $editUrl = route('Fingerprints.edit', [
//                     'pin' => $row['pin'],
//                     'scan_date' => $row['scan_date'],
//                 ]);

    //                 return '<a href="' . $editUrl . '" class="btn btn-sm btn-primary">Edit</a>';
//             })
//             ->rawColumns(['action'])
//             ->make(true);
//     }



    // foreach ($byMode as $mode => $items) {
    //     if ($mode >= 1 && $mode <= 10) {
    //         $earliest = $items->sortBy('scan_date')->first();

    //         $row['in_' . $mode] = $earliest && $earliest->scan_date
    //             ? Carbon::parse($earliest->scan_date)->format('H:i:s')
    //             : '';

    //         $row['device_' . $mode] = $earliest && $earliest->devicefingerprints
    //             ? $earliest->devicefingerprints->device_name
    //             : '';

    //     }
    // }














































    //   ini benar2
//     public function getFingerprints(Request $request)
// {
//     ini_set('memory_limit', '1024M');

    //         // Ambil semua employee + relasi position
//     $employees = Employee::with('position','store')
//         ->select('pin', 'employee_name', 'position_id','store_id')
//         ->get()
//         ->keyBy('pin');
// $startDate = $request->input('start_date', '2025-07-01');
// $endDate = $request->input('end_date', now()->toDateString());

    //         // Ambil fingerprint + relasi devicefingerprints
//     $fingerprints = Fingerprints::with('devicefingerprints')
//         ->select(['sn', 'scan_date', 'pin', 'inoutmode'])
//         ->whereBetween('scan_date', [$startDate, $endDate])
//         ->orderBy('scan_date')
//         ->get();

    //         // Kelompokkan berdasarkan PIN + tanggal (tanpa jam)
//     $grouped = $fingerprints->groupBy(function ($item) {
//         return $item->pin . '_' . Carbon::parse($item->scan_date)->toDateString();
//     });

    //         $result = [];

    //         foreach ($grouped as $group) {
//         $first = $group->first();
//         $pin = $first->pin;
//         $scanDate = Carbon::parse($first->scan_date)->toDateString();

    //             $employee = $employees->get($pin);

    //             $row = [
//             'pin' => $pin,
//             'employee_name' => $employee->employee_name ?? 'No Data',
//             'name' => $employee->store->name ?? 'No Data',
//             'position_name' => $employee ? optional($employee->position)->name : '-',
//             'device_name' => optional($first->devicefingerprints)->device_name ?? '-',
//             'scan_date' => $scanDate,
//         ];

    //             // Inisialisasi in_1 sampai in_10
//         for ($i = 1; $i <= 10; $i++) {
//             $row['in_' . $i] = null;
//         }

    //             // Kelompokkan berdasarkan inoutmode dan ambil waktu scan paling awal
//         $byMode = $group->groupBy('inoutmode');
//         foreach ($byMode as $mode => $items) {
//             if ($mode >= 1 && $mode <= 10) {
//                 // $row['in_' . $mode] = $items->min('scan_date'); // lebih efisien dari sortBy()->first() 
//                 $row['in_' . $mode] = Carbon::parse($items->min('scan_date'))->format('H:i:s');

    //                 }
//         }

    //             // Hitung durasi dari scan pertama ke scan terakhir
//         $scanTimes = collect(range(1, 10))
//             ->map(fn($i) => $row['in_' . $i])
//             ->filter()
//             ->sort()
//             ->values();

    //             $row['duration'] = $scanTimes->count() >= 2
//             ? Carbon::parse($scanTimes->first())->diffForHumans(Carbon::parse($scanTimes->last()), true)
//             : 'invalid';

    //             $result[] = $row;
//     }

    //         // Urutkan berdasarkan scan_date
//     $result = collect($result)->sortBy('scan_date')->values();

    //         return DataTables::of($result)
//      ->addColumn('action', function ($row) {
//         $editUrl = route('Fingerprints.edit', [
//             'pin' => $row['pin'],
//             'scan_date' => $row['scan_date'],
//         ]);

    //             return '<a href="' . $editUrl . '" class="btn btn-sm btn-primary">Edit</a>';
//     })
//     ->rawColumns(['action'])
//     ->make(true);
// }

    // ini yang bener
//     public function getFingerprints(Request $request)
// {
//     ini_set('memory_limit', '1024M');

    //         // Ambil semua employee + position
//     $employees = Employee::with('position','store')
//         ->select('pin', 'employee_name', 'position_id','store_id')
//         ->get()
//         ->keyBy('pin');

    //         // Ambil fingerprint + relasi devicefingerprints
//     $fingerprints = Fingerprints::with('devicefingerprints')
//         ->select(['sn', 'scan_date', 'pin', 'inoutmode'])
//         ->whereDate('scan_date', '>=', '2025-07-01')
//         ->orderBy('scan_date')
//         ->get();

    //         // Kelompokkan berdasarkan PIN + tanggal (tanpa jam)
//     $grouped = $fingerprints->groupBy(function ($item) {
//         return $item->pin . '_' . date('Y-m-d', strtotime($item->scan_date));
//     });

    //         $result = [];

    //         foreach ($grouped as $group) {
//         $first = $group->first();
//         $pin = $first->pin;
//         // $scanDate = date('Y-m-d', strtotime($first->scan_date));
//         $scanDate = Carbon::parse($first->scan_date)->toDateString();


    //             $employee = $employees->get($pin);

    //             $row = [
//             'pin' => $pin,
//                 'name' => $employee->store->name ?? 'No Data',

    //             'employee_name' => $employee->employee_name ?? 'Belum Masuk Sistem',
//             'position_name' => $employee->position->name ?? '-',
//             'device_name' => $first->devicefingerprints->device_name ?? '-',
//             'scan_date' => $scanDate,
//         ];
//         // Inisialisasi in_1 sampai in_10
//         for ($i = 1; $i <= 10; $i++) {
//             $row['in_' . $i] = null;
//         }

    //             // Kelompokkan berdasarkan inoutmode dan ambil scan_date pertama
//         $byMode = $group->groupBy('inoutmode');
//         foreach ($byMode as $mode => $items) {
//             if ($mode >= 1 && $mode <= 10) {
//                 $firstScan = $items->sortBy('scan_date')->first();
//                 $row['in_' . $mode] = $firstScan->scan_date;
//             }
//         }

    //             // Hitung durasi dari scan pertama ke scan terakhir
//         $scanTimes = collect(range(1, 10))
//             ->map(fn($i) => $row['in_' . $i])
//             ->filter()
//             ->sort()
//             ->values();

    //             if ($scanTimes->count() >= 2) {
//             $start = Carbon::parse($scanTimes->first());
//             $end = Carbon::parse($scanTimes->last());
//             $row['duration'] = $start->diffForHumans($end, true); // contoh: '10 jam 3 menit'
//         } else {
//             $row['duration'] = 'invalid';
//         }

    //             $result[] = $row;
//     }

    //         // Urutkan berdasarkan scan_date
//     usort($result, function ($a, $b) {
//         return strtotime($a['scan_date']) <=> strtotime($b['scan_date']);
//     });

    //         return DataTables::of(collect($result))->make(true);
// }

    //   public function getFingerprints(Request $request)
// {
//     ini_set('memory_limit', '1024M');

    //     // Ambil semua employee + position
//     $employees = Employee::with('position')
//         ->select('pin', 'employee_name', 'position_id')
//         ->get()
//         ->keyBy('pin');

    //     // Ambil fingerprint + relasi devicefingerprints
//     $fingerprints = Fingerprints::with('devicefingerprints')
//         ->select(['sn', 'scan_date', 'pin', 'inoutmode'])
//         ->whereDate('scan_date', '>=', '2025-07-01')
//         ->orderBy('scan_date')
//         ->get();

    //     // Kelompokkan berdasarkan PIN + tanggal (tanpa jam)
//     $grouped = $fingerprints->groupBy(function ($item) {
//         return $item->pin . '_' . date('Y-m-d', strtotime($item->scan_date));
//     });

    //     $result = [];

    //     foreach ($grouped as $group) {
//         $first = $group->first();
//         $pin = $first->pin;
//         $scanDate = date('Y-m-d', strtotime($first->scan_date));

    //         $employee = $employees->get($pin);

    //         $row = [
//             'pin' => $pin,
//             'employee_name' => $employee->employee_name ?? '-',
//             'position_name' => $employee->position->name ?? '-',
//             'device_name' => $first->devicefingerprints->device_name ?? '-',
//             'scan_date' => $scanDate,
//         ];

    //         // Inisialisasi kolom in_1 sampai in_10
//         for ($i = 1; $i <= 10; $i++) {
//             $row['in_' . $i] = null;
//         }

    //         // Kelompokkan group fingerprint berdasarkan inoutmode
//         $byMode = $group->groupBy('inoutmode');

    //         foreach ($byMode as $mode => $items) {
//             if ($mode >= 1 && $mode <= 10) {
//                 $firstScan = $items->sortBy('scan_date')->first();
//                 $row['in_' . $mode] = $firstScan->scan_date;
//             }
//         }

    //         $result[] = $row;
//     }

    //     // Urutkan berdasarkan scan_date ASC
//     usort($result, function ($a, $b) {
//         return strtotime($a['scan_date']) <=> strtotime($b['scan_date']);
//     });

    //     return DataTables::of(collect($result))->make(true);
// }




    // public function getFingerprints(Request $request)
    // {
    //     // Ambil semua employee + position dalam 1 query
    //     ini_set('memory_limit', '1024M');
    //     $employees = Employee::with('position')->get()->keyBy('pin');

    //     $query = Fingerprints::with('devicefingerprints')
    //         ->select([
    //             'sn',
    //             DB::raw('DATE(scan_date) as scan_date'),
    //             'pin',
    //             'verifymode',
    //             'inoutmode'
    //         ])
    //         ->whereDate('scan_date', '>=', '2025-07-01');

    //     return DataTables::of($query)
    //         ->addColumn('employee_name', function ($row) use ($employees) {
    //             return $employees[$row->pin]->employee_name ?? '-';
    //         })
    //         ->addColumn('position_name', function ($row) use ($employees) {
    //             return $employees[$row->pin]->position->name ?? '-';
    //         })
    //         ->addColumn('device_name', function ($row) {
    //             return $row->devicefingerprints->device_name ?? '-';
    //         })
    //         ->filterColumn('employee_name', function ($query, $keyword) {
    //             $pins = DB::table('employees_tables')
    //                 ->where('employee_name', 'like', "%{$keyword}%")
    //                 ->pluck('pin');

    //             $query->whereIn('pin', $pins);
    //         })
    //         ->filterColumn('device_name', function ($query, $keyword) {
    //             $sns = DB::connection('mysql_second')
    //                 ->table('device')
    //                 ->where('device_name', 'like', "%{$keyword}%")
    //                 ->pluck('sn');

    //             $query->whereIn('sn', $sns);
    //         })
    //         ->make(true);
    // }


    // public function getFingerprints(Request $request)
// {
//     ini_set('memory_limit', '1024M');

    //     $employees = Employee::with('position')->get()->keyBy('pin');

    //     $rows = DB::table('fingerprints')
//         ->select('pin', 'scan_date', 'inoutmode')
//         ->whereDate('scan_date', '>=', '2025-07-01')
//         ->orderBy('scan_date')
//         ->get()
//         ->groupBy(function ($item) {
//             return $item->pin . '_' . date('Y-m-d', strtotime($item->scan_date)); // Group by pin + date
//         });

    //     $data = [];

    //     foreach ($rows as $key => $groupedRows) {
//         $first = $groupedRows->first();
//         $pin = $first->pin;
//         $date = date('Y-m-d', strtotime($first->scan_date));

    //         $row = [
//             'pin' => $pin,
//             'employee_name' => $employees[$pin]->employee_name ?? '-',
//             'position_name' => $employees[$pin]->position->name ?? '-',
//         ];

    //         // mapping setiap inoutmode jadi kolom
//         foreach ($groupedRows as $item) {
//             $modeKey = 'in_' . $item->inoutmode;
//             $row[$modeKey] = $item->scan_date;
//         }

    //         $data[] = $row;
//     }

    //     return DataTables::of(collect($data))
//         ->make(true);
// }


}