<?php

namespace Database\Seeders;

use App\Models\Sector;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SectorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $s1 = new Sector();
        $s1->name = 'Sector 01';
        $s1->area = '100';
        $s1->district_id = 1;
        $s1->save();

        $s2 = new Sector();
        $s2->name = 'Sector 02';
        $s2->area = '100';
        $s2->district_id = 1;
        $s2->save();

        $s3 = new Sector();
        $s3->name = 'Sector 03';
        $s3->area = '100';
        $s3->district_id = 1;
        $s3->save();

    }
}
