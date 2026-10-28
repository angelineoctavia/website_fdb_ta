<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    // Tampilkan halaman Register
    public function showRegister()
    {
        return view('register');
    }

    // Proses Register
    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:50',
            'password' => 'required|string|min:6',
            'role'     => 'required|string|size:1',
            'phone'    => 'nullable|string|max:15',
        ]);

        // Auto-generate USERS_ID (Contoh: U0001, U0002, dst)
        $lastUser = DB::table('users')->orderBy('USERS_ID', 'desc')->first();
        if ($lastUser) {
            $numericId = (int) substr($lastUser->USERS_ID, 1) + 1;
            $userId = 'U' . str_pad($numericId, 4, '0', STR_PAD_LEFT);
        } else {
            $userId = 'U0001';
        }

        // Insert ke database sesuai struktur tabel
        DB::table('users')->insert([
            'USERS_ID'           => $userId,
            'USERS_NAME'         => $request->username,
            'USERS_PASSWORD'     => Hash::make($request->password), // Enkripsi password aman
            'USERS_ROLE'         => strtoupper($request->role),     // Contoh: 'C' (Customer), 'S' (Seller), 'A' (Admin)
            'USERS_PHONE_NUMBER' => $request->phone,
            'USERS_STATUS_DEL'   => 0,
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        return redirect()->route('login')->with('success', 'Registrasi berhasil! Silakan login.');
    }

    // Tampilkan halaman Login
    public function showLogin()
    {
        return view('login');
    }

    // Proses Login
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Cari user berdasarkan USERS_NAME
        $user = DB::table('users')->where('USERS_NAME', $request->username)->first();

        // Verifikasi password
        if ($user && Hash::check($request->password, $user->USERS_PASSWORD)) {
            // Simpan session login
            Session::put('user_id', $user->USERS_ID);
            Session::put('user_name', $user->USERS_NAME);
            Session::put('user_role', $user->USERS_ROLE);

            // Redirect langsung ke Home Customer
            return redirect()->route('home');
        }

        return back()->withErrors(['error' => 'Username atau Password salah!']);
    }

    // Logout
    public function logout()
    {
        Session::flush();
        return redirect()->route('login');
    }
}