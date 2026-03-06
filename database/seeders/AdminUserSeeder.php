<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admins = [
            'rochdi.karouali@glszentrum.com' => 'Rochdi Karouali',
            'amine.rafik@glszentrum.com' => 'Amine Rafik',
            'rafik@glszentrum.com' => 'Rafik',
            'abderrahimelmoulabbi@glszentrum.com' => 'Abderrahim Elmoulabbi',
            'achraf.elyounani@glszentrum.com' => 'Achraf Elyounani',
            'ichrak.fakroune@glszentrum.com' => 'Ichrak Fakroune',
        ];

        foreach ($admins as $email => $name) {
            DB::table('users')->updateOrInsert(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('Admin@12345'),
                    'email_verified_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
