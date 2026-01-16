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

        $steam = app(SteamService::class);

        foreach (array_keys(config('featured_games')) as $appid) {
            $steam->buildRequirementForApp((int) $appid);
        }
    }
}