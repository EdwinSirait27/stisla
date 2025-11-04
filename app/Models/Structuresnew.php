<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Structuresnew extends Model
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
    protected $table = 'structures_tables';
    protected $fillable = [
        'parent_id',
        'submitter',
        'company_id',
        'department_id',
        'salary_id',
        'position_id',
        'store_id',
        'structure_code',
        'is_manager',
        'type',
        'status',
        'role_summary',
        'key_respon',
        'qualifications',
        'work_location',
        'position_name',
        'approval_1',
        'approval_2',
        'reason_reject',
        'submission_status',
    ];
    protected $casts = [
        'is_manager' => 'boolean',

    ];
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }
    public function salary()
    {
        return $this->belongsTo(Salary::class, 'salary_id', 'id');
    }
    public function submitter()
    {
        return $this->belongsTo(Employee::class, 'submitter', 'id');
    }
    public function approval1()
    {
        return $this->belongsTo(Employee::class, 'approval_1', 'id');
    }
    public function approval2()
    {
        return $this->belongsTo(Employee::class, 'approval_2', 'id');
    }
    public function department()
    {
        return $this->belongsTo(Departments::class, 'department_id', 'id');
    }
    public function store()
    {
        return $this->belongsTo(Stores::class, 'store_id', 'id');
    }
    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id', 'id');
    }
    public function parent()
    {
        return $this->belongsTo(Structuresnew::class, 'parent_id', 'id');
    }
    public function children()
    {
        return $this->hasMany(Structuresnew::class, 'parent_id', 'id');
    }
    public function employee()
    {
        return $this->hasMany(Employee::class, 'structure_id', 'id');
    }
}
