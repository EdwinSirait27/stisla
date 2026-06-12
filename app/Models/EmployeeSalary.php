<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
class EmployeeSalary extends Model
{
   use HasFactory, LogsActivity;
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
    protected $table = 'employee_salaries';
    protected $fillable = [
        'employee_id',
        'basic_salary',
        'position_allowance',
        'daily_rate',
        'effective_date',
        'created_by',
        'meal_allowance',
        'transport_allowance',
        'house_allowance',
        'bpjs_ketenagakerjaan',
        'bpjs_kesehatan',
    ];
    protected $casts = [
        'basic_salary'       => 'decimal:2',
        'position_allowance' => 'decimal:2',
        'daily_rate'         => 'decimal:2',
        'meal_allowance'         => 'decimal:2',
        'transport_allowance'         => 'decimal:2',
        'house_allowance'         => 'decimal:2',
        'bpjs_ketenagakerjaan'         => 'decimal:2',
        'bpjs_kesehatan'         => 'decimal:2',
        'effective_date'     => 'date',
    ];
     public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('employee_salary')
            ->logOnly([
                'basic_salary',
                'position_allowance',
                'daily_rate',
                'effective_date',
                 'meal_allowance',
                 'house_allowance',
                 'transport_allowance',
        'bpjs_ketenagakerjaan',
        'bpjs_kesehatan',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => match($eventName) {
                'created' => 'Employee salary was created',
                'updated' => 'Employee salary was updated',
                'deleted' => 'Employee salary was deleted',
                default   => "Employee salary {$eventName}",
            });
    }

   
    public function tapActivity(Activity $activity, string $eventName): void
{
    $employeeName  = $this->employee?->employee_name ?? '-';
    $effectiveDate = $this->effective_date?->format('d/m/Y') ?? '-';
    $status        = $this->employee?->status_employee ?? '-';

    if ($eventName === 'created') {
        $activity->properties = $activity->properties->merge([
            'attributes' => [
                'employee_name'      => $employeeName,
                'status_employee'    => $status,
                'effective_date'     => $effectiveDate,
                'basic_salary'       => $this->basic_salary,
                'position_allowance' => $this->position_allowance,
                
                'daily_rate'         => $this->daily_rate,
                'meal_allowance' => $this->meal_allowance,
                'house_allowance' => $this->house_allowance,
                'transport_allowance' => $this->transport_allowance,
                'bpjs_kesehatan'            => $this->bpjs_kesehatan,
                'bpjs_ketenagakerjaan'      => $this->bpjs_ketenagakerjaan,
            ],
        ]);
    }

    if ($eventName === 'updated') {
        $activity->properties = $activity->properties->merge([
            'attributes' => [
                'employee_name'      => $employeeName,
                'status_employee'    => $status,
                'effective_date'     => $effectiveDate,
                'basic_salary'       => $this->basic_salary,
                'position_allowance' => $this->position_allowance,
                'daily_rate'         => $this->daily_rate,
                'meal_allowance' => $this->meal_allowance,
                'house_allowance' => $this->house_allowance,
                'transport_allowance' => $this->transport_allowance,
                'bpjs_kesehatan'            => $this->bpjs_kesehatan,
                'bpjs_ketenagakerjaan'      => $this->bpjs_ketenagakerjaan,
            ],
            'old' => [
                // Old value — nilai sebelum update
                'basic_salary'       => $this->getOriginal('basic_salary'),
                'position_allowance' => $this->getOriginal('position_allowance'),
                'daily_rate'         => $this->getOriginal('daily_rate'),
                'effective_date'     => $this->getOriginal('effective_date'),
                'meal_allowance'         => $this->getOriginal('meal_allowance'),
                'house_allowance'         => $this->getOriginal('house_allowance'),
                'transport_allowance'         => $this->getOriginal('transport_allowance'),
                'bpjs_kesehatan'         => $this->getOriginal('bpjs_kesehatan'),
                'bpjs_ketenagakerjaan'         => $this->getOriginal('bpjs_ketenagakerjaan'),
            ],
        ]);
    }

    if ($eventName === 'deleted') {
        $activity->properties = $activity->properties->merge([
            'old' => [
                'employee_name'      => $employeeName,
                'status_employee'    => $status,
                'effective_date'     => $effectiveDate,
                'basic_salary'       => $this->basic_salary,
                'position_allowance' => $this->position_allowance,
                'daily_rate'         => $this->daily_rate,
                'meal_allowance' => $this->meal_allowance,
                'transport_allowance' => $this->transport_allowance,
                'house_allowance' => $this->house_allowance,
                'bpjs_kesehatan'            => $this->bpjs_kesehatan,
                'bpjs_ketenagakerjaan'      => $this->bpjs_ketenagakerjaan,
            ],
        ]);
    }
}
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
    public function createdBy()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }
    // ── Ambil salary aktif untuk periode payroll ──
    public static function getActiveForPeriod(string $employeeId, int $month, int $year): ?self
    {
        $periodStart = \Carbon\Carbon::create($year, $month, 25)
            ->subMonth()
            ->setDay(26)
            ->toDateString();
        return self::where('employee_id', $employeeId)
            ->where('effective_date', '<=', $periodStart)
            ->latest('effective_date')
            ->first();
    }
}