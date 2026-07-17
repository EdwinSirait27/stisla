<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Mail\UserUpdateRequestedMail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use App\Models\Documents;
use App\Models\Employee;
use App\Models\User;
use Spatie\Permission\Models\Role;

use Carbon\Carbon;
use App\Models\SkLetter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserprofileController extends Controller
{
    public function indexpassword()
    {
        $user = Auth::user();
        return view('pages.change-password', compact('user'));
    }
//     public function switchRole(Request $request)
// {
//     /** @var \App\Models\User $user */
//     $user = Auth::user();

//     $allRoles = $user->all_roles_hrx ?? [];

//     $request->validate([
//         'active_role_hrx' => ['required', 'string', 'in:' . implode(',', $allRoles)],
//     ], [
//         'active_role_hrx.in' => 'Role yang dipilih tidak tersedia untuk akun Anda.',
//     ]);

//     $user->update([
//         'active_role_hrx' => $request->active_role_hrx,
//     ]);

//     Log::info('[SWITCH ROLE] Active role diubah', [
//         'user_id'     => $user->id,
//         'active_role' => $request->active_role_hrx,
//     ]);

//     return back()->with('status', 'Role aktif berhasil diubah.');
// }
public function switchRole(Request $request)
{
    /** @var \App\Models\User $user */
    $user = Auth::user();

    $allRoles = $user->all_roles_hrx ?? [];

    $request->validate([
        'active_role_hrx' => ['required', 'string', 'in:' . implode(',', $allRoles)],
    ], [
        'active_role_hrx.in' => 'Role yang dipilih tidak tersedia untuk akun Anda.',
    ]);

    $selectedRole = $request->active_role_hrx;

    $role = Role::findByName($selectedRole);
    $user->syncRoles([$role]);

    $user->update([
        'active_role_hrx' => $selectedRole,
    ]);

    // Reset cache Spatie tanpa logout
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    Log::info('[SWITCH ROLE] Active role diubah', [
        'user_id'     => $user->id,
        'active_role' => $selectedRole,
    ]);

    return back()->with('status', 'Role aktif berhasil diubah ke ' . $selectedRole . '.');
}
    public function index()
    {
        $user = User::with([
            'Employee.documents.companydocumentconfigs.documenttypes',

            'Employee.position',
            'Employee.store',
            'Employee.department',
            'Employee.skletters' => function ($query) {
                $query->where('status', 'Draft');
            },
        ])->find(Auth::id());
        return view('pages.feature-profile', compact('user'));
    }
    public function updateemailtelpphotos(Request $request)
    {
        $user = Auth::user();
// dd($user->employee->id, $user->employee->email);
        $validated = $request->validate([
            'email' => [
                'nullable',
                'email',
                'max:100',
                'not_regex:/[\r\n]/',
                Rule::unique('employees_tables', 'email')->ignore($user->employee->id)
            ],
            'telp_number' => [
                'nullable',
                'max:20',
                Rule::unique('employees_tables', 'telp_number')->ignore($user->employee->id)
            ],
    //          'email' => [
    //     'nullable',
    //     'email',
    //     'max:100',
    //     'not_regex:/[\r\n]/',
    //     Rule::unique('employees_tables', 'email')
    //         ->ignore($user->employee->id, 'id')
    // ],
    // 'telp_number' => [
    //     'nullable',
    //     'max:20',
    //     Rule::unique('employees_tables', 'telp_number')
    //         ->ignore($user->employee->id, 'id')
    // ],
            'photos' => [
                'nullable',
                'mimes:jpg,jpeg,png,webp',
                // 'max:512'
                'max:2048'
            ],
            'kk_photos' => [
                'nullable',
                'mimes:jpg,jpeg,png,webp',
                'max:512'
            ],
            'ktp_photos' => [
                'nullable',
                'mimes:jpg,jpeg,png,webp',
                'max:512'
            ],
            'signature' => [
                'nullable',
                'string'
            ],
            'signature_file' => [
                'nullable',
                'mimes:jpg,jpeg,png,webp',
                'max:512'
            ],

        ], [
            'photos.mimes' => 'Photo harus jpg, jpeg, png, atau webp',
            'photos.max'   => 'Photo maksimal 2048KB',
        ]);

        $changes      = [];
        $photoUpdated = false;

        /*
    |--------------------------------------------------------------------------
    | PHOTO
    |--------------------------------------------------------------------------
    */

        if ($request->hasFile('photos')) {


            $file     = $request->file('photos');
            $safeName = Str::slug($user->employee->employee_name);
            $fileName = $safeName . '-' . now()->timestamp . '-photos.png';
            $folder   = 'employees-photos';
            $oldPath  = $user->employee->photos;
            // Hapus foto lama
            if ($oldPath && Storage::disk('s3')->exists($oldPath)) {
                Storage::disk('s3')->delete($oldPath);
            } else {
                Log::info('[PHOTO] Tidak ada foto lama untuk dihapus');
            }

            // Upload baru
            $path = Storage::disk('s3')->putFileAs(
                $folder,
                $file,
                $fileName
            );

            Log::info('[PHOTO] Upload selesai', [
                'path'   => $path,
                'exists' => Storage::disk('s3')->exists($path),
            ]);

            $user->employee->photos = $path;
            $photoUpdated           = true;
        } else {
            Log::info('[PHOTO] Tidak ada file yang diupload');
        }
        if ($request->hasFile('kk_photos')) {

            Log::info('[KK PHOTO] File KK detected', [
                'original_name' => $request->file('kk_photos')->getClientOriginalName(),
                'size'          => $request->file('kk_photos')->getSize(),
                'mime'          => $request->file('kk_photos')->getMimeType(),
            ]);

            $file     = $request->file('kk_photos');
            $safeName = Str::slug($user->employee->employee_name);
            $fileName = $safeName . '-' . now()->timestamp . '-kk.png';
            $folder   = 'employees-kk-photos';
            $oldPath  = $user->employee->kk_photos;

            Log::info('[KK PHOTO] Info upload KK', [
                'safeName' => $safeName,
                'fileName' => $fileName,
                'folder'   => $folder,
                'oldPath'  => $oldPath ?? 'tidak ada',
            ]);

            // Hapus foto lama
            if ($oldPath && Storage::disk('s3')->exists($oldPath)) {
                Storage::disk('s3')->delete($oldPath);
            } else {
                Log::info('[KK PHOTO] Tidak ada foto KK lama untuk dihapus');
            }

            // Upload baru
            $path = Storage::disk('s3')->putFileAs(
                $folder,
                $file,
                $fileName
            );

            Log::info('[KK PHOTO] Upload KK selesai', [
                'path'   => $path,
                'exists' => Storage::disk('s3')->exists($path),
            ]);

            $user->employee->kk_photos = $path;
            $photoUpdated           = true;
        } else {
            Log::info('[KK PHOTO] Tidak ada file KK yang diupload');
        }
        if ($request->hasFile('ktp_photos')) {

            Log::info('[KTP PHOTO] File KTP detected', [
                'original_name' => $request->file('ktp_photos')->getClientOriginalName(),
                'size'          => $request->file('ktp_photos')->getSize(),
                'mime'          => $request->file('ktp_photos')->getMimeType(),
            ]);

            $file     = $request->file('ktp_photos');
            $safeName = Str::slug($user->employee->employee_name);
            $fileName = $safeName . '-' . now()->timestamp . '-ktp.png';
            $folder   = 'employees-ktp-photos';
            $oldPath  = $user->employee->ktp_photos;

            Log::info('[KTP PHOTO] Info upload KTP', [
                'safeName' => $safeName,
                'fileName' => $fileName,
                'folder'   => $folder,
                'oldPath'  => $oldPath ?? 'tidak ada',
            ]);

            // Hapus foto lama
            if ($oldPath && Storage::disk('s3')->exists($oldPath)) {
                Storage::disk('s3')->delete($oldPath);
            } else {
                Log::info('[KTP PHOTO] Tidak ada foto ktp lama untuk dihapus');
            }

            // Upload baru
            $path = Storage::disk('s3')->putFileAs(
                $folder,
                $file,
                $fileName
            );

            Log::info('[KTP PHOTO] Upload KTP selesai', [
                'path'   => $path,
                'exists' => Storage::disk('s3')->exists($path),
            ]);

            $user->employee->ktp_photos = $path;
            $photoUpdated           = true;
        } else {
            Log::info('[KTP PHOTO] Tidak ada file KTP yang diupload');
        }

        /*
    |--------------------------------------------------------------------------
    | EMAIL
    |--------------------------------------------------------------------------
    */

        if (!empty($validated['email']) && $validated['email'] !== $user->employee->email) {
            $user->employee->pending_email = $validated['email'];
            $changes['email']              = $validated['email'];
            Log::info('[EMAIL] Pending email diset', ['email' => $validated['email']]);
        }

        /*
    |--------------------------------------------------------------------------
    | TELP
    |--------------------------------------------------------------------------
    */
        if (!empty($validated['telp_number']) && $validated['telp_number'] !== $user->employee->telp_number) {
            $user->employee->pending_telp_number = $validated['telp_number'];
            $changes['telp_number']              = $validated['telp_number'];
            Log::info('[TELP] Pending telp diset', ['telp_number' => $validated['telp_number']]);
        }
        /*
|--------------------------------------------------------------------------
| SIGNATURE
|--------------------------------------------------------------------------
*/


        if ($request->filled('signature')) {

            if (!empty($user->employee->signature)) {
                return back()->withInput()->withErrors([
                    'signature' => 'Signature sudah tersedia, silahkan menghubungi administrator.'
                ]);
            }

            $signature = $request->signature;
            $signature = str_replace('data:image/png;base64,', '', $signature);
            $signature = str_replace(' ', '+', $signature);

            $safeName = Str::slug($user->employee->employee_name);

            // ✅ Fix: hapus double dash
            $fileName = $safeName . '-' . now()->timestamp . '-signature.png';

            $folder = 'employees-signatures-photos';
            $path   = $folder . '/' . $fileName;

            $decodedImage = base64_decode($signature);

            if ($decodedImage === false) {
                return back()->withErrors(['signature' => 'Signature gagal diproses.']);
            }

            Storage::disk('s3')->put($path, $decodedImage);

            $user->employee->signature = $path;
            $photoUpdated = true;
        }

        /*
|--------------------------------------------------------------------------
| SIGNATURE - FILE IMPORT
|--------------------------------------------------------------------------
*/

        if ($request->hasFile('signature_file')) {

            if (!empty($user->employee->signature)) {
                return back()->withInput()->withErrors([
                    'signature_file' => 'Signature sudah tersedia, silahkan menghubungi administrator.'
                ]);
            }

            $file     = $request->file('signature_file');
            $safeName = Str::slug($user->employee->employee_name);

            // ✅ Whitelist ekstensi, jangan pakai getClientOriginalExtension langsung
            $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
            $ext        = strtolower($file->getClientOriginalExtension());

            if (!in_array($ext, $allowedExt)) {
                return back()->withErrors(['signature_file' => 'Tipe file tidak diizinkan.']);
            }

            $fileName = $safeName . '-' . now()->timestamp . '-signature.' . $ext;
            $folder   = 'employees-signatures-photos';
            $path     = $folder . '/' . $fileName;

            Storage::disk('s3')->putFileAs($folder, $file, $fileName);

            $user->employee->signature = $path;
            $photoUpdated = true;
        }

        $user->employee->save();
        Log::info('[SAVE] Employee saved', ['employee_id' => $user->employee->id]);

        /*
    |--------------------------------------------------------------------------
    | SEND EMAIL APPROVAL
    |--------------------------------------------------------------------------
    */
        if (!empty($changes)) {
            Mail::to('hrd@asianbay.co.id')
                ->send(new UserUpdateRequestedMail($user, $changes));

            Log::info('[MAIL] Email approval terkirim', ['changes' => $changes]);

            return back()->with('status', 'Email or telephone number changes awaiting HR approval.');
        }

        if ($photoUpdated) {
            return back()->with('status', 'Updated successfully.');
        }

        return back()->with('status', 'No changes proposed.');
    }

    public function save(Request $request)
    {
        $request->validate([
            'signature' => 'nullable|string',
            'signature_file' => 'nullable|mimes:jpg,jpeg,png,webp|max:512',
        ]);

        $user     = auth()->user();
        $employee = $user->employee;

        // Jika bukan admin dan sudah punya signature → blok
        /** @var \App\Models\User|null $user */

        if (!$user->hasRole('Admin') && $employee->signature) {
            return back()->with('error', 'Anda sudah mempunyai signature, silakan hubungi administrator untuk mengupdate.');
        }

        /*
    |--------------------------------------------------------------------------
    | SIGNATURE - CANVAS (base64)
    |--------------------------------------------------------------------------
    */
        if ($request->filled('signature')) {

            Log::info('[SIGNATURE] Signature detected');

            $signature = $request->signature;
            $signature = str_replace('data:image/png;base64,', '', $signature);
            $signature = str_replace(' ', '+', $signature);

            $safeName = Str::slug($employee->employee_name);
            $fileName = $safeName . '-' . now()->timestamp . '-signature.png';
            $folder   = 'employees-signatures-photos';
            $path     = $folder . '/' . $fileName;

            $decodedImage = base64_decode($signature);

            if ($decodedImage === false) {
                Log::error('[SIGNATURE] Base64 decode gagal');
                return back()->withErrors([
                    'signature' => 'Signature gagal diproses.'
                ]);
            }

            Storage::disk('s3')->put($path, $decodedImage);

            Log::info('[SIGNATURE] Upload signature selesai', [
                'path'   => $path,
                'exists' => Storage::disk('s3')->exists($path),
            ]);

            $employee->signature = $path;
        }

        /*
    |--------------------------------------------------------------------------
    | SIGNATURE - FILE IMPORT
    |--------------------------------------------------------------------------
    */
        if ($request->hasFile('signature_file')) {

            Log::info('[SIGNATURE FILE] File detected', [
                'original_name' => $request->file('signature_file')->getClientOriginalName(),
                'size'          => $request->file('signature_file')->getSize(),
                'mime'          => $request->file('signature_file')->getMimeType(),
            ]);

            $file     = $request->file('signature_file');
            $safeName = Str::slug($employee->employee_name);
            $fileName = $safeName . '-' . now()->timestamp . '-signature.' . $file->getClientOriginalExtension();
            $folder   = 'employees-signatures-photos';
            $path     = $folder . '/' . $fileName;

            // Storage::disk('public')->putFileAs($folder, $file, $fileName);
            Storage::disk('s3')->putFileAs(
                $folder,
                $file,
                $fileName
            );

            Log::info('[SIGNATURE FILE] Upload selesai', [
                'path'   => $path,
                'exists' => Storage::disk('s3')->exists($path),
            ]);

            $employee->signature = $path;
        }

        // Jika tidak ada input sama sekali
        if (!$request->filled('signature') && !$request->hasFile('signature_file')) {
            return back()->withErrors([
                'signature' => 'Harap isi signature terlebih dahulu.'
            ]);
        }

        $employee->save();

        return back()->with('success', 'Signature updated!');
    }

    private function serveFile(string $filename, string $folder, string $column): \Illuminate\Http\Response
{
    if (!auth()->check()) {
        abort(401);
    }

    if (!preg_match('/^[\w\-]+\.(jpg|jpeg|png|gif|webp)$/i', $filename)) {
        abort(400, 'Invalid filename');
    }

    $filename          = basename($filename);
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $extension         = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    if (!in_array($extension, $allowedExtensions)) {
        abort(400, 'File type not allowed');
    }

    $user = auth()->user();

    // ManageEmployee → akses foto siapapun
    // User biasa → hanya foto milik sendiri
    if (!$user->can('ManageEmployee')) {
        $isOwner = Employee::where('id', $user->employee_id)
            ->where($column, $folder . '/' . $filename)
            ->exists();

        if (!$isOwner) {
            abort(403, 'Forbidden: You are not allowed to access this file');
        }
    }

    $fullPath = $folder . '/' . $filename;

    if (!Storage::disk('s3')->exists($fullPath)) {
        abort(404);
    }

    $file = Storage::disk('s3')->get($fullPath);

    $mimeTypes = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif',
        'webp' => 'image/webp',
    ];

    return response($file, 200)
        ->header('Content-Type', $mimeTypes[$extension])
        ->header('Content-Security-Policy', "default-src 'none'")
        ->header('X-Content-Type-Options', 'nosniff')
        ->header('Cache-Control', 'private, max-age=3600');
}

public function servePhoto($filename)
{
    return $this->serveFile($filename, 'employees-photos', 'photos');
}

public function serveSignature($filename)
{
    return $this->serveFile($filename, 'employees-signatures-photos', 'signature');
}

public function servePhotoktp($filename)
{
    return $this->serveFile($filename, 'employees-ktp-photos', 'ktp_photos');
}

public function servePhotokk($filename)
{
    return $this->serveFile($filename, 'employees-kk-photos', 'kk_photos');
}

    public function updatePassword(Request $request)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($user) {
                    if (!Hash::check($value, $user->password)) {
                        $fail('The current password is incorrect.');
                    }
                },
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:20',
                'different:current_password',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9])\S+$/',
                function ($attribute, $value, $fail) use ($user) {
                    if (strtolower($value) === strtolower($user->username)) {
                        $fail('Password tidak boleh sama dengan username.');
                    }
                },
            ],
        ], [], [], function ($validator) {
            $validator->after(function ($validator) {
                // tidak perlu tambahan logic di sini
            });
        });

        try {
            $isDefaultPassword = Hash::check(strtolower($user->username), $user->password);

            $user->password = Hash::make($validated['password']);
            $user->save();

            Log::info("User changed password", [
                'user_id'  => $user->id,
                'username' => $user->username,
                'ip'       => $request->ip(),
            ]);

            return redirect()->route('pages.feature-profile')
                ->with(
                    'success',
                    $isDefaultPassword
                        ? 'Password berhasil diubah. Selamat datang!'
                        : 'Password changed successfully.'
                );
        } catch (\Exception $e) {
            Log::error("Failed to change password", [
                'user_id'  => $user->id,
                'username' => $user->username,
                'ip'       => $request->ip(),
                'error'    => $e->getMessage(),
            ]);

            return redirect()->route('pages.change-password')
                ->with('error', 'Gagal mengubah password. Silakan coba lagi.');
        }
    }
    public function downloadDocument(string $id)
    {

        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $id)) {
            abort(400, 'Invalid ID');
        }
        $user = Auth::user();

        $document = Documents::with([
            'employee.position',
            'issued.position',
            'companydocumentconfigs.company',
            'companydocumentconfigs.documenttypes',
        ])
            ->where('employee_id', $user->employee_id)
            ->findOrFail($id);

        if (!$document->companydocumentconfigs || !$document->companydocumentconfigs->documenttypes) {
            abort(404, 'Document configuration not found');
        }

        $viewName = $document->companydocumentconfigs->documenttypes->view_name;

        // ✅ Whitelist view yang diizinkan
        $allowedViews = [
            'documents.types.SPK',
            'documents.types.SPPRP',
        ];
        $signatureData = null;
        if ($document->issued && $document->issued->signature) {
            $path = 'employees-signatures-photos/' . basename($document->issued->signature);
            if (Storage::disk('s3')->exists($path)) {
                $signatureData = 'data:image/png;base64,' . base64_encode(
                    Storage::disk('s3')->get($path)
                );
            }
        }

        if (!in_array($viewName, $allowedViews)) {
            abort(403, 'Invalid document view');
        }
        $pdf = Pdf::loadView($viewName, [
            'document' => $document,
            'employee' => $document->employee,
            'issued'   => $document->issued,
            'config'   => $document->companydocumentconfigs,
            'company'  => $document->companydocumentconfigs->company,
            'signatureData' => $signatureData,
        ])->setPaper('a4');

        /*
    |--------------------------------------------------------------------------
    | PASSWORD PDF
    |--------------------------------------------------------------------------
    | Format: yyyymmdd
    */

        $password = Carbon::parse(
            $user->employee->date_of_birth
        )->format('Ymd');

        /*
    |--------------------------------------------------------------------------
    | ENCRYPT PDF
    |--------------------------------------------------------------------------
    */

        $domPdf = $pdf->getDomPDF();
        $canvas = $domPdf->getCanvas();

        if (method_exists($canvas, 'get_cpdf')) {

            $cpdf = $canvas->get_cpdf();

            /*
        |--------------------------------------------------------------------------
        | setEncryption(userPassword, ownerPassword, permissions)
        |--------------------------------------------------------------------------
        */
            $cpdf->setEncryption(
                $password,
                $password
            );
        }
        $filename = str_replace('/', '-', $document->document_number) . '.pdf';
        return $pdf->download($filename);
    }
    public function downloadSkLetter(string $id)
    {
        $user = Auth::user();

        $skletter = SkLetter::with([
            'employees',
            'skType',
            'company',
            'approver1',
            'approver2',
            'approver3',
            'menimbang',
            'mengingat',
            'keputusan',
        ])
            ->where('id', $id)
            ->whereHas('employees', function ($query) use ($user) {
                $query->where(
                    'sk_letter_employees.employee_id',
                    $user->employee_id
                );
            })
            ->firstOrFail();

        /*
    |--------------------------------------------------------------------------
    | FILTER HANYA EMPLOYEE LOGIN
    |--------------------------------------------------------------------------
    */

        $skletter->setRelation(
            'employees',
            $skletter->employees->where('id', $user->employee_id)
        );

        $filename = 'SK-' . str_replace('/', '-', $skletter->sk_number) . '.pdf';

        $pdf = Pdf::loadView('pages.SkLetters.pdf', [
            'skLetter' => $skletter,
        ])->setPaper('a4', 'portrait');

        /*
    |--------------------------------------------------------------------------
    | PASSWORD PDF
    |--------------------------------------------------------------------------
    | Format password: yyyymmdd
    */

        $password = Carbon::parse(
            $user->employee->date_of_birth
        )->format('Ymd');

        /*
    |--------------------------------------------------------------------------
    | ENCRYPT PDF
    |--------------------------------------------------------------------------
    */

        $domPdf = $pdf->getDomPDF();
        $canvas = $domPdf->getCanvas();

        if (method_exists($canvas, 'get_cpdf')) {

            $cpdf = $canvas->get_cpdf();

            $cpdf->setEncryption(
                $password,
                $password
            );
        }

        return $pdf->download($filename);
    }
}
