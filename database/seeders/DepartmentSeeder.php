<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departmentNames = [
            'Marketing',
            'Engineering',
        ];
        foreach ($departmentNames as $name) {
            Department::updateOrCreate(['name' => $name]);
        }
    }
}
