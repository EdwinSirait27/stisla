<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Contract extends Model
{
    use HasFactory, LogsActivity;
    protected $table = 'contract';
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
    protected $casts = [
        'basic_salary' => 'decimal:2',
        'positional_allowance' => 'decimal:2',
        'daily_rate' => 'decimal:2',
    ];
    protected $fillable = [
        'employee_id',
        'structure_id',
        'contract_type',
        'start_date',
        'end_date',
        'basic_salary',
        'positional_allowance',
        'daily_rate',
        'contract_status',
        'file_path',
        'notes',
    ];
    public static function getContractStatusOptions()
    {
        return [
            'Active' => 'Active',
            'Expired' => 'Expired',
            'Terminated' => 'Terminated'
        ];
    }
    public static function getContractTypeOptions()
    {
        return [
            'PKWT' => 'PKWT',
            'On Job Training' => 'On Job Training',
            'DW' => 'DW'
        ];
    }
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
    public function structuresnew()
    {
        return $this->belongsTo(Structuresnew::class, 'structure_id', 'id');
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'employee_id',
                'structure_id',
                'contract_type',
                'start_date',
                'end_date',
                'basic_salary',
                'positional_allowance',
                'daily_rate',
                'contract_status',
                'file_path',
                'notes',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Contract {$eventName}");
    }
}
