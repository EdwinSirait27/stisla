<?php

namespace App\Http\Controllers;

use App\Imports\PHImport;
use App\Models\Ph;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Rules\NoXSSInput;
use App\Services\PublicHolidayRosterSync;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PHController extends Controller
{
    private const TYPES = ['All', 'Hindu', 'Non Hindu'];
    private const CLOSED_PERIOD_MESSAGE = 'Public Holiday sebelum periode berjalan tidak bisa diubah, karena periode itu mungkin sudah masuk payroll.';

    public function index()
    {
        /** @var \App\Models\User $user */
    $user = auth()->user();

    if (!$user) {
        abort(403);
    }
     if ($user->hasanyPermission(['ManagePH','ManagePHSPVManager','ViewPH'])) {
     }
        return view('pages.Pubholi.Pubholi');
    }
    
    

public function getPubholidays(Request $request)
{
         /** @var \App\Models\User $user */
    $user = auth()->user();

    if (!$user) {
        abort(403);
    }
    if ($user->hasanyPermission(['ManagePH','ManagePHSPVManager','ViewPH'])) {
     }
    $query = Ph::select([
        'id',
        'type',
        'date',
        'remark'
    ]);
    // Filter tanggal awal dan akhir
    if ($request->filled('start_date') && $request->filled('end_date')) {
        $query->whereBetween('date', [
            $request->start_date,
            $request->end_date
        ]);
    }

    // Filter satu tanggal
    if ($request->filled('date')) {
        $query->whereDate('date', $request->date);
    }

    $pubholidays = $query->get();

    return DataTables::of($pubholidays)
        ->addColumn('hashed_id', fn($ph) => self::hashId($ph->id))
        ->addColumn('editable', fn($ph) => PublicHolidayRosterSync::isSyncable($ph->date->toDateString()))
        ->make(true);
}
    public function create()
    {
        return view('pages.Pubholi.create', ['status_type' => self::TYPES]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatePh($request);
        $sync      = new PublicHolidayRosterSync();

        DB::transaction(function () use ($validated, $sync) {
            Ph::create($validated);
            $sync->syncDates([$validated['date']]);
        });

        return redirect()->route('pages.Pubholi')
            ->with('success', 'Public Holiday berhasil ditambahkan.' . $sync->message());
    }

    public function edit($hashedId)
    {
        $pubholi = $this->findByHash($hashedId);

        if (!PublicHolidayRosterSync::isSyncable($pubholi->date->toDateString())) {
            return redirect()->route('pages.Pubholi')->with('error', self::CLOSED_PERIOD_MESSAGE);
        }

        return view('pages.Pubholi.edit', [
            'pubholi'     => $pubholi,
            'hashedId'    => $hashedId,
            'status_type' => self::TYPES,
        ]);
    }

    public function update(Request $request, $hashedId)
    {
        $pubholi = $this->findByHash($hashedId);
        $oldDate = $pubholi->date->toDateString();

        if (!PublicHolidayRosterSync::isSyncable($oldDate)) {
            return redirect()->route('pages.Pubholi')->with('error', self::CLOSED_PERIOD_MESSAGE);
        }

        $validated = $this->validatePh($request, $pubholi->id);
        $sync      = new PublicHolidayRosterSync();

        DB::transaction(function () use ($pubholi, $validated, $oldDate, $sync) {
            $pubholi->update($validated);
            $sync->syncDates([$oldDate, $validated['date']]);
        });

        return redirect()->route('pages.Pubholi')
            ->with('success', 'Public Holiday berhasil diperbarui.' . $sync->message());
    }

    public function destroy($hashedId)
    {
        $pubholi = $this->findByHash($hashedId);
        $date    = $pubholi->date->toDateString();

        if (!PublicHolidayRosterSync::isSyncable($date)) {
            return redirect()->route('pages.Pubholi')->with('error', self::CLOSED_PERIOD_MESSAGE);
        }

        $sync = new PublicHolidayRosterSync();

        DB::transaction(function () use ($pubholi, $date, $sync) {
            $pubholi->delete();
            $sync->syncDates([$date]);
        });

        return redirect()->route('pages.Pubholi')
            ->with('success', 'Public Holiday berhasil dihapus.' . $sync->message());
    }

    private function validatePh(Request $request, ?string $ignoreId = null): array
    {
        $periodStart = PublicHolidayRosterSync::currentPeriodStart()->toDateString();

        return $request->validate([
            'date'   => [
                'required',
                'date',
                'after_or_equal:' . $periodStart,
                Rule::unique('ph')
                    ->where(fn($q) => $q->where('type', $request->input('type')))
                    ->ignore($ignoreId),
            ],
            'remark' => ['required', 'string', 'max:255', new NoXSSInput()],
            'type'   => ['required', Rule::in(self::TYPES)],
        ], [
            'date.after_or_equal' => self::CLOSED_PERIOD_MESSAGE,
            'date.unique'         => 'Public Holiday dengan tanggal dan tipe ini sudah ada.',
        ]);
    }

    private static function hashId(string $id): string
    {
        return substr(hash('sha256', $id . config('app.key')), 0, 8);
    }

    private function findByHash(string $hashedId): Ph
    {
        $pubholi = Ph::get()->first(fn($ph) => self::hashId($ph->id) === $hashedId);

        abort_if(!$pubholi, 404, 'Public Holiday not found.');

        return $pubholi;
    }

    public function indexphs()
    {
               $user     = auth()->user();
        /** @var \App\Models\User|null $user */
        if (!$user->hasPermissionTo('ManagePH')) {
            abort(403, 'Unauthorized');
        }
        $files = Storage::disk('public')->files('templatephs');
        return view('pages.ImportPH.ImportPH', compact('files'));
    }
    public function downloadphs($filename)
    {
              $user     = auth()->user();
        /** @var \App\Models\User|null $user */
        if (!$user->hasPermissionTo('ManagePH')) {
            abort(403, 'Unauthorized');
        }
        $path = 'templatephs/' . $filename;

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->download($path);
        }
        abort(404);
    }
   
    public function importPhs(Request $request)
{
          $user     = auth()->user();
        /** @var \App\Models\User|null $user */
        if (!$user->hasPermissionTo('ManagePH')) {
            abort(403, 'Unauthorized');
        }
    $request->validate([
        'file' => 'required|mimes:xlsx,csv,xls'
    ], [
        'file.required' => 'Please select an Excel file to import.',
        'file.mimes'    => 'File format must be Excel (xlsx, xls) or CSV.',
    ]);

    $import = new PHImport();
    $import->import($request->file('file'));

    // Validation failures (format salah, kolom kosong, dll)
    if ($import->failures()->isNotEmpty()) {
        return back()->with('failures', $import->failures());
    }

    // Runtime errors (exception tak terduga)
    if ($import->errors()->isNotEmpty()) {
        return back()->with('import_errors', $import->errors());
    }

    $sync = (new PublicHolidayRosterSync())->syncDates($import->importedDates);

    return back()->with('success', 'Public Holidays imported successfully!' . $sync->message());
}
}
