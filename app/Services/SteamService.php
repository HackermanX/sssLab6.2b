<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SteamService
{
    public function getAppIdByName(string $gameName): ?int
    {
        return Cache::remember("steam_appid_" . md5(strtolower($gameName)), now()->addHours(24), function () use ($gameName) {
            $response = Http::get('https://store.steampowered.com/api/storesearch/', [
                'term' => $gameName,
                'l'    => 'english',
                'cc'   => 'US',
            ]);

            if ($response->failed()) {
                Log::warning('Steam storesearch failed', ['game' => $gameName]);
                return null;
            }

            $items = $response->json('items', []);

            return $items[0]['id'] ?? null;
        });
    }

    public function getGameSpecs(int $appId): ?array
    {
        return Cache::remember("steam_specs_{$appId}", now()->addDays(7), function () use ($appId) {
            $response = Http::get('https://store.steampowered.com/api/appdetails', [
                'appids' => $appId,
                'cc'     => 'US',
                'l'      => 'english',
            ]);

            if ($response->failed()) {
                return null;
            }

            $data = $response->json("{$appId}");
            if (empty($data['success'])) {
                return null;
            }

            $game = $data['data'];

            return [
                'name'                  => $game['name'] ?? 'Unknown',
                'appid'                 => $appId,
                'steam_url'             => "https://store.steampowered.com/app/{$appId}/",
                'header_image'          => $game['header_image'] ?? null,
                'short_description'     => $game['short_description'] ?? null,
                'release_date'          => $game['release_date']['date'] ?? null,
                'platforms'             => array_keys(array_filter($game['platforms'] ?? [])),
                'genres'                => collect($game['genres'] ?? [])->pluck('description')->toArray(),
                'developers'            => $game['developers'] ?? [],
                'publishers'            => $game['publishers'] ?? [],
                'price'                 => $game['price_overview']['final_formatted'] ?? 'Free or N/A',

                'minimum_requirements'  => $this->cleanRequirements($game['pc_requirements']['minimum'] ?? ''),
                'recommended_requirements' => $this->cleanRequirements($game['pc_requirements']['recommended'] ?? ''),

                'metacritic'            => $game['metacritic']['score'] ?? null,
            ];
        });
    }

    public function getSpecsByName(string $gameName): ?array
    {
        $appId = $this->getAppIdByName($gameName);
        return $appId ? $this->getGameSpecs($appId) : null;
    }

    private function cleanRequirements(string $html): string
    {
        if (empty($html)) return 'Not specified';

        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text);
        $text = preg_replace('/(Minimum|Recommended):/', "\n$1:", $text);

        return trim($text);
    }
}