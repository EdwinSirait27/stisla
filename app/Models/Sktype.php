<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Sktype extends Model
{
    use HasFactory;
    protected $table = 'sk_type';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $casts = [
        'affects_salary'   => 'boolean',
        'affects_position' => 'boolean',
        'affects_status'   => 'boolean',
        'generates_contract' => 'boolean',
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
    
     public static function getCategoriesOptions(): array
    {
        return [
            'Employment'            => 'Employment',
            'Mutation' => 'Mutation',
            'Payroll'              => 'Payroll',
            'Disciplinary'              => 'Disciplinary',
            'Termination'              => 'Termination',
        ];
    }
    protected $fillable = [
        'sk_name',
        'nickname',
        'categories',
        'affects_salary',
        'affects_position',
        'affects_status',
        'generates_contract',
    ];

}
