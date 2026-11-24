<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

/**
 * Pengaturan TOIL & lembur (satu baris, id = 1). Diubah lewat halaman Pengaturan TOIL
 * (ToilSettingController, permission ManageToilSetting).
 *
 * Yang BUKAN pengaturan (aturan bisnis, tetap di kode): satu lembur per hari, DW selalu Cash
 * dan tidak bisa libur TOIL, saldo Cash tidak hangus tapi dibayar lewat payroll.
 *
 * Kalau tabel/baris belum ada (migrasi belum jalan), DEFAULTS dipakai: sama dengan nilai hardcode lama.
 */
class ToilSetting extends Model
{
    protected $table = 'toil_settings';

    public const DEFAULTS = [
        'toil_expiry_months'    => 3,
        'cash_expiry_months'    => 1,
        'expiring_soon_days'    => 14,
        'hours_per_day'         => 8,
        'max_leave_range_days'  => 31,
        'min_overtime_hours'    => 0.5,
        'max_overtime_hours'    => 24,
        'checkin_early_minutes' => 60,
    ];

    protected $fillable = [
        'toil_expiry_months',
        'cash_expiry_months',
        'expiring_soon_days',
        'hours_per_day',
        'max_leave_range_days',
        'min_overtime_hours',
        'max_overtime_hours',
        'checkin_early_minutes',
        'updated_by',
    ];

    protected $casts = [
        'toil_expiry_months'    => 'integer',
        'cash_expiry_months'    => 'integer',
        'expiring_soon_days'    => 'integer',
        'hours_per_day'         => 'float',
        'max_leave_range_days'  => 'integer',
        'min_overtime_hours'    => 'float',
        'max_overtime_hours'    => 'float',
        'checkin_early_minutes' => 'integer',
    ];

    private static ?self $cached = null;

    /** Pengaturan yang berlaku (dibaca sekali per request). */
    public static function current(): self
    {
        if (self::$cached) {
            return self::$cached;
        }

        $setting = null;
        try {
            $setting = self::find(1);
        } catch (\Throwable $e) {
            // tabel belum ada: pakai default
        }

        return self::$cached = $setting ?? new self(self::DEFAULTS);
    }

    /** Dipanggil setelah menyimpan, supaya sisa request membaca nilai baru. */
    public static function forgetCurrent(): void
    {
        self::$cached = null;
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    /** Tanggal hangus saldo dari lembur pada $date. */
    public function expiryDate($date, string $compensationType): Carbon
    {
        $months = $compensationType === 'Cash' ? $this->cash_expiry_months : $this->toil_expiry_months;

        return Carbon::parse($date)->addMonths($months);
    }

    /** Angka tanpa nol di belakang: 8.00 → "8", 0.50 → "0.5". */
    public static function num($value): string
    {
        return rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
    }
}
