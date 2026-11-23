<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Shifts extends Model
{
    use HasFactory;
    protected $table = 'shifts_tables';
    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = Uuid::uuid7()->toString();
            }
        });
    }
    protected $fillable = [
        'store_id',
        'shift_name',
        'code',
        'start_time',
        'end_time',
        'last_sync',
        'is_holiday',
        'is_split',
        'break_start',
        'break_end',
    ];

    protected $casts = [
        'is_split' => 'boolean',
    ];

    /** Ikut ke JSON (dropdown roster, API) supaya tampilan jam split shift seragam. */
    protected $appends = ['time_label'];
    public function setShiftNameAttribute($value)
{
    $upper = strtoupper($value);
    $this->attributes['shift_name'] = $upper;
    $this->attributes['code']       = substr($upper, 0, 2);
}
    /** Split shift yang datanya lengkap (dua blok). */
    public function isSplit(): bool
    {
        return (bool) $this->is_split && $this->break_start && $this->break_end;
    }

    /**
     * Blok kerja shift dalam format H:i:s.
     * Shift biasa: satu blok [start, end]. Split shift: [[start, break_start], [break_end, end]].
     */
    public function blocks(): array
    {
        $t = fn($v) => $v ? substr((string) $v, 0, 8) : null;

        return $this->isSplit()
            ? [[$t($this->start_time), $t($this->break_start)], [$t($this->break_end), $t($this->end_time)]]
            : [[$t($this->start_time), $t($this->end_time)]];
    }

    /**
     * Titik tengah jeda split shift (H:i:s). Scan sebelum titik ini milik blok 1, sesudahnya blok 2.
     * Null untuk shift biasa.
     */
    public function splitBoundary(): ?string
    {
        if (!$this->isSplit()) return null;

        $toMin = fn($v) => (int) substr($v, 0, 2) * 60 + (int) substr($v, 3, 2);
        $mid   = intdiv($toMin((string) $this->break_start) + $toMin((string) $this->break_end), 2);

        return sprintf('%02d:%02d:00', intdiv($mid, 60), $mid % 60);
    }

    /** Durasi kerja terjadwal (menit), jeda split shift tidak dihitung. */
    public function scheduledMinutes(): ?int
    {
        $total = 0;
        foreach ($this->blocks() as [$start, $end]) {
            if (!$start || !$end) return null;
            $s = (int) substr($start, 0, 2) * 60 + (int) substr($start, 3, 2);
            $e = (int) substr($end, 0, 2) * 60 + (int) substr($end, 3, 2);
            $total += $e > $s ? $e - $s : $e + 1440 - $s; // lewat tengah malam
        }

        return $total;
    }

    /** "07:00-15:00", atau "07:00-11:00 / 17:00-21:00" untuk split shift. Kosong kalau kolom jam tidak di-select. */
    public function getTimeLabelAttribute(): string
    {
        if (!array_key_exists('start_time', $this->attributes) || !array_key_exists('end_time', $this->attributes)) {
            return '';
        }

        return collect($this->blocks())
            ->map(fn($b) => substr((string) $b[0], 0, 5) . '-' . substr((string) $b[1], 0, 5))
            ->implode(' / ');
    }

    public function store()
    {
        return $this->belongsTo(Stores::class, 'store_id', 'id');
    }

}
