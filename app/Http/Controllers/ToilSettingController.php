<?php

namespace App\Http\Controllers;

use App\Models\ToilSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

/**
 * Pengaturan TOIL & lembur: masa berlaku saldo, jam per hari libur TOIL, batas jam lembur,
 * rentang libur TOIL, dan check-in lembur mobile. Satu pengaturan untuk semua (ToilSetting id = 1).
 *
 * Akses: ManageToilSetting (route group di web.php).
 */
class ToilSettingController extends Controller
{
    private const FIELDS = [
        'toil_expiry_months'    => 'Masa berlaku saldo TOIL (bulan)',
        'cash_expiry_months'    => 'Masa berlaku saldo Cash (bulan)',
        'expiring_soon_days'    => 'Tanda segera hangus (hari)',
        'hours_per_day'         => 'Jam per hari libur TOIL',
        'max_leave_range_days'  => 'Rentang libur TOIL maksimal (hari)',
        'min_overtime_hours'    => 'Jam lembur minimal per hari',
        'max_overtime_hours'    => 'Jam lembur maksimal per hari',
        'checkin_early_minutes' => 'Check-in lembur paling cepat (menit)',
    ];

    public function index()
    {
        $setting = ToilSetting::current();

        $history = collect();
        try {
            $history = Activity::where('log_name', 'toil_setting')->with('causer')->latest()->limit(10)->get();
        } catch (\Throwable $e) {
            // tabel activity_log belum ada
        }

        return view('pages.ToilSetting.index', [
            'setting' => $setting,
            'saved'   => $setting->exists,
            'labels'  => self::FIELDS,
            'history' => $history,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'toil_expiry_months'    => 'required|integer|between:1,24',
            'cash_expiry_months'    => 'required|integer|between:1,24',
            'expiring_soon_days'    => 'required|integer|between:0,90',
            'hours_per_day'         => 'required|numeric|between:1,24|multiple_of:0.5',
            'max_leave_range_days'  => 'required|integer|between:1,366',
            'min_overtime_hours'    => 'required|numeric|between:0.5,24|multiple_of:0.5',
            'max_overtime_hours'    => 'required|numeric|between:0.5,24|multiple_of:0.5|gte:min_overtime_hours',
            'checkin_early_minutes' => 'required|integer|between:0,240',
        ], [
            '*.required'                => 'Wajib diisi.',
            '*.integer'                 => 'Harus angka bulat.',
            '*.numeric'                 => 'Harus angka.',
            '*.between'                 => 'Harus antara :min dan :max.',
            '*.multiple_of'             => 'Harus kelipatan 0,5 jam.',
            'max_overtime_hours.gte'    => 'Tidak boleh lebih kecil dari jam lembur minimal.',
        ], self::FIELDS);

        $setting = DB::transaction(function () use ($data) {
            $setting = ToilSetting::lockForUpdate()->find(1) ?? new ToilSetting(['id' => 1] + ToilSetting::DEFAULTS);
            $setting->id = 1;
            $old = $setting->exists ? $setting->only(array_keys(self::FIELDS)) : ToilSetting::DEFAULTS;

            $setting->fill($data + ['updated_by' => auth()->id()])->save();

            $changes = [];
            foreach (self::FIELDS as $key => $label) {
                if ((float) $old[$key] !== (float) $setting->{$key}) {
                    $changes[$key] = ['label' => $label, 'old' => $old[$key], 'new' => $setting->{$key}];
                }
            }

            if ($changes) {
                activity('toil_setting')->causedBy(auth()->user())->performedOn($setting)->event('updated')
                    ->withProperties([
                        'old'        => array_map(fn($c) => $c['old'], $changes),
                        'attributes' => array_map(fn($c) => $c['new'], $changes),
                    ])
                    ->log('Pengaturan TOIL diubah: ' . collect($changes)->map(
                        fn($c) => "{$c['label']} " . ToilSetting::num($c['old']) . ' → ' . ToilSetting::num($c['new'])
                    )->implode('; '));
            }

            return $changes;
        });

        ToilSetting::forgetCurrent();

        return redirect()->route('toilsetting.index')->with(
            'success',
            $setting ? 'Pengaturan TOIL disimpan. Berlaku untuk lembur dan libur TOIL yang dibuat mulai sekarang.' : 'Tidak ada yang berubah.'
        );
    }
}
