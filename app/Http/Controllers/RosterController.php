<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Roster;
use App\Models\Shifts;
use App\Models\Stores;
use Carbon\Carbon;
use Illuminate\Http\Request;
class RosterController extends Controller
{
    public function index(Request $request)
    {
        $stores    = Stores::select('id', 'name')->whereNotNull('name')->orderBy('name')->get();
        // $startDate = $request->start_date ?? Carbon::now()->startOfMonth()->toDateString();
        // $endDate   = $request->end_date   ?? Carbon::now()->endOfMonth()->toDateString();
        $today = Carbon::now();

if ($today->day >= 26) {
    // Periode: 26 bulan ini - 25 bulan depan
    $startDate = $request->start_date 
        ?? $today->copy()->day(26)->toDateString();

    $endDate = $request->end_date 
        ?? $today->copy()->addMonth()->day(25)->toDateString();
} else {
    // Periode: 26 bulan lalu - 25 bulan ini
    $startDate = $request->start_date 
        ?? $today->copy()->subMonth()->day(26)->toDateString();

    $endDate = $request->end_date 
        ?? $today->copy()->day(25)->toDateString();
}
        $storeId   = $request->store_id;
        $employees = collect();
        $shifts    = collect();
        $dates     = [];
        if ($storeId) {
            $employees = Employee::with([
                'position:id,name',
                'store:id,name',
                'rosters' => function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('date', [$startDate, $endDate])
                      ->with('shift:id,shift_name,start_time,end_time');
                },
            ])
            ->whereNull('deleted_at')
            ->where('store_id', $storeId)
            ->whereIn('status', ['Active', 'Pending', 'On Leave'])
            ->orderBy('employee_name')
            ->get();
            $shifts = Shifts::where('store_id', $storeId)
                ->orderBy('shift_name')->get();

            $current = Carbon::parse($startDate);
            while ($current->lte(Carbon::parse($endDate))) {
                $dates[] = $current->copy();
                $current->addDay();
            }
        }

        return view('pages.Roster.Roster', compact(
            'employees', 'shifts', 'stores', 'dates', 'startDate', 'endDate', 'storeId'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees_tables,id',
            'shift_id'    => 'nullable|exists:shifts_tables,id',
            'date'        => 'required|date',
            'day_type'    => 'required|in:Work,Off,Public Holiday,Leave,Cuti Melahirkan'
        ]);

        $roster = Roster::updateOrCreate(
            ['employee_id' => $request->employee_id, 'date' => $request->date],
            [
                'shift_id' => $request->day_type === 'Work' ? $request->shift_id : null,
                'day_type' => $request->day_type,
                'notes'    => $request->notes,
            ]
        );

        return response()->json([
            'success'     => true,
            'roster'      => $roster->load('shift'),
            'roster_name' => $roster->shift?->shift_name ?? $request->day_type,
            'roster_time' => $roster->shift
                ? substr($roster->shift->start_time, 0, 5) . '-' . substr($roster->shift->end_time, 0, 5)
                : '',
        ]);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees_tables,id',
            'date'        => 'required|date',
        ]);

        Roster::where('employee_id', $request->employee_id)
            ->where('date', $request->date)
            ->delete();

        return response()->json(['success' => true]);
    }

    public function bulkAssign(Request $request)
    {
        $request->validate([
            'employee_ids'      => 'required|array|min:1',
            'employee_ids.*'    => 'exists:employees_tables,id',
            'shift_id'          => 'nullable|exists:shifts_tables,id',
            'start_date'        => 'required|date',
            'end_date'          => 'required|date|after_or_equal:start_date',
            'day_type'          => 'required|in:Work,Off,Public Holiday,Leave,Cuti Melahirkan',
            'skip_weekend'      => 'boolean',
            'saturday_shift'    => 'boolean',
            'saturday_shift_id' => 'nullable|exists:shifts_tables,id',
        ]);

        $dates   = [];
        $current = Carbon::parse($request->start_date);
        $end     = Carbon::parse($request->end_date);

        // while ($current->lte($end)) {
        //     if ($request->skip_weekend && $current->isWeekend()) {
        //         $current->addDay();
        //         continue;
        //     }
        //     $dates[] = $current->toDateString();
        //     $current->addDay();
        // }
        while ($current->lte($end)) {

            if ($request->skip_weekend && $current->isSunday()) {
                $current->addDay();
                continue;
            }
            $dates[] = $current->copy();
            $current->addDay();
        }

        $count = 0;
        foreach ($request->employee_ids as $empId) {
            foreach ($dates as $date) {

                // Handle Sabtu
                if ($date->isSaturday()) {
                    if ($request->saturday_shift && $request->saturday_shift_id) {
                        // Sabtu dengan shift khusus
                        Roster::updateOrCreate(
                            ['employee_id' => $empId, 'date' => $date->toDateString()],
                            [
                                'shift_id' => $request->saturday_shift_id,
                                'day_type' => 'Work',
                            ]
                        );
                        $count++;
                    } elseif ($request->skip_weekend) {
                        // Skip Sabtu jika skip_weekend aktif dan tidak ada shift sabtu khusus
                        continue;
                    } else {
                        // Sabtu tanpa shift khusus, pakai shift biasa
                        Roster::updateOrCreate(
                            ['employee_id' => $empId, 'date' => $date->toDateString()],
                            [
                                'shift_id' => $request->day_type === 'Work' ? $request->shift_id : null,
                                'day_type' => $request->day_type,
                            ]
                        );
                        $count++;
                    }
                    continue;
                }

                // Hari biasa (Senin–Jumat)
                Roster::updateOrCreate(
                    ['employee_id' => $empId, 'date' => $date->toDateString()],
                    [
                        'shift_id' => $request->day_type === 'Work' ? $request->shift_id : null,
                        'day_type' => $request->day_type,
                    ]
                );
                $count++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "assign schedule {$count} success.",
        ]);
    }

    /**
     * Copy roster dari periode sumber ke periode target.
     *
     * Perubahan: sekarang mendukung target_end sehingga panjang periode
     * target tidak harus sama dengan periode sumber. Roster sumber akan
     * di-loop secara siklik (berulang) mengisi hari-hari di periode target,
     * melewati tanggal yang sudah ada di target (tidak overwrite jika sudah ada).
     *
     * Jika target_end tidak dikirim, perilaku lama tetap berlaku:
     * panjang target = panjang sumber (offset sederhana).
     */
    public function copyRoster(Request $request)
    {
        $request->validate([
            'store_id'     => 'nullable|exists:stores_tables,id',
            'source_start' => 'required|date',
            'source_end'   => 'required|date|after_or_equal:source_start',
            'target_start' => 'required|date',
            'target_end'   => 'nullable|date|after_or_equal:target_start',
        ]);

        $sourceStart = Carbon::parse($request->source_start);
        $sourceEnd   = Carbon::parse($request->source_end);
        $targetStart = Carbon::parse($request->target_start);

        // Ambil semua roster sumber
        $sourceRosters = Roster::whereBetween('date', [
            $sourceStart->toDateString(),
            $sourceEnd->toDateString(),
        ])
        ->when($request->store_id, fn($q) =>
            $q->whereHas('employee', fn($eq) => $eq->where('store_id', $request->store_id))
        )
        ->orderBy('date')
        ->get();

        if ($sourceRosters->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada jadwal di periode sumber.',
            ]);
        }

        $count = 0;

        // ── Mode A: target_end diberikan → loop/siklik roster sumber ke target ──
        if ($request->filled('target_end')) {
            $targetEnd = Carbon::parse($request->target_end);

            // Kelompokkan roster sumber per (employee_id, offset_hari_dari_source_start)
            // Key: "{employee_id}_{offset}"
            $sourceMap = [];
            foreach ($sourceRosters as $src) {
                $offset = $sourceStart->diffInDays(Carbon::parse($src->date));
                $sourceMap[$src->employee_id][$offset] = $src;
            }

            $sourceLengthDays = $sourceStart->diffInDays($sourceEnd) + 1; // inklusif

            // Kumpulkan semua employee_id yang ada di sumber
            $employeeIds = array_keys($sourceMap);

            foreach ($employeeIds as $empId) {
                $current = $targetStart->copy();
                $dayIndex = 0; // offset di target

                while ($current->lte($targetEnd)) {
                    // Offset sumber yang bersesuaian (siklik)
                    $srcOffset = $dayIndex % $sourceLengthDays;

                    if (isset($sourceMap[$empId][$srcOffset])) {
                        $src = $sourceMap[$empId][$srcOffset];
                        Roster::updateOrCreate(
                            ['employee_id' => $empId, 'date' => $current->toDateString()],
                            ['shift_id' => $src->shift_id, 'day_type' => $src->day_type]
                        );
                        $count++;
                    }

                    $current->addDay();
                    $dayIndex++;
                }
            }

        } else {
            // ── Mode B: target_end tidak diberikan → offset sederhana (perilaku lama) ──
            $diffDays = $sourceStart->diffInDays($targetStart);

            foreach ($sourceRosters as $src) {
                $newDate = Carbon::parse($src->date)->addDays($diffDays)->toDateString();
                Roster::updateOrCreate(
                    ['employee_id' => $src->employee_id, 'date' => $newDate],
                    ['shift_id' => $src->shift_id, 'day_type' => $src->day_type]
                );
                $count++;
            }
        }
        return response()->json([
            'success' => true,
            'message' => "schedule copied {$count} successfully.",
        ]);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'employee_ids'   => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees_tables,id',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date|after_or_equal:start_date',
        ]);

        $count = Roster::whereIn('employee_id', $request->employee_ids)
            ->whereBetween('date', [$request->start_date, $request->end_date])
            ->delete();

        return response()->json([
            'success' => true,
            'message' => "Berhasil menghapus {$count} jadwal.",
        ]);
    }
}