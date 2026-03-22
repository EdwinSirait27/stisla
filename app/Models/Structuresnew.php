<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Structuresnew extends Model
{
    use HasFactory,  LogsActivity;
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
    protected $table = 'structures_tables';
    protected $fillable = [
        'parent_id',
        'structure_code',
        'is_manager',
        'status',
        'submission_position_id',
    ];
    protected $casts = [
        'is_manager' => 'boolean',
    ];
     public function parent()
    {
        return $this->belongsTo(Structuresnew::class, 'parent_id', 'id');
    }
    public function submissionposition()
    {
        return $this->belongsTo(Submissionposition::class, 'submission_position_id', 'id');
    }
    public function children()
    {
        return $this->hasMany(Structuresnew::class, 'parent_id', 'id');
    }
    public function employee()
    {
        return $this->hasMany(Employee::class, 'structure_id', 'id');
    }
    public function employees()
    {
        return $this->hasOne(Employee::class, 'structure_id', 'id');
    }
    public function allChildren()
    {
        return $this->children()->with('allChildren', 'submissionposition.positionRelation');
    }
   
    public function secondarySupervisors()
    {
        return $this->belongsToMany(
            Structuresnew::class,
            'structure_supervisors',
            'structure_id',
            'supervisor_id'
        );
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->useLogName('Structuresnew')
            ->setDescriptionForEvent(function (string $eventName) {
                $actor = auth()->user()->employee->employee_name
                    ?? auth()->user()->name
                    ?? 'system';

                $changes = $this->getChanges();
                $original = $this->getOriginal();

                $relationNames = [
                    'parent_id' => fn($id) => optional(Structuresnew::with('submissionposition.positionRelation')->find($id))->submissionposition->positionRelation->name ?? '-',
                ];

                $fieldLabels = [
                    'parent_id' => 'Direct Supervisor',

                ];
                $changesInfo = '';
                if ($eventName === 'updated' && !empty($changes)) {
                    $details = collect($changes)->map(function ($new, $field) use ($original, $relationNames, $fieldLabels) {
                        $old = $original[$field] ?? 'null';

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

                return "Structuresnew has been {$eventName} by {$actor}. {$changesInfo}";
            });
    }
}