<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            [
                'email' => 'admin@gmail.com'
            ],
            [
                'name'     => 'Admin',
                'mobile'   => '7768985529',
                'role'     => 'admin',
                'status'   => 1,
                'password' => Hash::make('password'),
            ]
        );

        $admin->assignRole('admin');
    }
}