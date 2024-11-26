<?php

namespace Database\Seeders;

use App\Models\Province;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProvinceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $p1 = new Province();
        $p1->name = 'CHICLAYO';
        $p1->code = '14001';
        $p1->department_id = 1;
        $p1->save();
    }
}
