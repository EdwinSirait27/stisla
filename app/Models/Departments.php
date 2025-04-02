<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Str;


class Departments extends Model
{
    use HasFactory,    HasUuids;

    protected $table = 'departments'; 
    protected $primaryKey = 'department_id';

    public $incrementing = false; // Nonaktifkan auto-increment
    protected $keyType = 'string'; // Pastikan tipe data adalah string
    protected $fillable = [
        'department_id',
        'departmentName',
        'status',
    ];
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (!$model->department_id) {
                $model->department_id = Str::uuid();
            }
        });
    }
    public function employee()
{
    return $this->hasOne(Employee::class, 'department_id');
}

}
