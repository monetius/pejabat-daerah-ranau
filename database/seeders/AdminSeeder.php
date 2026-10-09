<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Test account only. Change this password after your first login.
        User::updateOrCreate(
            ['email' => 'admin@pdranau.test'],
            ['name' => 'Admin PD Ranau', 'password' => 'Admin12345!', 'is_admin' => true]
        );
    }
}
