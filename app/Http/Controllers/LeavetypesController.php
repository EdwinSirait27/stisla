<?php

namespace App\Http\Controllers;

use App\Models\Leaves;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\Leavetypes;
use App\Rules\NoXSSInput;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LeavetypesController extends Controller
{
    /** Pilihan aman day_type di grid roster (tiap tipe butuh warna/CSS sendiri). */
    private const ROSTER_DAY_TYPES = ['Leave', 'Cuti Melahirkan'];

    /** Pilihan aturan gender. */
    private const GENDER_RULES = ['all', 'male', 'female'];

    public function index()
    {
        return view('pages.Leavestype.Leavestype');
    }

    public function getLeavestypes()
    {
        $leavestypes = Leavetypes::select([
            'id',
            'name',
            'is_paid',
            'default_balance',
            'is_special',
            'gender_rule',
            'fixed_days',
            'max_days',
            'require_attachment',
            'require_married',
            'allowed_status',
            'roster_day_type',
            'is_active',
        ])
            ->get()
            ->map(function ($type) {
                $type->id_hashed = substr(hash('sha256', $type->id . env('APP_KEY')), 0, 8);

                // Ringkasan aturan agar HR bisa lihat sekilas tanpa buka Edit.
                $type->tipe = $type->is_special
                    ? '<span class="badge badge-warning">Khusus</span>'
                    : '<span class="badge badge-light">Biasa</span>';

                $type->aturan = $this->ringkasAturan($type);

                $type->aktif = $type->is_active
                    ? '<span class="badge badge-primary">Aktif</span>'
                    : '<span class="badge badge-danger">Nonaktif</span>';

                $type->action = '
                    <a href="' . route('Leavestype.edit', $type->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Edit user"title="Edit Type: ' . e($type->name) . '">
                        <i class="fas fa-user-edit text-secondary"></i>
                    </a>';

                return $type;
            });

        return DataTables::of($leavestypes)
            ->rawColumns(['action', 'tipe', 'aturan', 'aktif'])
            ->make(true);
    }

    public function edit($hashedId)
    {
        $type = Leavetypes::get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });

        if (!$type) {
            abort(404, 'Leave Type not found.');
        }

        return view('pages.Leavestype.edit', [
            'type'           => $type,
            'hashedId'       => $hashedId,
            'genderRules'    => self::GENDER_RULES,
            'rosterDayTypes' => self::ROSTER_DAY_TYPES,
        ]);
    }

    public function create()
    {
        return view('pages.Leavestype.create', [
            'genderRules'    => self::GENDER_RULES,
            'rosterDayTypes' => self::ROSTER_DAY_TYPES,
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate(
            $this->rules(),
            $this->messages()
        );

        try {
            DB::beginTransaction();

            $type = Leavetypes::create($this->buildData($validatedData));

            DB::commit();

            return redirect()->route('pages.Leavestype')->with('success', 'Leavestype created Succesfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function update(Request $request, $hashedId)
    {
        $type = Leavetypes::get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });

        if (!$type) {
            return redirect()->route('pages.Leavestype')->with('error', 'ID tidak valid.');
        }

        $validatedData = $request->validate(
            $this->rules($type->id),
            $this->messages()
        );

        try {
            DB::beginTransaction();

            $type->update($this->buildData($validatedData));

            DB::commit();

            // FIX: sebelumnya route('pages.Leavetypes') — nama route salah
            // (tidak konsisten dengan store() yang pakai 'pages.Leavestype'),
            // menyebabkan RouteNotFoundException setelah update.
            return redirect()->route('pages.Leavestype')->with('success', 'Leavestype updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage()])
                ->withInput();
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Helper
    // ─────────────────────────────────────────────────────────────

    /**
     * Aturan validasi. $ignoreId diisi saat update agar nama sendiri
     * tidak dianggap duplikat.
     */
    private function rules(?string $ignoreId = null): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('leave_types_tables', 'name')->ignore($ignoreId),
                new NoXSSInput(),
            ],
            'is_paid'         => ['nullable', 'boolean'],
            'default_balance' => ['nullable', 'numeric', 'min:0', 'max:365'],

            // ── aturan Level 2 ──
            'is_special'         => ['nullable', 'boolean'],
            'gender_rule'        => ['nullable', Rule::in(self::GENDER_RULES)],
            'fixed_days'         => ['nullable', 'integer', 'min:1', 'max:365'],
            'max_days'           => ['nullable', 'integer', 'min:1', 'max:365'],
            'require_attachment' => ['nullable', 'boolean'],
            'require_married'    => ['nullable', 'boolean'],
            'allowed_status'     => ['nullable', 'string', 'max:100', new NoXSSInput()],
            'roster_day_type'    => ['nullable', Rule::in(self::ROSTER_DAY_TYPES)],
            'is_active'          => ['nullable', 'boolean'],
        ];
    }

    private function messages(): array
    {
        return [
            'name.required'           => 'name wajib diisi.',
            'name.string'             => 'name hanya boleh berupa teks.',
            'name.max'                => 'Username maksimal terdiri dari 255 karakter.',
            'name.unique'             => 'Nama jenis cuti sudah dipakai.',
            'default_balance.numeric' => 'Jatah saldo harus berupa angka.',
            'default_balance.min'     => 'Jatah saldo tidak boleh negatif.',
            'default_balance.max'     => 'Jatah saldo maksimal 365 hari.',
            'fixed_days.integer'      => 'Durasi dikunci harus berupa angka bulat.',
            'fixed_days.min'          => 'Durasi dikunci minimal 1 hari.',
            'fixed_days.max'          => 'Durasi dikunci maksimal 365 hari.',
            'max_days.integer'        => 'Durasi maksimal harus berupa angka bulat.',
            'max_days.min'            => 'Durasi maksimal minimal 1 hari.',
            'gender_rule.in'          => 'Aturan gender tidak valid.',
            'roster_day_type.in'      => 'Tampilan roster tidak valid.',
        ];
    }

    /**
     * Susun data yang akan disimpan.
     *
     * Kalau is_special TIDAK dicentang, semua kolom aturan dikembalikan ke
     * nilai netral — supaya tidak ada aturan "tersembunyi" yang masih aktif
     * dari pengaturan sebelumnya (mis. jenis cuti pernah khusus lalu diubah
     * jadi biasa, tapi gender_rule='female' tertinggal).
     */
    private function buildData(array $v): array
    {
        $isSpecial = (bool) ($v['is_special'] ?? false);

        $data = [
            'name'            => trim($v['name']),
            'is_paid'         => (bool) ($v['is_paid'] ?? false),
            'default_balance' => $v['default_balance'] ?? null,
            'is_special'      => $isSpecial,
            'is_active'       => (bool) ($v['is_active'] ?? false),
        ];

        if ($isSpecial) {
            // fixed_days dan max_days saling meniadakan: kalau durasi sudah
            // dikunci, batas atas tidak relevan.
            $fixedDays = $v['fixed_days'] ?? null;

            $data += [
                'gender_rule'        => $v['gender_rule'] ?? 'all',
                'fixed_days'         => $fixedDays,
                'max_days'           => $fixedDays ? null : ($v['max_days'] ?? null),
                'require_attachment' => (bool) ($v['require_attachment'] ?? false),
                'require_married'    => (bool) ($v['require_married'] ?? false),
                'allowed_status'     => $this->normalizeStatus($v['allowed_status'] ?? null),
                'roster_day_type'    => $v['roster_day_type'] ?? 'Leave',
            ];
        } else {
            $data += [
                'gender_rule'        => 'all',
                'fixed_days'         => null,
                'max_days'           => null,
                'require_attachment' => false,
                'require_married'    => false,
                'allowed_status'     => null,
                'roster_day_type'    => 'Leave',
            ];
        }

        return $data;
    }

    /** Rapikan csv status: "pkwt, dw" → "PKWT,DW". Kosong → null. */
    private function normalizeStatus(?string $raw): ?string
    {
        $raw = trim((string) $raw);

        if ($raw === '') {
            return null;
        }

        $parts = array_filter(array_map(
            fn($s) => strtoupper(trim($s)),
            explode(',', $raw)
        ));

        return $parts ? implode(',', $parts) : null;
    }

    /** Ringkasan aturan untuk kolom tabel. */
    private function ringkasAturan($type): string
    {
        if (!$type->is_special) {
            return '<span class="text-muted">&mdash;</span>';
        }

        $parts = [];

        if ($type->gender_rule && $type->gender_rule !== 'all') {
            $parts[] = ucfirst($type->gender_rule);
        }
        if ($type->fixed_days) {
            $parts[] = 'Kunci ' . $type->fixed_days . ' hari';
        } elseif ($type->max_days) {
            $parts[] = 'Maks ' . $type->max_days . ' hari';
        }
        if ($type->require_married) {
            $parts[] = 'Menikah';
        }
        if ($type->require_attachment) {
            $parts[] = 'Lampiran';
        }
        if ($type->allowed_status) {
            $parts[] = $type->allowed_status;
        }

        return $parts
            ? '<small>' . e(implode(' · ', $parts)) . '</small>'
            : '<span class="text-muted">&mdash;</span>';
    }
}