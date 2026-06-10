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
        'total_days',
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
        $employee  = $this->leavebalance->employees;
        $structure = $employee?->structuresnew;

        if (!$structure) {
            return collect();
        }

        // Telusuri ke atas dari struktur karyawan, cari struktur ber-is_manager = 1
        $managerStructureIds = [];
        $cur = $structure->parent_id;

        while ($cur) {
            $s = \App\Models\Structuresnew::select('id', 'parent_id', 'is_manager')->find($cur);
            if (!$s) break;

            if ($s->is_manager) {
                $managerStructureIds[] = $s->id;
            }
            $cur = $s->parent_id;
        }

        // Ambil employee yang menempati struktur-struktur manager itu
        $approverIds = !empty($managerStructureIds)
            ? \App\Models\Employee::whereIn('structure_id', $managerStructureIds)->pluck('id')
            : collect();

        // Tambahkan secondary supervisor dari struktur karyawan sendiri
        $secondary = $structure->secondarySupervisors->pluck('id');

        return $approverIds->merge($secondary)->unique()->values();

    }
    // cek apakah employee tertentu boleh approve
    public function canBeApprovedBy($employeeId)
    {
        return $this->approvers()->contains($employeeId);
    }
}
