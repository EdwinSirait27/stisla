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
    public function updateemailtelp(Request $request)
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
        ]);
        $changes = [];
        if (!empty($validated['email']) && $validated['email'] !== $user->employee->email) {
            $user->employee->pending_email = $validated['email'];
            $changes['email'] = $validated['email'];
        }
        if (!empty($validated['telp_number']) && $validated['telp_number'] !== $user->employee->telp_number) {
            $user->employee->pending_telp_number = $validated['telp_number'];
            $changes['telp_number'] = $validated['telp_number'];
        }
        $user->save();
        $user->employee->save();
        if (!empty($changes)) {
            Mail::to('hrmahendradatta@gmail.com')
                ->send(new UserUpdateRequestedMail($user, $changes));

            return back()->with('status', 'Changes have been submitted, awaiting HR approval.');
        }
            return back()->with('status', 'No changes were made.');
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