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
    public function indexpassword(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Status "password default" dari cache ForcePasswordChange (per hash password), supaya bcrypt tidak dijalankan ulang
        $cached = $request->session()->get('pwd_default');
        $isDefault = is_array($cached) && ($cached['fp'] ?? null) === hash('sha256', (string) $user->password)
            ? (bool) $cached['is']
            : \App\Services\LoginSessionService::hasDefaultPassword($user);

        return view('pages.change-password', [
            'user'      => $user,
            'isDefault' => $isDefault,
        ]);
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
            'Employee.grading',
            'Employee.group',
            'Employee.company',
            'Employee.bank',
            'Employee.atasanList:id,employee_name',
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
                // Unik hanya dicek kalau email diganti: data lama ada yang memakai email sama,
                // dan itu tidak boleh menghalangi karyawan menyimpan foto.
                $request->input('email') !== $user->employee->email
                    ? Rule::unique('employees_tables', 'email')->ignore($user->employee->id)
                    : 'nullable',
            ],
            'telp_number' => [
                'nullable',
                'max:20',
                $request->input('telp_number') !== $user->employee->telp_number
                    ? Rule::unique('employees_tables', 'telp_number')->ignore($user->employee->id)
                    : 'nullable',
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

        // Foto profil, KK, KTP: dikompres & dibersihkan (EXIF/lokasi GPS dibuang), ekstensi dari isi file.
        // File baru diunggah dulu; yang lama baru dihapus setelah berhasil, supaya tidak hilang kalau unggahan gagal.
        $uploads = app(\App\Services\UploadStorage::class);
        $safeName = $user->employee->employee_name;
        foreach ([
            'photos'     => ['employees-photos', 'photos'],
            'kk_photos'  => ['employees-kk-photos', 'kk'],
            'ktp_photos' => ['employees-ktp-photos', 'ktp'],
        ] as $field => [$folder, $suffix]) {
            if (!$request->hasFile($field)) continue;

            $oldPath = $user->employee->{$field};
            $path    = $uploads->put($request->file($field), $folder, $safeName . '-' . $suffix, \App\Services\UploadStorage::IMAGES, 's3', false, $field);

            if ($oldPath && $oldPath !== $path && Storage::disk('s3')->exists($oldPath)) {
                Storage::disk('s3')->delete($oldPath);
            }
            Log::info('[PROFILE UPLOAD] ' . $field . ' diperbarui', ['employee_id' => $user->employee->id, 'path' => $path]);

            $user->employee->{$field} = $path;
            $photoUpdated = true;
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

            // Hanya PNG asli dari canvas yang diterima, dibatasi ukurannya, lalu di-encode ulang (PNG transparan)
            $path = app(\App\Services\UploadStorage::class)->putSignatureDataUrl(
                $request->signature, 'employees-signatures-photos', $user->employee->employee_name . '-signature'
            );

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

            // Dikompres jadi PNG transparan; ekstensi dari isi file
            $path = app(\App\Services\UploadStorage::class)->put(
                $request->file('signature_file'), 'employees-signatures-photos', $user->employee->employee_name . '-signature',
                \App\Services\UploadStorage::IMAGES, 's3', true, 'signature_file'
            );

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

            $path = app(\App\Services\UploadStorage::class)->putSignatureDataUrl(
                $request->signature, 'employees-signatures-photos', $employee->employee_name . '-signature'
            );
            Log::info('[SIGNATURE] Upload signature selesai', ['path' => $path]);

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

            $path = app(\App\Services\UploadStorage::class)->put(
                $request->file('signature_file'), 'employees-signatures-photos', $employee->employee_name . '-signature',
                \App\Services\UploadStorage::IMAGES, 's3', true, 'signature_file'
            );
            Log::info('[SIGNATURE FILE] Upload selesai', ['path' => $path]);

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
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($user) {
                    if (!Hash::check($value, $user->password)) {
                        $fail('Password saat ini salah.');
                    }
                },
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:20',
                'confirmed',
                'different:current_password',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9])\S+$/',
                function ($attribute, $value, $fail) use ($user) {
                    if (strtolower($value) === strtolower((string) $user->username)) {
                        $fail('Password baru tidak boleh sama dengan username.');
                    }
                    if ($user->employee?->employee_pengenal && strtolower($value) === strtolower($user->employee->employee_pengenal)) {
                        $fail('Password baru tidak boleh sama dengan NIP.');
                    }
                },
            ],
        ], [
            'current_password.required' => 'Masukkan password saat ini.',
            'password.required'         => 'Masukkan password baru.',
            'password.min'              => 'Password baru minimal 8 karakter.',
            'password.max'              => 'Password baru maksimal 20 karakter.',
            'password.confirmed'        => 'Konfirmasi password baru tidak sama.',
            'password.different'        => 'Password baru harus berbeda dari password saat ini.',
            'password.regex'            => 'Password baru harus berisi huruf besar, huruf kecil, angka, dan simbol, tanpa spasi.',
        ]);

        $isDefaultPassword = \App\Services\LoginSessionService::hasDefaultPassword($user);

        try {
            $user->password = Hash::make($validated['password']);
            $user->remember_token = Str::random(60);
            $user->save();
        } catch (\Throwable $e) {
            Log::error('Failed to change password', [
                'user_id'  => $user->id,
                'username' => $user->username,
                'ip'       => $request->ip(),
                'error'    => $e->getMessage(),
            ]);

            return redirect()->route('pages.change-password')
                ->with('error', 'Gagal mengubah password. Silakan coba lagi.');
        }

        // Password sudah tersimpan. Kegagalan di bawah ini tidak boleh dilaporkan sebagai "gagal mengubah password".
        // Password baru: keluarkan semua perangkat lain (web lain + aplikasi mobile).
        // Sesi yang sedang dipakai tetap jalan, tapi ID-nya diganti.
        $otherDevicesSignedOut = true;
        try {
            $oldSessionId = $request->session()->getId();
            app(\App\Services\UserSessionService::class)->logoutEverywhere($user, $oldSessionId, 'password changed by user');
            $request->session()->regenerate();
            \App\Models\UserSession::where('user_id', $user->id)->where('session_id', $oldSessionId)
                ->update(['session_id' => $request->session()->getId(), 'last_activity' => now()]);
        } catch (\Throwable $e) {
            $otherDevicesSignedOut = false;
            Log::error('Password changed, but signing out other devices failed', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
        }

        Log::info('User changed password', [
            'user_id'  => $user->id,
            'username' => $user->username,
            'ip'       => $request->ip(),
        ]);

        $message = $isDefaultPassword
            ? 'Password berhasil diubah. Selamat datang!'
            : 'Password berhasil diubah.';
        $message .= $otherDevicesSignedOut
            ? ' Perangkat lain yang masih login sudah dikeluarkan.'
            : ' Perangkat lain belum berhasil dikeluarkan; hubungi admin kalau ada perangkat yang tidak Anda kenal.';

        return redirect()->route('pages.feature-profile')->with('success', $message);
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

        // Berita acara (jenis beralur approval) punya halaman & PDF sendiri
        if ($document->companydocumentconfigs?->documenttypes?->requires_approval) {
            return redirect()->route('beritaacara.pdf', $document->id);
        }

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
