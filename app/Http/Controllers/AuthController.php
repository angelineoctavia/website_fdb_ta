<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Support\Phone;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    /** Sequential ID: U0001, U0002, ... (must be called inside DB::transaction). */
    private function nextUserId(): string
    {
        $last = DB::table('users')->where('USERS_ID', 'like', 'U%')->lockForUpdate()->max('USERS_ID');
        return 'U' . str_pad($last ? ((int) substr($last, 1)) + 1 : 1, 4, '0', STR_PAD_LEFT);
    }

    public function register(Request $request)
    {
        // Validate input including strict Indonesian phone number format starting with 62
        // Role is restricted to C / S, so admin accounts cannot be created through this form.
        $request->validate([
            'username' => 'required|string|max:255|unique:users,USERS_NAME',
            'password' => 'required|min:6',
            'role' => 'required|in:C,S',
            'phone_number' => ['required', function ($attribute, $value, $fail) {
                if (!Phone::normalize($value)) {
                    $fail('Invalid phone number. Please enter a valid Indonesian mobile number starting with 62.');
                }
            }],
        ], [
            'username.required' => 'The username field is required.',
            'username.unique'   => 'This username has already been taken.',
            'password.required' => 'The password field is required.',
            'password.min'      => 'The password must be at least 6 characters.',
            'role.required'     => 'The role field is required.',
            'role.in'           => 'The selected role is invalid.',
            'phone_number.required' => 'The phone number field is required.',
        ]);

        // Save to database according to the user table structure
        DB::transaction(function () use ($request) {
            DB::table('users')->insert([
                'USERS_ID' => $this->nextUserId(),
                'USERS_NAME' => $request->username,
                'USERS_PASSWORD' => Hash::make($request->password),
                'USERS_ROLE' => $request->role,
                'USERS_PHONE_NUMBER' => Phone::normalize($request->phone_number),   // Saved in 628xxx format
                'USERS_STATUS_DEL' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return redirect()->route('login')->with('success', 'Registration successful! Please login.');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        // Find active user in database by username (inactive accounts cannot login)
        $user = DB::table('users')
            ->where('USERS_NAME', $request->username)
            ->where('USERS_STATUS_DEL', 0)
            ->first();

        if ($user && Hash::check($request->password, $user->USERS_PASSWORD)) {

            // Regenerate session ID after login to prevent session fixation
            $request->session()->regenerate();

            // Store user session data
            session([
                'user_id' => $user->USERS_ID,
                'user_name' => $user->USERS_NAME,
                'user_role' => $user->USERS_ROLE
            ]);

            // SMART ROUTING BASED ON ROLE
            if ($user->USERS_ROLE === 'S') {
                return redirect()->route('seller.dashboard'); // Redirect to Seller Center
            } elseif ($user->USERS_ROLE === 'A') {
                return redirect()->route('admin.dashboard'); // Redirect to Admin Center
            } else {
                return redirect()->route('home'); // Redirect to Customer Catalog
            }
        }

        return back()->withErrors(['error' => 'Username or password is wrong!'])->withInput();
    }

    public function logout(Request $request)
    {
        $request->session()->invalidate();       // Clear all session data + regenerate ID
        $request->session()->regenerateToken();  // Regenerate CSRF token
        return redirect()->route('login');
    }
}