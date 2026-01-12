<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\CPUbench;

class CpuBenchmarkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cpus = [
            ['name' => 'Intel Core i3-8100',     'score' => 6500],
            ['name' => 'AMD Ryzen 5 1400',       'score' => 7800],
            ['name' => 'Intel Core i5-6600',     'score' => 8200],
            ['name' => 'Intel Core i5-8400',     'score' => 9500], 
            ['name' => 'Intel Core i5-10400',    'score' => 12200], 
            ['name' => 'AMD Ryzen 5 3600',       'score' => 13500],
            ['name' => 'Intel Core i5-12400F',   'score' => 18000], 
            ['name' => 'AMD Ryzen 5 5600X',      'score' => 18500],
            ['name' => 'Intel Core i7-8700K',    'score' => 19000],
        ];

        foreach ($cpus as $cpu) {
            CPUbench::firstOrCreate(['name' => $cpu['name']], $cpu);
        }
    }
}
