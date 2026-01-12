<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\GPUbench;

class GpuBenchmarkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $gpus = [
            ['name' => 'NVIDIA GTX 970',   'score' => 5000],
            ['name' => 'NVIDIA GTX 1060',  'score' => 7000],
            ['name' => 'NVIDIA GTX 1660',  'score' => 9000],
            ['name' => 'NVIDIA RTX 2060',  'score' => 12000],
            ['name' => 'NVIDIA RTX 3060',  'score' => 16000],
        ];

        foreach ($gpus as $gpu) {
            GPUbench::firstOrCreate(['name' => $gpu['name']], $gpu);
        }
    }
}
