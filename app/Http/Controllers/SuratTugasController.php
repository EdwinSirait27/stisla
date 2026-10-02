<?php

namespace App\Http\Controllers;

use App\Models\Departments;
use App\Models\Documents;
use App\Models\DocumentTemporaryAssignment;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Stores;
use App\Services\SuratTugasService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class SuratTugasController extends Controller
{
    public function __construct(private SuratTugasService $suratTugasService)
    {
    }

    public function create(string $employeeId)
    {
        $this->authorizeManage();

        $employee = Employee::with(['primaryDepartment', 'primaryStore', 'primaryPosition'])
            ->findOrFail($employeeId);

        return view('pages.document.surat-tugas-form', [
            'employee'    => $employee,
            'document'    => null,
            'departments' => Departments::orderBy('department_name')->get(),
            'stores'      => Stores::orderBy('name')->get(),
            'positions'   => Position::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, string $employeeId)
    {
        $this->authorizeManage();

        $validated = $this->validateRequest($request);

        try {
            $this->suratTugasService->createSuratTugas($employeeId, $validated);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (Throwable $e) {
            Log::error('SuratTugasController@store failed', ['error' => $e->getMessage()]);

            return back()
                ->withErrors(['assignment' => 'Gagal membuat Surat Tugas. Silakan coba lagi.'])
                ->withInput();
        }

        return redirect()
            ->route('document.index')
            ->with('success', 'Surat Tugas berhasil dibuat.');
    }

    public function edit(string $documentId)
    {
        $this->authorizeManage();

        $document = Documents::with([
            'employee',
            'companydocumentconfigs.documenttypes',
            'assignments' => fn($q) => $q->whereNull('reverted_at'),
        ])->findOrFail($documentId);

        $this->assertIsSuratTugas($document);

        if ($document->status !== SuratTugasService::ACTIVE_STATUS) {
            abort(403, 'Surat Tugas ini sudah expired dan tidak bisa diedit.');
        }

        return view('pages.document.surat-tugas-form', [
            'employee'    => $document->employee,
            'document'    => $document,
            'departments' => Departments::orderBy('department_name')->get(),
            'stores'      => Stores::orderBy('name')->get(),
            'positions'   => Position::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, string $documentId)
    {
        $this->authorizeManage();

        $validated = $this->validateRequest($request);

        try {
            DB::transaction(function () use ($documentId, $validated) {
                $document = Documents::with('companydocumentconfigs.documenttypes')
                    ->where('id', $documentId)
                    ->lockForUpdate()
                    ->firstOrFail();

                $this->assertIsSuratTugas($document);

                if ($document->status !== SuratTugasService::ACTIVE_STATUS) {
                    throw new RuntimeException('Surat Tugas ini sudah expired dan tidak bisa diedit.');
                }

                $employee = Employee::where('id', $document->employee_id)->lockForUpdate()->firstOrFail();

                // Revert the currently active assignments first, then re-apply
                // the (possibly changed) target so we never double-apply.
                $activeAssignments = DocumentTemporaryAssignment::where('document_id', $document->id)
                    ->whereNull('reverted_at')
                    ->lockForUpdate()
                    ->get();

                $this->suratTugasService->revertAssignments($employee, $activeAssignments);

                $document->update([
                    'issued_date'  => $validated['issued_date'],
                    'expired_date' => $validated['expired_date'],
                ]);

                $this->suratTugasService->applyAssignments($employee, $document, $validated['targets']);
            }, 3);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (Throwable $e) {
            Log::error('SuratTugasController@update failed', ['error' => $e->getMessage()]);

            return back()
                ->withErrors(['assignment' => $e->getMessage() ?: 'Gagal mengubah Surat Tugas. Silakan coba lagi.'])
                ->withInput();
        }

        return redirect()
            ->route('document.index')
            ->with('success', 'Surat Tugas berhasil diperbarui.');
    }

    private function authorizeManage(): void
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();

        if (!$user || !$user->hasPermissionTo('ManageDocument')) {
            abort(403);
        }
    }

    private function assertIsSuratTugas(Documents $document): void
    {
        $nickname = $document->companydocumentconfigs->documenttypes->nickname ?? null;

        if ($nickname !== 'ST') {
            abort(404);
        }
    }

    /**
     * @return array{issued_date: string, expired_date: string, targets: array<string, string|string[]>}
     */
    private function validateRequest(Request $request): array
    {
        $validated = $request->validate([
            'department_id' => 'nullable|uuid|exists:departments_tables,id',
            'store_id'      => 'nullable|array',
            'store_id.*'    => 'uuid|exists:stores_tables,id',
            'position_id'   => 'nullable|uuid|exists:position_tables,id',
            'issued_date'   => 'required|date',
            'expired_date'  => 'required|date|after:issued_date',
        ]);

        $targets = array_filter([
            'department' => $validated['department_id'] ?? null,
            'store'      => $validated['store_id'] ?? null,
            'position'   => $validated['position_id'] ?? null,
        ]);

        if (empty($targets)) {
            throw ValidationException::withMessages([
                'assignment' => 'Pilih minimal satu tujuan pemindahan (departemen, store, atau posisi).',
            ]);
        }

        return [
            'issued_date'  => $validated['issued_date'],
            'expired_date' => $validated['expired_date'],
            'targets'      => $targets,
        ];
    }
}
