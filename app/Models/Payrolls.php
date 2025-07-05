<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;
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
            'month_year' => 'date:Y-m-d',
        'bonus' => 'float',
        'overtime' => 'float',
        'house_allowance' => 'float',
        'daily_allowance' => 'float',
        'meal_allowance' => 'float',
        'transport_allowance' => 'float',
        'bpjs_ket' => 'float',
        'bpjs_kes' => 'float',
        'debt' => 'float',
        'punishment' => 'float',
        'late_fine' => 'float',
        'deductions' => 'float',
        'salary' => 'float',
        'take_home' => 'float',
        'tax' => 'float',
    ];
    protected $fillable = [
        'employee_id',
        'attendance',
        'bonus',
        'overtime',
        'house_allowance',
        'daily_allowance',
        'meal_allowance',
        'transport_allowance',
        'bpjs_ket',
        'bpjs_kes',
        'debt',
        'punishment',
        'late_fine',
        'deductions',
        'salary',
        'take_home',
        'tax',
        'period',
        'month_year',
        'information',
        'attachment_file',
        'attachment_path',
    ];
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
