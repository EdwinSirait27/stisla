<?php

namespace App\Services;
use App\Models\SkLetter;
use App\Models\SkLetterEmployee;
use App\Models\Contract;
use App\Models\Employee;
use App\Models\SkKeputusan;
use App\Models\SkMengingat;
use App\Models\SkMenimbang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
class SkLetterService
{

// public function store(array $data): SkLetter
// {
//     return DB::transaction(function () use ($data) {

//         // 1. Buat SK Letter
//         $sk = SkLetter::create([
//             'sk_type_id'      => $data['sk_type_id'],
//             'title'           => $data['title'] ?? null,
//             'company_id'      => $data['company_id'],
//             // 'structure_id'    => $data['structure_id'] ?? null,
//             // 'approver_1'      => $data['approver_1'] ?? null,
//             'approver_1' => auth()->id(),
//             'approver_2'      => $data['approver_2'] ?? null,
//             'approver_3'      => $data['approver_3'] ?? null,
//             'effective_date'  => $data['effective_date'],
//             'inactive_date'   => $data['inactive_date'] ?? null,
//             'location'        => $data['location'] ?? null,
//             'menetapkan_text' => $data['menetapkan_text'] ?? null,
//             'notes'           => $data['notes'] ?? null,
//             'status'          => 'Draft',
//         ]);

//         // 2. Insert Employees ke pivot
//         foreach ($data['employees'] as $emp) {
//             // Ambil data employee saat ini untuk snapshot
//             $employee = Employee::findOrFail($emp['employee_id']);

//             SkLetterEmployee::create([
//                 'sk_letter_id'          => $sk->id,
//                 'employee_id'           => $employee->id,

//                 // Snapshot otomatis dari kondisi employee saat ini
//                 'previous_structure_id' => $employee->structure_id,
//                 'position_id'           => $employee->position_id,
//                 'group_id'              => $employee->group_id,
//                 'grading_id'            => $employee->grading_id,
//                 'department_id'         => $employee->department_id,

//                 // Data baru dari HR (bisa null jika tidak berubah)
//                 'new_structure_id'      => $emp['new_structure_id'] ?? null,
//                 'basic_salary'          => $emp['basic_salary'] ?? null,
//                 'positional_allowance'  => $emp['positional_allowance'] ?? null,
//                 'daily_rate'            => $emp['daily_rate'] ?? null,
//                 'notes'                 => $emp['notes'] ?? null,
//             ]);
//         }

//         // 3. Insert Menimbang
//         if (!empty($data['menimbang'])) {
//             foreach ($data['menimbang'] as $i => $text) {
//                 if (empty($text)) continue;
//                 SkMenimbang::create([
//                     'sk_letter_id'      => $sk->id,
//                     'content_menimbang' => $text,
//                     'order_no'          => $i + 1,
//                 ]);
//             }
//         }

//         // 4. Insert Mengingat
//         if (!empty($data['mengingat'])) {
//             foreach ($data['mengingat'] as $i => $text) {
//                 if (empty($text)) continue;
//                 SkMengingat::create([
//                     'sk_letter_id'      => $sk->id,
//                     'content_mengingat' => $text,
//                     'order_no'          => $i + 1,
//                 ]);
//             }
//         }

//         // 5. Insert Keputusan
//         if (!empty($data['keputusan'])) {
//             foreach ($data['keputusan'] as $i => $text) {
//                 if (empty($text)) continue;
//                 SkKeputusan::create([
//                     'sk_letter_id'      => $sk->id,
//                     'content_keputusan' => $text,
//                     'order_no'          => $i + 1,
//                 ]);
//             }
//         }

//         return $sk;
//     });
// }
public function store(array $data): SkLetter
{
    Log::info('SK Service store started', [
        'user_id' => auth()->id(),
        'employee_count' => count($data['employees'] ?? []),
    ]);

    return DB::transaction(function () use ($data) {

        Log::info('Database transaction started');

        // 1. Buat SK Letter
        $sk = SkLetter::create([
            'sk_type_id'      => $data['sk_type_id'],
            'title'           => $data['title'] ?? null,
            'company_id'      => $data['company_id'],

            // 'approver_1'      => auth()->id(),
            'approver_1' => auth()->user()->employee_id,
            'approver_2'      => $data['approver_2'] ?? null,
            'approver_3'      => $data['approver_3'] ?? null,

            'effective_date'  => $data['effective_date'],
            'inactive_date'   => $data['inactive_date'] ?? null,
            'location'        => $data['location'] ?? null,
            'menetapkan_text' => $data['menetapkan_text'] ?? null,
            'notes'           => $data['notes'] ?? null,
            'status'          => 'Draft',
        ]);

        Log::info('SK Letter created', [
            'sk_letter_id' => $sk->id,
            'title'        => $sk->title,
        ]);

        // 2. Insert Employees ke pivot
        foreach ($data['employees'] as $index => $emp) {

            Log::info('Processing employee', [
                'index'       => $index,
                'employee_id' => $emp['employee_id'],
            ]);

            // Snapshot employee lama
            $employee = Employee::findOrFail($emp['employee_id']);

            Log::info('Employee snapshot loaded', [
                'employee_id'           => $employee->id,
                'previous_structure_id' => $employee->structure_id,
                'position_id'           => $employee->position_id,
                'group_id'              => $employee->group_id,
                'grading_id'            => $employee->grading_id,
                'department_id'         => $employee->department_id,
            ]);

            $pivot = SkLetterEmployee::create([
                'sk_letter_id'          => $sk->id,
                'employee_id'           => $employee->id,

                // Snapshot lama
                'previous_structure_id' => $employee->structure_id,
                'position_id'           => $employee->position_id,
                'group_id'              => $employee->group_id,
                'grading_id'            => $employee->grading_id,
                'department_id'         => $employee->department_id,

                // Data baru
                'new_structure_id'      => $emp['new_structure_id'] ?? null,
                'basic_salary'          => $emp['basic_salary'] ?? null,
                'positional_allowance'  => $emp['positional_allowance'] ?? null,
                'daily_rate'            => $emp['daily_rate'] ?? null,
                'notes'                 => $emp['notes'] ?? null,
            ]);

            Log::info('SK employee pivot created', [
                'pivot_id'    => $pivot->id,
                'employee_id' => $employee->id,
            ]);
        }

        // 3. Insert Menimbang
        if (!empty($data['menimbang'])) {

            Log::info('Processing menimbang');

            foreach ($data['menimbang'] as $i => $text) {

                if (empty($text)) {
                    continue;
                }

                SkMenimbang::create([
                    'sk_letter_id'      => $sk->id,
                    'content_menimbang' => $text,
                    'order_no'          => $i + 1,
                ]);

                Log::info('Menimbang created', [
                    'order_no' => $i + 1,
                ]);
            }
        }

        // 4. Insert Mengingat
        if (!empty($data['mengingat'])) {

            Log::info('Processing mengingat');

            foreach ($data['mengingat'] as $i => $text) {

                if (empty($text)) {
                    continue;
                }

                SkMengingat::create([
                    'sk_letter_id'      => $sk->id,
                    'content_mengingat' => $text,
                    'order_no'          => $i + 1,
                ]);

                Log::info('Mengingat created', [
                    'order_no' => $i + 1,
                ]);
            }
        }

        // 5. Insert Keputusan
        if (!empty($data['keputusan'])) {

            Log::info('Processing keputusan');

            foreach ($data['keputusan'] as $i => $text) {

                if (empty($text)) {
                    continue;
                }

                SkKeputusan::create([
                    'sk_letter_id'      => $sk->id,
                    'content_keputusan' => $text,
                    'order_no'          => $i + 1,
                ]);

                Log::info('Keputusan created', [
                    'order_no' => $i + 1,
                ]);
            }
        }

        Log::info('Database transaction committed', [
            'sk_letter_id' => $sk->id,
        ]);

        return $sk;
    });
}
    public function update(SkLetter $skLetter, array $data): SkLetter
    {
        // Hanya bisa edit jika masih Draft
        if ($skLetter->status !== 'Draft') {
            throw new \Exception('SK yang sudah diproses tidak dapat diedit.');
        }
        DB::transaction(function () use ($skLetter, $data) {
            $skLetter->update([
                'sk_type_id'     => $data['sk_type_id'],
                'company_id'     => $data['company_id'],
                // 'structure_id'   => $data['structure_id'] ?? null,
                'approver_1'     => $data['approver_1'] ?? null,
                'approver_2'     => $data['approver_2'] ?? null,
                'approver_3'     => $data['approver_3'] ?? null,
                'effective_date' => $data['effective_date'],
                'inactive_date'  => $data['inactive_date'] ?? null,
                'notes'          => $data['notes'] ?? null,
            ]);
        });
        return $skLetter->fresh();
    }
    public function cancel(SkLetter $skLetter): SkLetter
    {
        if (!in_array($skLetter->status, ['Draft', 'Approved HR'])) {
            throw new \Exception('SK tidak dapat dibatalkan pada status ini.');
        }

        $skLetter->update(['status' => 'Cancelled']);

        return $skLetter->fresh();
    }
    // ─────────────────────────────────────────
    // Approval SK Letter
    // ─────────────────────────────────────────

    public function approve(SkLetter $skLetter): SkLetter
    {
        $user     = Auth::user();
        $now      = now();
        $employee = $user->employee;

        DB::transaction(function () use ($skLetter, $user, $now, $employee) {

            // Step 1 → Approved HR
            if (
                $skLetter->status === 'Draft' &&
                $user->hasRole('HeadHR')
            ) {
                $skLetter->update([
                    'status'        => 'Approved HR',
                    'approver_1'    => $employee->id,
                    'approver_1_at' => $now,
                ]);
                return;
            }

            // Step 2 → Approved Director
            if (
                $skLetter->status === 'Approved HR' &&
                $user->hasRole('Director')
            ) {
                $skLetter->update([
                    'status'        => 'Approved Director',
                    'approver_2'    => $employee->id,
                    'approver_2_at' => $now,
                ]);
                return;
            }

            // Step 3 → Approved Managing Director
            if (
                $skLetter->status === 'Approved Director' &&
                $user->hasRole('Managing Director')
            ) {
                $skLetter->update([
                    'status'        => 'Approved Managing Director',
                    'approver_3'    => $employee->id,
                    'approver_3_at' => $now,
                ]);
                return;
            }

            throw new \Exception('Anda tidak memiliki akses untuk approve SK ini.');
        });

        return $skLetter->fresh();
    }

    // ─────────────────────────────────────────
    // SK Letter Employee
    // ─────────────────────────────────────────

    public function addEmployee(SkLetter $skLetter, array $data): SkLetterEmployee
    {
        if ($skLetter->status !== 'Draft') {
            throw new \Exception('Karyawan hanya dapat ditambahkan saat SK masih Draft.');
        }

        // Cek duplikasi
        $exists = $skLetter->employees()
            ->where('employee_id', $data['employee_id'])
            ->exists();

        if ($exists) {
            throw new \Exception('Karyawan sudah ada di SK ini.');
        }

        $employee = Employee::findOrFail($data['employee_id']);

        return DB::transaction(function () use ($skLetter, $data, $employee) {
            return SkLetterEmployee::create([
                'sk_letter_id'          => $skLetter->id,
                'employee_id'           => $employee->id,
                // Rekam kondisi lama employee (snapshot)
                'previous_structure_id' => $employee->structure_id,
                'position_id'           => $employee->position_id,
                'group_id'              => $employee->group_id,
                'grading_id'            => $employee->grading_id,
                'department_id'         => $employee->department_id,
                // Data baru dari HR
                'new_structure_id'      => $data['new_structure_id'] ?? $employee->structure_id,
                'basic_salary'          => $data['basic_salary'] ?? null,
                'positional_allowance'  => $data['positional_allowance'] ?? null,
                'daily_rate'            => $data['daily_rate'] ?? null,
                'notes'                 => $data['notes'] ?? null,
            ]);
        });
    }

    public function updateEmployee(SkLetterEmployee $skLetterEmployee, array $data): SkLetterEmployee
    {
        if ($skLetterEmployee->skLetter->status !== 'Draft') {
            throw new \Exception('Data karyawan hanya dapat diubah saat SK masih Draft.');
        }
        $skLetterEmployee->update([
            'new_structure_id'     => $data['new_structure_id'] ?? $skLetterEmployee->new_structure_id,
            'basic_salary'         => $data['basic_salary'] ?? $skLetterEmployee->basic_salary,
            'positional_allowance' => $data['positional_allowance'] ?? $skLetterEmployee->positional_allowance,
            'daily_rate'           => $data['daily_rate'] ?? $skLetterEmployee->daily_rate,
            'notes'                => $data['notes'] ?? $skLetterEmployee->notes,
        ]);

        return $skLetterEmployee->fresh();
    }

    public function removeEmployee(SkLetterEmployee $skLetterEmployee): void
    {
        if ($skLetterEmployee->skLetter->status !== 'Draft') {
            throw new \Exception('Karyawan hanya dapat dihapus saat SK masih Draft.');
        }

        $skLetterEmployee->delete();
    }

    // ─────────────────────────────────────────
    // Generate Contract dari SK
    // ─────────────────────────────────────────

    public function generateContract(SkLetter $skLetter, array $data): Contract
    {
        if ($skLetter->status !== 'Approved Managing Director') {
            throw new \Exception('Contract hanya dapat dibuat setelah SK fully approved.');
        }

        $employee = Employee::findOrFail($data['employee_id']);

        // Ambil data pivot karyawan di SK ini
        $pivot = SkLetterEmployee::where('sk_letter_id', $skLetter->id)
            ->where('employee_id', $employee->id)
            ->firstOrFail();

        // Cek apakah contract untuk SK + employee ini sudah ada
        $contractExists = Contract::where('sk_letter_id', $skLetter->id)
            ->where('employee_id', $employee->id)
            ->exists();

        if ($contractExists) {
            throw new \Exception('Contract untuk karyawan ini sudah pernah dibuat dari SK yang sama.');
        }

        return DB::transaction(function () use ($skLetter, $employee, $pivot, $data) {
            return Contract::create([
                'employee_id'          => $employee->id,
                'sk_letter_id'         => $skLetter->id,
                'issuer_company_id'    => $skLetter->company_id,
                // Snapshot dari pivot
                'structure_id'         => $pivot->new_structure_id,
                'position_id'          => $pivot->position_id,
                'group_id'             => $pivot->group_id,
                'grading_id'           => $pivot->grading_id,
                'company_id'           => $employee->company_id,
                'department_id'        => $pivot->department_id,
                // Kompensasi dari pivot
                'basic_salary'         => $pivot->basic_salary,
                'positional_allowance' => $pivot->positional_allowance,
                'daily_rate'           => $pivot->daily_rate,
                // Data contract dari request
                'contract_type'        => $data['contract_type'],
                'start_date'           => $data['start_date'] ?? $skLetter->effective_date,
                'end_date'             => $data['end_date'] ?? null,
                'contract_status'      => 'Active',
                'notes'                => $data['notes'] ?? null,
            ]);
        });
    }
}