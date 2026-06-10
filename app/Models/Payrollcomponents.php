<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Payrollcomponents extends Model
{
    use HasFactory;
    public $incrementing = false;
    protected $keyType = 'string';
    protected $attributes = [
        'is_fixed' => false,
        'is_employer_burden' => false,
    ];
    protected $casts = [
        'is_fixed' => 'boolean',
        'is_employer_burden' => 'boolean',
    ];
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = Uuid::uuid7()->toString();
            }
        });
    }
    protected $table = 'payroll_components';
    protected $fillable = [
        'component_name',
        'type',
        'is_fixed',
        'is_employer_burden',
    ];
    public static function getTypeOptions()
    {
        return [
            'Deduction' => 'Deduction',
            'Income' => 'Income'
        ];
    }
    public function setComponentNameAttribute($value)
    {
        $this->attributes['component_name'] = strtoupper($value);
    }
    public function scopeIncome($query)
    {
        return $query->where('type', 'Income');
    }

    public function scopeDeduction($query)
    {
        return $query->where('type', 'Deduction');
    }

    public function scopeFixed($query)
    {
        return $query->where('is_fixed', true);
    }

    public function scopeEmployerBurden($query)
    {
        return $query->where('is_employer_burden', true);
    }

    public function scopeEmployeeBurden($query)
    {
        return $query->where('is_employer_burden', false);
    }
}
