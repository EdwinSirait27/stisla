<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Fingerprintrecap;
use App\Models\PublicHoliday;
use App\Models\Roster;
use App\Models\RosterPHCarryover;
use Carbon\Carbon;

/**
 * Menyesuaikan roster dan saldo PH setelah data PH berubah.
 * Hanya menyentuh periode berjalan ke depan, supaya periode lama yang
 * mungkin sudah masuk payroll tidak ikut berubah.
 */
class PublicHolidayRosterSync
{
    private array $stats = ['converted' => 0, 'removed' => 0, 'saldo' => 0, 'review' => 0];

    public static function currentPeriodStart(): Carbon
    {
        $today = today();

        return $today->day >= 26
            ? $today->copy()->day(26)
            : $today->copy()->startOfMonth()->subMonthNoOverflow()->day(26);
    }

    public static function isSyncable(string $date): bool
    {
        return Carbon::parse($date)->gte(self::currentPeriodStart());
    }

    /**
     * @param string[] $dates
     */
    public function syncDates(array $dates): self
    {
        foreach (array_unique($dates) as $date) {
            if (self::isSyncable($date)) {
                $this->syncDate(Carbon::parse($date)->toDateString());
            }
        }

        return $this;
    }

    public function message(): string
    {
        $parts = [];
        if ($this->stats['converted']) $parts[] = "{$this->stats['converted']} roster dijadikan Public Holiday";
        if ($this->stats['removed'])   $parts[] = "{$this->stats['removed']} roster PH dikosongkan dan perlu dijadwalkan ulang";
        if ($this->stats['saldo'])     $parts[] = "{$this->stats['saldo']} saldo PH dibuat karena karyawan sudah masuk";
        if ($this->stats['review'])    $parts[] = "{$this->stats['review']} data perlu dicek manual (cuti, sakit, TOIL, atau saldo yang sudah dipakai)";

        return $parts ? ' Roster: ' . implode(', ', $parts) . '.' : '';
    }

    private function syncDate(string $date): void
    {
        $holidays = [];

        foreach (Roster::with('employee')->whereDate('date', $date)->get() as $roster) {
            if (!$roster->employee) continue;

            $phDates = $this->holidayFor($roster->employee, $date, $holidays);

            array_key_exists($date, $phDates)
                ? $this->applyHoliday($roster, $date, $phDates[$date])
                : $this->removeHoliday($roster, $date);
        }

        // Saldo tanggal ini yang tidak lagi punya dasar PH
        foreach (RosterPHCarryover::with('employee')->whereDate('ph_date', $date)->get() as $carryover) {
            if (!$carryover->employee) continue;
            if (array_key_exists($date, $this->holidayFor($carryover->employee, $date, $holidays))) continue;

            if ($carryover->status === 'available') {
                $carryover->update(['status' => 'cancelled', 'used_date' => null]);
            } elseif ($carryover->status === 'used') {
                $this->stats['review']++;
            }
        }
    }

    private function holidayFor(Employee $employee, string $date, array &$holidays): array
    {
        return $holidays[$employee->id] ??= PublicHoliday::datesForEmployee($employee, $date, $date);
    }

    private function applyHoliday(Roster $roster, string $date, ?string $remark): void
    {
        $employeeId = $roster->employee_id;

        if ($roster->day_type === 'Public Holiday') {
            // Tadinya hari pengganti, sekarang PH asli: saldonya tidak perlu terpakai
            RosterPHCarryover::refundUsedOn($employeeId, $date);
            $roster->update(['notes' => $remark]);
            return;
        }

        if (!in_array($roster->day_type, ['Work', 'Off'], true)) {
            $this->stats['review']++;
            return;
        }

        $attended = Fingerprintrecap::where('employee_id', $employeeId)
            ->whereDate('date', $date)
            ->where('is_counted', 1)
            ->exists();

        $alreadyActive = RosterPHCarryover::where('employee_id', $employeeId)
            ->whereDate('ph_date', $date)
            ->whereIn('status', ['available', 'used'])
            ->exists();

        // Work tetap Work kalau karyawan sudah masuk, atau memang sengaja dijadwalkan
        // kerja di hari PH (saldonya sudah ada). Tanpa ini, edit keterangan PH atau
        // menambah PH tipe lain di tanggal yang sama akan menimpa jadwal kerja itu.
        if ($roster->day_type === 'Work' && ($attended || $alreadyActive)) {
            RosterPHCarryover::rememberWorkOnPh(
                $employeeId,
                $date,
                $remark ?? 'Public Holiday',
                RosterPHCarryover::expiryFor($date)->toDateString()
            );
            $roster->update(['notes' => $remark]);

            if (!$alreadyActive) {
                $this->stats['saldo']++;
            }
            return;
        }

        if (RosterPHCarryover::enjoyPh($employeeId, $date)) {
            $roster->update(['day_type' => 'Public Holiday', 'shift_id' => null, 'notes' => $remark]);
            $this->stats['converted']++;
        } else {
            $this->stats['review']++;
        }
    }

    private function removeHoliday(Roster $roster, string $date): void
    {
        if ($roster->day_type !== 'Public Holiday') return;

        // Hari pengganti memang bukan PH kalender, jadi tetap sah
        $isSubstitute = RosterPHCarryover::where('employee_id', $roster->employee_id)
            ->where('status', 'used')
            ->whereDate('used_date', $date)
            ->exists();

        if ($isSubstitute) return;

        $roster->delete();
        $this->stats['removed']++;
    }
}
