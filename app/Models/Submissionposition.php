<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Submissionposition extends Model
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
    protected $table = 'submission_position_tables';
    protected $fillable = [
        'employee_id',
        'role_summary',
        'key_respon',
        'qualifications',
        'work_location',
        'reason_reject',
        'reason_reject_dir',
        'type',
        'status',
        'notes',
        'notes_hr',
        'notes_dir',
        'salary_hr',
        'salary_hr_end',
        'salary_counter',
        'salary_counter_end',
        'store_id',
        'position_id',
        'approver_1',
        'approver_2',
    ];
    public function submitter()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
    public function store()
    {
        return $this->belongsTo(Stores::class, 'store_id', 'id');
    }
    public function positionRelation()
    {
        return $this->belongsTo(Position::class, 'position_id', 'id');
    }
    public function approver1()
    {
        return $this->belongsTo(Employee::class, 'approver_1', 'id');
    }
    public function approver2()
    {
        return $this->belongsTo(Employee::class, 'approver_2', 'id');
    }
    //  public function getActivitylogOptions(): LogOptions
    // {
    //     return LogOptions::defaults()
    //         ->logFillable()
    //         ->useLogName('Submissionposition')
    //         ->setDescriptionForEvent(function (string $eventName) {
    //             $actor = auth()->user()->employee->employee_name
    //                 ?? auth()->user()->name
    //                 ?? 'system';

    //             $target = optional($this->submitter)->employee_name ?? 'Unknown Employee';

    //             $changes = $this->getChanges();
    //             $original = $this->getOriginal();

    //             $relationNames = [
    //                 'position_id' => fn($id) => optional(Position::find($id))->name,
    //                 'employee_id' => fn($id) => optional(Employee::find($id))->employee_name,
    //                 'store_id' => fn($id) => optional(Stores::find($id))->name,
    //                 'approver_1' => fn($id) => optional(Employee::find($id))->employee_name,
    //                 'approver_2' => fn($id) => optional(Employee::find($id))->employee_name,
    //             ];

    //             $changesInfo = '';
    //             if ($eventName === 'updated' && !empty($changes)) {
    //                 $details = collect($changes)->map(function ($new, $field) use ($original, $relationNames) {
    //                     $old = $original[$field] ?? 'null';

    //                     if (isset($relationNames[$field])) {
    //                         $oldLabel = $relationNames[$field]($old) ?? $old;
    //                         $newLabel = $relationNames[$field]($new) ?? $new;
    //                         return "{$field}: {$oldLabel} → {$newLabel}";
    //                     }

    //                     if ($old == $new) return null;
    //                     return "{$field}: {$old} → {$new}";
    //                 })
    //                     ->filter()
    //                     ->values()
    //                     ->implode(', ');

    //                 $changesInfo = $details ? "Changes: {$details}" : '';
    //             }

    //             return "Submissionposition for {$target} has been {$eventName} by {$actor}. {$changesInfo}";
    //         });
    // }
    public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()
        ->logFillable()
        ->useLogName('Submissionposition')
        ->setDescriptionForEvent(function (string $eventName) {
            $actor = auth()->user()->employee->employee_name
                ?? auth()->user()->name
                ?? 'system';

            $target = optional($this->submitter)->employee_name ?? 'Unknown Employee';

            $changes = $this->getChanges();
            $original = $this->getOriginal();

            // Mapping relasi ID → nama entitas
            $relationNames = [
                'position_id' => fn($id) => optional(Position::find($id))->name,
                'employee_id' => fn($id) => optional(Employee::find($id))->employee_name,
                'store_id' => fn($id) => optional(Stores::find($id))->name,
                'approver_1' => fn($id) => optional(Employee::find($id))->employee_name,
                'approver_2' => fn($id) => optional(Employee::find($id))->employee_name,
            ];

            // Mapping label kolom → label human readable
            $fieldLabels = [
                'salary_hr' => 'Salary from HR',
                'salary_hr_end' => 'Salary HR set to ',
                'salary_counter' => 'Salary from DIR ',
                'salary_counter_end' => 'Salary Dir set to ',
                'updated_at' => 'Last Updated',
                'status' => 'Status',
                'approver_1' => 'HRD Verifier',
                'approver_2' => 'DIR Verivier',
                'store_id' => 'Location',
                'position_id' => 'Position Name',
            ];

            $changesInfo = '';
            if ($eventName === 'updated' && !empty($changes)) {
                $details = collect($changes)->map(function ($new, $field) use ($original, $relationNames, $fieldLabels) {
                    $old = $original[$field] ?? 'null';

                    // Ganti label field dengan nama yang lebih manusiawi
                    $label = $fieldLabels[$field] ?? ucfirst(str_replace('_', ' ', $field));

                    // Jika field termasuk relasi, tampilkan nama relasi
                    if (isset($relationNames[$field])) {
                        $oldLabel = $relationNames[$field]($old) ?? $old;
                        $newLabel = $relationNames[$field]($new) ?? $new;
                        return "{$label}: {$oldLabel} → {$newLabel}";
                    }

                    // Selain relasi, tampilkan perubahan nilai biasa
                    if ($old == $new) return null;
                    return "{$label}: {$old} → {$new}";
                })
                    ->filter()
                    ->values()
                    ->implode(', ');

                $changesInfo = $details ? "Changes: {$details}" : '';
            }

            return "Submissionposition for {$target} has been {$eventName} by {$actor}. {$changesInfo}";
        });
}

}
