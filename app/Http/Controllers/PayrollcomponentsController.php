<?php

namespace App\Http\Controllers;

use App\Models\Payrollcomponents;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class PayrollcomponentsController extends Controller
{
    /** Komponen manual yang nilainya disimpan di kolom tabel payrolls, bukan di payroll_details. */
    private const PAYROLL_COLUMNS = [
        'OVERTIME'      => 'overtime_amount',
        'REAMBURSE'     => 'reimburse_amount',
        'PUNISHMENT'    => 'punishment',
        'SO PUNISHMENT' => 'punishment_so',
        'DEBT'          => 'debt',
        'TAX'           => 'tax',
    ];

    /**
     * Otorisasi per aksi lewat permission (Spatie), bukan role.
     * Route di web.php memakai permission yang sama; ini lapis kedua kalau
     * controller dipanggil dari route lain.
     */
    public function __construct()
    {
        $this->middleware('permission:payroll.component.view')->only(['index', 'getPayrollcomponents']);
        $this->middleware('permission:payroll.component.create')->only(['create', 'store']);
        $this->middleware('permission:payroll.component.update')->only(['edit', 'update']);
    }

    /** Periode payroll terakhir yang sudah punya data payroll. */
    private function latestPeriod(): ?object
    {
        return DB::table('payroll_periods as pp')
            ->whereExists(fn($q) => $q->from('payrolls')->whereColumn('payrolls.payroll_period_id', 'pp.id'))
            ->orderByDesc('pp.period_start')
            ->first(['pp.id', 'pp.period_month', 'pp.period_year', 'pp.period_start', 'pp.period_end']);
    }

    private function periodLabel(?object $period): ?string
    {
        if (!$period) return null;
        return Carbon::create((int) $period->period_year, (int) $period->period_month, 1)->locale('id')->translatedFormat('F Y');
    }

    /**
     * Pemakaian tiap komponen: [component_id => ['payrolls' => total baris sepanjang waktu,
     * 'latest_total' => jumlah rupiah di periode terakhir, 'latest_people' => karyawan dengan nilai > 0]].
     */
    private function usage($components, ?object $latest): array
    {
        $out = [];

        $lines = DB::table('payroll_details')->groupBy('payroll_component_id')
            ->pluck(DB::raw('count(*)'), 'payroll_component_id');

        $latestDetails = $latest
            ? DB::table('payroll_details as d')->join('payrolls as p', 'p.id', '=', 'd.payroll_id')
                ->where('p.payroll_period_id', $latest->id)
                ->groupBy('d.payroll_component_id')
                ->get(['d.payroll_component_id', DB::raw('sum(d.amount) as total'), DB::raw('sum(d.amount > 0) as people')])
                ->keyBy('payroll_component_id')
            : collect();

        foreach ($components as $c) {
            $column = self::PAYROLL_COLUMNS[$c->component_name] ?? null;
            if ($column) {
                // Komponen manual: nilainya ada di kolom payrolls
                $all    = DB::table('payrolls')->where($column, '>', 0)->count();
                $latestRow = $latest
                    ? DB::table('payrolls')->where('payroll_period_id', $latest->id)
                        ->first([DB::raw("sum($column) as total"), DB::raw("sum($column > 0) as people")])
                    : null;
                $out[$c->id] = [
                    'payrolls'      => (int) $all,
                    'latest_total'  => (float) ($latestRow->total ?? 0),
                    'latest_people' => (int) ($latestRow->people ?? 0),
                ];
            } else {
                $d = $latestDetails->get($c->id);
                $out[$c->id] = [
                    'payrolls'      => (int) ($lines[$c->id] ?? 0),
                    'latest_total'  => (float) ($d->total ?? 0),
                    'latest_people' => (int) ($d->people ?? 0),
                ];
            }
        }

        return $out;
    }

    /** Cara komponen dihitung, dalam satu kalimat: dari flag + nama (lihat Payrollcomponents::SOURCES). */
    public static function describe(string $name, bool $fixed, bool $burden): array
    {
        $source = Payrollcomponents::sourceFor($name);
        [$mode, $from] = $source ?? [null, null];

        if ($fixed) {
            if ($mode === 'salary') {
                [$state, $text] = ['auto', "Ditambahkan otomatis saat payroll dibuat, nilainya dari {$from}."];
            } else {
                [$state, $text] = ['warn', 'Ditandai nilai tetap, tetapi tidak ada rumus untuk nama ini, jadi nilainya selalu 0 saat payroll dibuat.'];
            }
        } elseif ($mode === 'manual' || $mode === 'overtime') {
            [$state, $text] = ['manual', 'Nilainya dari ' . $from . '.'];
        } elseif ($mode === 'salary') {
            [$state, $text] = ['warn', "Nama ini punya rumus dari {$from}, tetapi tidak ditandai nilai tetap, jadi tidak ditambahkan saat payroll dibuat."];
        } else {
            [$state, $text] = ['none', 'Belum dipakai perhitungan payroll. Nama ini tidak dikenal rumus payroll dan tidak ditandai nilai tetap.'];
        }

        return ['state' => $state, 'text' => $text, 'mode' => $mode];
    }

    public function getPayrollcomponents(Request $request)
    {
        $components = Payrollcomponents::orderBy('type', 'desc')->orderBy('component_name')
            ->get(['id', 'component_name', 'type', 'is_fixed', 'is_employer_burden']);
        $latest = $this->latestPeriod();
        $usage  = $this->usage($components, $latest);
        $canEdit = (bool) $request->user()?->can('payroll.component.update');

        $rows = $components->map(function (Payrollcomponents $c) use ($usage, $canEdit) {
            $how = self::describe($c->component_name, $c->is_fixed, $c->is_employer_burden);
            return [
                'id'            => $c->id,
                'component_name' => $c->component_name,
                'type'          => $c->type,
                'is_fixed'      => $c->is_fixed,
                'is_employer_burden' => $c->is_employer_burden,
                'how_state'     => $how['state'],
                'how_text'      => $how['text'],
                'payrolls'      => $usage[$c->id]['payrolls'],
                'latest_total'  => $usage[$c->id]['latest_total'],
                'latest_people' => $usage[$c->id]['latest_people'],
                'edit_url'      => $canEdit ? route('editpayrollcomponents', $c->id) : null,
            ];
        });

        $stats = [
            'total'      => $rows->count(),
            'income'     => $rows->where('type', 'Income')->count(),
            'deduction'  => $rows->where('type', 'Deduction')->count(),
            'auto'       => $rows->where('how_state', 'auto')->count(),
            'attention'  => $rows->whereIn('how_state', ['warn', 'none'])->count(),
            'latest_period' => $this->periodLabel($latest),
        ];

        $rows = match ($request->input('flag')) {
            'Income', 'Deduction' => $rows->where('type', $request->input('flag')),
            'auto'      => $rows->where('how_state', 'auto'),
            'manual'    => $rows->where('how_state', 'manual'),
            'attention' => $rows->whereIn('how_state', ['warn', 'none']),
            default     => $rows,
        };

        // Teks di-escape di browser (esc), jadi tidak di-escape dua kali.
        return DataTables::of($rows->values())->escapeColumns([])->with('stats', $stats)->make(true);
    }

    private function validated(Request $request, ?Payrollcomponents $component = null): array
    {
        $request->merge(['component_name' => strtoupper(trim(preg_replace('/\s+/', ' ', (string) $request->input('component_name'))))]);

        $data = $request->validate([
            'component_name' => ['required', 'string', 'max:255', Rule::unique('payroll_components', 'component_name')->ignore($component?->id)],
            'type'           => 'required|in:Income,Deduction',
            'is_fixed'       => 'nullable|boolean',
            'is_employer_burden' => 'nullable|boolean',
        ], [
            'component_name.required' => 'Nama komponen wajib diisi.',
            'component_name.unique'   => 'Nama komponen sudah ada.',
            'type.required'           => 'Pilih jenis komponen.',
            'type.in'                 => 'Jenis komponen harus Pendapatan atau Potongan.',
        ]);

        // Checkbox: hidden input "0" + checkbox "1"; disimpan sebagai boolean di create maupun edit
        $data['is_fixed'] = $request->boolean('is_fixed');
        $data['is_employer_burden'] = $request->boolean('is_employer_burden');

        return $data;
    }

    public function store(Request $request)
    {
        $component = Payrollcomponents::create($this->validated($request));

        activity('payroll_component')->causedBy($request->user())->performedOn($component)->event('created')
            ->withProperties(['attributes' => $component->only(['component_name', 'type', 'is_fixed', 'is_employer_burden'])])
            ->log("Komponen payroll dibuat: {$component->component_name}");

        return redirect()->route('payrollcomponents')->with('success', "Komponen {$component->component_name} ditambahkan.");
    }

    public function update(Request $request, $id)
    {
        $component = Payrollcomponents::findOrFail($id);
        $old = $component->only(['component_name', 'type', 'is_fixed', 'is_employer_burden']);

        $component->update($this->validated($request, $component));

        if ($component->wasChanged()) {
            activity('payroll_component')->causedBy($request->user())->performedOn($component)->event('updated')
                ->withProperties(['old' => $old, 'attributes' => $component->only(['component_name', 'type', 'is_fixed', 'is_employer_burden'])])
                ->log("Komponen payroll diubah: {$old['component_name']}");
        }

        return redirect()->route('payrollcomponents')->with('success', "Komponen {$component->component_name} disimpan.");
    }

    private function formData(?Payrollcomponents $component = null): array
    {
        $latest = $this->latestPeriod();
        $usage = $component ? ($this->usage(collect([$component]), $latest)[$component->id] ?? null) : null;

        return [
            'types'    => Payrollcomponents::getTypeOptions(),
            'sources'  => Payrollcomponents::SOURCES,
            'existing' => Payrollcomponents::orderBy('type', 'desc')->orderBy('component_name')
                ->when($component, fn($q) => $q->where('id', '!=', $component->id))
                ->get(['component_name', 'type', 'is_fixed', 'is_employer_burden']),
            'usage'    => $usage,
            'latestPeriod' => $this->periodLabel($latest),
        ];
    }

    public function edit($id)
    {
        $payrolls = Payrollcomponents::findOrFail($id);
        return view('pages.payrollcomponents.editpayrollcomponents', ['payrolls' => $payrolls] + $this->formData($payrolls));
    }

    public function index()
    {
        return view('pages.payrollcomponents.payrollcomponents');
    }

    public function create()
    {
        return view('pages.payrollcomponents.createpayrollcomponents', $this->formData());
    }
}
