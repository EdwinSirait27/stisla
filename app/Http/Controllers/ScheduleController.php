<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Schedule;
use App\Models\Roster;
use App\Models\Stores;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $stores    = Stores::select('id', 'name')->whereNotNull('name')->orderBy('name')->get();
        $startDate = $request->start_date ?? Carbon::now()->startOfMonth()->toDateString();
        $endDate   = $request->end_date   ?? Carbon::now()->endOfMonth()->toDateString();
        $storeId   = $request->store_id;

        // ── Wajib pilih Store dulu ──
        $employees = collect();
        $rosters   = collect();
        $dates     = [];

        if ($storeId) {
            $employees = Employee::with([
                'position:id,name',
                'store:id,name',
                'schedules' => function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('date', [$startDate, $endDate])
                      ->with([
                          'roster.shift:id,shift_name,start_time,end_time',
                          'fingerprintRecap',
                      ]);
                },
            ])
            ->whereNull('deleted_at')
            ->where('store_id', $storeId)
            ->orderBy('employee_name')
            ->get();

            // ── Ambil roster unik berdasarkan shift untuk dropdown ──
            // Roster diambil dalam rentang tanggal yang dipilih
            // unique by shift_id agar tidak duplikat shift yang sama
            $rosters = Roster::with('shift:id,shift_name,start_time,end_time')
                ->whereNotNull('shift_id')
                ->whereBetween('date', [$startDate, $endDate])
                ->whereHas('employee', fn($eq) => $eq->where('store_id', $storeId))
                ->get()
                ->unique('shift_id')
                ->values();

            $current = Carbon::parse($startDate);
            while ($current->lte(Carbon::parse($endDate))) {
                $dates[] = $current->copy();
                $current->addDay();
            }
        }

        return view('pages.Schedule.Schedule', compact(
            'employees', 'stores', 'dates', 'startDate', 'endDate', 'storeId', 'rosters'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees_tables,id',
            'roster_id'   => 'nullable|exists:roster,id',
            'date'        => 'required|date',
            'day_type'    => 'required|in:Work,Off,Holiday,Leave',
            'notes'       => 'nullable|string',
        ]);

        $schedule = Schedule::updateOrCreate(
            [
                'employee_id' => $request->employee_id,
                'date'        => $request->date,
            ],
            [
                'roster_id' => $request->day_type === 'Work' ? $request->roster_id : null,
                'day_type'  => $request->day_type,
                'status'    => 'Scheduled',
                'notes'     => $request->notes,
            ]
        );

        return response()->json([
            'success'  => true,
            'schedule' => $schedule->load('roster.shift'),
        ]);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees_tables,id',
            'date'        => 'required|date',
        ]);

        Schedule::where('employee_id', $request->employee_id)
            ->where('date', $request->date)
            ->delete();

        return response()->json(['success' => true]);
    }

    public function bulkAssign(Request $request)
    {
        $request->validate([
            'employee_ids'   => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees_tables,id',
            'roster_id'      => 'nullable|exists:roster,id',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date|after_or_equal:start_date',
            'day_type'       => 'required|in:Work,Off,Holiday,Leave',
            'skip_weekend'   => 'boolean',
        ]);

        $dates   = [];
        $current = Carbon::parse($request->start_date);
        $end     = Carbon::parse($request->end_date);

        while ($current->lte($end)) {
            if ($request->skip_weekend && $current->isWeekend()) {
                $current->addDay();
                continue;
            }
            $dates[] = $current->toDateString();
            $current->addDay();
        }

        $count = 0;
        foreach ($request->employee_ids as $empId) {
            foreach ($dates as $date) {
                Schedule::updateOrCreate(
                    ['employee_id' => $empId, 'date' => $date],
                    [
                        'roster_id' => $request->day_type === 'Work' ? $request->roster_id : null,
                        'day_type'  => $request->day_type,
                        'status'    => 'Scheduled',
                    ]
                );
                $count++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Berhasil assign {$count} jadwal.",
        ]);
    }

    public function copySchedule(Request $request)
    {
        $request->validate([
            'store_id'     => 'nullable|exists:stores_tables,id',
            'source_start' => 'required|date',
            'source_end'   => 'required|date|after_or_equal:source_start',
            'target_start' => 'required|date',
        ]);

        $diffDays = Carbon::parse($request->source_start)
            ->diffInDays(Carbon::parse($request->target_start));

        $sourceSchedules = Schedule::whereBetween('date', [
            $request->source_start, $request->source_end,
        ])
        ->when($request->store_id, fn($q) =>
            $q->whereHas('employee', fn($eq) => $eq->where('store_id', $request->store_id))
        )
        ->get();

        $count = 0;
        foreach ($sourceSchedules as $src) {
            $newDate = Carbon::parse($src->date)->addDays($diffDays)->toDateString();
            Schedule::updateOrCreate(
                ['employee_id' => $src->employee_id, 'date' => $newDate],
                [
                    'roster_id' => $src->roster_id,
                    'day_type'  => $src->day_type,
                    'status'    => 'Scheduled',
                ]
            );
            $count++;
        }

        return response()->json([
            'success' => true,
            'message' => "Berhasil copy {$count} jadwal.",
        ]);
    }
}