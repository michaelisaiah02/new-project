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
            'Management',
            'Engineering 2 - Roda 4',
            'Engineering 3 - Roda 2',
            'Engineering 9 - Roda 2 & Heavy Duty',
        ];
        foreach ($departmentNames as $name) {
            Department::updateOrCreate(['name' => $name]);
        }
    }
}
