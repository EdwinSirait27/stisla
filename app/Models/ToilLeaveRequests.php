<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class ToilLeaveRequests extends Model
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

    protected $table = 'toil_leave_requests_tables';

    protected $fillable = [
        'employee_id',
        'toil_balance_id',
        'approver_id',
        'hours_used',
        'original_shift_id',
        'leave_date',
        'reason',
        'status',           // 'Pending' | 'Approved' | 'Rejected' | 'Cancelled'
        'approved_at',
        'rejected_reason',
    ];

    protected $casts = [
        'hours_used'  => 'decimal:2',
        'leave_date'  => 'date',
        'approved_at' => 'datetime',
    ];

    // ════════════════════════════════════════════════════════════════
    //   RELATIONSHIPS
    // ════════════════════════════════════════════════════════════════

    /**
     * Karyawan yang request klaim.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    /**
     * Saldo yang dipakai untuk klaim.
     */
    public function balance()
    {
        return $this->belongsTo(Toilbalances::class, 'toil_balance_id', 'id');
    }

    /**
     * Atasan yang approve / reject.
     */
    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approver_id', 'id');
    }

    // ════════════════════════════════════════════════════════════════
    //   SCOPES
    // ════════════════════════════════════════════════════════════════

    public function scopePending($query)
    {
        return $query->where('status', 'Pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'Approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'Rejected');
    }

    // ════════════════════════════════════════════════════════════════
    //   HELPERS
    // ════════════════════════════════════════════════════════════════

    /**
     * Cek apakah request bisa diapprove (saldo masih cukup).
     */
    public function canBeApproved(): bool
    {
        if ($this->status !== 'Pending') return false;
        if (!$this->balance) return false;
        return $this->balance->remaining_hours >= $this->hours_used;
    }

    /**
     * Cek apakah request masih bisa di-cancel oleh karyawan.
     */
    public function canBeCancelled(): bool
    {
        return $this->status === 'Pending';
    }

    public function canBeCancelledByManager(): bool
    {
    return $this->status === 'Approved'
        && $this->leave_date >= today();
    }
}