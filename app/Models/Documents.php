<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Documents extends Model
{
    use HasFactory, LogsActivity;
    protected $table = 'documents';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'company_document_config_id',
        'employee_id',
        'issued_by',
        'issued_date',
        'expired_date',
        'status',
        'file_path',
        'document_number',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // UUID
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = Uuid::uuid7()->toString();
            }

            // Auto generate document_number
            if (!$model->document_number) {
                $model->document_number = self::generateDocumentNumber($model);
            }
        });
    }
    protected static function generateDocumentNumber(Documents $model): string
    {
        $romanMonths = [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII'
        ];

        $config       = Companydocumentconfigs::with(['documenttypes', 'company'])
            ->find($model->company_document_config_id);
        $documentCode = strtoupper(str_replace(' ', '-', $config->documenttypes->nickname));
        $companyNick  = strtoupper($config->company->nickname);

        // PAK (Surat Keterangan Kerja) acuan nomornya pakai end_date karyawan.
        // SPK & SPPRP acuan nomornya pakai join_date karyawan.
        // ST (Surat Tugas) acuan nomornya pakai issued_date yang diinput manual di form.
        // Tipe lain tetap pakai tanggal sekarang.
        if ($documentCode === 'PAK' && $model->employee?->end_date) {
            $referenceDate = Carbon::parse($model->employee->end_date);
        } elseif (in_array($documentCode, ['SPK', 'SPPRP']) && $model->employee?->join_date) {
            $referenceDate = Carbon::parse($model->employee->join_date);
        } elseif ($documentCode === 'ST' && $model->issued_date) {
            $referenceDate = Carbon::parse($model->issued_date);
        } else {
            $referenceDate = now();
        }

        $year       = $referenceDate->year;
        $romanMonth = $romanMonths[$referenceDate->month];

        $suffix = "/{$documentCode}-{$companyNick}/{$romanMonth}/{$year}";

        // Cari sequence tertinggi berdasarkan pattern document_number
        $last = self::whereExists(function ($q) use ($config) {
            $q->selectRaw(1)
                ->from('company_document_configs')
                ->whereColumn('company_document_configs.id', 'documents.company_document_config_id')
                ->where('company_document_configs.company_id', $config->company_id)
                ->where('company_document_configs.document_type_id', $config->document_type_id);
        })
            ->where('document_number', 'like', "%{$suffix}")
            ->lockForUpdate()
            ->orderByRaw('CAST(SUBSTRING_INDEX(document_number, "/", 1) AS UNSIGNED) DESC')
            ->value('document_number');

        $sequence = $last ? ((int) explode('/', $last)[0]) + 1 : 1;

        return str_pad($sequence, 3, '0', STR_PAD_LEFT) . $suffix;
    }

    // Relationships
    public function companydocumentconfigs()
    {
        return $this->belongsTo(Companydocumentconfigs::class, 'company_document_config_id', 'id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function issued()
    {
        return $this->belongsTo(Employee::class, 'issued_by', 'id');
    }

    public function assignments()
    {
        return $this->hasMany(DocumentTemporaryAssignment::class, 'document_id', 'id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('document')
            ->setDescriptionForEvent(function (string $eventName) {
                $actor = auth()->user()->employee->employee_name
                    ?? auth()->user()->name
                    ?? 'system';
                $target = $this->document_number ?? ($this->employee->employee_name ?? 'Unknown Document');

                $fieldLabels = [
                    'company_document_config_id' => 'Document Config',
                    'employee_id'                => 'Employee',
                    'issued_by'                  => 'Issued By',
                    'issued_date'                => 'Issued Date',
                    'expired_date'               => 'Expired Date',
                    'status'                     => 'Status',
                    'file_path'                  => 'File Path',
                    'document_number'            => 'Document Number',
                ];

                $changesInfo = '';
                if ($eventName === 'updated') {
                    $changes  = $this->getChanges();
                    $original = $this->getOriginal();

                    $details = collect($changes)
                        ->filter(fn($value, $field) => $field !== 'updated_at' && ($original[$field] ?? null) != $value)
                        ->map(function ($new, $field) use ($original, $fieldLabels) {
                            $old   = $original[$field] ?? 'null';
                            $label = $fieldLabels[$field] ?? ucfirst(str_replace('_', ' ', $field));

                            return "{$label}: {$old} → {$new}";
                        })
                        ->values()
                        ->implode(', ');

                    if ($details) {
                        $changesInfo = " ({$details})";
                    }
                }

                return match ($eventName) {
                    'created' => "{$actor} created document {$target}",
                    'updated' => "{$actor} updated document {$target}{$changesInfo}",
                    'deleted' => "{$actor} deleted document {$target}",
                    default   => "{$actor} {$eventName} document {$target}",
                };
            });
    }
}
