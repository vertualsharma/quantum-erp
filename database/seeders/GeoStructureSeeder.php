<?php

namespace Database\Seeders;

use App\Models\State;
use App\Models\District;
use App\Models\Municipality;
use Illuminate\Database\Seeder;

class GeoStructureSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Province / State
        $koshi = State::create([
            'name' => 'Koshi Province',
            'code' => 'P1'
        ]);

        $bagmati = State::create([
            'name' => 'Bagmati Province',
            'code' => 'P3'
        ]);

        // 2. Seed Districts linked to State
        $jhapa = District::create([
            'name' => 'Jhapa',
            'code' => 'JHP',
            'state_id' => $koshi->id,
        ]);

        $kathmandu = District::create([
            'name' => 'Kathmandu',
            'code' => 'KTM',
            'state_id' => $bagmati->id,
        ]);

        // 3. Seed Municipalities linked to District
        $municipalities = [
            ['name' => 'Arjundhara Municipality', 'code' => 'ARJ', 'district_id' => $jhapa->id],
            ['name' => 'Birtamod Municipality', 'code' => 'BIR', 'district_id' => $jhapa->id],
            ['name' => 'Damak Municipality', 'code' => 'DMK', 'district_id' => $jhapa->id],
            ['name' => 'Mechinagar Municipality', 'code' => 'MEC', 'district_id' => $jhapa->id],
            ['name' => 'Kathmandu Metropolitan City', 'code' => 'KMC', 'district_id' => $kathmandu->id],
            ['name' => 'Lalitpur Metropolitan City', 'code' => 'LMC', 'district_id' => $kathmandu->id],
        ];

        foreach ($municipalities as $municipality) {
            Municipality::create($municipality);
        }
    }
}