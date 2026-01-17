<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->updateOrInsert(
            ['email' => 'admin@glssprachenzentrum.ma'],
            [
                'name' => 'GLS Admin',
                'password' => Hash::make('Admin@12345'),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
