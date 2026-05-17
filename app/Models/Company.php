<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;
class Company extends Model
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
    protected $table = 'company_tables'; 
    protected $fillable = [
        'name',
        'header',
        'website',
        'email',
        'foto',
        'address',
        'npwp',  
        'remark',  
        'nickname',
        ];
        public function getFotoUrlAttribute()
{
    if (!$this->foto) return null;
    return asset('storage/' . $this->foto);
}
    public function user()
    {
        return $this->belongsTo(User::class, 'manager_id', 'id');   
}
    public function employees()
{
    return $this->hasMany(Employee::class, 'company_id', 'id');
}
 public function setNameAttribute($value)
    {
        $this->attributes['name'] = strtoupper($value);
    }
     public function setHeaderAttribute($value)
    {
        $this->attributes['header'] = strtoupper($value);
    }
     public function setAddressAttribute($value)
    {
        $this->attributes['address'] = strtoupper($value);
    }
     public function setNicknameAttribute($value)
    {
        $this->attributes['nickname'] = strtoupper($value);
    }
}
