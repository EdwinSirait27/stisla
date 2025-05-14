<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;
class Employee extends Model
{
    use HasFactory;
    protected $table = 'employees_tables';
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

    protected $casts = [
        'join_date' => 'date:Y-m-d', // Otomatis format Y-m-d saat diambil
        
    ];
    protected $fillable = [
        'employee_name',
        'employee_pengenal',
        'position_id',
        'company_id',
        'store_id',
        'bank_account_number',
        'banks_id',
        'department_id',
        'fingerprint_id',
        'status_employee',
        'join_date',
        'marriage',
        'child',
        'telp_number',
        'nik',
        'gender',
        'date_of_birth',
        'place_of_birth',
        'biological_mother_name',
        'religion',
        'current_address',
        'id_card_address',
        'last_education',
        'institution',
        'npwp',
        'bpjs_kes',
        'bpjs_ket',
        'email',
        'emergency_contact_name',
        'status',
        'notes'
        
        
    ];
    public function department()
    {
        return $this->belongsTo(Departments::class, 'department_id');
    }
    public function bank()
    {
        return $this->belongsTo(Banks::class, 'banks_id');
    }
    public function store()
    {
        return $this->belongsTo(Stores::class, 'store_id');
    }
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }
    public function finger()
    {
        return $this->belongsTo(Fingerprints::class, 'fingerprint_id');
    }
    public function users()
    {
        return $this->hasOne(User::class, 'employee_id');
    }
    public function getLengthOfServiceAttribute()
    {
        return $this->created_at->diffInDays(now()) . ' days';
    }
    public function getJoinDateAttribute($value)
    {
        return Carbon::parse($value)->format('y-m-d');
    }
    public function payrolls()
    {
        return $this->hasMany(Payrolls::class, 'employee_id');
    }
}