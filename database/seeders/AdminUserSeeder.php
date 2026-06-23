<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@club.local',
            'password' => bcrypt('password'),
            'status' => 'active',
            'approved_at' => now(),
        ]);

        $admin->assignRole('admin');
    }
}
