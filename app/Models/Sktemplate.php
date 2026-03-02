<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpKernel\HttpCache\Store;
class Sktemplate extends Model
{
    use HasFactory;
    public $incrementing = false;
    protected $keyType = 'string';
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = Uuid::uuid7()->toString();
            }
        });
    }
    protected $table = 'sk_template';
    protected $fillable = [
        'template_name',
        // 'sk_type_id',
        'company_id',
        // 'store_id',
    ];
    // public function sktype()
    // {
    //     return $this->belongsTo(Sktype::class, 'sk_type_id', 'id');
    // }
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }
    // public function store()
    // {
    //     return $this->belongsTo(Stores::class, 'store_id', 'id');
    // }
}
