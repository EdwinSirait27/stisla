<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Skdetail extends Model
{
    use HasFactory;
    protected $table = 'sk_employee';
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
    protected $fillable = [
        'sk_employee_id',
        'user_id',
        'old_posiiton_id',
        'new_position_id',
        'old_department_id',
        'new_department_id',
        'old_company_id',
        'new_company_id',
        'old_salary',
        'new_salary',
        'notes',
        'effective_date'
    ];
    protected $casts = [
        'effective_date' => 'date:Y-m-d',
    ];
    public function skemployee()
    {
        return $this->belongsTo(Skemployee::class, 'sk_employee_id', 'id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function oldpos()
    {
        return $this->belongsTo(Position::class, 'old_position_id', 'id');
    }
    public function newpos()
    {
        return $this->belongsTo(Position::class, 'new_position_id', 'id');
    }
    public function oldcom()
    {
        return $this->belongsTo(Company::class, 'old_company_id', 'id');
    }
    public function newcom()
    {
        return $this->belongsTo(Company::class, 'new_company_id', 'id');
    }
    public function olddep()
    {
        return $this->belongsTo(Departments::class, 'old_department_id', 'id');
    }
    public function newdep()
    {
        return $this->belongsTo(Departments::class, 'new_department_id', 'id');
    }
}
