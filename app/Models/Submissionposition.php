<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;
class Submissionposition extends Model
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
    protected $table = 'submission_position_tables';
    protected $fillable = [
        'employee_id',
        'position_name',
        'role_summary',
        'key_respon',
        'qualifications',
        'work_location',
        'reason_reject',
        'type',
        'status',
        'notes',
        'approver_1',
        'approver_2',
    ];
    public function submitter()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
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
