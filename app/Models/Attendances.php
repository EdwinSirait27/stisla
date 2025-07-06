<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Attendances extends Model
{

    use HasFactory;
     protected $table = 'attendance'; 
   protected $fillable = [
        'employee_id',
        'tanggal',
        'kantor',
        'jam_masuk',
        'jam_keluar',
        'jam_masuk2',
        'jam_keluar2',
        'jam_masuk3',
        'jam_keluar3',
        'jam_masuk4',
        'jam_keluar4',
        'jam_masuk5',
        'jam_keluar5',
        'jam_masuk6',
        'jam_keluar6',
        'jam_masuk7',
        'jam_keluar7',
        'jam_masuk8',
        'jam_keluar8',
        'jam_masuk9',
        'jam_keluar9',
        'jam_masuk10',
        'jam_keluar10',
    ];
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id','id');
    }
}