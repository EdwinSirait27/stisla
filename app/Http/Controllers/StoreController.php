<?php

namespace App\Http\Controllers;

use App\Models\Stores;
use App\Rules\NoXSSInput;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\DataTables;

/**
 * Master lokasi kerja (route dijaga permission:ManageStores).
 *
 * latitude/longitude/geofence_radius dipakai Api\AttendanceController: karyawan attendance_type
 * store_bound hanya bisa absen mobile di dalam radius lokasi utamanya, dan ditolak sama sekali kalau
 * koordinat lokasinya kosong. Shift (shifts_tables) dan roster juga per lokasi.
 * FK employees_tables, employee_stores, shifts_tables, structures → stores_tables ON DELETE CASCADE,
 * jadi lokasi sengaja tidak bisa dihapus dari aplikasi.
 */
class StoreController extends Controller
{
    private const RADIUS_MIN = 20;
    private const RADIUS_MAX = 2000;

    public function index()
    {
        return view('pages.Store.Store');
    }

    public function getStores()
    {
        $rows = $this->storeRows();

        // Teks di-escape di browser (esc), jadi tidak di-escape dua kali.
        return DataTables::of($rows->values())
            ->escapeColumns([])
            ->with('stats', [
                'total'          => $rows->count(),
                'with_location'  => $rows->where('has_location', true)->count(),
                'blocked_stores' => $rows->where('has_location', false)->where('store_bound', '>', 0)->count(),
                'blocked_people' => $rows->where('has_location', false)->sum('store_bound'),
                'no_shift'       => $rows->where('employees', '>', 0)->where('shifts', 0)->count(),
                'employees'      => $rows->sum('employees'),
            ])
            ->make(true);
    }

    public function create()
    {
        return view('pages.Store.create', ['store' => null, 'usage' => null, 'radiusMin' => self::RADIUS_MIN, 'radiusMax' => self::RADIUS_MAX]);
    }

    public function edit($hashedId)
    {
        $store = $this->findByHash($hashedId);
        abort_unless($store, 404, 'Lokasi tidak ditemukan.');

        $row = $this->storeRows($store->id)->first();

        return view('pages.Store.edit', [
            'store'     => $store,
            'hashedId'  => $hashedId,
            'usage'     => $row,
            'radiusMin' => self::RADIUS_MIN,
            'radiusMax' => self::RADIUS_MAX,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, null);

        try {
            $store = Stores::create($data);
        } catch (\Throwable $e) {
            Log::error('Gagal menyimpan lokasi', ['message' => $e->getMessage()]);
            return back()->withInput()->withErrors(['error' => 'Lokasi tidak bisa disimpan. Coba lagi atau cek storage/logs/laravel.log.']);
        }

        return redirect()->route('pages.Store')->with('success', "Lokasi {$store->name} ditambahkan. Buat shift-nya di menu Shifts agar roster bisa dibuat.");
    }

    public function update(Request $request, $hashedId)
    {
        $store = $this->findByHash($hashedId);
        if (!$store) {
            return redirect()->route('pages.Store')->with('error', 'Lokasi tidak ditemukan.');
        }

        $data = $this->validated($request, $store);

        try {
            $store->update($data);
        } catch (\Throwable $e) {
            Log::error('Gagal memperbarui lokasi', ['id' => $store->id, 'message' => $e->getMessage()]);
            return back()->withInput()->withErrors(['error' => 'Perubahan tidak bisa disimpan. Coba lagi atau cek storage/logs/laravel.log.']);
        }

        return redirect()->route('pages.Store')->with('success', "Lokasi {$store->name} disimpan.");
    }

    // ── Helper ────────────────────────────────────────────────────

    /** ID pendek di URL. config('app.key'), bukan env(): saat config di-cache env() bernilai null. */
    private function hashId(string $id): string
    {
        return substr(hash('sha256', $id . config('app.key')), 0, 8);
    }

    private function findByHash(string $hashedId): ?Stores
    {
        return Stores::get()->first(fn($s) => hash_equals($this->hashId($s->id), $hashedId));
    }

    /** Baris lokasi + jumlah karyawan aktif (lokasi utama), karyawan store_bound, dan shift. */
    private function storeRows(?string $onlyId = null)
    {
        $emp = DB::table('employee_stores as es')
            ->join('employees_tables as e', 'e.id', '=', 'es.employee_id')
            ->where('es.is_primary', true)->where('e.status', 'Active')
            ->when($onlyId, fn($q) => $q->where('es.store_id', $onlyId))
            ->groupBy('es.store_id')
            ->get(['es.store_id', DB::raw('count(*) as total'), DB::raw("sum(e.attendance_type = 'store_bound') as bound")])
            ->keyBy('store_id');
        $shifts = DB::table('shifts_tables')->when($onlyId, fn($q) => $q->where('store_id', $onlyId))
            ->groupBy('store_id')->pluck(DB::raw('count(*)'), 'store_id');

        return Stores::when($onlyId, fn($q) => $q->where('id', $onlyId))->orderBy('name')->get()
            ->map(function (Stores $s) use ($emp, $shifts) {
                $e = $emp->get($s->id);
                return [
                    'name'         => $s->name,
                    'nickname'     => $s->nickname,
                    'address'      => $s->address,
                    'phone_num'    => $s->phone_num,
                    'latitude'     => $s->latitude !== null ? (float) $s->latitude : null,
                    'longitude'    => $s->longitude !== null ? (float) $s->longitude : null,
                    'radius'       => (int) $s->geofence_radius,
                    'has_location' => $s->latitude !== null && $s->longitude !== null,
                    'employees'    => (int) ($e->total ?? 0),
                    'store_bound'  => (int) ($e->bound ?? 0),
                    'shifts'       => (int) ($shifts[$s->id] ?? 0),
                    'edit_url'     => route('Store.edit', $this->hashId($s->id)),
                ];
            });
    }

    private function validated(Request $request, ?Stores $current): array
    {
        $phone = preg_replace('/[\s\-().]/', '', (string) $request->input('phone_num'));
        $request->merge([
            'name'      => preg_replace('/\s+/', ' ', trim((string) $request->input('name'))),
            'nickname'  => strtoupper(preg_replace('/\s+/', '', (string) $request->input('nickname'))),
            'phone_num' => $phone !== '' && !preg_match('/^0+$/', $phone) ? $phone : null,
            'latitude'  => ($lat = str_replace(',', '.', trim((string) $request->input('latitude')))) !== '' ? $lat : null,
            'longitude' => ($lng = str_replace(',', '.', trim((string) $request->input('longitude')))) !== '' ? $lng : null,
        ]);

        $v = $request->validate([
            'name'            => ['required', 'string', 'min:2', 'max:100', Rule::unique('stores_tables', 'name')->ignore($current?->id), new NoXSSInput()],
            'nickname'        => ['required', 'string', 'min:2', 'max:10', 'alpha_num', Rule::unique('stores_tables', 'nickname')->ignore($current?->id)],
            'address'         => ['required', 'string', 'max:500', new NoXSSInput()],
            'phone_num'       => ['nullable', 'regex:/^\+?\d{6,15}$/'],
            'latitude'        => ['nullable', 'required_with:longitude', 'numeric', 'between:-90,90'],
            'longitude'       => ['nullable', 'required_with:latitude', 'numeric', 'between:-180,180'],
            'geofence_radius' => ['required', 'integer', 'min:' . self::RADIUS_MIN, 'max:' . self::RADIUS_MAX],
        ], [
            'name.required'          => 'Nama lokasi wajib diisi.',
            'name.unique'            => 'Nama lokasi sudah ada.',
            'name.max'               => 'Nama lokasi maksimal 100 karakter.',
            'nickname.required'      => 'Kode lokasi wajib diisi.',
            'nickname.alpha_num'     => 'Kode lokasi hanya huruf dan angka, tanpa spasi.',
            'nickname.max'           => 'Kode lokasi maksimal 10 karakter.',
            'nickname.unique'        => 'Kode lokasi sudah dipakai lokasi lain.',
            'address.required'       => 'Alamat wajib diisi.',
            'address.max'            => 'Alamat maksimal 500 karakter.',
            'phone_num.regex'        => 'Nomor telepon 6 sampai 15 digit, boleh diawali +.',
            'latitude.required_with' => 'Isi latitude dan longitude sekaligus.',
            'longitude.required_with'=> 'Isi latitude dan longitude sekaligus.',
            'latitude.between'       => 'Latitude harus antara -90 dan 90.',
            'longitude.between'      => 'Longitude harus antara -180 dan 180.',
            'geofence_radius.required' => 'Radius wajib diisi.',
            'geofence_radius.min'    => 'Radius minimal ' . self::RADIUS_MIN . ' meter (GPS ponsel bisa meleset 10 sampai 20 meter).',
            'geofence_radius.max'    => 'Radius maksimal ' . self::RADIUS_MAX . ' meter.',
        ]);

        // Koordinat (0, 0) hampir pasti salah isi, bukan lokasi di Indonesia.
        if (isset($v['latitude']) && (float) $v['latitude'] === 0.0 && (float) $v['longitude'] === 0.0) {
            throw ValidationException::withMessages(['latitude' => 'Koordinat 0, 0 tidak valid. Pilih titik di peta.']);
        }

        return [
            'name'            => $v['name'],
            'nickname'        => $v['nickname'],
            'address'         => trim($v['address']),
            'phone_num'       => $v['phone_num'] ?? null,
            'latitude'        => isset($v['latitude']) ? round((float) $v['latitude'], 7) : null,
            'longitude'       => isset($v['longitude']) ? round((float) $v['longitude'], 7) : null,
            'geofence_radius' => (int) $v['geofence_radius'],
        ];
    }
}
