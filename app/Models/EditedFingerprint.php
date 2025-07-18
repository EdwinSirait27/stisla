<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EditedFingerprint extends Model
{
    // use HasFactory;
     protected $table = 'edited_fingerprints';
    protected $guarded = [];
    protected $dates = ['scan_date'];
}
