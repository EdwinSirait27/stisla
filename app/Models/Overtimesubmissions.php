<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;

class Overtimesubmissions extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType   = 'string';

    protected static function boot()
    {
        parent::boot();

        // Auto-generate UUID v7
        static::creating(function ($model) {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = Uuid::uuid7()->toString();
            }
        });

        // ✅ Saat CREATE langsung Approved
        static::created(function ($model) {
            if ($model->status === 'Approved') {
                $model->createOrUpdateBalance();
            }
        });

        // ✅ Saat UPDATE status berubah jadi Approved
        static::saved(function ($model) {
            if ($model->status === 'Approved' && $model->wasChanged('status')) {
                $model->createOrUpdateBalance();
            }
        });
    }

    protected $table = 'overtime_submissions_tables';

    protected $fillable = [
        'employee_id',
        'date',
        'start_time',
        'end_time',
        'total_hours',
        'reason',
        'compensation_type',
        'status',
        'approver_id',
        'approved_at',
        'payroll_period_id',
        'notes',
    ];

    protected $casts = [
        'date'        => 'date',
        'total_hours' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    // ════════════════════════════════════════════════════════════════
    //   RELATIONSHIPS
    // ════════════════════════════════════════════════════════════════

    public function employees()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approver_id', 'id');
    }

    public function balance()
    {
        return $this->hasOne(Toilbalances::class, 'overtime_submission_id', 'id');
    }

    public function payrolls()
    {
        return $this->belongsTo(Payrolls::class, 'payroll_period_id', 'id');
    }

    // ════════════════════════════════════════════════════════════════
    //   SCOPES
    // ════════════════════════════════════════════════════════════════

    public function scopeCash($query)
    {
        return $query->where('compensation_type', 'Cash');
    }

    public function scopeToil($query)
    {
        return $query->where('compensation_type', 'Toil');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'Approved');
    }

    // ════════════════════════════════════════════════════════════════
    //   HELPER METHODS
    // ════════════════════════════════════════════════════════════════

    public function createOrUpdateBalance(): void
    {
        $expiresAt = $this->compensation_type === 'Cash'
            ? Carbon::parse($this->date)->addMonth()
            : Carbon::parse($this->date)->addMonths(3);

        Toilbalances::updateOrCreate(
            ['overtime_submission_id' => $this->id],
            [
                'employee_id'  => $this->employee_id,
                'earned_hours' => $this->total_hours,
                'used_hours'   => 0,
                'expires_at'   => $expiresAt,
                'status'       => 'active',
            ]
        );
    }
}