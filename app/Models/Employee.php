<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;
    protected $table = 'employees'; // Nama tabel di database

    protected $fillable = [
        'name',
        'email',
        'phone',
        'position',
        'salary'
    ];
    public function user()
{
    return $this->hasOne(User::class, 'employee_id');
}

}
