<?php

namespace App\Http\Controllers;

use App\Models\RosterSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Pengaturan roster: jendela tanggal setiap bulan saat SPV/manager boleh mengubah roster
 * (dipakai RosterController::checkRosterWindow). HR dengan ManageRoster tidak dibatasi.
 * Hanya satu pengaturan yang aktif; mengaktifkan satu menonaktifkan yang lain.
 *
 * Akses: ManageRosterSetting (route group di web.php).
 */
class RosterSettingController extends Controller
{
    private static function hashId($id): string
    {
        return substr(hash('sha256', 'roster-setting' . $id . config('app.key')), 0, 8);
    }

    private function findByHash(string $hash): RosterSetting
    {
        $setting = RosterSetting::all()->first(fn($s) => hash_equals(self::hashId($s->id), $hash));
        abort_if(!$setting, 404, 'Pengaturan roster tidak ditemukan.');
        return $setting;
    }

    /** Kalender satu bulan untuk pratinjau: tanggal mana yang terbuka bagi SPV/manager. */
    private function monthPreview(RosterSetting $setting, Carbon $month): array
    {
        $start = $month->copy()->startOfMonth();
        $days  = [];
        for ($d = $start->copy(); $d->month === $start->month; $d->addDay()) {
            $days[] = [
                'day'      => $d->day,
                'weekday'  => $d->dayOfWeekIso, // 1 = Senin
                'open'     => $setting->isOpenOn($d),
                'is_today' => $d->isToday(),
            ];
        }
        return [
            'label'  => $start->locale('id')->translatedFormat('F Y'),
            'offset' => $start->dayOfWeekIso - 1,
            'days'   => $days,
            'open'   => collect($days)->where('open', true)->count(),
        ];
    }

    /** Akun yang dibatasi jendela: punya ManageRosterSPVManager tapi tidak punya ManageRoster. */
    private function affectedAccounts(): ?int
    {
        try {
            $spv = User::permission('ManageRosterSPVManager')->pluck('id');
            $hr  = User::permission('ManageRoster')->pluck('id');
            return $spv->diff($hr)->count();
        } catch (\Throwable $e) {
            return null; // permission belum dibuat
        }
    }

    public function index()
    {
        $settings = RosterSetting::orderByDesc('is_active')->orderByDesc('id')->get()
            ->each(fn($s) => $s->hash = self::hashId($s->id));
        $current = $settings->firstWhere('is_active', true);

        return view('pages.RosterSetting.index', [
            'settings'   => $settings,
            'current'    => $current,
            'isOpen'     => (bool) $current?->isOpenOn(),
            'nextChange' => $current?->nextChange(),
            'months'     => $current ? [$this->monthPreview($current, now()), $this->monthPreview($current, now()->addMonthNoOverflow())] : [],
            'affected'   => $this->affectedAccounts(),
        ]);
    }

    public function create()
    {
        return view('pages.RosterSetting.edit', [
            'setting' => new RosterSetting(['open_day' => 20, 'close_day' => 28, 'is_active' => true]),
            'hash'    => null,
            'hasOtherActive' => RosterSetting::where('is_active', true)->exists(),
        ]);
    }

    public function edit(string $hashedId)
    {
        $setting = $this->findByHash($hashedId);

        return view('pages.RosterSetting.edit', [
            'setting' => $setting,
            'hash'    => $hashedId,
            'hasOtherActive' => RosterSetting::where('is_active', true)->where('id', '!=', $setting->id)->exists(),
        ]);
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'open_day'  => 'required|integer|between:1,31',
            'close_day' => 'required|integer|between:1,31',
        ], [
            'open_day.required'  => 'Tanggal buka wajib diisi.',
            'open_day.between'   => 'Tanggal buka harus antara 1 dan 31.',
            'close_day.required' => 'Tanggal tutup wajib diisi.',
            'close_day.between'  => 'Tanggal tutup harus antara 1 dan 31.',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        return $data;
    }

    /** Simpan; kalau aktif, pengaturan lain dinonaktifkan supaya hanya satu yang berlaku. */
    private function save(RosterSetting $setting, array $data): RosterSetting
    {
        return DB::transaction(function () use ($setting, $data) {
            $setting->fill($data)->save();
            if ($setting->is_active) {
                RosterSetting::where('id', '!=', $setting->id)->where('is_active', true)->update(['is_active' => false]);
            }
            return $setting;
        });
    }

    private function describe(RosterSetting $s): string
    {
        $span = $s->open_day <= $s->close_day ? "tanggal {$s->open_day} sampai {$s->close_day}" : "tanggal {$s->open_day} sampai {$s->close_day} bulan berikutnya";
        return $span . ($s->is_active ? ' (aktif)' : ' (nonaktif)');
    }

    public function store(Request $request)
    {
        $setting = $this->save(new RosterSetting(), $this->validated($request));

        activity('roster_setting')->causedBy(auth()->user())->performedOn($setting)->event('created')
            ->withProperties(['attributes' => $setting->only(['open_day', 'close_day', 'is_active'])])
            ->log('Jendela roster dibuat: ' . $this->describe($setting));

        return redirect()->route('rostersetting.index')->with('success', 'Jendela roster disimpan.');
    }

    public function update(Request $request, string $hashedId)
    {
        $setting = $this->findByHash($hashedId);
        $old     = $setting->only(['open_day', 'close_day', 'is_active']);
        $setting = $this->save($setting, $this->validated($request));

        activity('roster_setting')->causedBy(auth()->user())->performedOn($setting)->event('updated')
            ->withProperties(['old' => $old, 'attributes' => $setting->only(['open_day', 'close_day', 'is_active'])])
            ->log('Jendela roster diubah: ' . $this->describe($setting));

        return redirect()->route('rostersetting.index')->with('success', 'Jendela roster diperbarui.');
    }
}
