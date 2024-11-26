<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $d1 = new Department();
        $d1->name = 'LAMBAYEQUE';
        $d1->code = '10001';
        $d1->save();
    }
}
