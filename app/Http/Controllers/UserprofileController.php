<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use App\Mail\UserUpdateRequestedMail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use App\Models\Documents;
use App\Models\User;
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
    public function index()
    {
        $user = User::with([
            'Employee.documents.companydocumentconfigs.documenttypes',
            'Employee.position',
            'Employee.skletters' => function ($query) {
                $query->where('status', 'Draft');
                // $query->where('status', 'Approved Managing Director');
            },
        ])->find(Auth::id());
        // @dd($user->Employee->skletters->pluck('status'));


        return view('pages.feature-profile', compact('user'));
    }

    public function updateemailtelpphotos(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'email' => [
                'nullable',
                'email',
                'max:100',
                Rule::unique('employees_tables', 'email')->ignore($user->employee->id)
            ],
            'telp_number' => [
                'nullable',
                'max:20',
                Rule::unique('employees_tables', 'telp_number')->ignore($user->employee->id)
            ],
            'photos' => [
                'nullable',
                'mimes:jpg,jpeg,png,webp',
                'max:512'
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
            // 'signature' => [
            //     'nullable',
            //     'string'
            // ],
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
            'photos.max'   => 'Photo maksimal 512KB',
        ]);

        $changes      = [];
        $photoUpdated = false;

        /*
    |--------------------------------------------------------------------------
    | PHOTO
    |--------------------------------------------------------------------------
    */

        if ($request->hasFile('photos')) {

            Log::info('[PHOTO] File detected', [
                'original_name' => $request->file('photos')->getClientOriginalName(),
                'size'          => $request->file('photos')->getSize(),
                'mime'          => $request->file('photos')->getMimeType(),
            ]);

            $file     = $request->file('photos');
            $safeName = Str::slug($user->employee->employee_name);
            $fileName = $safeName . '.' . $file->getClientOriginalExtension();
            $folder   = 'employees-photos';
            $oldPath  = $user->employee->photos;

            Log::info('[PHOTO] Info upload ', [
                'safeName' => $safeName,
                'fileName' => $fileName,
                'folder'   => $folder,
                'oldPath'  => $oldPath ?? 'tidak ada',
            ]);

            // Hapus foto lama
            if ($oldPath && Storage::disk('local')->exists($oldPath)) {
                Storage::disk('local')->delete($oldPath);
                Log::info('[PHOTO] Foto lama dihapus', ['oldPath' => $oldPath]);
            } else {
                Log::info('[PHOTO] Tidak ada foto lama untuk dihapus');
            }

            // Upload baru
            $path = $file->storeAs($folder, $fileName, 'local');

            Log::info('[PHOTO] Upload selesai', [
                'path'   => $path,
                'exists' => Storage::disk('local')->exists($path),
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
            $fileName = $safeName . '.' . $file->getClientOriginalExtension();
            $folder   = 'employees-kk-photos';
            $oldPath  = $user->employee->kk_photos;

            Log::info('[KK PHOTO] Info upload KK', [
                'safeName' => $safeName,
                'fileName' => $fileName,
                'folder'   => $folder,
                'oldPath'  => $oldPath ?? 'tidak ada',
            ]);

            // Hapus foto lama
            if ($oldPath && Storage::disk('local')->exists($oldPath)) {
                Storage::disk('local')->delete($oldPath);
                Log::info('[KK PHOTO] Foto KK lama dihapus', ['oldPath' => $oldPath]);
            } else {
                Log::info('[KK PHOTO] Tidak ada foto KK lama untuk dihapus');
            }

            // Upload baru
            $path = $file->storeAs($folder, $fileName, 'local');

            Log::info('[KK PHOTO] Upload KK selesai', [
                'path'   => $path,
                'exists' => Storage::disk('local')->exists($path),
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
            $fileName = $safeName . '.' . $file->getClientOriginalExtension();
            $folder   = 'employees-ktp-photos';
            $oldPath  = $user->employee->ktp_photos;

            Log::info('[KTP PHOTO] Info upload KTP', [
                'safeName' => $safeName,
                'fileName' => $fileName,
                'folder'   => $folder,
                'oldPath'  => $oldPath ?? 'tidak ada',
            ]);

            // Hapus foto lama
            if ($oldPath && Storage::disk('local')->exists($oldPath)) {
                Storage::disk('local')->delete($oldPath);
                Log::info('[KTP PHOTO] Foto lama KTP dihapus', ['oldPath' => $oldPath]);
            } else {
                Log::info('[KTP PHOTO] Tidak ada foto ktp lama untuk dihapus');
            }

            // Upload baru
            $path = $file->storeAs($folder, $fileName, 'local');

            Log::info('[KTP PHOTO] Upload KTP selesai', [
                'path'   => $path,
                'exists' => Storage::disk('local')->exists($path),
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

            /*
    |--------------------------------------------------------------------------
    | CEK APAKAH SUDAH ADA SIGNATURE
    |--------------------------------------------------------------------------
    */

            if (!empty($user->employee->signature)) {

                Log::warning('[SIGNATURE] Gagal upload, signature sudah ada', [
                    'employee_id' => $user->employee->id,
                    'signature'   => $user->employee->signature,
                ]);

                return back()
                    ->withInput()
                    ->withErrors([
                        'signature' => 'Signature sudah tersedia, silahkan menghubungi administrator.'
                    ]);
            }

            Log::info('[SIGNATURE] Signature detected');

            $signature = $request->signature;

            // hapus prefix base64
            $signature = str_replace('data:image/png;base64,', '', $signature);
            $signature = str_replace(' ', '+', $signature);

            $safeName = Str::slug($user->employee->employee_name);

            $fileName = $safeName . '-signature.png';

            $folder = 'employees-signatures';

            $path = $folder . '/' . $fileName;

            $decodedImage = base64_decode($signature);

            if ($decodedImage === false) {

                Log::error('[SIGNATURE] Base64 decode gagal');

                return back()->withErrors([
                    'signature' => 'Signature gagal diproses.'
                ]);
            }

            $result = Storage::disk('public')->put(
                $path,
                $decodedImage
            );

            Log::info('[SIGNATURE] Upload signature selesai', [
                'result' => $result,
                'path'   => $path,
                'exists' => Storage::disk('public')->exists($path),
            ]);

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
                Log::warning('[SIGNATURE FILE] Gagal upload, signature sudah ada', [
                    'employee_id' => $user->employee->id,
                ]);
                return back()
                    ->withInput()
                    ->withErrors([
                        'signature_file' => 'Signature sudah tersedia, silahkan menghubungi administrator.'
                    ]);
            }

            Log::info('[SIGNATURE FILE] File detected', [
                'original_name' => $request->file('signature_file')->getClientOriginalName(),
                'size'          => $request->file('signature_file')->getSize(),
                'mime'          => $request->file('signature_file')->getMimeType(),
            ]);

            $file     = $request->file('signature_file');
            $safeName = Str::slug($user->employee->employee_name);
            $fileName = $safeName . '-signature.' . $file->getClientOriginalExtension();
            $folder   = 'employees-signatures';
            $path     = $folder . '/' . $fileName;

            Storage::disk('public')->putFileAs($folder, $file, $fileName);

            Log::info('[SIGNATURE FILE] Upload selesai', [
                'path'   => $path,
                'exists' => Storage::disk('public')->exists($path),
            ]);

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
            $fileName = $safeName . '-signature.png';
            $folder   = 'employees-signatures';
            $path     = $folder . '/' . $fileName;

            $decodedImage = base64_decode($signature);

            if ($decodedImage === false) {
                Log::error('[SIGNATURE] Base64 decode gagal');
                return back()->withErrors([
                    'signature' => 'Signature gagal diproses.'
                ]);
            }

            Storage::disk('public')->put($path, $decodedImage);

            Log::info('[SIGNATURE] Upload signature selesai', [
                'path'   => $path,
                'exists' => Storage::disk('public')->exists($path),
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
            $fileName = $safeName . '-signature.' . $file->getClientOriginalExtension();
            $folder   = 'employees-signatures';
            $path     = $folder . '/' . $fileName;

            Storage::disk('public')->putFileAs($folder, $file, $fileName);

            Log::info('[SIGNATURE FILE] Upload selesai', [
                'path'   => $path,
                'exists' => Storage::disk('public')->exists($path),
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
    /*|--------------------------------------------------------------------------
| SERVE PHOTO
|--------------------------------------------------------------------------*/
    public function serveSignature($filename)
    {
        $path = storage_path('app/public/employees-signatures/' . $filename);

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->file($path);
    }
    public function servePhoto($filename)
    {
        $path = 'employees-photos/' . $filename;

        Log::info('[SERVE PHOTO] Request foto', [
            'filename' => $filename,
            'path'     => $path,
            'exists'   => Storage::disk('local')->exists($path),
        ]);

        if (!Storage::disk('local')->exists($path)) {
            Log::warning('[SERVE PHOTO] File tidak ditemukan', ['path' => $path]);
            abort(404);
        }

        return response()->file(
            Storage::disk('local')->path($path)
        );
    }
    public function servePhotoktp($filename)
    {
        $path = 'employees-ktp-photos/' . $filename;

        Log::info('[SERVE PHOTO KTP] Request foto KTP', [
            'filename' => $filename,
            'path'     => $path,
            'exists'   => Storage::disk('local')->exists($path),
        ]);

        if (!Storage::disk('local')->exists($path)) {
            Log::warning('[SERVE PHOTO KTP] File KTP tidak ditemukan', ['path' => $path]);
            abort(404);
        }

        return response()->file(
            Storage::disk('local')->path($path)
        );
    }
    public function servePhotokk($filename)
    {
        $path = 'employees-kk-photos/' . $filename;

        Log::info('[SERVE PHOTO KK] Request foto KK', [
            'filename' => $filename,
            'path'     => $path,
            'exists'   => Storage::disk('local')->exists($path),
        ]);

        if (!Storage::disk('local')->exists($path)) {
            Log::warning('[SERVE PHOTO KK] File KK tidak ditemukan', ['path' => $path]);
            abort(404);
        }

        return response()->file(
            Storage::disk('local')->path($path)
        );
    }
    // public function updatePassword(Request $request)
    // {
    //             /** @var \App\Models\User|null $user */

    //     $user = Auth::user();

    //     $validated = $request->validate([
    //         'password' => [
    //             'nullable',
    //             'string',
    //             'min:8',
    //             'max:20',
    //             'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9])\S+$/'
    //         ],
    //     ]);

    //     $changes = [];
    //     $passwordChanged = false;

    //     // Update password
    //     if (!empty($validated['password'])) {
    //         $user->password = Hash::make($validated['password']);
    //         $passwordChanged = true;
    //     }
    //     $user->save();
    //     $user->employee->save();
    //     if (empty($changes) && !$passwordChanged) {
    //         return back()->with('status', 'No changes proposed.');
    //     }

    //     if ($passwordChanged) {
    //         return back()->with('status', 'Password changed successfully.');
    //     }
    // }
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
            'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9])\S+$/'
        ],
    ]);
    $user->password = Hash::make($validated['password']);
    $user->save();

    return back()->with('status', 'Password changed successfully.');
}
    public function downloadDocument(string $id)
    {
        $user = Auth::user();

        $document = Documents::with([
            'employee.position',
            'issued.position',
            'companydocumentconfigs.company',
            'companydocumentconfigs.documenttypes',
        ])
            ->where('employee_id', $user->employee_id)
            ->findOrFail($id);

        $viewName = $document->companydocumentconfigs->documenttypes->view_name;

        $pdf = Pdf::loadView($viewName, [
            'document' => $document,
            'employee' => $document->employee,
            'issued'   => $document->issued,
            'config'   => $document->companydocumentconfigs,
            'company'  => $document->companydocumentconfigs->company,
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
