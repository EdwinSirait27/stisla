<?php

namespace App\Http\Controllers;

use App\Models\Grading;
use App\Models\Groups;
use App\Rules\NoXSSInput;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTables;

/**
 * Master grading (jenjang jabatan).
 *
 * - level: angka kecil = jenjang lebih tinggi. Employee::atasan() mencari atasan otomatis dari
 *   karyawan dengan level lebih kecil, jadi urutan level menentukan rantai atasan.
 *   Kolom level berupa ENUM dan dibandingkan sebagai teks ('10' < '9'), sehingga level dibatasi 1–9
 *   supaya perbandingan itu tetap benar.
 * - meal_allowance di sini hanya acuan: gaji membaca meal_allowance milik EmployeeSalary.
 * - FK employees_tables → grading ON DELETE CASCADE, jadi grading sengaja tidak bisa dihapus.
 */
class GradingController extends Controller
{
    private const MAX_LEVEL = 9;

    public function index()
    {
        return view('pages.Grading.Grading');
    }

    public function getGradings()
    {
        $rows = $this->ladder()->map(fn($g) => $g + ['edit_url' => route('Grading.edit', $this->hashId($g['id']))]);

        // Teks di-escape di browser (esc), jadi tidak di-escape dua kali.
        return DataTables::of($rows->map(fn($r) => collect($r)->except('id')->all()))
            ->escapeColumns([])
            ->with('stats', [
                'total'      => $rows->count(),
                'employees'  => $rows->sum('employees'),
                'no_grading' => DB::table('employees_tables')->where('status', 'Active')->whereNull('grading_id')->count(),
                'shared'     => $rows->groupBy('level')->filter(fn($g) => $g->count() > 1)->count(),
            ])
            ->make(true);
    }

    public function create()
    {
        return view('pages.Grading.create', $this->formData(null));
    }

    public function edit($hashedId)
    {
        $grading = $this->findByHash($hashedId);
        abort_unless($grading, 404, 'Grading tidak ditemukan.');

        return view('pages.Grading.edit', $this->formData($grading) + ['hashedId' => $hashedId]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, null);

        try {
            $grading = Grading::create($data);
        } catch (\Throwable $e) {
            Log::error('Gagal menyimpan grading', ['message' => $e->getMessage()]);
            return back()->withInput()->withErrors(['error' => 'Grading tidak bisa disimpan. Coba lagi atau cek storage/logs/laravel.log.']);
        }

        return redirect()->route('pages.Grading')->with('success', "Grading {$grading->grading_name} ditambahkan.");
    }

    public function update(Request $request, $hashedId)
    {
        $grading = $this->findByHash($hashedId);
        if (!$grading) {
            return redirect()->route('pages.Grading')->with('error', 'Grading tidak ditemukan.');
        }

        $data = $this->validated($request, $grading);

        try {
            $grading->update($data);
        } catch (\Throwable $e) {
            Log::error('Gagal memperbarui grading', ['id' => $grading->id, 'message' => $e->getMessage()]);
            return back()->withInput()->withErrors(['error' => 'Perubahan tidak bisa disimpan. Coba lagi atau cek storage/logs/laravel.log.']);
        }

        return redirect()->route('pages.Grading')->with('success', "Grading {$grading->grading_name} disimpan.");
    }

    // ── Helper ────────────────────────────────────────────────────

    /** ID pendek di URL. config('app.key'), bukan env(): saat config di-cache env() bernilai null. */
    private function hashId(string $id): string
    {
        return substr(hash('sha256', $id . config('app.key')), 0, 8);
    }

    private function findByHash(string $hashedId): ?Grading
    {
        return Grading::with('groups')->get()->first(fn($g) => hash_equals($this->hashId($g->id), $hashedId));
    }

    /** Semua grading urut level (1 = tertinggi), dengan jumlah karyawan aktif. */
    private function ladder()
    {
        $employees = DB::table('employees_tables')->where('status', 'Active')->whereNotNull('grading_id')
            ->groupBy('grading_id')->pluck(DB::raw('count(*)'), 'grading_id');

        return Grading::with('groups:id,group_name,remark')->get()
            ->sortBy(fn($g) => [$g->level === null || $g->level === '' ? 99 : (int) $g->level, $g->grading_name])
            ->values()
            ->map(fn(Grading $g) => [
                'id'             => $g->id,
                'grading_name'   => $g->grading_name,
                'grading_code'   => $g->grading_code,
                'level'          => $g->level === null || $g->level === '' ? null : (int) $g->level,
                'meal_allowance' => (float) $g->meal_allowance,
                'group_name'     => $g->groups?->group_name,
                'group_remark'   => $g->groups?->remark,
                'employees'      => (int) ($employees[$g->id] ?? 0),
            ]);
    }

    private function formData(?Grading $grading): array
    {
        return [
            'grading'  => $grading,
            'groups'   => Groups::orderBy('group_name')->get(['id', 'group_name', 'remark']),
            'ladder'   => $this->ladder()->map(fn($g) => collect($g)->only(['id', 'grading_name', 'grading_code', 'level', 'employees'])->all())->values(),
            'maxLevel' => self::MAX_LEVEL,
            'employees'=> $grading ? DB::table('employees_tables')->where('grading_id', $grading->id)->where('status', 'Active')->count() : 0,
        ];
    }

    private function validated(Request $request, ?Grading $current): array
    {
        $request->merge([
            'grading_name'   => preg_replace('/\s+/', ' ', trim((string) $request->input('grading_name'))),
            'grading_code'   => strtoupper(preg_replace('/\s+/', '', (string) $request->input('grading_code'))) ?: null,
            'meal_allowance' => preg_replace('/[^\d]/', '', (string) $request->input('meal_allowance')) ?: 0,
        ]);

        $v = $request->validate([
            'grading_name'   => ['required', 'string', 'min:2', 'max:100', Rule::unique('grading', 'grading_name')->ignore($current?->id), new NoXSSInput()],
            'grading_code'   => ['nullable', 'string', 'max:10', 'alpha_num', Rule::unique('grading', 'grading_code')->ignore($current?->id)],
            'level'          => ['required', 'integer', 'min:1', 'max:' . self::MAX_LEVEL],
            'group_id'       => ['nullable', 'string', Rule::exists('groups_tables', 'id')],
            'meal_allowance' => ['required', 'integer', 'min:0', 'max:100000000'],
        ], [
            'grading_name.required' => 'Nama grading wajib diisi.',
            'grading_name.unique'   => 'Nama grading sudah ada.',
            'grading_name.max'      => 'Nama grading maksimal 100 karakter.',
            'grading_code.alpha_num'=> 'Kode hanya huruf dan angka, mis. C3.',
            'grading_code.max'      => 'Kode maksimal 10 karakter.',
            'grading_code.unique'   => 'Kode grading sudah dipakai grading lain.',
            'level.required'        => 'Pilih level.',
            'level.integer'         => 'Level harus angka.',
            'level.min'             => 'Level minimal 1.',
            'level.max'             => 'Level maksimal ' . self::MAX_LEVEL . '.',
            'group_id.exists'       => 'Golongan tidak ditemukan.',
            'meal_allowance.integer'=> 'Uang makan harus angka rupiah.',
            'meal_allowance.max'    => 'Uang makan terlalu besar.',
        ]);

        return [
            'grading_name'   => $v['grading_name'],
            'grading_code'   => $v['grading_code'] ?? null,
            'level'          => (string) $v['level'],
            'group_id'       => $v['group_id'] ?? null,
            'meal_allowance' => (int) $v['meal_allowance'],
        ];
    }
}
