<?php

namespace App\Http\Controllers;

use App\Models\Leavetypes;
use App\Rules\NoXSSInput;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\DataTables;

/**
 * Master jenis cuti. Datanya dipakai oleh:
 * - LeaverequestController: aturan cuti khusus (gender, menikah, status, lampiran, durasi) dan
 *   roster_day_type yang ditulis ke roster (lalu dihitung PayrollService sebagai hari dibayar).
 * - leave:generate-special (GenerateSpecialLeave): saldo cuti khusus dari default_balance.
 * - leave:anniversary (GiveAnnualLeave): saldo Annual Leave, ID-nya ditulis langsung di command
 *   dengan jatah 12/15 hari, jadi default_balance Annual Leave tidak dipakai.
 * - LeaveController, dashboard Human & Manager: nama dan filter jenis cuti.
 */
class LeavetypesController extends Controller
{
    /** Pilihan aman day_type di grid roster (tiap tipe butuh warna/CSS sendiri). */
    private const ROSTER_DAY_TYPES = ['Leave', 'Cuti Melahirkan'];

    /** Pilihan aturan gender. */
    private const GENDER_RULES = ['all', 'male', 'female'];

    /** Annual Leave: dipakai langsung oleh command leave:anniversary (GiveAnnualLeave). */
    public const ANNUAL_LEAVE_ID = '019ab134-12f3-73ab-a003-d096b76b25f0';

    /** Status kepegawaian yang memang tidak punya hak cuti (LeaverequestController::isBlockedStatus). */
    private const BLOCKED_STATUSES = ['DW', 'ON JOB TRAINING', 'RESIGN'];

    public function index()
    {
        return view('pages.Leavestype.Leavestype', ['year' => now()->year]);
    }

    public function getLeavestypes()
    {
        $year  = now()->year;
        $types = Leavetypes::orderByDesc('is_active')->orderBy('name')->get();

        $balances = DB::table('leave_balances_tables')
            ->where('year', $year)
            ->groupBy('leave_type_id')
            ->get(['leave_type_id', DB::raw('count(*) as employees'), DB::raw('sum(balance_days) as days_left')])
            ->keyBy('leave_type_id');

        $requests = DB::table('leave_requests as r')
            ->join('leave_balances_tables as b', 'b.id', '=', 'r.leave_balance_id')
            ->whereYear('r.start_date', $year)
            ->groupBy('b.leave_type_id', 'r.status')
            ->get(['b.leave_type_id', 'r.status', DB::raw('count(*) as c'), DB::raw('sum(r.total_days) as days')])
            ->groupBy('leave_type_id');

        $rows = $types->map(function (Leavetypes $t) use ($balances, $requests) {
            $bal = $balances->get($t->id);
            $req = collect($requests->get($t->id, []))->keyBy('status');

            return [
                'id_hashed'          => $this->hashId($t->id),
                'name'               => $t->name,
                'is_annual'          => $t->id === self::ANNUAL_LEAVE_ID,
                'is_paid'            => (bool) $t->is_paid,
                'is_special'         => (bool) $t->is_special,
                'is_active'          => (bool) $t->is_active,
                'default_balance'    => $t->default_balance !== null ? (float) $t->default_balance : null,
                'roster_day_type'    => $t->roster_day_type ?: 'Leave',
                'rules'              => $this->ruleList($t),
                'employees'          => (int) ($bal->employees ?? 0),
                'days_left'          => (float) ($bal->days_left ?? 0),
                'req_pending'        => (int) ($req->get('Pending')->c ?? 0),
                'req_approved'       => (int) ($req->get('Approved')->c ?? 0),
                'req_approved_days'  => (float) ($req->get('Approved')->days ?? 0),
                'edit_url'           => route('Leavestype.edit', $this->hashId($t->id)),
            ];
        });

        return DataTables::of($rows)
            ->with('stats', [
                'total'     => $types->count(),
                'active'    => $types->where('is_active', true)->count(),
                'special'   => $types->where('is_special', true)->count(),
                'employees' => (int) DB::table('leave_balances_tables')->where('year', $year)->distinct()->count('employee_id'),
                'pending'   => (int) $rows->sum('req_pending'),
            ])
            ->make(true);
    }

    public function create()
    {
        return view('pages.Leavestype.create', $this->formData(null));
    }

    public function edit($hashedId)
    {
        $type = $this->findByHash($hashedId);
        abort_unless($type, 404, 'Jenis cuti tidak ditemukan.');

        return view('pages.Leavestype.edit', $this->formData($type) + ['hashedId' => $hashedId]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, null);

        try {
            $type = Leavetypes::create($data);
        } catch (\Throwable $e) {
            Log::error('Gagal menyimpan jenis cuti', ['message' => $e->getMessage()]);
            return back()->withInput()->withErrors(['error' => 'Jenis cuti tidak bisa disimpan. Coba lagi atau cek storage/logs/laravel.log.']);
        }

        $next = $type->is_special && $type->default_balance
            ? ' Saldonya belum terbuat: jalankan php artisan leave:generate-special agar karyawan yang memenuhi syarat mendapat saldo.'
            : '';

        return redirect()->route('pages.Leavestype')->with('success', "Jenis cuti {$type->name} ditambahkan.{$next}");
    }

    public function update(Request $request, $hashedId)
    {
        $type = $this->findByHash($hashedId);
        if (!$type) {
            return redirect()->route('pages.Leavestype')->with('error', 'Jenis cuti tidak ditemukan.');
        }

        $data = $this->validated($request, $type);

        try {
            $type->update($data);
        } catch (\Throwable $e) {
            Log::error('Gagal memperbarui jenis cuti', ['id' => $type->id, 'message' => $e->getMessage()]);
            return back()->withInput()->withErrors(['error' => 'Perubahan tidak bisa disimpan. Coba lagi atau cek storage/logs/laravel.log.']);
        }

        return redirect()->route('pages.Leavestype')->with('success', "Jenis cuti {$type->name} disimpan.");
    }

    // ─────────────────────────────────────────────────────────────
    // Helper
    // ─────────────────────────────────────────────────────────────

    /**
     * ID pendek di URL. Memakai config('app.key'), bukan env(): saat config di-cache env() bernilai
     * null sehingga hash-nya hanya sha256 dari ID mentah dan bisa ditebak.
     */
    private function hashId(string $id): string
    {
        return substr(hash('sha256', $id . config('app.key')), 0, 8);
    }

    private function findByHash(string $hashedId): ?Leavetypes
    {
        return Leavetypes::get()->first(fn($t) => hash_equals($this->hashId($t->id), $hashedId));
    }

    /** Status kepegawaian yang bisa dipilih: yang ada di data karyawan, kecuali yang memang tanpa hak cuti. */
    private function statusOptions(?Leavetypes $type): array
    {
        $fromDb = DB::table('employees_tables')->whereNotNull('status_employee')->distinct()->pluck('status_employee')
            ->map(fn($s) => strtoupper(trim($s)))
            ->reject(fn($s) => $s === '' || in_array($s, self::BLOCKED_STATUSES, true));
        $current = collect(explode(',', (string) $type?->allowed_status))->map(fn($s) => strtoupper(trim($s)))->filter();

        return $fromDb->merge($current)->unique()->sort()->values()->all();
    }

    private function formData(?Leavetypes $type): array
    {
        $usage = null;
        if ($type) {
            $year  = now()->year;
            $usage = [
                'employees' => DB::table('leave_balances_tables')->where('leave_type_id', $type->id)->where('year', $year)->count(),
                'requests'  => DB::table('leave_requests as r')->join('leave_balances_tables as b', 'b.id', '=', 'r.leave_balance_id')
                    ->where('b.leave_type_id', $type->id)->count(),
                'pending'   => DB::table('leave_requests as r')->join('leave_balances_tables as b', 'b.id', '=', 'r.leave_balance_id')
                    ->where('b.leave_type_id', $type->id)->where('r.status', 'Pending')->count(),
                'year'      => $year,
            ];
        }

        // Karyawan Active per (gender, menikah, status) untuk hitungan "siapa yang bisa mengajukan" di form.
        $eligibility = DB::table('employees_tables')
            ->where('status', 'Active')
            ->groupBy('gender', 'marriage', 'status_employee')
            ->get(['gender', 'marriage', 'status_employee', DB::raw('count(*) as c')])
            ->map(fn($r) => [
                'g' => strtolower(trim((string) $r->gender)),
                'm' => strtolower(trim((string) $r->marriage)) === 'yes',
                's' => strtoupper(trim((string) $r->status_employee)),
                'c' => (int) $r->c,
            ])->values();

        return [
            'type'           => $type,
            'eligibility'    => $eligibility,
            'blockedStatuses'=> self::BLOCKED_STATUSES,
            'isAnnual'       => $type?->id === self::ANNUAL_LEAVE_ID,
            'usage'          => $usage,
            'genderRules'    => self::GENDER_RULES,
            'rosterDayTypes' => self::ROSTER_DAY_TYPES,
            'statusOptions'  => $this->statusOptions($type),
            'selectedStatus' => collect(explode(',', (string) $type?->allowed_status))->map(fn($s) => strtoupper(trim($s)))->filter()->values()->all(),
        ];
    }

    private function validated(Request $request, ?Leavetypes $current): array
    {
        $request->merge(['name' => preg_replace('/\s+/', ' ', trim((string) $request->input('name')))]);
        if (is_array($request->input('allowed_status'))) {
            $request->merge(['allowed_status' => array_values(array_map(fn($s) => strtoupper(trim((string) $s)), $request->input('allowed_status')))]);
        }

        $v = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:100', Rule::unique('leave_types_tables', 'name')->ignore($current?->id), new NoXSSInput()],
            'is_paid'            => ['nullable', 'boolean'],
            'is_active'          => ['nullable', 'boolean'],
            'default_balance'    => ['nullable', 'numeric', 'min:0.5', 'max:365'],
            'is_special'         => ['nullable', 'boolean'],
            'gender_rule'        => ['nullable', Rule::in(self::GENDER_RULES)],
            'fixed_days'         => ['nullable', 'integer', 'min:1', 'max:365'],
            'max_days'           => ['nullable', 'integer', 'min:1', 'max:365'],
            'require_attachment' => ['nullable', 'boolean'],
            'require_married'    => ['nullable', 'boolean'],
            'allowed_status'     => ['nullable', 'array'],
            'allowed_status.*'   => ['string', Rule::in($this->statusOptions($current))],
            'roster_day_type'    => ['nullable', Rule::in(self::ROSTER_DAY_TYPES)],
        ], [
            'name.required'          => 'Nama jenis cuti wajib diisi.',
            'name.min'               => 'Nama jenis cuti minimal 3 karakter.',
            'name.max'               => 'Nama jenis cuti maksimal 100 karakter.',
            'name.unique'            => 'Nama jenis cuti sudah dipakai.',
            'default_balance.numeric'=> 'Jatah saldo harus berupa angka.',
            'default_balance.min'    => 'Jatah saldo minimal 0,5 hari. Kosongkan kalau tidak memakai saldo.',
            'default_balance.max'    => 'Jatah saldo maksimal 365 hari.',
            'fixed_days.integer'     => 'Durasi tetap harus berupa angka bulat.',
            'fixed_days.min'         => 'Durasi tetap minimal 1 hari.',
            'fixed_days.max'         => 'Durasi tetap maksimal 365 hari.',
            'max_days.integer'       => 'Durasi maksimal harus berupa angka bulat.',
            'max_days.min'           => 'Durasi maksimal minimal 1 hari.',
            'max_days.max'           => 'Durasi maksimal maksimal 365 hari.',
            'gender_rule.in'         => 'Aturan gender tidak valid.',
            'allowed_status.*.in'    => 'Status kepegawaian tidak dikenal.',
            'roster_day_type.in'     => 'Tampilan roster tidak valid.',
        ]);

        $isSpecial = (bool) ($v['is_special'] ?? false);
        $isActive  = (bool) ($v['is_active'] ?? false);
        $balance   = isset($v['default_balance']) ? (float) $v['default_balance'] : null;
        $fixed     = $isSpecial ? ($v['fixed_days'] ?? null) : null;
        $max       = $isSpecial && !$fixed ? ($v['max_days'] ?? null) : null;

        $errors = [];
        if ($current?->id === self::ANNUAL_LEAVE_ID) {
            // Annual Leave diberikan command leave:anniversary lewat ID ini; syarat khusus akan
            // membuat leave:generate-special ikut memberi saldo, dan nonaktif menyembunyikannya dari filter.
            if ($isSpecial) $errors['is_special'] = 'Annual Leave tidak bisa dijadikan cuti khusus: saldonya diberikan otomatis oleh leave:anniversary.';
            if (!$isActive) $errors['is_active'] = 'Annual Leave tidak bisa dinonaktifkan: saldonya tetap dibagikan otomatis oleh leave:anniversary.';
        }
        if ($fixed && $balance !== null && $fixed > $balance) {
            $errors['fixed_days'] = "Durasi tetap {$fixed} hari lebih besar dari jatah saldo {$this->days($balance)} hari, jadi cuti ini tidak akan pernah bisa diajukan.";
        }
        if ($max && $balance !== null && $max > $balance) {
            $errors['max_days'] = "Durasi maksimal {$max} hari lebih besar dari jatah saldo {$this->days($balance)} hari.";
        }
        if ($isSpecial && !$current?->id && $balance === null) {
            $errors['default_balance'] = 'Cuti khusus butuh jatah saldo, karena saldonya dibuat dari angka ini oleh leave:generate-special.';
        }
        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        $status = collect($v['allowed_status'] ?? [])->map(fn($s) => strtoupper(trim($s)))->filter()->unique()->sort()->values();

        // Cuti biasa: semua aturan dikembalikan ke netral supaya tidak ada aturan tersembunyi yang tertinggal.
        return [
            'name'               => $v['name'],
            'is_paid'            => (bool) ($v['is_paid'] ?? false),
            'is_active'          => $isActive,
            'default_balance'    => $balance,
            'is_special'         => $isSpecial,
            'gender_rule'        => $isSpecial ? ($v['gender_rule'] ?? 'all') : 'all',
            'fixed_days'         => $fixed,
            'max_days'           => $max,
            'require_attachment' => $isSpecial && (bool) ($v['require_attachment'] ?? false),
            'require_married'    => $isSpecial && (bool) ($v['require_married'] ?? false),
            'allowed_status'     => $isSpecial && $status->isNotEmpty() ? $status->implode(',') : null,
            'roster_day_type'    => $isSpecial ? ($v['roster_day_type'] ?? 'Leave') : 'Leave',
        ];
    }

    private function days(float $v): string
    {
        return rtrim(rtrim(number_format($v, 1, ',', '.'), '0'), ',');
    }

    /** Aturan dalam kalimat pendek, untuk tabel. */
    private function ruleList(Leavetypes $t): array
    {
        if (!$t->is_special) {
            return [];
        }

        return array_values(array_filter([
            $t->gender_rule === 'female' ? 'Khusus perempuan' : ($t->gender_rule === 'male' ? 'Khusus laki-laki' : null),
            $t->require_married ? 'Sudah menikah' : null,
            $t->allowed_status ? 'Status ' . str_replace(',', ', ', $t->allowed_status) : null,
            $t->fixed_days ? "Tepat {$t->fixed_days} hari kalender" : ($t->max_days ? "Maksimal {$t->max_days} hari" : null),
            $t->require_attachment ? 'Wajib lampiran' : null,
            ($t->roster_day_type ?: 'Leave') !== 'Leave' ? "Roster: {$t->roster_day_type}" : null,
        ]));
    }
}
