<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;

class Toilbalances extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType   = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = Uuid::uuid7()->toString();
            }
        });
    }

    protected $table = 'toil_balances_tables';

    protected $fillable = [
        'employee_id',
        'overtime_submission_id',
        'earned_hours',
        'used_hours',
        'expires_at',
        'status',         // 'active' | 'fully_used' | 'expired' | 'cancelled'
        'paid_at',        // (Cash) kapan masuk payroll
        'paid_period',    // (Cash) periode payroll, misal "2026-05"
    ];

    protected $casts = [
        'earned_hours' => 'decimal:2',
        'used_hours'   => 'decimal:2',
        'expires_at'   => 'date',
        'paid_at'      => 'datetime',
    ];

    // ════════════════════════════════════════════════════════════════
    //   RELATIONSHIPS
    // ════════════════════════════════════════════════════════════════

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    /**
     * Assignment yang menjadi sumber saldo.
     * NOTE: pakai class name 'Overtimesubmissions' (sesuai existing project).
     */
    public function overtimeSubmission()
    {
        return $this->belongsTo(Overtimesubmissions::class, 'overtime_submission_id', 'id');
    }

    /**
     * Leave requests yang pakai saldo ini.
     */
    public function leaveRequests()
    {
        return $this->hasMany(ToilLeaveRequests::class, 'toil_balance_id', 'id');
    }

    // ════════════════════════════════════════════════════════════════
    //   SCOPES
    // ════════════════════════════════════════════════════════════════

    public function scopeActive($query)
    {
        return $query
            ->where('status', 'active')
            ->where('expires_at', '>=', today());
    }

    /**
     * Scope: filter berdasarkan compensation_type via JOIN.
     * Pakai: ->ofType('Cash') atau ->ofType('Toil')
     */
    public function scopeOfType($query, string $compensationType)
    {
        return $query->whereHas('overtimeSubmission', function ($q) use ($compensationType) {
            $q->where('compensation_type', $compensationType);
        });
    }

    // ════════════════════════════════════════════════════════════════
    //   HELPERS
    // ════════════════════════════════════════════════════════════════

    public function getRemainingHoursAttribute(): float
    {
        return max(0, (float) $this->earned_hours - (float) $this->used_hours);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getDaysUntilExpiredAttribute(): int
    {
        if (!$this->expires_at) return 0;
        return max(0, (int) now()->diffInDays($this->expires_at, false));
    }

    /**
     * Hitung saldo aktif (Cash atau Toil) untuk karyawan tertentu.
     */
    public static function calculateActiveBalance(string $employeeId, string $compensationType): float
    {
        return (float) self::ofType($compensationType)
            ->active()
            ->where('employee_id', $employeeId)
            ->get()
            ->sum(fn($balance) => $balance->remaining_hours);
    }

    /**
     * Tandai saldo Cash sebagai sudah dibayar.
     */
    public function markAsPaid(string $period): void
    {
        $this->update([
            'paid_at'     => now(),
            'paid_period' => $period,
        ]);
    }

    /**
     * Refresh status berdasarkan kondisi terkini.
     * - active → expired (kalau lewat expires_at)
     * - active → fully_used (kalau saldo habis)
     */
    public function refreshStatus(): void
    {
        $newStatus = $this->status;

        if ($this->isExpired() && $this->status === 'active') {
            $newStatus = 'expired';
        } elseif ($this->remaining_hours <= 0 && $this->status === 'active') {
            $newStatus = 'fully_used';
        }

        if ($newStatus !== $this->status) {
            $this->update(['status' => $newStatus]);
        }
    }
}