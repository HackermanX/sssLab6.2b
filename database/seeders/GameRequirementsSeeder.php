<?php

namespace Database\Seeders;

use App\Models\GameRequirement;
use App\Services\SteamService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GameRequirementsSeeder extends Seeder
{
     public function run(): void
    {
        GameRequirement::truncate();

        $games = config('featured_games');
        $steam = app(SteamService::class);

        foreach ($games as $appid => $meta) {
            $req = $steam->getRequirements($appid);

            if (! $req || empty($req['minimum'])) {
                // Skip if Steam doesn't give usable data
                continue;
            }

            [$cpuScore, $gpuScore, $ramGb] = $steam->scoresFromParsed($req['minimum']);

            GameRequirement::create([
                'appid'               => $appid,
                'name'                => $meta['title'],
                'slug'                => Str::slug($meta['title']),
                'year'                => $meta['year'],
                'minimum_parsed'      => $req['minimum'],
                'recommended_parsed'  => $req['recommended'],
                'min_cpu_score'       => $cpuScore,
                'min_gpu_score'       => $gpuScore,
                'min_ram_gb'          => $ramGb,
            ]);
        }
    }
}