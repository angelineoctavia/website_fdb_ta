<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $pw = env('ADMIN_PASSWORD');
        if (!$pw) {
            $this->command->error('Isi ADMIN_PASSWORD di file .env dulu.');
            return;
        }

        DB::table('users')->updateOrInsert(
            ['USERS_ID' => 'ADM01'],
            [
                'USERS_NAME'       => 'admin',
                'USERS_PASSWORD'   => Hash::make($pw),
                'USERS_ROLE'       => 'A',   // sama dengan ADMIN_ROLE di AdminController
                'USERS_STATUS_DEL' => 0,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]
        );
    }
}