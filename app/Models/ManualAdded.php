<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Ramsey\Uuid\Uuid;
class ManualAdded extends Model
{
    use HasFactory;
    protected $connection = 'mysql_second'; // koneksi ke database kedua
    protected $table = 'manual_added';   
    public function devicefingerprints()
{
    return $this->belongsTo(Devicefingerprint::class, 'sn', 'sn');
}

}
