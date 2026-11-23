<?php

namespace App\Http\Controllers;

use App\Models\Shifts;
use App\Models\Stores;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\DataTables;

/**
 * Master shift per lokasi.
 *
 * - ManageShifts: semua lokasi, boleh tambah/ubah/hapus.
 * - ManageShiftsSPVManager / ViewShifts: hanya lokasi yang tertaut ke karyawannya, baca saja.
 *
 * Catatan data:
 * - Kode shift = dua huruf pertama nama (lihat Shifts::setShiftNameAttribute) dan dipakai
 *   RosterImport untuk mencocokkan isi template roster, jadi kode harus unik per lokasi.
 * - FK roster.shift_id ON DELETE SET NULL dan employee_shifts ON DELETE CASCADE: menghapus shift
 *   yang sudah dipakai akan menghapus jejak riwayat roster, jadi itu ditolak.
 */
class ShiftsController extends Controller
{
    /** Kode isi template roster yang bukan shift (RosterImport $dayTypeMap); kode shift dua huruf tidak boleh sama. */
    private const RESERVED_CODES = ['PH'];

    private const TIME_REGEX = '/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/';

    public function index()
    {
        $scope  = $this->storeScope();
        $stores = Stores::select('id', 'name')
            ->whereNotNull('name')
            ->when($scope !== null, fn($q) => $q->whereIn('id', $scope))
            ->orderBy('name')
            ->get();

        return view('pages.Shifts.Shifts', [
            'stores'    => $stores,
            'canManage' => $this->canManage(),
        ]);
    }

    public function getData(Request $request)
    {
        $scope = $this->storeScope();

        $query = Shifts::query()
            ->leftJoin('stores_tables', 'stores_tables.id', '=', 'shifts_tables.store_id')
            ->select([
                'shifts_tables.id',
                'shifts_tables.store_id',
                'shifts_tables.shift_name',
                'shifts_tables.code',
                'shifts_tables.start_time',
                'shifts_tables.end_time',
                'shifts_tables.is_split',
                'shifts_tables.break_start',
                'shifts_tables.break_end',
                'shifts_tables.updated_at',
                'stores_tables.name as store_name',
            ])
            ->selectSub(fn($q) => $q->from('roster')->selectRaw('count(*)')->whereColumn('roster.shift_id', 'shifts_tables.id'), 'roster_count')
            ->selectSub(fn($q) => $q->from('roster')->selectRaw('max(date)')->whereColumn('roster.shift_id', 'shifts_tables.id'), 'last_used')
            ->when($scope !== null, fn($q) => $q->whereIn('shifts_tables.store_id', $scope))
            ->when($request->filled('store_id'), fn($q) => $q->where('shifts_tables.store_id', $request->input('store_id')));

        $canManage = $this->canManage();

        // Teks di-escape di browser (esc), jadi tidak di-escape dua kali.
        return DataTables::of($query)
            ->escapeColumns([])
            ->filterColumn('store_name', fn($q, $k) => $q->where('stores_tables.name', 'like', "%{$k}%"))
            ->orderColumn('store_name', 'stores_tables.name $1, shifts_tables.start_time asc')
            ->orderColumn('roster_count', 'roster_count $1')
            ->editColumn('start_time', fn($r) => substr((string) $r->start_time, 0, 5))
            ->editColumn('end_time', fn($r) => substr((string) $r->end_time, 0, 5))
            ->editColumn('is_split', fn($r) => (bool) $r->is_split)
            ->editColumn('break_start', fn($r) => $r->break_start ? substr((string) $r->break_start, 0, 5) : null)
            ->editColumn('break_end', fn($r) => $r->break_end ? substr((string) $r->break_end, 0, 5) : null)
            ->editColumn('store_name', fn($r) => $r->store_name ?? '-')
            ->editColumn('roster_count', fn($r) => (int) $r->roster_count)
            ->editColumn('updated_at', fn($r) => $r->updated_at?->format('d M Y'))
            ->addColumn('can_manage', $canManage)
            ->with('stats', fn() => $this->stats($scope))
            ->make(true);
    }

    public function store(Request $request)
    {
        $this->authorizeManage();
        $data = $this->validated($request);

        $shift = Shifts::create($data + ['is_holiday' => 0]);

        return response()->json([
            'success' => true,
            'message' => "Shift {$shift->shift_name} ({$shift->code}) ditambahkan.",
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->authorizeManage();
        $shift = Shifts::findOrFail($id);
        $used  = $this->usage($shift->id);

        // Dicek sebelum validasi nama, supaya alasan sebenarnya yang tampil.
        if ($request->filled('store_id') && (string) $request->input('store_id') !== (string) $shift->store_id && $used['roster'] > 0) {
            throw ValidationException::withMessages([
                'store_id' => "Lokasi tidak bisa dipindah: shift ini sudah dipakai di {$used['roster']} jadwal roster lokasi lama. Buat shift baru di lokasi tujuan.",
            ]);
        }

        $data = $this->validated($request, $shift);

        $shift->update($data);

        return response()->json([
            'success' => true,
            'message' => "Shift {$shift->shift_name} disimpan." . ($used['roster'] > 0 ? " Jam baru berlaku untuk {$used['roster']} jadwal roster yang memakai shift ini." : ''),
        ]);
    }

    public function destroy($id)
    {
        $this->authorizeManage();

        return DB::transaction(function () use ($id) {
            $shift = Shifts::lockForUpdate()->findOrFail($id);
            $used  = $this->usage($shift->id);

            if (array_sum($used) > 0) {
                $parts = array_filter([
                    $used['roster'] ? "{$used['roster']} jadwal roster" : null,
                    $used['employee_shifts'] ? "{$used['employee_shifts']} jadwal karyawan" : null,
                    $used['toil'] ? "{$used['toil']} pengajuan TOIL" : null,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Shift ini tidak bisa dihapus karena masih dipakai di ' . implode(', ', $parts) . '. Menghapusnya akan mengosongkan riwayat roster tersebut.',
                ], 422);
            }

            $name = $shift->shift_name;
            $shift->delete();

            return response()->json(['success' => true, 'message' => "Shift {$name} dihapus."]);
        });
    }

    // ── Helper ────────────────────────────────────────────────────

    private function canManage(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();

        return (bool) $user?->hasPermissionTo('ManageShifts');
    }

    private function authorizeManage(): void
    {
        abort_unless($this->canManage(), 403, 'Hanya pemegang permission ManageShifts yang bisa mengubah master shift.');
    }

    /** null = semua lokasi; array = id lokasi yang boleh dilihat. */
    private function storeScope(): ?array
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        abort_unless($user, 403);

        if ($user->hasPermissionTo('ManageShifts')) {
            return null;
        }
        abort_unless($user->hasAnyPermission(['ManageShiftsSPVManager', 'ViewShifts']), 403);

        $employee = $user->employee;
        abort_unless($employee, 403, 'Akun Anda belum terhubung ke data karyawan.');

        return $employee->store()->pluck('stores_tables.id')->all();
    }

    private function validated(Request $request, ?Shifts $current = null): array
    {
        $request->merge(['shift_name' => preg_replace('/\s+/', ' ', trim((string) $request->input('shift_name')))]);

        $request->validate([
            'store_id'   => 'required|exists:stores_tables,id',
            'shift_name' => ['required', 'string', 'min:2', 'max:50', 'regex:/^[A-Za-z0-9]{2}[A-Za-z0-9 .\-\/]*$/'],
            'start_time' => ['required', 'regex:' . self::TIME_REGEX],
            'end_time'   => ['required', 'regex:' . self::TIME_REGEX, 'different:start_time'],
            'is_split'    => 'nullable|boolean',
            'break_start' => ['nullable', 'required_if:is_split,1', 'regex:' . self::TIME_REGEX],
            'break_end'   => ['nullable', 'required_if:is_split,1', 'regex:' . self::TIME_REGEX],
        ], [
            'store_id.required'    => 'Pilih lokasi.',
            'shift_name.required'  => 'Nama shift wajib diisi.',
            'shift_name.regex'     => 'Nama shift harus diawali dua huruf atau angka (dipakai sebagai kode di template roster), lalu boleh berisi huruf, angka, spasi, titik, - atau /.',
            'start_time.required'  => 'Jam masuk wajib diisi.',
            'start_time.regex'     => 'Format jam masuk harus JJ:MM.',
            'end_time.required'    => 'Jam pulang wajib diisi.',
            'end_time.regex'       => 'Format jam pulang harus JJ:MM.',
            'end_time.different'   => 'Jam pulang tidak boleh sama dengan jam masuk.',
            'break_start.required_if' => 'Jam pulang blok 1 wajib diisi untuk split shift.',
            'break_start.regex'       => 'Format jam pulang blok 1 harus JJ:MM.',
            'break_end.required_if'   => 'Jam masuk blok 2 wajib diisi untuk split shift.',
            'break_end.regex'         => 'Format jam masuk blok 2 harus JJ:MM.',
        ]);

        $isSplit = $request->boolean('is_split');
        if ($isSplit) {
            // Kedua blok harus di hari yang sama dan berurutan. Split shift lewat tengah malam belum didukung
            // karena rekap finger dan absen mobile memisahkan blok berdasarkan jam di tanggal kerja.
            $start = substr($request->input('start_time'), 0, 5);
            $bs    = substr($request->input('break_start'), 0, 5);
            $be    = substr($request->input('break_end'), 0, 5);
            $end   = substr($request->input('end_time'), 0, 5);
            if (!($start < $bs && $bs < $be && $be < $end)) {
                throw ValidationException::withMessages([
                    'break_start' => 'Untuk split shift, urutan jam harus: masuk blok 1 < pulang blok 1 < masuk blok 2 < pulang blok 2, di hari yang sama (tidak lewat tengah malam).',
                ]);
            }
        }

        $name    = strtoupper($request->input('shift_name'));
        $code    = substr($name, 0, 2);
        $storeId = (string) $request->input('store_id');

        if (in_array($code, self::RESERVED_CODES, true)) {
            throw ValidationException::withMessages([
                'shift_name' => "Nama shift tidak boleh diawali \"{$code}\": kode itu dipakai template roster untuk Public Holiday.",
            ]);
        }

        $siblings = Shifts::where('store_id', $storeId)
            ->when($current, fn($q) => $q->where('id', '!=', $current->id))
            ->get(['id', 'shift_name', 'code']);

        if ($siblings->contains(fn($s) => strtoupper((string) $s->shift_name) === $name)) {
            throw ValidationException::withMessages(['shift_name' => "Shift {$name} sudah ada di lokasi ini."]);
        }
        if ($clash = $siblings->first(fn($s) => strtoupper((string) $s->code) === $code)) {
            throw ValidationException::withMessages([
                'shift_name' => "Kode {$code} sudah dipakai shift {$clash->shift_name} di lokasi ini. Dua huruf pertama nama shift menjadi kode di template roster, jadi harus berbeda.",
            ]);
        }

        return [
            'store_id'   => $storeId,
            'shift_name' => $name,
            'start_time' => substr($request->input('start_time'), 0, 5),
            'end_time'   => substr($request->input('end_time'), 0, 5),
            'is_split'    => $isSplit,
            'break_start' => $isSplit ? substr($request->input('break_start'), 0, 5) : null,
            'break_end'   => $isSplit ? substr($request->input('break_end'), 0, 5) : null,
        ];
    }

    /** Berapa banyak data lain yang menunjuk ke shift ini. */
    private function usage(string $shiftId): array
    {
        return [
            'roster'          => DB::table('roster')->where('shift_id', $shiftId)->count(),
            'employee_shifts' => DB::table('employee_shifts_tables')->where('shift_id', $shiftId)->count(),
            'toil'            => DB::table('toil_leave_requests_tables')->where('original_shift_id', $shiftId)->count(),
        ];
    }

    /** Ringkasan untuk lokasi yang boleh dilihat (tidak ikut filter tabel). */
    private function stats(?array $scope): array
    {
        $stores = Stores::whereNotNull('name')
            ->when($scope !== null, fn($q) => $q->whereIn('id', $scope))
            ->orderBy('name')
            ->get(['id', 'name']);

        $shifts = Shifts::whereIn('store_id', $stores->pluck('id'))->get(['id', 'store_id', 'start_time', 'end_time', 'is_split']);
        $usedIds = DB::table('roster')->whereIn('shift_id', $shifts->pluck('id'))->distinct()->pluck('shift_id');
        $withShift = $shifts->pluck('store_id')->unique();

        return [
            'total'          => $shifts->count(),
            'stores'         => $stores->count(),
            'stores_covered' => $withShift->count(),
            'stores_without' => $stores->whereNotIn('id', $withShift)->pluck('name')->values(),
            'used'           => $usedIds->count(),
            'overnight'      => $shifts->filter(fn($s) => $s->end_time < $s->start_time)->count(),
            'split'          => $shifts->where('is_split', true)->count(),
        ];
    }
}
