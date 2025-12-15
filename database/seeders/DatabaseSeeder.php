<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create test user
        User::create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Create sample employees
        Employee::create([
            'firstName' => 'Juan',
            'middleName' => 'Santos',
            'lastName' => 'Dela Cruz',
            'email' => 'juan.delacruz@example.com',
            'contact' => '+639171234567',
            'position' => 'Software Engineer',
            'department' => 'IT',
            'gender' => 'Male',
            'status' => 'Active',
            'address' => 'Manila, Philippines',
            'birthdate' => '1990-05-15',
            'salary' => 50000,
            'employeeType' => 'Full-time',
        ]);

        Employee::create([
            'firstName' => 'Maria',
            'middleName' => 'Reyes',
            'lastName' => 'Garcia',
            'email' => 'maria.garcia@example.com',
            'contact' => '+639181234567',
            'position' => 'HR Manager',
            'department' => 'Human Resources',
            'gender' => 'Female',
            'status' => 'Active',
            'address' => 'Quezon City, Philippines',
            'birthdate' => '1988-08-20',
            'salary' => 60000,
            'employeeType' => 'Full-time',
        ]);
    }
}