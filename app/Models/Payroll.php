<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;

class Payroll extends Model
{
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

    protected $table = 'payrolls';

    protected $fillable = [
        'employee_id',
        'payroll_period_id',
        'period_month',
        'period_year',
        'period_start',
        'period_end',

        'working_days',
        'attendance_days',
        'absent_days',

        'is_prorate',
        'prorate_days',
        'prorate_ratio',

        'basic_salary',
        'position_allowance',
        'daily_rate',

        'overtime_amount',
        'reimburse_amount',
        
        'punishment',
        'punishment_so',
        'debt',
        'tax',

        'gross_salary',
        'total_income',
        'total_deduction',
        'net_salary',

        'status',
        'approved_by',
        'approved_at',
        'paid_at',
        'note',
    ];

    protected $casts = [
        'period_start'       => 'date',
        'period_end'         => 'date',
        'is_prorate'         => 'boolean',
        'prorate_ratio'      => 'decimal:4',
        'basic_salary'       => 'decimal:2',
        'position_allowance' => 'decimal:2',
        'daily_rate'         => 'decimal:2',
        'overtime_amount'    => 'decimal:2',
        'reimburse_amount'   => 'decimal:2',
        'punishment'   => 'decimal:2',
        'punishment_so'   => 'decimal:2',
        'debt'   => 'decimal:2',
        'tax'   => 'decimal:2',
        'gross_salary'       => 'decimal:2',
        'total_income'       => 'decimal:2',
        'total_deduction'    => 'decimal:2',
        'net_salary'         => 'decimal:2',
        'approved_at'        => 'datetime',
        'paid_at'            => 'datetime',
    ];

    // ── Relasi ──
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function period()
    {
        return $this->belongsTo(PayrollPeriod::class, 'payroll_period_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    public function details()
    {
        return $this->hasMany(PayrollDetail::class, 'payroll_id');
    }

    public function incomeDetails()
    {
        return $this->hasMany(PayrollDetail::class, 'payroll_id')
            ->where('type', 'Income');
    }

    public function deductionDetails()
    {
        return $this->hasMany(PayrollDetail::class, 'payroll_id')
            ->where('type', 'Deduction');
    }

    // ── Scopes ──
    public function scopeForPeriod($query, string $periodId)
    {
        return $query->where('payroll_period_id', $periodId);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /** Nama periode dari period_month + period_year, mis. "Juni 2026" (PayrollPeriod::period_label). */
    public function periodLabel(): string
    {
        return $this->period?->period_label
            ?? (new PayrollPeriod([
                'period_month' => $this->period_month,
                'period_year'  => $this->period_year,
            ]))->period_label;
    }

    /**
     * Nama file slip: Slip_Gaji_{employee_name}_{payrollPeriod}.pdf
     * mis. Slip_Gaji_Agnes_Abon_Geroda_Juni_2026.pdf
     * Spasi jadi "_", karakter yang tidak aman untuk nama file dibuang.
     */
    public function slipFilename(): string
    {
        $clean = fn(string $text) => trim(
            preg_replace('/_+/', '_', preg_replace('/[^A-Za-z0-9_-]+/', '', str_replace(' ', '_', trim($text)))),
            '_'
        );

        return 'Slip_Gaji_' . $clean($this->employee->employee_name ?? 'Karyawan')
            . '_' . $clean($this->periodLabel()) . '.pdf';
    }

    /**
     * Hari kerja yang dipakai rumus gaji. Kalau working_days kosong (0), pakai jumlah
     * hari Senin sampai Sabtu dalam periode (sama seperti saat generate).
     */
    public function effectiveWorkingDays(): int
    {
        if ((int) $this->working_days > 0) {
            return (int) $this->working_days;
        }

        return self::calendarWorkingDays($this->period_start, $this->period_end);
    }

    /** Jumlah hari dalam periode dikurangi hari Minggu. */
    public static function calendarWorkingDays($periodStart, $periodEnd): int
    {
        $start = \Carbon\Carbon::parse($periodStart)->startOfDay();
        $end = \Carbon\Carbon::parse($periodEnd)->startOfDay();
        $working = 0;

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            if ($date->dayOfWeek !== \Carbon\Carbon::SUNDAY) {
                $working++;
            }
        }

        return $working;
    }

    /**
     * Gaji sesuai kehadiran. SATU-SATUNYA tempat rumus ini, dipakai oleh
     * PayrollService::recalculateNet, halaman detail, dan PDF slip.
     *
     * PKWT/OJT: floor(gross × hadir / hari kerja), hadir dibatasi maksimal hari kerja.
     * DW:       daily_rate × hadir.
     *
     * Dihitung dalam sen (bilangan bulat) dan dikali dulu baru dibagi. Rumus lama
     * floor(gross / hari kerja × hadir) memakai float, sehingga karyawan yang hadir
     * penuh bisa kurang Rp1 (mis. 7.500.000 × 26/26 = 7.499.999).
     */
    public function earnedSalary(): float
    {
        $attendance = max(0, (int) $this->attendance_days);

        if (strtoupper($this->employee->status_employee ?? '') === 'DW') {
            $dailyCents = (int) round((float) $this->daily_rate * 100);
            return ($dailyCents * $attendance) / 100;
        }

        $workingDays = $this->effectiveWorkingDays();
        $counted = min($attendance, $workingDays);
        if ($workingDays <= 0 || $counted <= 0) {
            return 0;
        }

        $grossCents = (int) round((float) $this->gross_salary * 100);

        // floor ke rupiah penuh: intdiv(sen × hadir, hari kerja × 100)
        return (float) intdiv($grossCents * $counted, $workingDays * 100);
    }

    /**
     * Rincian perhitungan net (halaman detail payroll + PDF slip), mengikuti rumus
     * PayrollService::recalculateNet supaya angka di layar/slip = angka tersimpan.
     * Butuh relasi employee dan details.component.
     */
    public function salaryBreakdown(): array
    {
        $payroll = $this;
        $isDw = strtoupper($payroll->employee->status_employee ?? '') === 'DW';
        $workingDays = $payroll->effectiveWorkingDays();
        $attendance = (int) $payroll->attendance_days;
        $countedDays = $isDw ? $attendance : min($attendance, $workingDays);
        $earned = $payroll->earnedSalary();

        $employeeDetails = $payroll->details->filter(fn($d) => !($d->component->is_employer_burden ?? false));
        $employerDetails = $payroll->details->filter(fn($d) => (bool) ($d->component->is_employer_burden ?? false));
        $componentType = fn($d) => $d->component->type ?? $d->type;

        $income = $employeeDetails->filter(fn($d) => $componentType($d) === 'Income')
            ->map(fn($d) => ['label' => $d->component->component_name ?? '-', 'amount' => (float) $d->amount])
            ->values()->all();
        $income[] = ['label' => 'Overtime', 'amount' => (float) $payroll->overtime_amount];
        $income[] = ['label' => 'Reimburse', 'amount' => (float) $payroll->reimburse_amount];

        $deduction = $employeeDetails->filter(fn($d) => $componentType($d) === 'Deduction')
            ->map(fn($d) => ['label' => $d->component->component_name ?? '-', 'amount' => (float) $d->amount])
            ->values()->all();
        foreach ([
            'Punishment'    => $payroll->punishment,
            'SO punishment' => $payroll->punishment_so,
            'Debt'          => $payroll->debt,
            'Tax'           => $payroll->tax,
        ] as $label => $amount) {
            $deduction[] = ['label' => $label, 'amount' => (float) $amount];
        }

        $incomeTotal = array_sum(array_column($income, 'amount'));
        $deductionTotal = array_sum(array_column($deduction, 'amount'));
        $computedNet = round($earned + $incomeTotal - $deductionTotal, 2);

        return [
            'is_dw'           => $isDw,
            'working_days'    => $workingDays,
            'attendance'      => $attendance,
            'counted_days'    => $countedDays,
            'capped'          => !$isDw && $attendance > $workingDays,
            'earned'          => $earned,
            'income'          => $income,
            'income_total'    => $incomeTotal,
            'deduction'       => $deduction,
            'deduction_total' => $deductionTotal,
            'employer'        => $employerDetails->map(fn($d) => [
                'label'  => $d->component->component_name ?? '-',
                'type'   => $componentType($d),
                'amount' => (float) $d->amount,
            ])->values()->all(),
            'computed_net'    => $computedNet,
            // Selisih muncul kalau net tersimpan belum dihitung ulang setelah data berubah
            'mismatch'        => abs($computedNet - (float) $payroll->net_salary) >= 1,
        ];
    }
}
