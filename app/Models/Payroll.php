<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;

class Payroll extends Model
{
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

    protected $table = 'payrolls';

    protected $fillable = [
        'employee_id',
        'payroll_period_id',
        'period_month',
        'period_year',
        'period_start',
        'period_end',

        'working_days',
        'attendance_days',
        'absent_days',

        'is_prorate',
        'prorate_days',
        'prorate_ratio',

        'basic_salary',
        'position_allowance',
        'daily_rate',

        'overtime_amount',
        'reimburse_amount',

        'gross_salary',
        'total_income',
        'total_deduction',
        'net_salary',

        'status',
        'approved_by',
        'approved_at',
        'paid_at',
        'note',
    ];

    protected $casts = [
        'period_start'       => 'date',
        'period_end'         => 'date',
        'is_prorate'         => 'boolean',
        'prorate_ratio'      => 'decimal:4',
        'basic_salary'       => 'decimal:2',
        'position_allowance' => 'decimal:2',
        'daily_rate'         => 'decimal:2',
        'overtime_amount'    => 'decimal:2',
        'reimburse_amount'   => 'decimal:2',
        'gross_salary'       => 'decimal:2',
        'total_income'       => 'decimal:2',
        'total_deduction'    => 'decimal:2',
        'net_salary'         => 'decimal:2',
        'approved_at'        => 'datetime',
        'paid_at'            => 'datetime',
    ];

    // ── Relasi ──
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function period()
    {
        return $this->belongsTo(PayrollPeriod::class, 'payroll_period_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    public function details()
    {
        return $this->hasMany(PayrollDetail::class, 'payroll_id');
    }

    public function incomeDetails()
    {
        return $this->hasMany(PayrollDetail::class, 'payroll_id')
            ->where('type', 'Income');
    }

    public function deductionDetails()
    {
        return $this->hasMany(PayrollDetail::class, 'payroll_id')
            ->where('type', 'Deduction');
    }

    // ── Scopes ──
    public function scopeForPeriod($query, string $periodId)
    {
        return $query->where('payroll_period_id', $periodId);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }
}