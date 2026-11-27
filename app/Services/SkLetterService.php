<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Employee;
use App\Models\EmployeeDepartment;
use App\Models\EmployeePosition;
use App\Models\EmployeeSalary;
use App\Models\EmployeeStore;
use App\Models\SkKeputusan;
use App\Models\SkLetter;
use App\Models\SkLetterEmployee;
use App\Models\SkMengingat;
use App\Models\SkMenimbang;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * Surat Keputusan (SK).
 *
 * Alur (approver dipilih per SK):
 *   Draft (HeadHR menyusun & bisa mengubah)
 *     → HeadHR mengajukan: approver_1 = HeadHR, status "Approved HR", menunggu approver_2
 *     → approver_2 menyetujui: kalau ada approver_3 status "Approved Director", kalau tidak langsung final
 *     → approver_3 menyetujui: final ("Approved Managing Director" = disetujui penuh)
 *   Approver yang sedang mendapat giliran bisa mengembalikan ke Draft (alasan wajib); persetujuan
 *   dimulai lagi dari awal. HeadHR bisa membatalkan selama belum final.
 *
 * Setelah final, perubahan per karyawan diterapkan sesuai flag jenis SK:
 * - affects_salary   : baris Employee Salary baru berlaku mulai tanggal efektif SK
 *                      (tunjangan lain & BPJS disalin dari gaji terakhir).
 * - affects_position : jabatan, departemen, lokasi, grading, group baru (pivot utama + kolom karyawan).
 * - affects_status   : jenis karyawan baru; SK kategori Termination menjadikan karyawan Resign.
 * Penempatan & status diterapkan pada tanggal efektif: langsung kalau sudah tiba, selain itu oleh
 * command sk:apply-effective (harian). Gaji langsung dicatat karena Employee Salary punya tanggal berlaku.
 */
class SkLetterService
{
    private const PLACEMENT = [
        // kolom SK => [model pivot, FK pivot, kolom di employees_tables]
        'new_position_id'   => [EmployeePosition::class, 'position_id', 'position_id'],
        'new_department_id' => [EmployeeDepartment::class, 'department_id', 'department_id'],
        'new_store_id'      => [EmployeeStore::class, 'store_id', 'store_id'],
    ];

    public function store(array $data): SkLetter
    {
        return DB::transaction(function () use ($data) {
            $sk = SkLetter::create([
                'sk_type_id'      => $data['sk_type_id'],
                'title'           => $data['title'] ?? null,
                'company_id'      => $data['company_id'],
                'approver_1'      => auth()->user()?->employee_id,
                'approver_2'      => $data['approver_2'] ?? null,
                'approver_3'      => $data['approver_3'] ?? null,
                'effective_date'  => $data['effective_date'],
                'inactive_date'   => $data['inactive_date'] ?? null,
                'location'        => $data['location'] ?? '',
                'menetapkan_text' => $data['menetapkan_text'] ?? null,
                'notes'           => $data['notes'] ?? null,
                'status'          => 'Draft',
            ]);

            $this->syncEmployees($sk, $data['employees']);
            $this->syncClauses($sk, $data);

            return $sk;
        });
    }

    /** Ubah SK Draft. Nomor SK & approver_1 tetap. */
    public function update(SkLetter $skLetter, array $data): SkLetter
    {
        DB::transaction(function () use ($skLetter, $data) {
            $sk = SkLetter::whereKey($skLetter->id)->lockForUpdate()->firstOrFail();
            if ($sk->status !== 'Draft') {
                throw ValidationException::withMessages(['status' => 'SK yang sudah diajukan tidak dapat diedit.']);
            }

            $sk->update([
                'sk_type_id'      => $data['sk_type_id'],
                'title'           => $data['title'] ?? $sk->title,
                'company_id'      => $data['company_id'],
                'approver_2'      => $data['approver_2'] ?? null,
                'approver_3'      => $data['approver_3'] ?? null,
                'effective_date'  => $data['effective_date'],
                'inactive_date'   => $data['inactive_date'] ?? null,
                'location'        => $data['location'] ?? '',
                'menetapkan_text' => $data['menetapkan_text'] ?? null,
                'notes'           => $data['notes'] ?? null,
            ]);

            $this->syncEmployees($sk, $data['employees']);
            $this->syncClauses($sk, $data);
        });

        return $skLetter->fresh();
    }

    /** Karyawan: hapus yang dikeluarkan, perbarui yang tetap (snapshot lama dipertahankan), tambah yang baru. */
    private function syncEmployees(SkLetter $sk, array $employees): void
    {
        $keepIds = collect($employees)->pluck('employee_id')->all();
        SkLetterEmployee::where('sk_letter_id', $sk->id)->whereNotIn('employee_id', $keepIds)->delete();

        foreach ($employees as $emp) {
            $values = [];
            foreach (['new_position_id', 'new_department_id', 'new_store_id', 'new_grading_id', 'new_group_id', 'new_status_employee',
                      'basic_salary', 'positional_allowance', 'daily_rate', 'notes'] as $field) {
                $v = $emp[$field] ?? null;
                $values[$field] = $v === '' ? null : $v;
            }

            $row = SkLetterEmployee::where('sk_letter_id', $sk->id)->where('employee_id', $emp['employee_id'])->first();
            if ($row) {
                $row->update($values);
                continue;
            }

            // Snapshot kondisi sekarang (tercetak sebagai "dari" dan dipakai riwayat)
            $employee = Employee::findOrFail($emp['employee_id']);
            SkLetterEmployee::create($values + [
                'sk_letter_id'             => $sk->id,
                'employee_id'              => $employee->id,
                'previous_structure_id'    => $employee->structure_id,
                'position_id'              => $employee->primaryPosition()->value('position_tables.id') ?? $employee->position_id,
                'department_id'            => $employee->primaryDepartment()->value('departments_tables.id') ?? $employee->department_id,
                'previous_store_id'        => $employee->primaryStore()->value('stores_tables.id') ?? $employee->store_id,
                'grading_id'               => $employee->grading_id,
                'group_id'                 => $employee->group_id,
                'previous_status_employee' => $employee->status_employee,
            ]);
        }
    }

    /** Menimbang / mengingat / keputusan ditulis ulang sesuai urutan di form (baris kosong dilewati). */
    private function syncClauses(SkLetter $sk, array $data): void
    {
        foreach ([
            'menimbang' => [SkMenimbang::class, 'content_menimbang'],
            'mengingat' => [SkMengingat::class, 'content_mengingat'],
            'keputusan' => [SkKeputusan::class, 'content_keputusan'],
        ] as $key => [$model, $column]) {
            $model::where('sk_letter_id', $sk->id)->delete();
            $order = 0;
            foreach ((array) ($data[$key] ?? []) as $text) {
                if (trim((string) $text) === '') continue;
                $model::create(['sk_letter_id' => $sk->id, $column => $text, 'order_no' => ++$order]);
            }
        }
    }

    // ─────────────────────────────────────────
    // Approval
    // ─────────────────────────────────────────

    /** HeadHR mengajukan Draft: tercatat sebagai approver 1, lalu menunggu approver 2. */
    public function submit(SkLetter $skLetter, Employee $hr): SkLetter
    {
        DB::transaction(function () use ($skLetter, $hr) {
            $sk = SkLetter::whereKey($skLetter->id)->lockForUpdate()->firstOrFail();
            if ($sk->status !== 'Draft') {
                throw ValidationException::withMessages(['status' => 'SK ini sudah diajukan.']);
            }
            if (!$sk->approver_2) {
                throw ValidationException::withMessages(['approver_2' => 'Pilih approver 2 dulu lewat Edit SK.']);
            }
            if (!SkLetterEmployee::where('sk_letter_id', $sk->id)->exists()) {
                throw ValidationException::withMessages(['employees' => 'SK belum berisi karyawan.']);
            }
            if (in_array($hr->id, array_filter([$sk->approver_2, $sk->approver_3]), true)) {
                throw ValidationException::withMessages(['approver_2' => 'Pengaju tidak boleh menjadi approver 2 atau 3.']);
            }

            $sk->update([
                'status'               => 'Approved HR',
                'approver_1'           => $hr->id,
                'approver_1_at'        => now(),
                'approver_1_signature' => $this->snapshotSignature($sk, $hr, 1),
                'approver_2_at'        => null,
                'approver_3_at'        => null,
                'returned_note'        => null,
                'returned_by'          => null,
                'returned_at'          => null,
            ]);
        });

        return $skLetter->fresh();
    }

    /** Approver yang sedang mendapat giliran menyetujui. */
    public function approve(SkLetter $skLetter, Employee $approver): SkLetter
    {
        $final = DB::transaction(function () use ($skLetter, $approver) {
            $sk = SkLetter::whereKey($skLetter->id)->lockForUpdate()->firstOrFail();
            $step = $sk->currentStep();
            if (!$step || $sk->{$step[0]} !== $approver->id) {
                throw ValidationException::withMessages(['status' => 'SK ini tidak sedang menunggu persetujuan Anda.']);
            }

            $n = $step[0] === 'approver_2' ? 2 : 3;
            $final = $n === 3 || !$sk->approver_3;
            $sk->update([
                $step[1]                  => now(),
                "approver_{$n}_signature" => $this->snapshotSignature($sk, $approver, $n),
                'status'                  => $final ? SkLetter::FINAL_STATUS : 'Approved Director',
                'completed_at'            => $final ? now() : null,
            ]);

            return $final;
        });

        $skLetter->refresh();
        if ($final) {
            $this->applyApproved($skLetter);
        }

        return $skLetter;
    }

    /** Dikembalikan ke Draft untuk diperbaiki; persetujuan dimulai lagi dari awal. */
    public function returnToDraft(SkLetter $skLetter, Employee $approver, string $note): SkLetter
    {
        DB::transaction(function () use ($skLetter, $approver, $note) {
            $sk = SkLetter::whereKey($skLetter->id)->lockForUpdate()->firstOrFail();
            if (!$sk->currentStep() || $sk->currentApproverId() !== $approver->id) {
                throw ValidationException::withMessages(['status' => 'SK ini tidak sedang menunggu persetujuan Anda.']);
            }
            $sk->update([
                'status'        => 'Draft',
                'approver_1_at' => null,
                'approver_2_at' => null,
                'approver_3_at' => null,
                'returned_note' => $note,
                'returned_by'   => $approver->id,
                'returned_at'   => now(),
            ]);
        });

        return $skLetter->fresh();
    }

    public function cancel(SkLetter $skLetter): SkLetter
    {
        DB::transaction(function () use ($skLetter) {
            $sk = SkLetter::whereKey($skLetter->id)->lockForUpdate()->firstOrFail();
            if (in_array($sk->status, [SkLetter::FINAL_STATUS, 'Cancelled'], true)) {
                throw ValidationException::withMessages(['status' => 'SK yang sudah disetujui penuh atau dibatalkan tidak bisa dibatalkan.']);
            }
            $sk->update(['status' => 'Cancelled']);
        });

        return $skLetter->fresh();
    }

    /** Salinan tanda tangan profil saat menyetujui, supaya PDF tidak berubah kalau tanda tangan diganti. */
    private function snapshotSignature(SkLetter $sk, Employee $employee, int $n): ?string
    {
        $source = $employee->signature;
        if (!$source) return null;
        $disk = Storage::disk('s3');
        try {
            if ($disk->exists($source)) {
                $target = "sk-letters/signatures/{$sk->id}/approver-{$n}.png";
                $disk->copy($source, $target);
                return $target;
            }
        } catch (\Throwable $e) {
            report($e);
        }
        return $source;
    }

    // ─────────────────────────────────────────
    // Penerapan ke data karyawan
    // ─────────────────────────────────────────

    /** Dipanggil saat SK disetujui penuh. */
    public function applyApproved(SkLetter $sk): void
    {
        $sk->loadMissing('sktype');

        if ($sk->sktype?->affects_salary) {
            foreach (SkLetterEmployee::where('sk_letter_id', $sk->id)->whereNull('salary_id')->get() as $row) {
                $this->applySalary($sk, $row);
            }
        }

        if ($sk->effective_date && $sk->effective_date->copy()->startOfDay()->lte(today())) {
            $this->applyPlacement($sk);
        }
    }

    /** Penempatan & status untuk SK final yang tanggal efektifnya sudah tiba (dipanggil juga oleh scheduler). */
    public function applyPlacement(SkLetter $sk): int
    {
        $sk->loadMissing('sktype');
        $type = $sk->sktype;
        if (!$type || (!$type->affects_position && !$type->affects_status)) {
            SkLetterEmployee::where('sk_letter_id', $sk->id)->whereNull('applied_at')->update(['applied_at' => now()]);
            return 0;
        }

        $done = 0;
        foreach (SkLetterEmployee::where('sk_letter_id', $sk->id)->whereNull('applied_at')->get() as $row) {
            DB::transaction(function () use ($sk, $type, $row) {
                $employee = Employee::whereKey($row->employee_id)->lockForUpdate()->first();
                if (!$employee) return;

                $updates = [];
                if ($type->affects_position) {
                    foreach (self::PLACEMENT as $field => [$pivotClass, $fk, $column]) {
                        if (!$row->{$field}) continue;
                        $pivotClass::where('employee_id', $employee->id)->where('is_primary', true)
                            ->where($fk, '!=', $row->{$field})->update(['is_primary' => false]);
                        $pivot = $pivotClass::where('employee_id', $employee->id)->where($fk, $row->{$field})->first();
                        $pivot ? $pivot->update(['is_primary' => true])
                               : $pivotClass::create(['employee_id' => $employee->id, $fk => $row->{$field}, 'is_primary' => true]);
                        $updates[$column] = $row->{$field};
                    }
                    if ($row->new_grading_id) $updates['grading_id'] = $row->new_grading_id;
                    if ($row->new_group_id)   $updates['group_id'] = $row->new_group_id;
                }
                if ($type->affects_status) {
                    if ($type->categories === 'Termination') {
                        $updates['status'] = 'Resign';
                        $updates['end_date'] = $sk->effective_date;
                    } elseif ($row->new_status_employee) {
                        $updates['status_employee'] = $row->new_status_employee;
                    }
                }

                if ($updates) {
                    $employee->update($updates);
                }
                $row->update(['applied_at' => now()]);
            });
            $done++;
        }

        return $done;
    }

    /** Gaji baru berlaku mulai tanggal efektif SK; komponen lain disalin dari gaji terakhir. */
    private function applySalary(SkLetter $sk, SkLetterEmployee $row): void
    {
        if ($row->basic_salary === null && $row->positional_allowance === null && $row->daily_rate === null) {
            return;
        }

        $latest = EmployeeSalary::where('employee_id', $row->employee_id)->latest('effective_date')->first();
        $salary = EmployeeSalary::updateOrCreate(
            ['employee_id' => $row->employee_id, 'effective_date' => $sk->effective_date->toDateString()],
            [
                'basic_salary'         => $row->basic_salary ?? $latest?->basic_salary ?? 0,
                'position_allowance'   => $row->positional_allowance ?? $latest?->position_allowance ?? 0,
                'daily_rate'           => $row->daily_rate ?? $latest?->daily_rate ?? 0,
                'meal_allowance'       => $latest?->meal_allowance ?? 0,
                'transport_allowance'  => $latest?->transport_allowance ?? 0,
                'house_allowance'      => $latest?->house_allowance ?? 0,
                'bpjs_ketenagakerjaan' => $latest?->bpjs_ketenagakerjaan ?? 0,
                'bpjs_kesehatan'       => $latest?->bpjs_kesehatan ?? 0,
                'created_by'           => auth()->user()?->employee_id,
            ]
        );
        $row->update(['salary_id' => $salary->id]);
        // Nominal tidak ditulis ke log (sensitif)
        Log::info('SK: gaji baru dicatat', ['sk_letter_id' => $sk->id, 'employee_id' => $row->employee_id, 'effective_date' => $sk->effective_date->toDateString()]);
    }

    // ─────────────────────────────────────────
    // Generate Contract dari SK (belum ada tombolnya di UI)
    // ─────────────────────────────────────────

    public function generateContract(SkLetter $skLetter, array $data): Contract
    {
        if (!$skLetter->isFinal()) {
            throw new \Exception('Contract hanya dapat dibuat setelah SK disetujui penuh.');
        }

        $employee = Employee::findOrFail($data['employee_id']);
        $pivot = SkLetterEmployee::where('sk_letter_id', $skLetter->id)->where('employee_id', $employee->id)->firstOrFail();

        if (Contract::where('sk_letter_id', $skLetter->id)->where('employee_id', $employee->id)->exists()) {
            throw new \Exception('Contract untuk karyawan ini sudah pernah dibuat dari SK yang sama.');
        }

        return DB::transaction(fn() => Contract::create([
            'employee_id'          => $employee->id,
            'sk_letter_id'         => $skLetter->id,
            'issuer_company_id'    => $skLetter->company_id,
            'structure_id'         => $pivot->new_structure_id,
            // Penempatan BARU dari SK (dulu keliru memakai snapshot lama)
            'position_id'          => $pivot->new_position_id ?? $pivot->position_id,
            'group_id'             => $pivot->new_group_id ?? $pivot->group_id,
            'grading_id'           => $pivot->new_grading_id ?? $pivot->grading_id,
            'company_id'           => $employee->company_id,
            'department_id'        => $pivot->new_department_id ?? $pivot->department_id,
            'basic_salary'         => $pivot->basic_salary,
            'positional_allowance' => $pivot->positional_allowance,
            'daily_rate'           => $pivot->daily_rate,
            'contract_type'        => $data['contract_type'],
            'start_date'           => $data['start_date'] ?? $skLetter->effective_date,
            'end_date'             => $data['end_date'] ?? null,
            'contract_status'      => 'Active',
            'notes'                => $data['notes'] ?? null,
        ]));
    }
}
