<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class StudentProfileController extends Controller
{
    public function edit()
    {
        $student = Auth::guard('student')->user();
        return view('student.profile', compact('student'));
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

        // Password change — verify current first
        if (!empty($validated['password'])) {
            if (empty($request->current_password) || !Hash::check($request->current_password, $student->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.'])->withInput();
            }
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }
        unset($validated['current_password']);

        // Profile picture upload
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

        // Map validated keys to model columns
        $student->update([
            'name'            => $validated['first_name'],
            'middle_name'     => $validated['middle_name'] ?? null,
            'last_name'       => $validated['last_name'],
            'email'           => $validated['email'],
            'profile_picture' => $validated['profile_picture'] ?? $student->profile_picture,
            ...( isset($validated['password']) ? ['password' => $validated['password']] : [] ),
        ]);

        return back()->with('success', 'Profile updated successfully!');
    }
}
