<?php

namespace App\Http\Controllers;

use App\Models\Position;
use App\Models\PositionResponsibility;
use App\Rules\NoXSSInput;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\DataTables;

/**
 * Master jabatan (route dijaga permission:ManagePositions).
 *
 * Jabatan dipakai karyawan (employee_positions + kolom position_id di employees_tables), SK, kontrak,
 * dan struktur organisasi. FK employees_tables → position_tables ON DELETE CASCADE, jadi jabatan
 * sengaja tidak bisa dihapus dari aplikasi: menghapusnya ikut menghapus data karyawan.
 */
class PositionController extends Controller
{
    /** Daftar isian per jabatan: field form => [tipe di position_responsibilities, label]. */
    public const LISTS = [
        'key_respon'    => [PositionResponsibility::TYPE_KEY_RESPON, 'Tanggung jawab utama'],
        'qualification' => [PositionResponsibility::TYPE_QUALIFICATION, 'Kualifikasi'],
        'requirement'   => [PositionResponsibility::TYPE_REQUIREMENT, 'Persyaratan pelamar'],
        'skill'         => [PositionResponsibility::TYPE_SKILL, 'Keahlian'],
        'benefit'       => [PositionResponsibility::TYPE_BENEFIT, 'Keuntungan'],
        'allowance'     => [PositionResponsibility::TYPE_ALLOWANCE, 'Tunjangan'],
    ];

    private const MAX_ITEMS = 30;

    public function index()
    {
        return view('pages.Position.Position');
    }

    public function getPositions(Request $request)
    {
        $positions = Position::orderBy('name')->get(['id', 'name', 'role_summary', 'publish_career', 'vacancy', 'career_start_date', 'career_end_date']);

        $employees = DB::table('employee_positions as ep')
            ->join('employees_tables as e', 'e.id', '=', 'ep.employee_id')
            ->where('ep.is_primary', true)
            ->groupBy('ep.position_id')
            ->get([
                'ep.position_id',
                DB::raw("sum(e.status = 'Active') as active"),
                DB::raw("sum(e.status in ('Pending', 'On Leave')) as other"),
            ])
            ->keyBy('position_id');

        $items = DB::table('position_responsibilities')
            ->groupBy('position_id', 'type')
            ->get(['position_id', 'type', DB::raw('count(*) as c')])
            ->groupBy('position_id');

        $rows = $positions->map(function (Position $p) use ($employees, $items) {
            $emp    = $employees->get($p->id);
            $counts = collect($items->get($p->id, []))->pluck('c', 'type');

            return [
                'name'          => $p->name,
                'role_summary'  => $p->role_summary,
                'employees'     => (int) ($emp->active ?? 0),
                'employees_other' => (int) ($emp->other ?? 0),
                'key_respon'    => (int) ($counts[PositionResponsibility::TYPE_KEY_RESPON] ?? 0),
                'qualification' => (int) ($counts[PositionResponsibility::TYPE_QUALIFICATION] ?? 0),
                'career'        => $this->careerState($p),
                'vacancy'       => $p->vacancy,
                'career_start'  => $p->career_start_date?->format('d M Y'),
                'career_end'    => $p->career_end_date?->format('d M Y'),
                'edit_url'      => route('Position.edit', $this->hashId($p->id)),
            ];
        });

        $noDesc = fn($r) => !$r['role_summary'] && !$r['key_respon'];
        $stats = [
            'total'       => $rows->count(),
            'used'        => $rows->where('employees', '>', 0)->count(),
            'unused'      => $rows->where('employees', 0)->where('employees_other', 0)->count(),
            'no_job_desc' => $rows->where('employees', '>', 0)->filter($noDesc)->count(),
            'career_open' => $rows->where('career', 'open')->count(),
        ];

        $rows = match ($request->input('flag')) {
            'used'        => $rows->where('employees', '>', 0),
            'unused'      => $rows->where('employees', 0)->where('employees_other', 0),
            'no_job_desc' => $rows->where('employees', '>', 0)->filter($noDesc),
            'career'      => $rows->where('career', '!=', 'draft'),
            default       => $rows,
        };

        // Teks di-escape di browser (esc), jadi tidak di-escape dua kali.
        return DataTables::of($rows->values())->escapeColumns([])->with('stats', $stats)->make(true);
    }

    public function create()
    {
        return view('pages.Position.create', $this->formData(null));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, null);

        try {
            $position = DB::transaction(function () use ($data) {
                $position = Position::create($data['position']);
                $this->saveLists($position, $data['lists']);
                return $position;
            });
        } catch (\Throwable $e) {
            Log::error('Gagal menyimpan jabatan', ['message' => $e->getMessage()]);
            return back()->withInput()->withErrors(['error' => 'Jabatan tidak bisa disimpan. Coba lagi atau cek storage/logs/laravel.log.']);
        }

        return redirect()->route('pages.Position')->with('success', "Jabatan {$position->name} ditambahkan.");
    }

    public function edit($hashedId)
    {
        $position = $this->findByHash($hashedId);
        abort_unless($position, 404, 'Jabatan tidak ditemukan.');

        return view('pages.Position.edit', $this->formData($position) + ['hashedId' => $hashedId]);
    }

    public function update(Request $request, $hashedId)
    {
        $position = $this->findByHash($hashedId);
        if (!$position) {
            return redirect()->route('pages.Position')->with('error', 'Jabatan tidak ditemukan.');
        }

        $data = $this->validated($request, $position);

        try {
            DB::transaction(function () use ($position, $data) {
                $position->update($data['position']);
                PositionResponsibility::where('position_id', $position->id)->delete();
                $this->saveLists($position, $data['lists']);
            });
        } catch (\Throwable $e) {
            Log::error('Gagal memperbarui jabatan', ['id' => $position->id, 'message' => $e->getMessage()]);
            return back()->withInput()->withErrors(['error' => 'Perubahan tidak bisa disimpan. Coba lagi atau cek storage/logs/laravel.log.']);
        }

        return redirect()->route('pages.Position')->with('success', "Jabatan {$position->name} disimpan.");
    }

    // ── Helper ────────────────────────────────────────────────────

    /** ID pendek di URL. config('app.key'), bukan env(): saat config di-cache env() bernilai null. */
    private function hashId(string $id): string
    {
        return substr(hash('sha256', $id . config('app.key')), 0, 8);
    }

    private function findByHash(string $hashedId): ?Position
    {
        $id = Position::pluck('id')->first(fn($id) => hash_equals($this->hashId($id), $hashedId));

        return $id ? Position::with('positionResponsibilities')->find($id) : null;
    }

    /** open = tayang sekarang, scheduled = belum mulai, ended = lewat tanggal, draft = tidak dipublikasikan. */
    private function careerState(Position $p): string
    {
        if (!$p->publish_career) return 'draft';
        $today = today();
        if ($p->career_start_date && $p->career_start_date->gt($today)) return 'scheduled';
        if ($p->career_end_date && $p->career_end_date->lt($today)) return 'ended';
        return 'open';
    }

    private function formData(?Position $position): array
    {
        $lists = [];
        foreach (self::LISTS as $field => [$type]) {
            $lists[$field] = $position
                ? $position->positionResponsibilities->where('type', $type)->sortBy('order')->pluck('description')->values()->all()
                : [];
        }

        $usage = null;
        if ($position) {
            $emp = DB::table('employee_positions as ep')
                ->join('employees_tables as e', 'e.id', '=', 'ep.employee_id')
                ->where('ep.position_id', $position->id)->where('ep.is_primary', true);
            $usage = [
                'active'    => (clone $emp)->where('e.status', 'Active')->count(),
                'other'     => (clone $emp)->whereIn('e.status', ['Pending', 'On Leave'])->count(),
                'names'     => (clone $emp)->where('e.status', 'Active')->orderBy('e.employee_name')->limit(6)->pluck('e.employee_name')->all(),
                'sk'        => DB::table('sk_letter_employees')->where('position_id', $position->id)->count(),
                'contracts' => DB::table('contracts')->where('position_id', $position->id)->count(),
            ];
        }

        return [
            'position' => $position,
            'lists'    => $lists,
            'listMeta' => self::LISTS,
            'usage'    => $usage,
            'career'   => $position ? $this->careerState($position) : 'draft',
        ];
    }

    private function validated(Request $request, ?Position $current): array
    {
        // Baris kosong dari form (tombol tambah yang tidak diisi) dibuang, bukan dianggap error.
        $merge = ['name' => preg_replace('/\s+/', ' ', trim((string) $request->input('name')))];
        foreach (array_keys(self::LISTS) as $field) {
            $merge[$field] = collect((array) $request->input($field, []))
                ->map(fn($v) => preg_replace('/\s+/', ' ', trim((string) $v)))
                ->filter(fn($v) => $v !== '')->values()->all();
        }
        $request->merge($merge);

        $rules = [
            'name'              => ['required', 'string', 'min:2', 'max:100', Rule::unique('position_tables', 'name')->ignore($current?->id), new NoXSSInput()],
            'role_summary'      => ['nullable', 'string', 'max:2000', new NoXSSInput()],
            'publish_career'    => ['nullable', 'boolean'],
            'vacancy'           => ['nullable', 'integer', 'min:1', 'max:999', 'required_if:publish_career,1'],
            'career_start_date' => ['nullable', 'date_format:Y-m-d', 'required_if:publish_career,1'],
            'career_end_date'   => ['nullable', 'date_format:Y-m-d', 'after_or_equal:career_start_date', 'required_if:publish_career,1'],
        ];
        $messages = [
            'name.required'                    => 'Nama jabatan wajib diisi.',
            'name.min'                         => 'Nama jabatan minimal 2 karakter.',
            'name.max'                         => 'Nama jabatan maksimal 100 karakter.',
            'name.unique'                      => 'Nama jabatan sudah ada. Pakai jabatan yang sudah ada atau beri nama lain.',
            'role_summary.max'                 => 'Ringkasan peran maksimal 2.000 karakter.',
            'vacancy.integer'                  => 'Jumlah lowongan harus angka bulat.',
            'vacancy.min'                      => 'Jumlah lowongan minimal 1.',
            'vacancy.required_if'              => 'Isi jumlah lowongan kalau lowongan dipublikasikan.',
            'career_start_date.required_if'    => 'Isi tanggal mulai tayang kalau lowongan dipublikasikan.',
            'career_end_date.required_if'      => 'Isi tanggal selesai tayang kalau lowongan dipublikasikan.',
            'career_end_date.after_or_equal'   => 'Tanggal selesai tayang tidak boleh sebelum tanggal mulai.',
        ];
        foreach (self::LISTS as $field => [, $label]) {
            $rules[$field]        = ['array', 'max:' . self::MAX_ITEMS];
            $rules[$field . '.*'] = ['string', 'max:255', new NoXSSInput()];
            $messages[$field . '.max']   = "{$label} maksimal " . self::MAX_ITEMS . ' baris.';
            $messages[$field . '.*.max'] = "Setiap baris {$label} maksimal 255 karakter.";
        }

        $v = $request->validate($rules, $messages);

        foreach (array_keys(self::LISTS) as $field) {
            $dupes = collect($v[$field])->map(fn($s) => mb_strtolower($s))->duplicates();
            if ($dupes->isNotEmpty()) {
                throw ValidationException::withMessages([$field => self::LISTS[$field][1] . ' berisi baris yang sama dua kali: "' . $v[$field][$dupes->keys()->first()] . '".']);
            }
        }

        $publish = (bool) ($v['publish_career'] ?? false);

        return [
            'position' => [
                'name'              => $v['name'],
                'role_summary'      => filled($v['role_summary'] ?? null) ? trim($v['role_summary']) : null,
                'publish_career'    => $publish,
                // Data lowongan tetap disimpan walau tidak dipublikasikan, supaya bisa ditayangkan lagi.
                // Kolom vacancy NOT NULL default 1: kosong = pakai nilai lama (atau 1 untuk jabatan baru).
                'vacancy'           => $v['vacancy'] ?? ($current?->vacancy ?? 1),
                'career_start_date' => $v['career_start_date'] ?? null,
                'career_end_date'   => $v['career_end_date'] ?? null,
            ],
            'lists' => collect(self::LISTS)->mapWithKeys(fn($meta, $field) => [$field => $v[$field]])->all(),
        ];
    }

    private function saveLists(Position $position, array $lists): void
    {
        $rows = [];
        foreach ($lists as $field => $items) {
            foreach ($items as $order => $description) {
                $rows[] = [
                    'position_id' => $position->id,
                    'type'        => self::LISTS[$field][0],
                    'description' => $description,
                    'order'       => $order,
                ];
            }
        }
        foreach ($rows as $row) {
            PositionResponsibility::create($row); // lewat model supaya UUID & timestamp terisi
        }
    }
}
