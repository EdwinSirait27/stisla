<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class PayrollDetail extends Model
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

    protected $table = 'payroll_details';

    protected $fillable = [
        'payroll_id',
        'payroll_component_id',
        'type',
        'amount',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    // ── Relasi ──
    public function payroll()
    {
        return $this->belongsTo(Payroll::class, 'payroll_id');
    }

    public function component()
    {
        return $this->belongsTo(Payrollcomponents::class, 'payroll_component_id');
    }

    // ── Scope ──
    public function scopeIncome($query)
    {
        return $query->where('type', 'Income');
    }

    public function scopeDeduction($query)
    {
        return $query->where('type', 'Deduction');
    }

    public function scopeEmployerBurden($query)
    {
        return $query->whereHas('component', fn($q) =>
            $q->where('is_employer_burden', true)
        );
    }

    public function scopeEmployeeBurden($query)
    {
        return $query->whereHas('component', fn($q) =>
            $q->where('is_employer_burden', false)
        );
    }
}

// App\Models\EmployeeSalary::where('employee_id', $emp->id)->where('effective_date', '<=', '2026-05-26')->latest('effective_date')->first();

// App\Models\Roster::where('employee_id', $emp->id)->whereBetween('date', ['2026-05-26', '2026-06-25'])->whereIn('day_type', ['Work', 'Public Holiday', 'Leave', 'Cuti Melahirkan'])->count();
// App\Models\EmployeeSalary::where('employee_id', '0196bf55-feb6-73d2-9ae2-5696b09b3275')->get(['effective_date', 'basic_salary']);