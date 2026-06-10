<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Carbon\Carbon;

class PayrollPeriod extends Model
{
    use LogsActivity;

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

    protected $table = 'payroll_periods';

    protected $fillable = [
        'period_month',
        'period_year',
        'period_start',
        'period_end',
        'status',
        'created_by',
        'locked_by',
        'locked_at',
        'note',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end'   => 'date',
        'locked_at'    => 'datetime',
    ];

    // ── Spatie ActivityLog ──
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('payroll_period')
            ->logOnly(['period_month', 'period_year', 'status', 'note'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => match($eventName) {
                'created' => 'Payroll period was created',
                'updated' => 'Payroll period was updated',
                'deleted' => 'Payroll period was deleted',
                default   => "Payroll period {$eventName}",
            });
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        $attributes = [
            'period'     => $this->period_month . '/' . $this->period_year,
            'start'      => $this->period_start?->format('d/m/Y'),
            'end'        => $this->period_end?->format('d/m/Y'),
            'status'     => $this->status,
        ];

        if ($eventName === 'updated') {
            $activity->properties = $activity->properties->merge([
                'attributes' => $attributes,
                'old' => [
                    'status' => $this->getOriginal('status'),
                    'note'   => $this->getOriginal('note'),
                ],
            ]);
        } else {
            $activity->properties = $activity->properties->merge([
                'attributes' => $attributes,
            ]);
        }
    }
      public function setNoteAttribute($value)
    {
        $this->attributes['note'] = strtoupper($value);
    }

    // ── Relasi ──
    public function createdBy()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    public function lockedBy()
    {
        return $this->belongsTo(Employee::class, 'locked_by');
    }

    public function payrolls()
    {
        return $this->hasMany(Payroll::class, 'payroll_period_id');
    }

    // ── Helper generate periode dari bulan & tahun ──
    public static function generatePeriod(int $month, int $year): array
    {
        $periodEnd   = Carbon::create($year, $month, 25);
        $periodStart = Carbon::create($year, $month, 25)->subMonth()->setDay(26);

        return [
            'period_month' => $month,
            'period_year'  => $year,
            'period_start' => $periodStart->toDateString(),
            'period_end'   => $periodEnd->toDateString(),
        ];
    }

    // ── Scope ──
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeLocked($query)
    {
        return $query->where('status', 'locked');
    }

    // ── Helper label bulan ──
    public function getPeriodLabelAttribute(): string
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
            4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return ($months[$this->period_month] ?? $this->period_month) . ' ' . $this->period_year;
    }

    // ── Status helpers ──
    public function isOpen(): bool   { return $this->status === 'open'; }
    public function isClosed(): bool { return $this->status === 'closed'; }
    public function isLocked(): bool { return $this->status === 'locked'; }
}