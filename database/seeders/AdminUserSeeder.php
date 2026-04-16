<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
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
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('Admin@12345'),
                    'email_verified_at' => now(),
                ]
            );

            // Assign Super Admin role
            if (! $user->hasRole('Super Admin')) {
                $user->assignRole('Super Admin');
            }
        }
    }
}
