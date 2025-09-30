<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Mail\UserUpdateRequestedMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class UserprofileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return view('pages.feature-profile', compact('user'));
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
        ]);

        $changes = [];
        $passwordChanged = false;

        // Update password
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
            $passwordChanged = true;
        }

        // Pending email update
        if (!empty($validated['email']) && $validated['email'] !== $user->employee->email) {
            $user->employee->pending_email = $validated['email'];
            $changes['email'] = $validated['email'];
        }

        // Pending telp update
        if (!empty($validated['telp_number']) && $validated['telp_number'] !== $user->employee->telp_number) {
            $user->employee->pending_telp_number = $validated['telp_number'];
            $changes['telp_number'] = $validated['telp_number'];
        }

        // Simpan perubahan
        $user->save();
        $user->employee->save();

        // Tentukan respon berdasarkan perubahan
        if (empty($changes) && !$passwordChanged) {
            return back()->with('status', 'No changes proposed.');
        }

        if (!empty($changes)) {
            Mail::to('hrmahendradatta@gmail.com')
                ->send(new UserUpdateRequestedMail($user, $changes));

            return back()->with('status', 'Changes have been submitted, awaiting HR approval.');
        }

        if ($passwordChanged) {
            return back()->with('status', 'Password changed successfully.');
        }
    }
    //     public function updatePassword(Request $request)
    // {
    //     $user = Auth::user();
    //     $validated = $request->validate([
    //         'password' => [
    //             'nullable',
    //             'string',
    //             'min:8',
    //             'max:20',
    //             'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9])\S+$/'
    //         ],
    //         'email'       => ['nullable', 'email', 'max:100', Rule::unique('employees_tables', 'email')->ignore($user->employee->id)],
    //         'telp_number' => ['nullable', 'max:20', Rule::unique('employees_tables', 'telp_number')->ignore($user->employee->id) ],
    //     ]);

    //     $changes = [];

    //     if (!empty($validated['password'])) {
    //         $user->password = Hash::make($validated['password']);
    //     }

    //     if (!empty($validated['email']) && $validated['email'] !== $user->employee->email) {
    //         $user->employee->pending_email = $validated['email'];
    //         $changes['email'] = $validated['email'];
    //     }

    //     if (!empty($validated['telp_number']) && $validated['telp_number'] !== $user->employee->telp_number) {
    //         $user->employee->pending_telp_number = $validated['telp_number']; // typo fixed (epending → pending)
    //         $changes['telp_number'] = $validated['telp_number'];
    //     }

    //     $user->save();
    //     $user->employee->save();

    //     if (!empty($changes)) {
    //         Mail::to('hrmahendradatta@gmail.com')
    //             ->send(new UserUpdateRequestedMail($user, $changes));
    //     }

    //     return back()->with('status', 'Perubahan sudah diajukan, menunggu persetujuan HR.');
    // }

















    // public function updatePassword(Request $request)
    // {
    //     $validated = $request->validate([
    //         'password' => [
    //             'nullable',
    //             'string',
    //             'min:8',
    //             'max:20',
    //             'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9])\S+$/'
    //         ],
    //         'email'       => ['nullable', 'email', 'max:100', 'unique:employees_tables,email'],
    //         'telp_number' => ['nullable', 'max:20'],
    //     ]);

    //     $user = Auth::user();

    //     // Update password kalau ada
    //     if (!empty($validated['password'])) {
    //         $user->password = Hash::make($validated['password']);
    //     }

    //     // Pending email update
    //     if (!empty($validated['email']) && $validated['email'] !== $user->employee->email) {
    //         $user->employee->pending_email = $validated['email'];

    //         Mail::to('hrmahendradatta@gmail.com')
    //             ->send(new UserUpdateRequestedMail($user, 'email', $validated['email']));
    //     }

    //     // Pending telp update
    //     if (!empty($validated['telp_number']) && $validated['telp_number'] !== $user->employee->telp_number) {
    //         $user->employee->epending_telp_number = $validated['telp_number'];

    //         Mail::to('hrmahendradatta@gmail.com')
    //             ->send(new UserUpdateRequestedMail($user, 'telp_number', $validated['telp_number']));
    //     }

    //     $user->save();

    //     return back()->with('status', 'Perubahan sudah diajukan, menunggu persetujuan HR.');
    // }
}




 // public function updatePassword(Request $request)
    // {
    //     $request->validate(
    //         [
    //             'password' => ['nullable', 'string', 'min:8', 'max:20', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9])\S+$/'],
    //             'email'    => 'nullable','email','max:100','unique:users,email',
    //         ],
    //         [
    //             'password.regex' => 'Password must contain at least 1 uppercase letter, 1 lowercase letter, 1 number, and 1 symbol, and must not contain spaces.',
    //             'password.min' => 'Password must be at least 8 characters.',
    //             'password.max' => 'Password maximum 20 characters.',
    //         ]
    //     );

    //     $user = Auth::user();
    //     if ($request->filled('password')) {
    //         $user->password = Hash::make($request->password);
    //     }
        

    //     // Update name jika diisi (harus ada karena required)

    //     if ($user->save()) {
    //         Log::info("Data berhasil diperbarui untuk user ID {$user->id}");
    //         return back()->with('status', 'Success');
    //     } else {
    //         Log::error("Gagal menyimpan data untuk user ID {$user->id}");
    //         return back()->withErrors(['update' => 'Gagal menyimpan perubahan']);
    //     }
    // }
//     public function updatePassword(Request $request)
// {
//     $validated = $request->validate([
//         'password' => [
//             'nullable', 'string', 'min:8', 'max:20',
//             'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9])\S+$/'
//         ],
//         'email' => ['nullable','email','max:100','unique:employees_tables,email'],
//         'telp_number' => ['nullable','max:20'],
//     ]);

//     $user = Auth::user();

//     // Update password kalau ada
//     if (!empty($validated['password'])) {
//         $user->password = Hash::make($validated['password']);
//     }

//     // Pending email update
//     if (!empty($validated['email']) && $validated['email'] !== $user->email) {
//         $user->pending_email = $validated['email'];

//         // Notifikasi ke HR
//         Notification::route('mail', 'hrmahendradatta@gmail.com')
//             ->notify(new UserUpdateRequested($user, 'email'));
//     }
//     // Pending telp update
//     if (!empty($validated['telp_number']) && $validated['telp_number'] !== $user->telp_number) {
//         $user->pending_telp_number = $validated['telp_number'];

//         // Notifikasi ke HR
//         Notification::route('mail', 'hr@example.com')
//             ->notify(new UserUpdateRequested($user, 'telp_number'));
//     }

//     $user->save();

//     return back()->with('status', 'Perubahan sudah diajukan, menunggu persetujuan HR.');
// }