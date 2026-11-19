<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class RosterPHCarryover extends Model
{
    use HasUuids;

    protected $table = 'roster_ph_carryovers';

    protected $fillable = [
        'employee_id',
        'ph_date',
        'ph_name',
        'expired_at',
        'status',
        'used_date',
    ];

    protected $casts = [
        'ph_date'    => 'date',
        'expired_at' => 'date',
        'used_date'  => 'date',
    ];

    /**
     * Saldo yang boleh dipakai untuk hari pengganti di tanggal tertentu.
     * Saldo baru sah kalau karyawan terbukti masuk di hari PH: ada rekap
     * kehadiran yang dihitung (is_counted = 1, dari scan atau Manual Recap).
     */
    public function scopeUsableOn($query, $date)
    {
        $recapTable = (new Fingerprintrecap)->getTable();

        return $query
            ->where('status', 'available')
            ->whereDate('expired_at', '>=', $date)
            ->whereDate('ph_date', '<', $date)
            ->whereExists(function ($q) use ($recapTable) {
                $q->selectRaw('1')
                    ->from($recapTable)
                    ->whereColumn($recapTable . '.employee_id', 'roster_ph_carryovers.employee_id')
                    ->whereColumn($recapTable . '.date', 'roster_ph_carryovers.ph_date')
                    ->where($recapTable . '.is_counted', 1);
            });
    }

    /**
     * Karyawan menikmati PH langsung di tanggal PH-nya, sehingga saldo tanggal itu batal.
     * Mengembalikan false kalau PH tidak bisa dinikmati: saldonya sudah dipakai untuk
     * hari pengganti, atau karyawan sudah tercatat masuk di hari PH. Roster jangan ditimpa.
     */
    public static function enjoyPh(string $employeeId, string $phDate): bool
    {
        $attended = Fingerprintrecap::where('employee_id', $employeeId)
            ->whereDate('date', $phDate)
            ->where('is_counted', 1)
            ->exists();

        if ($attended) {
            return false;
        }

        $carryover = self::where('employee_id', $employeeId)->whereDate('ph_date', $phDate)->first();

        if (!$carryover || $carryover->status === 'cancelled') {
            return true;
        }

        if ($carryover->status === 'used') {
            return false;
        }

        $carryover->update(['status' => 'cancelled', 'used_date' => null]);

        return true;
    }

    /**
     * Hari pengganti di tanggal ini batal: saldonya kembali tersedia.
     */
    public static function refundUsedOn(string $employeeId, string $date): int
    {
        return self::where('employee_id', $employeeId)
            ->where('status', 'used')
            ->whereDate('used_date', $date)
            ->update(['status' => 'available', 'used_date' => null]);
    }

    /**
     * Masa berlaku saldo: akhir periode tanggal PH (tgl 25) + 2 periode.
     * Contoh: PH 17 Feb → 25 Apr; PH 31 Jan → periode berakhir 25 Feb → 25 Apr.
     */
    public static function expiryFor(string $phDate): Carbon
    {
        $date      = Carbon::parse($phDate);
        $periodEnd = $date->day >= 26
            ? $date->copy()->startOfMonth()->addMonth()->day(25)
            : $date->copy()->day(25);

        return $periodEnd->addMonthsNoOverflow(2);
    }

    /**
     * Karyawan dijadwalkan kerja di hari PH: saldo dibuat, atau diaktifkan lagi kalau
     * sempat dibatalkan. Saldo yang sudah dipakai tidak berubah.
     */
    public static function rememberWorkOnPh(string $employeeId, string $phDate, string $phName, string $expiredAt): void
    {
        $carryover = self::where('employee_id', $employeeId)->whereDate('ph_date', $phDate)->first();

        if (!$carryover) {
            self::create([
                'employee_id' => $employeeId,
                'ph_date'     => $phDate,
                'ph_name'     => $phName,
                'expired_at'  => $expiredAt,
                'status'      => 'available',
            ]);
        } elseif ($carryover->status === 'cancelled') {
            $carryover->update(['status' => 'available', 'used_date' => null]);
        }
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}