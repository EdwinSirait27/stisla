<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\Pivot;


use Ramsey\Uuid\Uuid;

class EmployeeDepartment extends Pivot
{
   use HasUuids;

    protected $table = 'employee_departments';
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
        'employee_id',
        'department_id',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function department()
    {
        return $this->belongsTo(Departments::class, 'department_id', 'id');
    }
}





// $storeId = '019a230d-6146-7001-848d-046ccdbdf163';

// $employeeIds = [
// '0196bf55-fedd-7128-bf8e-5fb0de67f8f3',
// '0196bf55-ff17-700b-ba4b-bb3ffe2b56fb',
// '0196bf55-ff39-7395-8d71-9b83aeec8344',
// '0196bf55-ff47-72aa-95a0-a46357de8721',
// '0196bf55-ff6a-72b4-9c19-d4cbb2265442',
// '0196bf55-ff77-73ab-92dd-141d83326e11',
// '01973a24-3e1e-7152-bd71-2d400e6df145',
// '01975e67-ab59-7210-a6fb-d02fb4757220',
// '01975e67-ab9b-71bc-ae5b-3d117d4625f9',
// '01975e67-abb5-724f-85fa-e627504d57c4',
// '01975e67-abbf-72bb-a347-35d87285e602',
// '01976735-341b-7237-a452-f687b01c1282',
// '0197f86d-da47-7228-a03e-ea048323ca6d',
// '01981cd5-54ce-73bf-85c2-a1475abb4d1e',
// ];


// foreach ($employeeIds as $employeeId) {
//     \App\Models\EmployeeStore::create([
//         'employee_id' => $employeeId,
//         'store_id' => $storeId,
//         'is_primary' => true,
//     ]);
// }