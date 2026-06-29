<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EditedFingerprintAttachment extends Model
{
   protected $connection = 'mysql_second';
    protected $table = 'edited_fingerprint_attachments';
    public $timestamps = false; 
    protected $fillable = [
        'edited_fingerprint_id',
        'attachment',
        ];

    public function editedFingerprint()
    {
        return $this->belongsTo(EditedFingerprint::class);
    }

}
