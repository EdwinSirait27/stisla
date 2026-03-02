<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Models\Sktemplate;
use App\Models\Sktype;
use App\Models\Stores;
use App\Models\Company;
    use Illuminate\Validation\Rule;

class SktemplateController extends Controller
{
    public function sktemplate()
    {
        return view('pages.Sktemplate.Sktemplate');
    }
    public function getSktemplates()
    {
        $sktemplates = Sktemplate::select(['id', 'template_name','company_id'])->with('company')
            ->get()
            ->map(function ($sktemplate) {
                $sktemplate->id_hashed = substr(hash('sha256', $sktemplate->id . env('APP_KEY')), 0, 8);
                $sktemplate->action = '
                    <a href="' . route('Sktemplate.edit', $sktemplate->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Edit SK Template"title="Edit SK Template: ' . e($sktemplate->template_name) . '">
                        <i class="fas fa-user-edit text-secondary"></i>
                    </a>';
                return $sktemplate;
            });
        return DataTables::of($sktemplates)
            ->addColumn('name_company', function ($sktemplate) {
                return $sktemplate->company->name ?? 'Empty';
            })
           
            ->rawColumns(['action', 'name_company'])
            ->make(true);
    }
    public function edit($hashedId)
    {
        $sktemplate = Sktemplate::get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });
        $companies = Company::pluck('name','id');
        // $sktypes = Sktype::select('sk_name')->get();
        // $stores = Stores::select('name')->get();

        if (!$sktemplate) {
            abort(404, 'SK Template not found.');
        }
        return view('pages.Sktemplate.edit', [
            'sktemplate' => $sktemplate,
            'companies' => $companies,
            // 'stores' => $stores,
            // 'sktypes' => $sktypes,
            'hashedId' => $hashedId
        ]);
    }

    public function create()
    {
        $companies = Company::pluck('name', 'id');
        // $sktypes = Sktype::pluck('sk_name','id');
        // $stores = Stores::pluck('name','id');
        return view('pages.Sktemplate.create', compact('companies'));
    }
public function store(Request $request)
{
    // normalisasi uppercase
    $request->merge([
        'template_name' => strtoupper($request->template_name)
    ]);

    $validatedData = $request->validate([
        'template_name' => [
            'required',
            'string',
            'max:255',
            // UNIQUE PER STORE
            Rule::unique('sk_template')->where(function ($query) use ($request) {
                return $query->where('template_name', $request->template_name);
            }),
        ],
        // 'store_id' => ['required', 'exists:stores_tables,id'],
        'company_id' => ['required', 'exists:company_tables,id'],
        // 'sk_type_id' => ['required', 'exists:sk_type,id'],
    ]);
    try {
        DB::beginTransaction();
        Sktemplate::create([
            'template_name' => $validatedData['template_name'],
            'company_id' => $validatedData['company_id'],
            // 'sk_type_id' => $validatedData['sk_type_id'],
            // 'store_id' => $validatedData['store_id'],
        ]);
        DB::commit();
        return redirect()->route('pages.Sktemplate')
            ->with('success', 'SK Template created successfully!');

    } catch (\Exception $e) {
        DB::rollBack();

        return back()
            ->withErrors(['error' => $e->getMessage()])
            ->withInput();
    }
}
   public function update(Request $request, $hashedId)
{
    $sktemplate = Sktemplate::get()->first(function ($u) use ($hashedId) {
        $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
        return $expectedHash === $hashedId;
    });

    if (!$sktemplate) {
        return redirect()->route('pages.Sktemplate')
            ->with('error', 'ID tidak valid.');
    }

    // samakan uppercase dulu
    $request->merge([
        'template_name' => strtoupper($request->template_name)
    ]);

    $validatedData = $request->validate([
        'template_name' => [
            'required',
            'string',
            'max:255',

            // UNIQUE PER STORE (IGNORE CURRENT RECORD)
            Rule::unique('sk_template')
                ->where(function ($query) use ($request) {
                    return $query->where('template_name', $request->template_name);
                })
                ->ignore($sktemplate->id),
        ],
        // 'store_id' => ['required', 'exists:stores_tables,id'],
        'company_id' => ['required', 'exists:company_tables,id'],
        // 'sk_type_id' => ['required', 'exists:company_tables,id'],
    ]);

    DB::beginTransaction();

    try {
        $sktemplate->update([
            'template_name' => $validatedData['template_name'],
            // 'store_id' => $validatedData['store_id'],
            'company_id' => $validatedData['company_id'],
            // 'sk_type_id' => $validatedData['sk_type_id'],
        ]);

        DB::commit();

        return redirect()->route('pages.Sktemplate')
            ->with('success', 'SK Template updated successfully!');

    } catch (\Exception $e) {
        DB::rollBack();

        return back()
            ->withErrors(['error' => $e->getMessage()])
            ->withInput();
    }
}
}
