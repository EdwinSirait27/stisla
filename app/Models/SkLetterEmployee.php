<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Ramsey\Uuid\Uuid;

class SkLetterEmployee extends Pivot
{
    protected $table = 'sk_letter_employees';
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
        'basic_salary'         => 'decimal:2',
        'positional_allowance' => 'decimal:2',
        'daily_rate'           => 'decimal:2',
    ];

    protected $fillable = [
        'sk_letter_id',
        'employee_id',
        'previous_structure_id',
        'new_structure_id',
        'position_id',
        'group_id',
        'grading_id',
        'department_id',
        'basic_salary',
        'positional_allowance',
        'daily_rate',
        'notes',
    ];

    public function skLetter()
    {
        return $this->belongsTo(SkLetter::class, 'sk_letter_id', 'id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function previousStructure()
    {
        return $this->belongsTo(Structuresnew::class, 'previous_structure_id', 'id');
    }

    public function newStructure()
    {
        return $this->belongsTo(Structuresnew::class, 'new_structure_id', 'id');
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

    public function department()
    {
        return $this->belongsTo(Departments::class, 'department_id', 'id');
    }
}