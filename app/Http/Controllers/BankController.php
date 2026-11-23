<?php

namespace App\Http\Controllers;

use App\Models\Banks;
use App\Models\Employee;
use App\Rules\NoXSSInput;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

/**
 * Master bank (tujuan transfer gaji karyawan, employees_tables.banks_id).
 *
 * Permission (dipasang di routes/web.php):
 * - bank.view   : index, getBanks
 * - bank.create : create, store
 * - bank.update : edit, update
 *
 * Tidak ada hapus: bank dipakai di data karyawan, dan FK banks_id akan mengosongkan rekening mereka.
 */
class BankController extends Controller
{
    /** Status karyawan yang masih digaji (sama dengan daftar karyawan aktif di modul lain). */
    private const ACTIVE_STATUSES = ['Active', 'Pending', 'On Leave', 'Mutation'];

    /**
     * Hash 8 karakter untuk URL. Memakai config('app.key'), bukan env(): env() bernilai null
     * saat config di-cache, sehingga hash lama sebenarnya tidak memakai key sama sekali.
     */
    private static function hashId(string $id): string
    {
        return substr(hash('sha256', $id . config('app.key')), 0, 8);
    }

    private function findByHash(string $hashedId): ?Banks
    {
        foreach (Banks::query()->select('id')->cursor() as $row) {
            if (hash_equals(self::hashId($row->id), $hashedId)) {
                return Banks::find($row->id);
            }
        }
        return null;
    }

    private function user(): ?\App\Models\User
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        return $user;
    }

    /** Karyawan aktif yang belum dihapus. */
    private function activeEmployees()
    {
        return Employee::query()->whereNull('deleted_at')->whereIn('status', self::ACTIVE_STATUSES);
    }

    /** Nama bank untuk peringatan nama mirip di form (selain bank yang sedang diubah). */
    private function existingNames(?string $exceptId = null)
    {
        return Banks::when($exceptId, fn($q) => $q->where('id', '!=', $exceptId))->orderBy('name')->pluck('name')->values();
    }

    /** Spasi ganda dirapikan sebelum validasi, supaya "Bank  BCA" dan "Bank BCA" dianggap sama. */
    private function normalizeName(Request $request): void
    {
        $request->merge(['name' => preg_replace('/\s+/u', ' ', trim((string) $request->input('name')))]);
    }

    private function rules(?Banks $current = null): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                $current ? Rule::unique('banks_tables', 'name')->ignore($current->id) : Rule::unique('banks_tables', 'name'),
                new NoXSSInput(),
            ],
        ];
    }

    private function messages(): array
    {
        return [
            'name.required' => 'Nama bank wajib diisi.',
            'name.string'   => 'Nama bank hanya boleh berupa teks.',
            'name.min'      => 'Nama bank minimal 2 karakter.',
            'name.max'      => 'Nama bank maksimal 255 karakter.',
            'name.unique'   => 'Bank dengan nama ini sudah terdaftar.',
        ];
    }

    public function index()
    {
        $withBank = $this->activeEmployees()->whereNotNull('banks_id')->count();

        return view('pages.Bank.Bank', [
            'stats' => [
                'banks'         => Banks::count(),
                'active'        => $withBank,
                'without_bank'  => $this->activeEmployees()->whereNull('banks_id')->count(),
                'unused'        => Banks::whereNotExists(fn($q) => $q->select(DB::raw(1))->from('employees_tables')
                    ->whereColumn('employees_tables.banks_id', 'banks_tables.id')
                    ->whereNull('employees_tables.deleted_at'))->count(),
            ],
            'canCreate' => (bool) $this->user()?->can('bank.create'),
        ]);
    }

    public function getBanks()
    {
        $statuses  = self::ACTIVE_STATUSES;
        $canUpdate = (bool) $this->user()?->can('bank.update');

        $query = Banks::query()
            ->select(['banks_tables.id', 'banks_tables.name', 'banks_tables.updated_at'])
            ->selectSub(fn($q) => $q->from('employees_tables')->selectRaw('count(*)')
                ->whereColumn('employees_tables.banks_id', 'banks_tables.id')
                ->whereNull('employees_tables.deleted_at')
                ->whereIn('employees_tables.status', $statuses), 'active_employees')
            ->selectSub(fn($q) => $q->from('employees_tables')->selectRaw('count(*)')
                ->whereColumn('employees_tables.banks_id', 'banks_tables.id')
                ->whereNull('employees_tables.deleted_at'), 'total_employees');

        // Dasar persentase: semua karyawan aktif yang sudah punya bank
        $activeTotal = $this->activeEmployees()->whereNotNull('banks_id')->count();

        // Teks di-escape di browser (esc), jadi kolom tidak di-escape dua kali di server
        return DataTables::eloquent($query)
            ->escapeColumns([])
            ->orderColumn('active_employees', 'active_employees $1')
            ->orderColumn('total_employees', 'total_employees $1')
            ->editColumn('active_employees', fn($b) => (int) $b->active_employees)
            ->editColumn('total_employees', fn($b) => (int) $b->total_employees)
            ->editColumn('updated_at', fn($b) => $b->updated_at?->timezone(config('app.timezone'))->locale('id')->translatedFormat('d M Y'))
            ->addColumn('edit_url', fn($b) => $canUpdate ? route('Bank.edit', self::hashId($b->id)) : null)
            ->removeColumn('id')
            ->with('active_total', $activeTotal)
            ->make(true);
    }

    public function create()
    {
        return view('pages.Bank.create', [
            'existingNames' => $this->existingNames(),
        ]);
    }

    public function store(Request $request)
    {
        $this->normalizeName($request);
        $validated = $request->validate($this->rules(), $this->messages());

        try {
            $bank = Banks::create(['name' => $validated['name']]);
        } catch (\Throwable $e) {
            Log::error('Bank store failed', ['user_id' => auth()->id(), 'error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Bank gagal disimpan. Coba lagi, atau hubungi admin kalau masih gagal.');
        }

        return redirect()->route('pages.Bank')->with('success', "{$bank->name} ditambahkan ke daftar bank.");
    }

    public function edit(string $hashedId)
    {
        $bank = $this->findByHash($hashedId);
        if (!$bank) {
            return redirect()->route('pages.Bank')->with('error', 'Bank tidak ditemukan. Mungkin tautannya sudah kedaluwarsa, buka lagi dari daftar.');
        }

        return view('pages.Bank.edit', [
            'bank'          => $bank,
            'hashedId'      => $hashedId,
            'existingNames' => $this->existingNames($bank->id),
            'usage'         => [
                'active' => $this->activeEmployees()->where('banks_id', $bank->id)->count(),
                'total'  => Employee::whereNull('deleted_at')->where('banks_id', $bank->id)->count(),
            ],
        ]);
    }

    public function update(Request $request, string $hashedId)
    {
        $bank = $this->findByHash($hashedId);
        if (!$bank) {
            return redirect()->route('pages.Bank')->with('error', 'Bank tidak ditemukan. Mungkin tautannya sudah kedaluwarsa, buka lagi dari daftar.');
        }

        $this->normalizeName($request);
        $validated = $request->validate($this->rules($bank), $this->messages());

        $old = $bank->name;
        if ($old === $validated['name']) {
            return redirect()->route('pages.Bank')->with('success', "Tidak ada perubahan pada {$old}.");
        }

        try {
            $bank->update(['name' => $validated['name']]);
        } catch (\Throwable $e) {
            Log::error('Bank update failed', ['user_id' => auth()->id(), 'bank_id' => $bank->id, 'error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Perubahan gagal disimpan. Coba lagi, atau hubungi admin kalau masih gagal.');
        }

        return redirect()->route('pages.Bank')->with('success', "Nama bank diubah dari {$old} menjadi {$bank->name}.");
    }
}
