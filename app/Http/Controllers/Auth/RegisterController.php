<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'first_name'  => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name'   => 'required|string|max:255',
            'email'       => 'required|string|email|max:255|unique:users',
            'student_id'  => 'nullable|string|max:255',
            'department'  => 'required|string',
            'year_level'  => 'required|string',
            'password'    => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name'            => $validated['first_name'],
            'middle_name'     => $validated['middle_name'] ?? null,
            'last_name'       => $validated['last_name'],
            'email'           => $validated['email'],
            'student_id'      => $validated['student_id'] ?? null,
            'department'      => $validated['department'],
            'year_level'      => $validated['year_level'],
            'password'        => Hash::make($validated['password']),
            'role'            => 'student',
            'approval_status' => 'pending',
        ]);

        event(new Registered($user));

        return redirect()->route('login.student')
            ->with('registration_pending', 'Your account request has been submitted! Please wait for your Department Head to approve your account.');
    }
}
