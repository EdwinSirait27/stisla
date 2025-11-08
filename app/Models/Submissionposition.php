<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;
class Submissionposition extends Model
{
    use HasFactory;
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
}
