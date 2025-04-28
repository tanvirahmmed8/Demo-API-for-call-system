<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample users
        $users = [
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => Hash::make('password'),
                'phone_number' => '1234567890',
                'balance' => 100.00,
                'payment_status' => 'paid',
                'active_status' => 'active',
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'password' => Hash::make('password'),
                'phone_number' => '9876543210',
                'balance' => 50.00,
                'payment_status' => 'pending',
                'active_status' => 'active',
            ],
            [
                'name' => 'Bob Johnson',
                'email' => 'bob@example.com',
                'password' => Hash::make('password'),
                'phone_number' => '5551234567',
                'balance' => 0.00,
                'payment_status' => 'unpaid',
                'active_status' => 'inactive',
            ],
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }
    }
}
