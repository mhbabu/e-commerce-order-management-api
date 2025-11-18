<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'  => 'Admin User',
                'email' => 'admin@example.com',
                'role'  => 'admin',
            ],
            [
                'name'  => 'TechMart Electronics',
                'email' => 'vendor1@example.com',
                'role'  => 'vendor',
            ],
            [
                'name'  => 'GadgetHub Store',
                'email' => 'vendor2@example.com',
                'role'  => 'vendor',
            ],
            [
                'name'  => 'SmartHome Solutions',
                'email' => 'vendor3@example.com',
                'role'  => 'vendor',
            ],
            [
                'name'  => 'Customer User',
                'email' => 'customer@example.com',
                'role'  => 'customer',
            ],
        ];

        foreach ($users as $user) {
            User::factory()->create(
                $user + ['email_verified_at' => now()]
            );
        }
    }
}
