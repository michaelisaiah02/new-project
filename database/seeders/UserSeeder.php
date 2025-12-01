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
        // Data login password akan sama untuk semua user agar mudah diingat
        $password = '00000';

        User::factory()->create([
            'name' => 'Michael',
            'employeeID' => '12025',
            'password' => Hash::make($password),
            'department' => 1,
            'approved' => true,
        ]);
        User::factory()->create([
            'name' => 'Freddy',
            'employeeID' => '12345',
            'password' => Hash::make($password),
            'department' => 2,
            'checked' => true,
        ]);
    }
}
