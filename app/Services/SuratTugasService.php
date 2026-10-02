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
 * Handles the department/store/position pivot mutations behind a Surat
 * Tugas (ST) document: applying a temporary move when the ST is issued,
 * and reverting it (back to whatever was primary before) when the ST is
 * edited or once it expires.
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
     * Create a new Surat Tugas document for an employee and apply its
     * department/store/position move, all inside a locked transaction so
     * concurrent create/edit/expire calls for the same employee serialize
     * instead of racing each other.
     *
     * @param array{issued_date: string, expired_date: string, targets: array<string, string|string[]>} $validated
     */
    public function createSuratTugas(string $employeeId, array $validated): Documents
    {
        return DB::transaction(function () use ($employeeId, $validated) {
            $employee = Employee::where('id', $employeeId)->lockForUpdate()->firstOrFail();

            $config = $this->resolveConfig($employee);

            $hasActive = Documents::where('company_document_config_id', $config->id)
                ->where('employee_id', $employee->id)
                ->where('status', self::ACTIVE_STATUS)
                ->lockForUpdate()
                ->exists();

            if ($hasActive) {
                throw ValidationException::withMessages([
                    'assignment' => 'Karyawan ini masih memiliki Surat Tugas yang sedang aktif.',
                ]);
            }

            $document = Documents::create([
                'company_document_config_id' => $config->id,
                'employee_id'                => $employee->id,
                'issued_by'                  => auth()->user()->employee_id,
                'issued_date'                => $validated['issued_date'],
                'expired_date'               => $validated['expired_date'],
                'status'                     => self::ACTIVE_STATUS,
            ]);

            $this->applyAssignments($employee, $document, $validated['targets']);

            return $document;
        }, 3);
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

    /**
     * Move the employee into the given target department/store(s)/position,
     * recording a snapshot of what changed so it can be reverted later.
     *
     * Each type accepts either a single reference id or an array of ids
     * (e.g. several stores at once). The first id becomes the new primary
     * (swapping out whatever was primary before, exactly like a single
     * select); any further ids are just added as extra, non-primary
     * assignments for the duration of the ST.
     *
     * Caller is responsible for wrapping this in a transaction with the
     * employee row locked (`lockForUpdate`) to avoid concurrent moves.
     *
     * @param array<string, string|string[]> $targets type => reference id(s)
     */
    public function applyAssignments(Employee $employee, Documents $document, array $targets): void
    {
        $denormalized = [];

        foreach ($targets as $type => $ids) {
            [$pivotClass, $fk, $referenceClass, $nameColumn, $employeeColumn] = self::TYPES[$type];

            $ids = array_values(array_unique((array) $ids));

            if (empty($ids)) {
                continue;
            }

            $primaryTargetId = array_shift($ids);

            $previousPivot = $pivotClass::where('employee_id', $employee->id)
                ->where('is_primary', true)
                ->lockForUpdate()
                ->first();

            if ($previousPivot && $previousPivot->{$fk} === $primaryTargetId) {
                throw ValidationException::withMessages([
                    'assignment' => "Karyawan sudah berada di {$type} tujuan tersebut.",
                ]);
            }

            $targetModel = $referenceClass::find($primaryTargetId);

            if (!$targetModel) {
                throw ValidationException::withMessages([
                    'assignment' => "Data tujuan {$type} tidak ditemukan.",
                ]);
            }

            $targetPivot = $pivotClass::where('employee_id', $employee->id)
                ->where($fk, $primaryTargetId)
                ->lockForUpdate()
                ->first();

            $wasCreated = false;

            if ($previousPivot) {
                $previousPivot->update(['is_primary' => false]);
            }

            if ($targetPivot) {
                $targetPivot->update(['is_primary' => true]);
            } else {
                $targetPivot = $pivotClass::create([
                    'employee_id' => $employee->id,
                    $fk           => $primaryTargetId,
                    'is_primary'  => true,
                ]);
                $wasCreated = true;
            }

            $previousName = null;
            if ($previousPivot) {
                $previousReferenceModel = $referenceClass::find($previousPivot->{$fk});
                $previousName = $previousReferenceModel->{$nameColumn} ?? null;
            }

            DocumentTemporaryAssignment::create([
                'document_id'           => $document->id,
                'type'                  => $type,
                'previous_pivot_id'     => $previousPivot?->id,
                'previous_reference_id' => $previousPivot?->{$fk},
                'previous_name'         => $previousName,
                'new_pivot_id'          => $targetPivot->id,
                'new_reference_id'      => $primaryTargetId,
                'new_name'              => $targetModel->{$nameColumn},
                'was_created'           => $wasCreated,
                'is_primary_change'     => true,
            ]);

            $denormalized[$employeeColumn] = $primaryTargetId;

            // Any further ids are extra, non-primary assignments — the
            // employee just needs to be assigned to them for the duration
            // of the ST, nothing "primary" changes for these.
            foreach ($ids as $extraId) {
                if ($extraId === $primaryTargetId) {
                    continue;
                }

                if ($previousPivot && $previousPivot->{$fk} === $extraId) {
                    // Already assigned (and just demoted above) — nothing to add.
                    continue;
                }

                $extraModel = $referenceClass::find($extraId);

                if (!$extraModel) {
                    throw ValidationException::withMessages([
                        'assignment' => "Data tujuan {$type} tidak ditemukan.",
                    ]);
                }

                $extraPivot = $pivotClass::where('employee_id', $employee->id)
                    ->where($fk, $extraId)
                    ->lockForUpdate()
                    ->first();

                $extraWasCreated = false;

                if (!$extraPivot) {
                    $extraPivot = $pivotClass::create([
                        'employee_id' => $employee->id,
                        $fk           => $extraId,
                        'is_primary'  => false,
                    ]);
                    $extraWasCreated = true;
                }

                DocumentTemporaryAssignment::create([
                    'document_id'           => $document->id,
                    'type'                  => $type,
                    'previous_pivot_id'     => null,
                    'previous_reference_id' => null,
                    'previous_name'         => null,
                    'new_pivot_id'          => $extraPivot->id,
                    'new_reference_id'      => $extraId,
                    'new_name'              => $extraModel->{$nameColumn},
                    'was_created'           => $extraWasCreated,
                    'is_primary_change'     => false,
                ]);
            }
        }

        if (!empty($denormalized)) {
            $employee->update($denormalized);
        }
    }

    /**
     * Undo a set of (not yet reverted) assignment snapshots, restoring the
     * employee's previous department/store/position pivot state.
     *
     * Caller is responsible for wrapping this in a transaction with the
     * employee row locked (`lockForUpdate`) to avoid concurrent moves.
     *
     * @param Collection<int, DocumentTemporaryAssignment> $assignments
     */
    public function revertAssignments(Employee $employee, Collection $assignments): void
    {
        $denormalized = [];

        foreach ($assignments as $assignment) {
            [$pivotClass, , , , $employeeColumn] = self::TYPES[$assignment->type];

            if ($assignment->was_created) {
                $pivotClass::where('id', $assignment->new_pivot_id)->delete();
            } else {
                $pivotClass::where('id', $assignment->new_pivot_id)->update(['is_primary' => false]);
            }

            if ($assignment->previous_pivot_id) {
                $restored = $pivotClass::where('id', $assignment->previous_pivot_id)->first();
                if ($restored) {
                    $restored->update(['is_primary' => true]);
                }
            }

            $assignment->update(['reverted_at' => now()]);

            if ($assignment->is_primary_change) {
                $denormalized[$employeeColumn] = $assignment->previous_reference_id;
            }
        }

        if (!empty($denormalized)) {
            $employee->update($denormalized);
        }
    }
}
