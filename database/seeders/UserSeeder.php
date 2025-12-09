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
            'id' => '12025',
            'password' => Hash::make($password),
            'department_id' => 1,
            'whatsapp' => '6281234567890',
            'email' => fake()->unique()->safeEmail(),
            'approved' => true,
        ]);
        User::factory()->create([
            'name' => 'Freddy',
            'id' => '10009',
            'password' => Hash::make($password),
            'department_id' => 2,
            'whatsapp' => '6281234567891',
            'email' => fake()->unique()->safeEmail(),
            'checked' => true,
        ]);
    }
}
