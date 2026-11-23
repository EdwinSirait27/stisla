<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PublicHoliday;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Hari libur nasional (tabel ph) untuk aplikasi mobile, per kelompok agama.
 * Tipe PH: 'Hindu' (khusus karyawan Hindu), 'Non Hindu', dan 'All' (semua karyawan).
 * Aturan berlaku sama dengan PublicHoliday::datesForEmployee: DW tidak mendapat PH, dan
 * PH hari Minggu hangus untuk store statis (Head Office, Holding, Distribution Center).
 */
class HolidayController extends Controller
{
    private const GROUPS = [
        'mine'      => null,
        'hindu'     => ['Hindu', 'All'],
        'non_hindu' => ['Non Hindu', 'All'],
        'all'       => ['Hindu', 'Non Hindu', 'All'],
    ];

    public function index(Request $request)
    {
        $employee = $request->user()->employee;
        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'Data karyawan tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'year'  => 'nullable|integer|min:2020|max:2100',
            'group' => 'nullable|in:' . implode(',', array_keys(self::GROUPS)),
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Filter tidak valid', 'errors' => $validator->errors()], 422);
        }

        $year  = (int) ($request->year ?? now()->year);
        $group = $request->group ?? 'mine';

        $isHindu       = $employee->religion === 'Hindu';
        $myTypes       = $isHindu ? ['Hindu', 'All'] : ['Non Hindu', 'All'];
        $isDw          = strtoupper($employee->status_employee ?? '') === 'DW';
        $isStaticStore = in_array($employee->primaryStore()->value('stores_tables.id'), PublicHoliday::STATIC_STORE_IDS, true);
        $types         = self::GROUPS[$group] ?? $myTypes;

        $today = now()->toDateString();
        $rows = PublicHoliday::whereYear('date', $year)->whereIn('type', $types)->orderBy('date')->get();

        $holidays = $rows->map(function (PublicHoliday $ph) use ($myTypes, $isDw, $isStaticStore, $today) {
            $date      = Carbon::parse($ph->date)->timezone(config('app.timezone'));
            $forMyType = in_array($ph->type, $myTypes, true);
            $sundayLost = $forMyType && $isStaticStore && $date->isSunday();

            // Kenapa PH ini tidak berlaku untuk saya, kalau tidak berlaku
            $note = match (true) {
                !$forMyType => $ph->type === 'Hindu' ? 'Khusus karyawan beragama Hindu' : 'Khusus karyawan non Hindu',
                $isDw       => 'Karyawan DW tidak mendapat libur nasional',
                $sundayLost => 'Jatuh hari Minggu, tidak diganti untuk lokasi Anda',
                default     => null,
            };

            return [
                'id'            => $ph->id,
                'date'          => $date->toDateString(),
                'day_label'     => $date->locale('id')->translatedFormat('l'),
                'date_label'    => $date->locale('id')->translatedFormat('d F Y'),
                'month'         => (int) $date->month,
                'month_label'   => $date->locale('id')->translatedFormat('F'),
                'remark'        => $ph->remark,
                'type'          => $ph->type,
                'applies_to_me' => $forMyType && !$isDw && !$sundayLost,
                'note'          => $note,
                'is_past'       => $date->toDateString() < $today,
                'is_today'      => $date->toDateString() === $today,
                'days_until'    => $date->toDateString() >= $today ? (int) now()->startOfDay()->diffInDays($date->copy()->startOfDay()) : null,
            ];
        })->values();

        // Libur berikutnya yang berlaku untuk saya, dicari lintas tahun supaya akhir tahun tetap terisi
        $next = null;
        if (!$isDw) {
            $next = PublicHoliday::where('date', '>=', $today)->whereIn('type', $myTypes)->orderBy('date')->get()
                ->first(fn($ph) => !($isStaticStore && Carbon::parse($ph->date)->timezone(config('app.timezone'))->isSunday()));
        }
        $nextDate = $next ? Carbon::parse($next->date)->timezone(config('app.timezone')) : null;

        $years = PublicHoliday::selectRaw('YEAR(date) as y')->distinct()->orderBy('y')->pluck('y')->map(fn($y) => (int) $y)->values();

        return response()->json([
            'success' => true,
            'data' => [
                'year'      => $year,
                'years'     => $years,
                'group'     => $group,
                'me'        => [
                    'religion'           => $employee->religion,
                    'group'              => $isHindu ? 'hindu' : 'non_hindu',
                    'group_label'        => $isHindu ? 'Hindu' : 'Non Hindu',
                    'is_dw'              => $isDw,
                    'sunday_not_replaced' => $isStaticStore,
                ],
                'next'      => $nextDate ? [
                    'date'       => $nextDate->toDateString(),
                    'day_label'  => $nextDate->locale('id')->translatedFormat('l'),
                    'date_label' => $nextDate->locale('id')->translatedFormat('d F Y'),
                    'remark'     => $next->remark,
                    'type'       => $next->type,
                    'days_until' => (int) now()->startOfDay()->diffInDays($nextDate->copy()->startOfDay()),
                ] : null,
                'total'          => $holidays->count(),
                'total_for_me'   => $holidays->where('applies_to_me', true)->count(),
                'remaining_for_me' => $holidays->where('applies_to_me', true)->where('is_past', false)->count(),
                'holidays'       => $holidays,
            ],
        ]);
    }
}
