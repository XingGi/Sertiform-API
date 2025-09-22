<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cek apakah user admin sudah ada
        $admin = User::where('email', 'admin@admin.com')->first();

        // Jika belum ada, buat user baru
        if (!$admin) {
            User::create([
                'name' => 'Master Admin',
                'email' => 'admin@admin.com',
                'email_verified_at' => now(),
                'password' => Hash::make('12345678'), // Password di-hash demi keamanan
            ]);
        }
    }
}
