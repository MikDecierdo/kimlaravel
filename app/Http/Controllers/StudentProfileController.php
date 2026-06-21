<?php

namespace App\Http\Controllers;

use App\Mail\PasswordChangeCode;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class StudentProfileController extends Controller
{
    public function edit()
    {
        $student = Auth::guard('student')->user();
        $pending = session('password_change_pending');
        return view('student.profile', compact('student', 'pending'));
    }

    public function update(Request $request)
    {
        $student = Auth::guard('student')->user();

        $validated = $request->validate([
            'first_name'      => 'required|string|max:255',
            'middle_name'     => 'nullable|string|max:255',
            'last_name'       => 'required|string|max:255',
            'email'           => 'required|email|unique:users,email,' . $student->id,
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'current_password'=> 'nullable|string',
            'password'        => 'nullable|string|min:8|confirmed',
        ]);

        $passwordChanged = false;

        if (!empty($validated['password'])) {
            if (empty($request->current_password) || !Hash::check($request->current_password, $student->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.'])->withInput();
            }

            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            session([
                'password_change_pending' => [
                    'code'       => $code,
                    'expires_at' => now()->addMinutes(10)->timestamp,
                    'password'   => bcrypt($validated['password']),
                ],
            ]);

            Mail::to($student->email)->send(new PasswordChangeCode($code, $student->name));

            $passwordChanged = true;
        }

        unset($validated['password'], $validated['current_password'], $validated['password_confirmation']);

        if ($request->hasFile('profile_picture')) {
            if ($student->profile_picture) {
                $old = public_path($student->profile_picture);
                if (file_exists($old)) unlink($old);
            }
            $path = $request->file('profile_picture')->store('students', 'public');
            $validated['profile_picture'] = '/storage/' . $path;
        } else {
            unset($validated['profile_picture']);
        }

        $student->update([
            'name'            => $validated['first_name'],
            'middle_name'     => $validated['middle_name'] ?? null,
            'last_name'       => $validated['last_name'],
            'email'           => $validated['email'],
            'profile_picture' => $validated['profile_picture'] ?? $student->profile_picture,
        ]);

        if ($passwordChanged) {
            return back()->with('success', 'A verification code has been sent to your email. Please enter it below to complete the password change.');
        }

        return back()->with('success', 'Profile updated successfully!');
    }

    public function verifyPasswordChange(Request $request)
    {
        $request->validate([
            'verification_code' => 'required|string|size:6',
        ]);

        $pending = session('password_change_pending');

        if (!$pending || !isset($pending['code'], $pending['expires_at'], $pending['password'])) {
            return back()->withErrors(['verification_code' => 'No pending password change request. Please try again.']);
        }

        if (now()->timestamp > $pending['expires_at']) {
            session()->forget('password_change_pending');
            return back()->withErrors(['verification_code' => 'Verification code has expired. Please request a new password change.']);
        }

        if ($request->verification_code !== $pending['code']) {
            return back()->withErrors(['verification_code' => 'Invalid verification code.']);
        }

        $student = Auth::guard('student')->user();
        $student->update(['password' => $pending['password']]);

        if ($student->role === 'staff') {
            Staff::where('email', $student->email)->update(['password' => $pending['password']]);
        }

        session()->forget('password_change_pending');

        return back()->with('success', 'Password changed successfully!');
    }

    public function resendVerificationCode()
    {
        $pending = session('password_change_pending');

        if (!$pending || !isset($pending['code'])) {
            return back()->withErrors(['verification_code' => 'No pending password change request.']);
        }

        if (now()->timestamp > $pending['expires_at']) {
            session()->forget('password_change_pending');
            return back()->withErrors(['verification_code' => 'Verification code has expired. Please request a new password change.']);
        }

        $student = Auth::guard('student')->user();
        Mail::to($student->email)->send(new PasswordChangeCode($pending['code'], $student->name));

        return back()->with('success', 'A new verification code has been sent to your email.');
    }
}
