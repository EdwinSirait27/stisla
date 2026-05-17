<?php

// namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
// use Ramsey\Uuid\Uuid;
// use Spatie\Activitylog\Traits\LogsActivity;
// use Spatie\Activitylog\LogOptions;

// class Contract extends Model
// {
//     use HasFactory, LogsActivity;
//     protected $table = 'contract';
//     public $incrementing = false;
//     protected $keyType = 'string';
//     protected static function boot()
//     {
//         parent::boot();
//         static::creating(function ($model) {
//             if (!$model->getKey()) {
//                 $model->{$model->getKeyName()} = Uuid::uuid7()->toString();
//             }
//         });
//     }
//     protected $casts = [
//         'basic_salary' => 'decimal:2',
//         'positional_allowance' => 'decimal:2',
//         'daily_rate' => 'decimal:2',
//     ];
//     protected $fillable = [
//         'employee_id',
//         'structure_id',
//         'contract_type',
//         'start_date',
//         'end_date',
//         'basic_salary',
//         'positional_allowance',
//         'daily_rate',
//         'contract_status',
//         'file_path',
//         'notes',
//     ];
//     public static function getContractStatusOptions()
//     {
//         return [
//             'Active' => 'Active',
//             'Expired' => 'Expired',
//             'Terminated' => 'Terminated'
//         ];
//     }
//     public static function getContractTypeOptions()
//     {
//         return [
//             'PKWT' => 'PKWT',
//             'On Job Training' => 'On Job Training',
//             'DW' => 'DW'
//         ];
//     }
//     public function employee()
//     {
//         return $this->belongsTo(Employee::class, 'employee_id', 'id');
//     }
//     public function structuresnew()
//     {
//         return $this->belongsTo(Structuresnew::class, 'structure_id', 'id');
//     }
//     public function getActivitylogOptions(): LogOptions
//     {
//         return LogOptions::defaults()
//             ->logOnly([
//                 'employee_id',
//                 'structure_id',
//                 'contract_type',
//                 'start_date',
//                 'end_date',
//                 'basic_salary',
//                 'positional_allowance',
//                 'daily_rate',
//                 'contract_status',
//                 'file_path',
//                 'notes',
//             ])
//             ->logOnlyDirty()
//             ->dontSubmitEmptyLogs()
//             ->setDescriptionForEvent(fn(string $eventName) => "Contract {$eventName}");
//     }
// }

// namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
// use Ramsey\Uuid\Uuid;
// use Spatie\Activitylog\Traits\LogsActivity;
// use Spatie\Activitylog\LogOptions;

// class Contract extends Model
// {
//     use HasFactory, LogsActivity;

//     protected $table = 'contract';
//     public $incrementing = false;
//     protected $keyType = 'string';

//     protected static function boot()
//     {
//         parent::boot();
//         static::creating(function ($model) {
//             if (!$model->getKey()) {
//                 $model->{$model->getKeyName()} = Uuid::uuid7()->toString();
//             }
//         });
//     }

//     protected $casts = [
//         'basic_salary'          => 'decimal:2',
//         'positional_allowance'  => 'decimal:2',
//         'daily_rate'            => 'decimal:2',
//         'start_date'            => 'date',
//         'end_date'              => 'date',
//     ];

//     protected $fillable = [
//         'employee_id',
//         'sk_letter_id',       // relasi ke SK yang menerbitkan contract ini
//         'structure_id',
//         'position_id',        // snapshot saat contract dibuat
//         'group_id',           // snapshot saat contract dibuat
//         'grading_id',         // snapshot saat contract dibuat
//         'company_id',         // snapshot saat contract dibuat
//         'department_id',      // snapshot saat contract dibuat
//         'contract_type',
//         'start_date',
//         'end_date',
//         'basic_salary',
//         'positional_allowance',
//         'daily_rate',
//         'contract_status',
//         'file_path',
//         'notes',
//     ];

//     public static function getContractStatusOptions(): array
//     {
//         return [
//             'Active'     => 'Active',
//             'Expired'    => 'Expired',
//             'Terminated' => 'Terminated',
//         ];
//     }

//     public static function getContractTypeOptions(): array
//     {
//         return [
//             'PKWT'             => 'PKWT',
//             'On Job Training'  => 'On Job Training',
//             'DW'               => 'DW',
//         ];
//     }

//     public function employee()
//     {
//         return $this->belongsTo(Employee::class, 'employee_id', 'id');
//     }

//     public function skLetter()
//     {
//         return $this->belongsTo(SkLetter::class, 'sk_letter_id', 'id');
//     }

//     public function structuresnew()
//     {
//         return $this->belongsTo(Structuresnew::class, 'structure_id', 'id');
//     }

//     public function position()
//     {
//         return $this->belongsTo(Position::class, 'position_id', 'id');
//     }

//     public function group()
//     {
//         return $this->belongsTo(Groups::class, 'group_id', 'id');
//     }

//     public function grading()
//     {
//         return $this->belongsTo(Grading::class, 'grading_id', 'id');
//     }

//     public function company()
//     {
//         return $this->belongsTo(Company::class, 'company_id', 'id');
//     }

//     public function department()
//     {
//         return $this->belongsTo(Departments::class, 'department_id', 'id');
//     }

//     public function getActivitylogOptions(): LogOptions
//     {
//         return LogOptions::defaults()
//             ->logFillable()
//             ->logOnlyDirty()
//             ->dontSubmitEmptyLogs()
//             ->useLogName('Contract')
//             ->setDescriptionForEvent(function (string $eventName) {
//                 $actor = auth()->user()?->employee?->employee_name
//                     ?? auth()->user()?->name
//                     ?? 'system';

//                 $target = optional($this->employee)->employee_name ?? 'Unknown Employee';

//                 $changes = $this->getChanges();
//                 $original = $this->getOriginal();

//                 $relationNames = [
//                     'employee_id'  => fn($id) => optional(Employee::find($id))->employee_name,
//                     'structure_id' => fn($id) => optional(Structuresnew::with('submissionposition.positionRelation')->find($id))->submissionposition?->positionRelation?->name,
//                     'position_id'  => fn($id) => optional(Position::find($id))->name,
//                     'company_id'   => fn($id) => optional(Company::find($id))->name,
//                     'department_id'=> fn($id) => optional(Departments::find($id))->name,
//                 ];

//                 $fieldLabels = [
//                     'employee_id'          => 'Employee',
//                     'sk_letter_id'         => 'SK Letter',
//                     'structure_id'         => 'Structure',
//                     'position_id'          => 'Position',
//                     'contract_type'        => 'Contract type',
//                     'start_date'           => 'Start date',
//                     'end_date'             => 'End date',
//                     'basic_salary'         => 'Basic salary',
//                     'positional_allowance' => 'Positional allowance',
//                     'daily_rate'           => 'Daily rate',
//                     'contract_status'      => 'Status',
//                 ];

//                 $changesInfo = '';
//                 if ($eventName === 'updated' && !empty($changes)) {
//                     $details = collect($changes)
//                         ->map(function ($new, $field) use ($original, $relationNames, $fieldLabels) {
//                             $old   = $original[$field] ?? 'null';
//                             $label = $fieldLabels[$field] ?? ucfirst(str_replace('_', ' ', $field));

//                             if (isset($relationNames[$field])) {
//                                 $oldLabel = $relationNames[$field]($old) ?? $old;
//                                 $newLabel = $relationNames[$field]($new) ?? $new;
//                                 return "{$label}: {$oldLabel} → {$newLabel}";
//                             }

//                             if ($old == $new) return null;
//                             return "{$label}: {$old} → {$new}";
//                         })
//                         ->filter()
//                         ->values()
//                         ->implode(', ');

//                     $changesInfo = $details ? "Changes: {$details}" : '';
//                 }

//                 return "Contract for {$target} has been {$eventName} by {$actor}. {$changesInfo}";
//             });
//     }
// }
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Contract extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'contracts';
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
        'basic_salary'          => 'decimal:2',
        'positional_allowance'  => 'decimal:2',
        'daily_rate'            => 'decimal:2',
        'start_date'            => 'date',
        'end_date'              => 'datetime',
        'signed_by_employee_at' => 'datetime',
    ];

    protected $fillable = [
        'employee_id',
        'sk_letter_id',
        'issuer_company_id',
        'structure_id',
        'position_id',
        'group_id',
        'grading_id',
        'company_id',
        'department_id',
        'signed_by_employee',
        'signed_by_employee_at',
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

    public static function getContractStatusOptions(): array
    {
        return [
            'Active'     => 'Active',
            'Expired'    => 'Expired',
            'Terminated' => 'Terminated',
        ];
    }

    public static function getContractTypeOptions(): array
    {
        return [
            'PKWT'            => 'PKWT',
            'On Job Training' => 'On Job Training',
            'DW'              => 'DW',
        ];
    }
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function skLetter()
    {
        return $this->belongsTo(SkLetter::class, 'sk_letter_id', 'id');
    }

    public function issuerCompany()
    {
        return $this->belongsTo(Company::class, 'issuer_company_id', 'id');
    }

    public function structure()
    {
        return $this->belongsTo(Structuresnew::class, 'structure_id', 'id');
    }

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id', 'id');
    }

    public function group()
    {
        return $this->belongsTo(Groups::class, 'group_id', 'id');
    }

    public function grading()
    {
        return $this->belongsTo(Grading::class, 'grading_id', 'id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function department()
    {
        return $this->belongsTo(Departments::class, 'department_id', 'id');
    }

    public function signedByEmployee()
    {
        return $this->belongsTo(Employee::class, 'signed_by_employee', 'id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('Contract')
            ->setDescriptionForEvent(function (string $eventName) {
                $actor = auth()->user()?->employee?->employee_name
                    ?? auth()->user()?->name
                    ?? 'system';

                $target = optional($this->employee)->employee_name ?? 'Unknown Employee';

                $changes  = $this->getChanges();
                $original = $this->getOriginal();

                $relationNames = [
                    'employee_id'        => fn($id) => optional(Employee::find($id))->employee_name,
                    'sk_letter_id'       => fn($id) => optional(SkLetter::find($id))->id,
                    'issuer_company_id'  => fn($id) => optional(Company::find($id))->name,
                    'structure_id'       => fn($id) => optional(
                        Structuresnew::with('submissionposition.positionRelation')->find($id)
                    )->submissionposition?->positionRelation?->name,
                    'position_id'        => fn($id) => optional(Position::find($id))->name,
                    'group_id'           => fn($id) => optional(Groups::find($id))->group_name,
                    'grading_id'         => fn($id) => optional(Grading::find($id))->grading_name,
                    'company_id'         => fn($id) => optional(Company::find($id))->name,
                    'department_id'      => fn($id) => optional(Departments::find($id))->name,
                    'signed_by_employee' => fn($id) => optional(Employee::find($id))->employee_name,
                ];

                $fieldLabels = [
                    'employee_id'           => 'Employee',
                    'sk_letter_id'          => 'SK letter',
                    'issuer_company_id'     => 'Issuer company',
                    'structure_id'          => 'Structure',
                    'position_id'           => 'Position',
                    'group_id'              => 'Group',
                    'grading_id'            => 'Grading',
                    'company_id'            => 'Employee company',
                    'department_id'         => 'Department',
                    'signed_by_employee'    => 'Signed by',
                    'signed_by_employee_at' => 'Signed at',
                    'contract_type'         => 'Contract type',
                    'start_date'            => 'Start date',
                    'end_date'              => 'End date',
                    'basic_salary'          => 'Basic salary',
                    'positional_allowance'  => 'Positional allowance',
                    'daily_rate'            => 'Daily rate',
                    'contract_status'       => 'Status',
                ];

                $changesInfo = '';
                if ($eventName === 'updated' && !empty($changes)) {
                    $details = collect($changes)
                        ->map(function ($new, $field) use ($original, $relationNames, $fieldLabels) {
                            $old   = $original[$field] ?? 'null';
                            $label = $fieldLabels[$field] ?? ucfirst(str_replace('_', ' ', $field));

                            if (isset($relationNames[$field])) {
                                $oldLabel = $relationNames[$field]($old) ?? $old;
                                $newLabel = $relationNames[$field]($new) ?? $new;
                                return "{$label}: {$oldLabel} → {$newLabel}";
                            }

                            if ($old == $new) return null;
                            return "{$label}: {$old} → {$new}";
                        })
                        ->filter()
                        ->values()
                        ->implode(', ');

                    $changesInfo = $details ? "Changes: {$details}" : '';
                }

                return "Contract for {$target} has been {$eventName} by {$actor}. {$changesInfo}";
            });
    }
}