<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class TestUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'stevenhydrotech@gmail.com'],
            [
                'firstname' => 'Admin',
                'lastname'  => 'Ako',
                'username'  => 'admin',
                'password'  => Hash::make('password'),
                'role'      => 'admin', // Matches: 'in:admin,manager,staff,warehouse_staff'
                'is_active' => true,    // Good practice so the account isn't blocked on login
                'theme'     => 'light',
            ]
        );
    }
}