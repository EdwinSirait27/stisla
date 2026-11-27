<?php

namespace App\Http\Controllers;

use App\Models\Departments;
use App\Models\Documents;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Stores;
use App\Services\SuratTugasService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class SuratTugasController extends Controller
{
    public function __construct(private SuratTugasService $suratTugasService)
    {
    }

    /** Form lama per karyawan: sekarang satu pintu di halaman Buat dokumen, karyawan sudah terpilih. */
    public function create(string $employeeId)
    {
        $this->authorizeManage();
        $employee = Employee::findOrFail($employeeId);

        return redirect()->route('documents.create', ['employee_id' => $employee->id]);
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

        $document->employee->load(['primaryDepartment', 'primaryStore', 'primaryPosition']);

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
            $this->suratTugasService->updateSuratTugas($documentId, $validated);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (Throwable $e) {
            Log::error('SuratTugasController@update failed', ['document_id' => $documentId, 'error' => $e->getMessage()]);

            return back()
                ->withErrors(['assignment' => 'Gagal mengubah Surat Tugas. Silakan coba lagi.'])
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
            'expired_date'  => 'required|date|after_or_equal:issued_date|after_or_equal:today',
        ], [
            'expired_date.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai atau sebelum hari ini.',
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
