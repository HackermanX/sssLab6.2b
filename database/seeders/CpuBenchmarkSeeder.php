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
            ['name' => 'Intel Core i3-8100',   'score' => 4000],
            ['name' => 'Intel Core i5-8400',   'score' => 6500],
            ['name' => 'Intel Core i5-10400',  'score' => 9000],
            ['name' => 'Intel Core i5-12400F', 'score' => 14000],
            ['name' => 'Intel Core i7-8700K',  'score' => 15000],
        ];

        foreach ($cpus as $cpu) {
            CPUbench::firstOrCreate(['name' => $cpu['name']], $cpu);
        }
    }
}
