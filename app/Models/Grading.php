<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Grading extends Model
{
    use HasFactory;
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = Uuid::uuid7()->toString();
            }
        });
    }
    protected $table = 'grading';
    protected $fillable = [
        'grading_code',
        'grading_name',
        'level',
        'meal_allowance',
        'group_id',
    ];
      protected $casts = [
        'meal_allwance' => 'decimal:2',
    ];
     public function groups()
    {
        return $this->belongsTo(Groups::class, 'group_id', 'id');
    }
}
