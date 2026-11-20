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
        'status',         
        'paid_at',        
        'paid_period',    
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

    /**
     * Saldo TOIL yang boleh dipakai untuk cuti di tanggal tertentu:
     * masih berlaku di tanggal itu dan lemburnya sudah terjadi (bukan lembur tanggal depan).
     */
    public function scopeUsableForLeave($query, $leaveDate)
    {
        return $query
            ->where('status', 'active')
            ->where('expires_at', '>=', Carbon::parse($leaveDate)->toDateString())
            ->whereHas('overtimeSubmission', function ($q) {
                $q->where('compensation_type', 'Toil')
                    ->whereDate('date', '<=', today());
            });
    }

    // ════════════════════════════════════════════════════════════════
    //   HELPERS
    // ════════════════════════════════════════════════════════════════

    /**
     * Mutex per karyawan untuk semua perubahan saldo TOIL.
     * Wajib dipanggil di dalam transaksi; kunci lepas saat commit / rollback.
     */
    public static function lockEmployees(array $employeeIds): void
    {
        Employee::whereIn('id', $employeeIds)
            ->orderBy('id')
            ->lockForUpdate()
            ->pluck('id');
    }

    /**
     * Saldo masih berlaku sampai akhir hari expires_at (sama dengan scopeActive).
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->lt(today());
    }

    public function getDaysUntilExpiredAttribute(): int
    {
        if (!$this->expires_at) return 0;
        // Dihitung per tanggal (bukan jam sekarang): berlaku sampai akhir hari expires_at
        return max(0, (int) today()->diffInDays($this->expires_at->copy()->startOfDay(), false));
    }

    /**
     * Status untuk ditampilkan: saldo TOIL berstatus active yang tanggalnya sudah lewat
     * dianggap expired walau command harian toil:expire-balances belum jalan.
     * Cash tidak hangus (tetap dibayar lewat payroll), jadi statusnya apa adanya.
     */
    public function effectiveStatus(?string $compensationType = null): string
    {
        $type = $compensationType ?? $this->overtimeSubmission?->compensation_type;

        return $this->status === 'active' && $type === 'Toil' && $this->isExpired() ? 'expired' : (string) $this->status;
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
        // Status akhir (paid / cancelled) tidak diubah otomatis
        if (!in_array($this->status, ['active', 'fully_used', 'expired'], true)) {
            return;
        }

        if ($this->remaining_hours <= 0) {
            $newStatus = 'fully_used';
        } elseif ($this->isExpired()) {
            $newStatus = 'expired';
        } else {
            // fully_used → active lagi kalau jam ditambah / leave di-cancel
            $newStatus = 'active';
        }

        if ($newStatus !== $this->status) {
            $this->update(['status' => $newStatus]);
        }
    }
}