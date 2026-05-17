<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Documenttypes extends Model
{
    use HasFactory;
    protected $table = 'document_types';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $casts = [
        'is_active'   => 'boolean',
    ];
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
        'document_name',
        'view_name',
        'nickname',
        'is_active',
    ];
    public function setDocumentNameAttribute($value)
    {
        $this->attributes['document_name'] = strtoupper($value);
    }
    public function setNicknameAttribute($value)
    {
        $this->attributes['nickname'] = strtoupper($value);
    }
}

// $type = \App\Models\DocumentType::create([
//     'document_name'      => 'Surat Pengantar Pembukaan Rekening Payroll',
//     'nickname'      => 'SPPRP',
//     'view_name' => 'documents.types.SPPRP',
//     'is_active' => true,
// ]);
