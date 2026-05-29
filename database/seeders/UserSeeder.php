<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Admin User
        User::create([
            'name' => 'System Admin',
            'email' => 'admin@deor.edu',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        // Student Users
        User::create([
            'name' => 'John Doe',
            'email' => 'john.doe@deor.edu',
            'password' => Hash::make('password123'),
            'role' => 'student',
        ]);

        User::create([
            'name' => 'Jane Smith',
            'email' => 'jane.smith@deor.edu',
            'password' => Hash::make('password123'),
            'role' => 'student',
        ]);

        User::create([
            'name' => 'Mike Johnson',
            'email' => 'mike.johnson@deor.edu',
            'password' => Hash::make('password123'),
            'role' => 'student',
        ]);

        // Clearance Checker Users
        User::create([
            'name' => 'Ms. Sarah Williams',
            'email' => 'sarah.williams@deor.edu',
            'password' => Hash::make('password123'),
            'role' => 'clearance_checker',
        ]);

        User::create([
            'name' => 'Mr. Robert Brown',
            'email' => 'robert.brown@deor.edu',
            'password' => Hash::make('password123'),
            'role' => 'clearance_checker',
        ]);

        User::create([
            'name' => 'Mrs. Emily Davis',
            'email' => 'emily.davis@deor.edu',
            'password' => Hash::make('password123'),
            'role' => 'clearance_checker',
        ]);
    }
}
