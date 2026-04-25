<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ManualRecapLog extends Model
{
    use HasUuids;

    protected $table = 'manual_recap_logs';

    protected $fillable = [
        'employee_id',
        'pin',
        'date',
        'time_in',
        'time_out',
        'reason',
        'hr_id',
        'hr_name',
        'submitted_at',
        'email_sent',
        'email_sent_at',
        'whatsapp_sent',
        'whatsapp_sent_at',
        'notification_error',
    ];

    protected $casts = [
        'date'              => 'date',
        'submitted_at'      => 'datetime',
        'email_sent_at'     => 'datetime',
        'whatsapp_sent_at'  => 'datetime',
        'email_sent'        => 'boolean',
        'whatsapp_sent'     => 'boolean',
    ];

    // Karyawan yang di-override absensinya
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    // HR yang mengajukan
    public function hr()
    {
        return $this->belongsTo(Employee::class, 'hr_id');
    }

    // File-file bukti pendukung (multiple)
    public function evidences()
    {
        return $this->hasMany(ManualRecapEvidence::class, 'manual_recap_log_id');
    }
}