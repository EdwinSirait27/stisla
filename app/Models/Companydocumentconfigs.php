<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Companydocumentconfigs extends Model
{
     use HasFactory;
    protected $table = 'company_document_configs';
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
        'company_id',
        'document_type_id',
        'bank_name',
        'savings_type',
        'promo_code',
        'community_code',
        'service_name',
        'pic_name',
        'pic_email',
        'pic_name_2',
        'pic_email_2',
        'is_active',
        ];
         public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }
         public function documenttypes()
    {
        return $this->belongsTo(Documenttypes::class, 'document_type_id', 'id');
    }
}
