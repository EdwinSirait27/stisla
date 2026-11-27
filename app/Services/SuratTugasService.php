<?php

namespace App\Services;

use App\Models\Companydocumentconfigs;
use App\Models\Departments;
use App\Models\Documents;
use App\Models\DocumentTemporaryAssignment;
use App\Models\Employee;
use App\Models\EmployeeDepartment;
use App\Models\EmployeePosition;
use App\Models\EmployeeStore;
use App\Models\Position;
use App\Models\Stores;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Pemindahan sementara di balik Surat Tugas (ST).
 *
 * Alur:
 * 1. Dibuat: tujuan dicatat sebagai rencana di document_temporary_assignments (applied_at kosong).
 * 2. Diterapkan pada tanggal mulai (langsung kalau tanggal mulai hari ini/sudah lewat, selain itu
 *    oleh documents:expire-surat-tugas): pivot primary dipindah ke tujuan, kolom denormalisasi
 *    karyawan ikut diganti, kondisi "sebelum" dicatat dari pivot primary atau, kalau pivot kosong,
 *    dari kolom karyawan (pivot riwayat belum lengkap untuk semua karyawan).
 * 3. Dikembalikan setelah tanggal selesai lewat (tanggal selesai masih termasuk masa tugas).
 *    Kalau HR sudah memindah karyawan secara manual selama ST, bagian itu tidak ditimpa.
 */
class SuratTugasService
{
    /**
     * Status a Surat Tugas has while it is currently in effect (downloadable
     * / sendable). It moves to 'expired' once its expired_date has passed
     * and the assignment has been reverted.
     */
    public const ACTIVE_STATUS = 'issued';

    /**
     * Map of assignment type => [pivot model, pivot FK column, reference model, reference name column, employee denormalized column].
     */
    private const TYPES = [
        'department' => [EmployeeDepartment::class, 'department_id', Departments::class, 'department_name', 'department_id'],
        'store'      => [EmployeeStore::class, 'store_id', Stores::class, 'name', 'store_id'],
        'position'   => [EmployeePosition::class, 'position_id', Position::class, 'name', 'position_id'],
    ];

    public function resolveConfig(Employee $employee): Companydocumentconfigs
    {
        $config = Companydocumentconfigs::with('documenttypes')
            ->where('company_id', $employee->company_id)
            ->whereHas('documenttypes', fn($q) => $q->where('nickname', 'ST'))
            ->where('is_active', true)
            ->first();

        if (!$config) {
            throw ValidationException::withMessages([
                'assignment' => 'Konfigurasi dokumen Surat Tugas belum tersedia untuk perusahaan karyawan ini.',
            ]);
        }

        return $config;
    }

    /**
     * Buat ST. Tujuan dicatat sebagai rencana; kalau tanggal mulai sudah tiba langsung diterapkan.
     * Satu transaksi dengan baris karyawan dikunci supaya buat/ubah/kedaluwarsa tidak saling balapan.
     *
     * @param array{issued_date: string, expired_date: string, targets: array<string, string|string[]>} $validated
     */
    public function createSuratTugas(string $employeeId, array $validated): Documents
    {
        return DB::transaction(function () use ($employeeId, $validated) {
            $employee = Employee::where('id', $employeeId)->lockForUpdate()->firstOrFail();

            $config = $this->resolveConfig($employee);
            $this->assertNoOverlap($config, $employee, $validated['issued_date'], $validated['expired_date']);

            $document = Documents::create([
                'company_document_config_id' => $config->id,
                'employee_id'                => $employee->id,
                'issued_by'                  => auth()->user()?->employee_id,
                'issued_date'                => $validated['issued_date'],
                'expired_date'               => $validated['expired_date'],
                'status'                     => self::ACTIVE_STATUS,
            ]);

            $this->planAssignments($employee, $document, $validated['targets']);
            if ($this->hasStarted($document)) {
                $this->applyPlanned($employee, $document, true);
            }

            return $document;
        }, 3);
    }

    /**
     * Ubah ST yang masih berlaku/terjadwal: pemindahan yang sudah diterapkan dikembalikan dulu,
     * rencana lama dibuang, lalu tujuan baru direncanakan (dan diterapkan kalau sudah mulai).
     */
    public function updateSuratTugas(string $documentId, array $validated): Documents
    {
        return DB::transaction(function () use ($documentId, $validated) {
            $document = Documents::with('companydocumentconfigs.documenttypes')->where('id', $documentId)->lockForUpdate()->firstOrFail();
            if (($document->companydocumentconfigs->documenttypes->nickname ?? null) !== 'ST') {
                abort(404);
            }
            if ($document->status !== self::ACTIVE_STATUS) {
                throw ValidationException::withMessages(['assignment' => 'Surat Tugas ini sudah kedaluwarsa dan tidak bisa diubah.']);
            }

            $employee = Employee::where('id', $document->employee_id)->lockForUpdate()->firstOrFail();
            $this->assertNoOverlap($document->companydocumentconfigs, $employee, $validated['issued_date'], $validated['expired_date'], $document->id);

            $this->revertDocument($employee, $document);

            $document->update([
                'issued_date'  => $validated['issued_date'],
                'expired_date' => $validated['expired_date'],
            ]);

            $this->planAssignments($employee, $document, $validated['targets']);
            if ($this->hasStarted($document)) {
                $this->applyPlanned($employee, $document, true);
            }

            return $document;
        }, 3);
    }

    /** Tanggal mulai sudah tiba (hari ini atau lewat). */
    public function hasStarted(Documents $document): bool
    {
        return \Carbon\Carbon::parse($document->issued_date)->startOfDay()->lte(today());
    }

    /**
     * Satu karyawan tidak boleh punya dua ST yang periodenya bertumpuk. ST berurutan
     * (yang kedua mulai setelah yang pertama selesai) boleh.
     */
    private function assertNoOverlap(Companydocumentconfigs $config, Employee $employee, string $from, string $until, ?string $exceptId = null): void
    {
        $clash = Documents::where('company_document_config_id', $config->id)
            ->where('employee_id', $employee->id)
            ->where('status', self::ACTIVE_STATUS)
            ->when($exceptId, fn($q) => $q->where('id', '!=', $exceptId))
            ->whereDate('issued_date', '<=', $until)
            ->whereDate('expired_date', '>=', $from)
            ->lockForUpdate()
            ->first();

        if ($clash) {
            throw ValidationException::withMessages([
                'assignment' => "Karyawan ini sudah punya Surat Tugas {$clash->document_number} yang periodenya bertumpuk ("
                    . \Carbon\Carbon::parse($clash->issued_date)->format('d/m/Y') . ' s/d ' . \Carbon\Carbon::parse($clash->expired_date)->format('d/m/Y') . ').',
            ]);
        }
    }

    /**
     * Create a Surat Tugas for each of the given employees, applying the
     * same target assignment and date range to all of them. Each employee
     * is created in its own transaction (see createSuratTugas), so one
     * employee failing (e.g. already has an active ST) does not block the
     * rest of the batch.
     *
     * @param string[] $employeeIds
     * @param array{issued_date: string, expired_date: string, targets: array<string, string|string[]>} $validated
     * @return array{created: Documents[], failed: array<string, string>} failed maps employee_id => reason
     */
    public function createSuratTugasForMany(array $employeeIds, array $validated): array
    {
        $created = [];
        $failed  = [];

        foreach ($employeeIds as $employeeId) {
            try {
                $created[] = $this->createSuratTugas($employeeId, $validated);
            } catch (ValidationException $e) {
                $failed[$employeeId] = collect($e->errors())->flatten()->first() ?? 'Gagal membuat Surat Tugas.';
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('createSuratTugasForMany failed for employee', [
                    'employee_id' => $employeeId,
                    'error'       => $e->getMessage(),
                ]);
                $failed[$employeeId] = 'Gagal membuat Surat Tugas.';
            }
        }

        return ['created' => $created, 'failed' => $failed];
    }

    /** Penempatan utama saat ini: [pivot primary|null, id referensi (pivot, atau kolom karyawan kalau pivot kosong)]. */
    private function currentPrimary(Employee $employee, string $type): array
    {
        [$pivotClass, $fk, , , $employeeColumn] = self::TYPES[$type];
        $pivot = $pivotClass::where('employee_id', $employee->id)->where('is_primary', true)->lockForUpdate()->first();

        return [$pivot, $pivot?->{$fk} ?? $employee->{$employeeColumn}];
    }

    /**
     * Catat tujuan sebagai rencana (belum mengubah pivot). Id pertama per jenis = penempatan utama baru,
     * id berikutnya = tambahan non-utama selama ST.
     *
     * @param array<string, string|string[]> $targets type => reference id(s)
     */
    public function planAssignments(Employee $employee, Documents $document, array $targets): void
    {
        foreach ($targets as $type => $ids) {
            [, , $referenceClass, $nameColumn] = self::TYPES[$type];
            $ids = array_values(array_unique(array_filter((array) $ids)));
            if (!$ids) {
                continue;
            }

            [, $currentRef] = $this->currentPrimary($employee, $type);

            foreach ($ids as $i => $id) {
                $model = $referenceClass::find($id);
                if (!$model) {
                    throw ValidationException::withMessages(['assignment' => "Data tujuan {$type} tidak ditemukan."]);
                }
                $primary = $i === 0;
                DocumentTemporaryAssignment::create([
                    'document_id'           => $document->id,
                    'type'                  => $type,
                    // Perkiraan "dari" untuk PDF; dihitung ulang saat diterapkan
                    'previous_reference_id' => $primary ? $currentRef : null,
                    'previous_name'         => $primary && $currentRef ? $referenceClass::find($currentRef)?->{$nameColumn} : null,
                    'new_pivot_id'          => null,
                    'new_reference_id'      => $id,
                    'new_name'              => $model->{$nameColumn},
                    'was_created'           => false,
                    'is_primary_change'     => $primary,
                    'applied_at'            => null,
                ]);
            }
        }
    }

    /**
     * Terapkan rencana ST yang belum diterapkan. $strict: tolak kalau karyawan sudah berada di tujuan
     * utama (dipakai saat HR menyimpan form); dari scheduler, tujuan yang sudah sama dilewati saja.
     *
     * Pemanggil wajib membungkus dengan transaksi dan mengunci baris karyawan.
     */
    public function applyPlanned(Employee $employee, Documents $document, bool $strict = false): void
    {
        $rows = DocumentTemporaryAssignment::where('document_id', $document->id)
            ->whereNull('applied_at')->whereNull('reverted_at')
            ->orderByDesc('is_primary_change')->orderBy('created_at')
            ->lockForUpdate()->get();

        $denormalized = [];
        $primaryRefs = [];   // type => referensi utama sebelum ST (untuk baris tambahan)

        foreach ($rows as $row) {
            [$pivotClass, $fk, $referenceClass, $nameColumn, $employeeColumn] = self::TYPES[$row->type];
            $target = $row->new_reference_id;

            if ($row->is_primary_change) {
                [$previousPivot, $previousRef] = $this->currentPrimary($employee, $row->type);
                $primaryRefs[$row->type] = $previousRef;

                if ($previousRef === $target) {
                    if ($strict) {
                        throw ValidationException::withMessages(['assignment' => "Karyawan sudah berada di {$row->type} tujuan tersebut."]);
                    }
                    // Sudah di tujuan: tidak ada yang diubah, dan tidak ada yang perlu dikembalikan
                    $row->update(['previous_reference_id' => $previousRef, 'applied_at' => now()]);
                    continue;
                }

                $previousPivot?->update(['is_primary' => false]);
                $targetPivot = $pivotClass::where('employee_id', $employee->id)->where($fk, $target)->lockForUpdate()->first();
                $wasCreated = !$targetPivot;
                if ($targetPivot) {
                    $targetPivot->update(['is_primary' => true]);
                } else {
                    $targetPivot = $pivotClass::create(['employee_id' => $employee->id, $fk => $target, 'is_primary' => true]);
                }

                $row->update([
                    'previous_pivot_id'     => $previousPivot?->id,
                    'previous_reference_id' => $previousRef,
                    'previous_name'         => $previousRef ? $referenceClass::find($previousRef)?->{$nameColumn} : null,
                    'new_pivot_id'          => $targetPivot->id,
                    'was_created'           => $wasCreated,
                    'applied_at'            => now(),
                ]);
                $denormalized[$employeeColumn] = $target;
                continue;
            }

            // Tambahan non-utama: cukup pastikan karyawan tercatat di sana selama ST
            $existing = $pivotClass::where('employee_id', $employee->id)->where($fk, $target)->lockForUpdate()->first();
            if (!$existing && ($primaryRefs[$row->type] ?? null) === $target) {
                // Penempatan utama lama yang baru saja diturunkan: tetap ada, tidak perlu pivot baru
                $row->update(['applied_at' => now()]);
                continue;
            }
            if (!$existing) {
                $existing = $pivotClass::create(['employee_id' => $employee->id, $fk => $target, 'is_primary' => false]);
                $row->update(['new_pivot_id' => $existing->id, 'was_created' => true, 'applied_at' => now()]);
            } else {
                // Sudah tercatat sebelumnya: tidak disentuh saat dikembalikan
                $row->update(['new_pivot_id' => $existing->id, 'was_created' => false, 'applied_at' => now()]);
            }
        }

        if ($denormalized) {
            $employee->update($denormalized);
        }
    }

    /**
     * Kembalikan semua pemindahan ST ini dan tutup rencana yang belum sempat diterapkan.
     * Pemanggil wajib membungkus dengan transaksi dan mengunci baris karyawan.
     */
    public function revertDocument(Employee $employee, Documents $document): void
    {
        $rows = DocumentTemporaryAssignment::where('document_id', $document->id)->whereNull('reverted_at')->lockForUpdate()->get();

        // Rencana yang belum diterapkan: tidak ada yang perlu dikembalikan
        $planned = $rows->whereNull('applied_at');
        if ($planned->isNotEmpty()) {
            DocumentTemporaryAssignment::whereIn('id', $planned->pluck('id'))->delete();
        }

        $this->revertAssignments($employee, $rows->whereNotNull('applied_at')->values());
    }

    /**
     * Kembalikan pemindahan yang sudah diterapkan ke penempatan sebelum ST.
     *
     * Aman terhadap perubahan manual HR selama ST: penempatan utama hanya dikembalikan kalau karyawan
     * masih berada di tujuan ST. Kalau HR sudah memindahnya ke tempat lain, keputusan HR dipertahankan.
     * Pivot dicari dari id-nya, atau dari karyawan + referensi kalau baris pivotnya sudah dibuat ulang.
     *
     * @param Collection<int, DocumentTemporaryAssignment> $assignments
     */
    public function revertAssignments(Employee $employee, Collection $assignments): void
    {
        $denormalized = [];

        // Tambahan dulu, baru penempatan utama
        foreach ($assignments->sortBy(fn($a) => $a->is_primary_change ? 1 : 0) as $assignment) {
            [$pivotClass, $fk, , , $employeeColumn] = self::TYPES[$assignment->type];
            $findPivot = fn(?string $id, ?string $ref) => ($id ? $pivotClass::where('id', $id)->where('employee_id', $employee->id)->first() : null)
                ?? ($ref ? $pivotClass::where('employee_id', $employee->id)->where($fk, $ref)->first() : null);

            if (!$assignment->is_primary_change) {
                $pivot = $assignment->was_created ? $findPivot($assignment->new_pivot_id, $assignment->new_reference_id) : null;
                // Pivot tambahan dari ST dihapus, kecuali HR menjadikannya penempatan utama
                if ($pivot && !$pivot->is_primary) {
                    $pivot->delete();
                }
                $assignment->update(['reverted_at' => now()]);
                continue;
            }

            // Tujuan utama ternyata sudah sama saat diterapkan: tidak ada yang diubah
            if (!$assignment->new_pivot_id) {
                $assignment->update(['reverted_at' => now()]);
                continue;
            }

            [$currentPivot, $currentRef] = $this->currentPrimary($employee, $assignment->type);
            if ($currentRef !== $assignment->new_reference_id) {
                // HR sudah memindah karyawan selama ST: penempatan utama HR tidak ditimpa. Pivot yang
                // dibuat ST (kini bukan utama) tetap dibersihkan supaya tidak tertinggal sebagai lokasi tambahan.
                $stPivot = $assignment->was_created ? $findPivot($assignment->new_pivot_id, $assignment->new_reference_id) : null;
                if ($stPivot && !$stPivot->is_primary) {
                    $stPivot->delete();
                }
                \Illuminate\Support\Facades\Log::info('SuratTugas revert dilewati: penempatan sudah diubah manual', [
                    'document_id' => $assignment->document_id, 'type' => $assignment->type,
                ]);
                $assignment->update(['reverted_at' => now()]);
                continue;
            }

            $newPivot = $findPivot($assignment->new_pivot_id, $assignment->new_reference_id);
            if ($newPivot) {
                $assignment->was_created ? $newPivot->delete() : $newPivot->update(['is_primary' => false]);
            }

            $previousPivot = $findPivot($assignment->previous_pivot_id, $assignment->previous_reference_id);
            if ($previousPivot) {
                $pivotClass::where('employee_id', $employee->id)->where('is_primary', true)->where('id', '!=', $previousPivot->id)->update(['is_primary' => false]);
                $previousPivot->update(['is_primary' => true]);
            }

            // Kolom karyawan kembali ke nilai sebelum ST (dari pivot, atau dari kolom kalau pivot kosong)
            $denormalized[$employeeColumn] = $assignment->previous_reference_id;
            $assignment->update(['reverted_at' => now()]);
        }

        if ($denormalized) {
            $employee->update($denormalized);
        }
    }
}
