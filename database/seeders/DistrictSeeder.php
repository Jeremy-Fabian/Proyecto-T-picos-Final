<?php

namespace Database\Seeders;

use App\Models\District;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $d1 = new District();
        $d1->name = 'CHICLAYO';
        $d1->code = '14001';
        $d1->province_id = 1;
        $d1->department_id = 1;
        $d1->save();
    }
}
