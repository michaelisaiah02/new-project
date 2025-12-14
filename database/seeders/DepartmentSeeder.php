<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departmentNames = [
            'Management',
            'Marketing',
            'Engineering 2',
            'Engineering 3',
            'Engineering 9',
        ];
        foreach ($departmentNames as $name) {
            Department::updateOrCreate(['name' => $name]);
        }
    }
}
