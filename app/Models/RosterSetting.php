<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Jendela tanggal setiap bulan saat SPV/manager (ManageRosterSPVManager tanpa ManageRoster)
 * boleh mengubah roster. HR (ManageRoster) tidak dibatasi. Hanya satu pengaturan yang aktif.
 * Jendela boleh lintas bulan, misal buka tanggal 25 dan tutup tanggal 3 bulan berikutnya.
 */
class RosterSetting extends Model
{
    use HasFactory;

    protected $table = 'roster_settings';

    // Tabel memakai $table->id() (angka auto increment) dan punya created_at/updated_at
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $casts = [
        'open_day'  => 'integer',
        'close_day' => 'integer',
        'is_active' => 'boolean',
    ];

    protected $fillable = [
        'open_day',
        'close_day',
        'is_active',
    ];

    /** Pengaturan yang sedang berlaku. */
    public static function current(): ?self
    {
        return self::where('is_active', true)->orderByDesc('id')->first();
    }

    /** Apakah tanggal ini (default: hari ini) berada di dalam jendela. */
    public function isOpenOn(?Carbon $date = null): bool
    {
        $day = ($date ?? now())->day;

        if ($this->open_day <= $this->close_day) {
            return $day >= $this->open_day && $day <= $this->close_day;
        }

        // Lintas bulan: misal buka 25, tutup 3
        return $day >= $this->open_day || $day <= $this->close_day;
    }

    /** Tanpa pengaturan aktif, jendela dianggap tertutup. */
    public static function isWithinWindow(): bool
    {
        return (bool) self::current()?->isOpenOn();
    }

    /** Tanggal berikutnya (setelah hari ini) saat status buka/tutup berubah, maksimal 62 hari ke depan. */
    public function nextChange(?Carbon $from = null): ?Carbon
    {
        $date  = ($from ?? today())->copy();
        $state = $this->isOpenOn($date);
        for ($i = 1; $i <= 62; $i++) {
            $d = $date->copy()->addDays($i);
            if ($this->isOpenOn($d) !== $state) {
                return $d;
            }
        }
        return null;
    }
}
