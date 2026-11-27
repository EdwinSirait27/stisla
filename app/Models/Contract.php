<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Contract extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'contracts';
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
        'basic_salary'          => 'decimal:2',
        'positional_allowance'  => 'decimal:2',
        'daily_rate'            => 'decimal:2',
        'start_date'            => 'date',
        'end_date'              => 'date',
        'terminated_at'         => 'date',
        'signed_by_employee_at' => 'datetime',
        'approver_1_at'         => 'datetime',
        'approver_2_at'         => 'datetime',
        'approver_3_at'         => 'datetime',
        'returned_at'           => 'datetime',
        'approved_at'           => 'datetime',
    ];

    protected $fillable = [
        'employee_id',
        'sk_letter_id',
        'issuer_company_id',
        'structure_id',
        'position_id',
        'group_id',
        'grading_id',
        'company_id',
        'department_id',
        'signed_by_employee',
        'signed_by_employee_at',
        'contract_type',
        'start_date',
        'end_date',
        'basic_salary',
        'positional_allowance',
        'daily_rate',
        'contract_status',
        'file_path',
        'notes',
        'store_id',
        'terminated_at',
        'terminated_reason',
        'terminated_by',
        'created_by',
        'approver_1',
        'approver_2',
        'approver_3',
        'approver_1_at',
        'approver_2_at',
        'approver_3_at',
        'approver_1_signature',
        'approver_2_signature',
        'approver_3_signature',
        'returned_note',
        'returned_by',
        'returned_at',
        'approved_at',
    ];

    /** Status yang masih "terbuka" (belum berlaku, belum selesai): satu karyawan hanya boleh punya satu. */
    public const OPEN_STATUSES = ['Draft', 'Pending', 'Active'];

    /**
     * Langkah approval yang sedang menunggu: [kolom approver, kolom waktu] atau null.
     * Pending: approver 2 dulu, lalu approver 3 (kalau dipilih).
     */
    public function currentStep(): ?array
    {
        if ($this->contract_status !== 'Pending') return null;
        if (!$this->approver_2_at) return ['approver_2', 'approver_2_at'];
        if ($this->approver_3 && !$this->approver_3_at) return ['approver_3', 'approver_3_at'];
        return null;
    }

    public function currentApproverId(): ?string
    {
        $step = $this->currentStep();
        return $step ? $this->{$step[0]} : null;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->contract_status) {
            'Draft'   => $this->returned_at ? 'Dikembalikan' : 'Draft',
            'Pending' => 'Menunggu ' . (($this->currentStep()[0] ?? null) === 'approver_3'
                ? ($this->approver3?->employee_name ?? 'approver 3')
                : ($this->approver2?->employee_name ?? 'approver 2')),
            default   => self::getContractStatusOptions()[$this->contract_status] ?? (string) $this->contract_status,
        };
    }

    public function approver1() { return $this->belongsTo(Employee::class, 'approver_1', 'id'); }
    public function approver2() { return $this->belongsTo(Employee::class, 'approver_2', 'id'); }
    public function approver3() { return $this->belongsTo(Employee::class, 'approver_3', 'id'); }

    /** Nominal gaji: tidak ditulis ke activity log (sensitif). */
    public const SALARY_FIELDS = ['basic_salary', 'positional_allowance', 'daily_rate'];

    public static function getContractStatusOptions(): array
    {
        return [
            'Draft'      => 'Draft',
            'Pending'    => 'Menunggu persetujuan',
            'Active'     => 'Berlaku',
            'Expired'    => 'Berakhir',
            'Terminated' => 'Diakhiri lebih awal',
            'Cancelled'  => 'Dibatalkan',
        ];
    }

    public static function getContractTypeOptions(): array
    {
        return [
            'PKWT'            => 'PKWT',
            'PKWTT'           => 'PKWTT',
            'On Job Training' => 'On Job Training',
            'DW'              => 'DW',
        ];
    }
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function skLetter()
    {
        return $this->belongsTo(SkLetter::class, 'sk_letter_id', 'id');
    }

    public function issuerCompany()
    {
        return $this->belongsTo(Company::class, 'issuer_company_id', 'id');
    }

   
    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id', 'id');
    }

    public function group()
    {
        return $this->belongsTo(Groups::class, 'group_id', 'id');
    }

    public function grading()
    {
        return $this->belongsTo(Grading::class, 'grading_id', 'id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function department()
    {
        return $this->belongsTo(Departments::class, 'department_id', 'id');
    }

    public function store()
    {
        return $this->belongsTo(Stores::class, 'store_id', 'id');
    }

    /** Sisa hari sampai tanggal berakhir (negatif kalau sudah lewat), null kalau tanpa batas. */
    public function daysLeft(): ?int
    {
        return $this->end_date ? (int) today()->diffInDays($this->end_date, false) : null;
    }

    public function signedByEmployee()
    {
        return $this->belongsTo(Employee::class, 'signed_by_employee', 'id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logExcept([...self::SALARY_FIELDS, 'approver_1_signature', 'approver_2_signature', 'approver_3_signature'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('Contract')
            ->setDescriptionForEvent(function (string $eventName) {
                $actor = auth()->user()?->employee?->employee_name ?? auth()->user()?->name ?? 'system';
                $target = optional($this->employee)->employee_name ?? 'karyawan';

                $relationNames = [
                    'employee_id'       => fn($id) => optional(Employee::find($id))->employee_name,
                    'issuer_company_id' => fn($id) => optional(Company::find($id))->name,
                    'position_id'       => fn($id) => optional(Position::find($id))->name,
                    'group_id'          => fn($id) => optional(Groups::find($id))->group_name,
                    'grading_id'        => fn($id) => optional(Grading::find($id))->grading_name,
                    'company_id'        => fn($id) => optional(Company::find($id))->name,
                    'department_id'     => fn($id) => optional(Departments::find($id))->department_name,
                    'store_id'          => fn($id) => optional(Stores::find($id))->name,
                    'terminated_by'     => fn($id) => optional(Employee::find($id))->employee_name,
                    'approver_1'        => fn($id) => optional(Employee::find($id))->employee_name,
                    'approver_2'        => fn($id) => optional(Employee::find($id))->employee_name,
                    'approver_3'        => fn($id) => optional(Employee::find($id))->employee_name,
                    'returned_by'       => fn($id) => optional(Employee::find($id))->employee_name,
                    'contract_status'   => fn($v) => self::getContractStatusOptions()[$v] ?? $v,
                ];
                $fieldLabels = [
                    'issuer_company_id' => 'Perusahaan penerbit', 'position_id' => 'Jabatan', 'group_id' => 'Group',
                    'grading_id' => 'Grading', 'company_id' => 'Perusahaan', 'department_id' => 'Departemen', 'store_id' => 'Lokasi',
                    'contract_type' => 'Jenis', 'start_date' => 'Mulai', 'end_date' => 'Berakhir', 'contract_status' => 'Status',
                    'terminated_at' => 'Diakhiri', 'terminated_reason' => 'Alasan diakhiri', 'file_path' => 'Dokumen', 'notes' => 'Catatan',
                    'approver_1' => 'Diajukan oleh', 'approver_2' => 'Approver 2', 'approver_3' => 'Approver 3',
                    'approver_1_at' => 'Diajukan', 'approver_2_at' => 'Disetujui approver 2', 'approver_3_at' => 'Disetujui approver 3',
                    'returned_note' => 'Alasan dikembalikan', 'returned_by' => 'Dikembalikan oleh', 'returned_at' => 'Dikembalikan', 'approved_at' => 'Disetujui penuh',
                ];

                $changesInfo = '';
                if ($eventName === 'updated') {
                    $changes = collect($this->getChanges())->except(['updated_at', 'created_at', 'approver_1_signature', 'approver_2_signature', 'approver_3_signature']);
                    $original = $this->getOriginal();
                    $salaryChanged = $changes->keys()->intersect(self::SALARY_FIELDS)->isNotEmpty();
                    $details = $changes->except(self::SALARY_FIELDS)->map(function ($new, $field) use ($original, $relationNames, $fieldLabels) {
                        $old = $original[$field] ?? null;
                        if ($old == $new) return null;
                        $label = $fieldLabels[$field] ?? ucfirst(str_replace('_', ' ', $field));
                        if ($field === 'file_path') return "{$label} diganti";
                        $fmt = fn($v) => $v === null || $v === '' ? '-' : (isset($relationNames[$field]) ? ($relationNames[$field]($v) ?? $v) : ($v instanceof \DateTimeInterface ? $v->format('Y-m-d') : (is_string($v) && preg_match('/^\d{4}-\d{2}-\d{2} 00:00:00$/', $v) ? substr($v, 0, 10) : $v)));
                        return "{$label}: {$fmt($old)} → {$fmt($new)}";
                    })->filter()->values();
                    if ($salaryChanged) $details->push('Gaji diubah');
                    $changesInfo = $details->isNotEmpty() ? ' Perubahan: ' . $details->implode(', ') : '';
                }

                $verb = ['created' => 'dibuat', 'updated' => 'diubah', 'deleted' => 'dihapus'][$eventName] ?? $eventName;
                return "Kontrak {$target} {$verb} oleh {$actor}.{$changesInfo}";
            });
    }
}