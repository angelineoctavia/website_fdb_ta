<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        // Validasi input termasuk format nomor telepon Indonesia (08 atau +62)
        $request->validate([
            'username' => 'required|string|max:255|unique:users,USERS_NAME',
            'password' => 'required|min:6',
            'role' => 'required|in:C,S',
            'phone_number' => ['required', 'regex:/^(\+62|08)[0-9]{8,11}$/'],
        ], [
            'phone_number.regex' => 'Format nomor telepon harus valid berawalan 08 atau +62 (10-13 digit).'
        ]);

        // Simpan ke database sesuai struktur tabel user
        DB::table('users')->insert([
            'USERS_ID' => 'U' . rand(1000, 9999),
            'USERS_NAME' => $request->username,
            'USERS_PASSWORD' => Hash::make($request->password),
            'USERS_ROLE' => $request->role,
            'USERS_PHONE_NUMBER' => $request->phone_number,
            'USERS_STATUS_DEL' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('login')->with('success', 'Registrasi berhasil! Silakan login.');
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

        // Cari user di database berdasarkan username
        $user = DB::table('users')->where('USERS_NAME', $request->username)->first();

        if ($user && Hash::check($request->password, $user->USERS_PASSWORD)) {
            
            // Simpan session user
            session([
                'user_id' => $user->USERS_ID,
                'user_name' => $user->USERS_NAME,
                'user_role' => $user->USERS_ROLE
            ]);

            // SMART ROUTING BERDASARKAN ROLE
            if ($user->USERS_ROLE === 'S') {
                return redirect()->route('seller.dashboard'); // Masuk ke Seller Center
            } elseif ($user->USERS_ROLE === 'A') {
                return redirect()->route('admin.dashboard'); // Masuk ke Admin Panel (kalau ada)
            } else {
                return redirect()->route('home'); // Masuk ke Katalog Customer
            }
        }

        return back()->withErrors(['error' => 'Username atau password salah!'])->withInput();
    }

    public function logout(Request $request)
    {
        $request->session()->flush();
        return redirect()->route('login');
    }
}