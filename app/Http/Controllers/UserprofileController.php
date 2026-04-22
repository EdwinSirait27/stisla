<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use App\Mail\UserUpdateRequestedMail;
use Illuminate\Support\Facades\Mail;
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
        $user = Auth::user();
        return view('pages.feature-profile', compact('user'));
    }
//     public function updateemailtelpphotos(Request $request)
//     {
//         $user = Auth::user();
//         $validated = $request->validate([
//             'email' => [
//                 'nullable',
//                 'email',
//                 'max:100',
//                 Rule::unique('employees_tables', 'email')->ignore($user->employee->id)
//             ],
//             'telp_number' => [
//                 'nullable',
//                 'max:20',
//                 Rule::unique('employees_tables', 'telp_number')->ignore($user->employee->id)
//             ],
//               'photos' => [
//                 'nullable',
//                 'mimes:jpg,jpeg,png,webp',
//                 'max:512'
//             ],
//              'photos.mimes' => 'The photo must be a file of type: jpg, jpeg, png, webp.',
//             'photos.max' => 'photos must under 512 kb.',
//         ]);
//         $changes = [];
//             if ($request->hasFile('photos')) {
//     $file = $request->file('photos');

//     $fileName = hash('sha256', $file->getClientOriginalName() . time()) . '.' .
//         $file->getClientOriginalExtension();

//     $folderPath = 'employeesphotos/' . date('Y/m');

//     Storage::disk('public')->putFileAs($folderPath, $file, $fileName);

//     $newFilePath = $folderPath . '/' . $fileName;

//     if ($filePath && Storage::disk('public')->exists($filePath)) {
//         Storage::disk('public')->delete($filePath);
//     }

//     $filePath = $validatedData['photos'] = $newFilePath;
// }
//         if (!empty($validated['email']) && $validated['email'] !== $user->employee->email) {
//             $user->employee->pending_email = $validated['email'];
//             $changes['email'] = $validated['email'];
//         }
//         if (!empty($validated['telp_number']) && $validated['telp_number'] !== $user->employee->telp_number) {
//             $user->employee->pending_telp_number = $validated['telp_number'];
//             $changes['telp_number'] = $validated['telp_number'];
//         }
//         $user->save();
//         $user->employee->save();
//         if (!empty($changes)) {
//             Mail::to('hrd@asianbay.co.id')
//                 ->send(new UserUpdateRequestedMail($user, $changes));

//             return back()->with('status', 'Changes have been submitted, awaiting HR approval.');
//         }
//             return back()->with('status', 'No changes were made.');
//        }
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
    ], [
        'photos.mimes' => 'Photo harus jpg, jpeg, png, atau webp',
        'photos.max'   => 'Photo maksimal 512KB',
    ]);

    $changes = [];
    $photoUpdated = false;

    // ✅ PHOTO (langsung save, tanpa approval)
    if ($request->hasFile('photos')) {
        $file = $request->file('photos');

        $fileName = hash('sha256', $file->getClientOriginalName() . time()) . '.' .
            $file->getClientOriginalExtension();

        $folderPath = 'employeesphotos/' . date('Y/m');

        Storage::disk('public')->putFileAs($folderPath, $file, $fileName);

        $newFilePath = $folderPath . '/' . $fileName;

        $oldPath = $user->employee->photos;

        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        $user->employee->photos = $newFilePath;
        $photoUpdated = true;
    }

    // ✅ EMAIL (butuh approval)
    if (!empty($validated['email']) && $validated['email'] !== $user->employee->email) {
        $user->employee->pending_email = $validated['email'];
        $changes['email'] = $validated['email'];
    }

    // ✅ TELP (butuh approval)
    if (!empty($validated['telp_number']) && $validated['telp_number'] !== $user->employee->telp_number) {
        $user->employee->pending_telp_number = $validated['telp_number'];
        $changes['telp_number'] = $validated['telp_number'];
    }

    $user->employee->save();

    // ✅ kalau ada perubahan email/telp → kirim email
    if (!empty($changes)) {
        Mail::to('hrd@asianbay.co.id')
            ->send(new UserUpdateRequestedMail($user, $changes));

        return back()->with('status', 'Email or telephone number changes awaiting HR approval.');
    }

    // ✅ kalau cuma update foto
    if ($photoUpdated) {
        return back()->with('status', 'Photo updated successfully.');
    }

    return back()->with('status', 'No changes proposed.');
}
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'password' => [
                'nullable',
                'string',
                'min:8',
                'max:20',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9])\S+$/'
            ],
        ]);

        $changes = [];
        $passwordChanged = false;

        // Update password
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
            $passwordChanged = true;
        }
        $user->save();
        $user->employee->save();
        if (empty($changes) && !$passwordChanged) {
            return back()->with('status', 'No changes proposed.');
        }
        
        if ($passwordChanged) {
            return back()->with('status', 'Password changed successfully.');
        }
    }
}