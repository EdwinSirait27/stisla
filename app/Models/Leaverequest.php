<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Leaverequest extends Model
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
    protected $table = 'leave_requests';
   protected $fillable = [
        'leave_balance_id',
        'start_date',
        'end_date',
        'employee_reason',
        'approver_reason',
        'status',
        'approved_by',
    ];

    // relasi utama
    public function leavebalance()
    {
        return $this->belongsTo(Leavebalance::class, 'leave_balance_id', 'id');
    }

    // employee yang mengajukan
    public function employee()
    {
        return $this->leavebalance->employees;
    }

    // tipe cuti
    public function leaveType()
    {
        return $this->leaveBalance->leaves;
    }

    // approver (employee)
    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approved_by', 'id');
    }
    /**
     * Ambil semua approver berdasarkan struktur organisasi:
     * - Primary manager
     * - Secondary supervisor
     */
    public function approvers()
    {
        $employee = $this->leavebalance->employees;

        $structure = $employee->structuresnew; // relasi ke model Structuresnew

        // manager utama
        $primary = $structure->employees()->where('is_manager', 1)->get();

        // secondary supervisor
        $secondary = $structure->secondarySupervisors;

        return $primary->merge($secondary);
    }

    // cek apakah employee tertentu boleh approve
    public function canBeApprovedBy($employeeId)
    {
        return $this->approvers()->pluck('id')->contains($employeeId);
    }
}