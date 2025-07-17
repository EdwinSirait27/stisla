<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Devicefingerprint extends Model
{
    use HasFactory;
    protected $connection = 'mysql_second'; // koneksi ke database kedua
    protected $table = 'device';     
}
