<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendancetotal extends Model
{
    use HasFactory;
    use HasFactory;
     protected $table = 'attendancetotal'; 
   protected $fillable = [
        'attendance_id',
        'month',
        'total',
        
    ];
    public function attendance()
    {
        return $this->belongsTo(Attendances::class, 'attendance_id','id');
    }
}
