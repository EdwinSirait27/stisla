<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Crypt;
class Payrolls extends Model
{
    use HasFactory;
    protected $table = 'payrolls_tables';
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
        'month_year' => 'date:Y-m-d', // Otomatis format Y-m-d saat diambil
    ];
    protected $fillable = [
        'employee_id',
        'attendance',
        'bonus',
        'daily_allowance',
        'overtime',
        'house_allowance',
        'meal_allowance',
        'transport_allowance',
        'bpjs_ket',
        'bpjs_kes',
        'mesh',
        'punishment',
        'late_fine',
        'deductions',
        'salary',
        'month_year',
        'information',
        'attachment_file',   
    ];
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
    
        
}
