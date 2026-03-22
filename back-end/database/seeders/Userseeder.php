<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ── Create the main test user ─────────────────────
        // Use this to login in Postman and Angular:
        //   Email:    john@quvio.com
        //   Password: password123
        User::firstOrCreate(
            ['email' => 'john@quvio.com'],
            [
                'name'     => 'John Doe',
                'password' => Hash::make('password123'),
            ]
        );

        $this->command->info('✅ User created: john@quvio.com / password123');
    }
}