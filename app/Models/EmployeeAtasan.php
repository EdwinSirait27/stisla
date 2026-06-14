<?php

namespace App\Models;
use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\Relations\Pivot;

class EmployeeAtasan extends Pivot
{
    protected $table = 'employee_atasans';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'employee_id',
        'atasan_id',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function atasan()
    {
        return $this->belongsTo(Employee::class, 'atasan_id', 'id');
    }
}
