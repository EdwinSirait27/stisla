<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Ramsey\Uuid\Uuid;
class Fingerprints extends Model
{
    use HasFactory;
    protected $connection = 'mysql_second'; // koneksi ke database kedua
    protected $table = 'att_log';   
    public function devicefingerprints()
{
    return $this->belongsTo(Devicefingerprint::class, 'sn', 'sn');
}

}
