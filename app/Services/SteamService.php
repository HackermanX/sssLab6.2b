<?php

namespace App\Services;

use App\Models\GameRequirement;
use App\Models\CPUbench;
use App\Models\GPUbench;
use Illuminate\Support\Facades\Http;

class SteamService
{
    public function getRequirements(int $appId): ?array
    {
        return $this->fetchParseAndSave($appId);
    }

    private function fetchParseAndSave(int $appId): ?array
    {
        $response = Http::get('https://store.steampowered.com/api/appdetails', [
            'appids' => $appId,
            'cc'     => 'US',
            'l'      => 'english',
        ]);

        if ($response->failed() || ! $response->json("{$appId}.success")) {
            return null;
        }

        $pc = $response->json("{$appId}.data.pc_requirements");

        $minHtml = $pc['minimum']     ?? ($pc[0]['minimum']     ?? null);
        $recHtml = $pc['recommended'] ?? ($pc[0]['recommended'] ?? null);

        $minParsed = $this->parse($minHtml);
        $recParsed = $this->parse($recHtml);

        return [
            'minimum'     => $minParsed,
            'recommended' => $recParsed,
        ];
    }

    public function scoresFromParsed(?array $parsed): array
    {
        if (! $parsed) {
            return [null, null, null];
        }

        $cpuText = strtolower($parsed['processor'] ?? '');
        $gpuText = strtolower($parsed['graphics']  ?? '');
        $ramText = $parsed['memory']              ?? '';

        $ramGb    = $this->ramFromText($ramText);
        $cpuScore = $this->bestCpuScoreFromText($cpuText);
        $gpuScore = $this->bestGpuScoreFromText($gpuText);

        return [$cpuScore, $gpuScore, $ramGb];
    }

    public function storageFromParsed(?array $parsed): ?int
    {
        if (! $parsed) {
            return null;
        }

        $text = $parsed['storage'] ?? '';
        if ($text === '') {
            return null;
        }

        if (preg_match('/(\d+)\s*tb/i', $text, $m)) {
            return (int) $m[1] * 1024;
        }

        if (preg_match('/(\d+)\s*gb/i', $text, $m)) {
            return (int) $m[1];
        }

        return null;
    }

    public function buildRequirementForApp(int $appId): ?GameRequirement
    {
        $req = $this->getRequirements($appId);
        if (! $req) {
            return null;
        }

        [$minCpu, $minGpu, $minRam] = $this->scoresFromParsed($req['minimum']);
        [$recCpu, $recGpu, $recRam] = $this->scoresFromParsed($req['recommended']);

        $minStorage = $this->storageFromParsed($req['minimum']     ?? null);
        $recStorage = $this->storageFromParsed($req['recommended'] ?? null);

        $cfg = config('featured_games')[$appId] ?? [];

        return GameRequirement::updateOrCreate(
            ['appid' => $appId],
            [
                'name'               => $cfg['title'] ?? 'Unknown',
                'year'               => $cfg['year']  ?? null,
              //  'image'              => $cfg['image'] ?? null,

                'minimum_parsed'     => $req['minimum'],
                'recommended_parsed' => $req['recommended'],

                'min_cpu_score'      => $minCpu,
                'min_gpu_score'      => $minGpu,
                'min_ram_gb'         => $minRam,
                'min_storage_gb'     => $minStorage,

                'rec_cpu_score'      => $recCpu,
                'rec_gpu_score'      => $recGpu,
                'rec_ram_gb'         => $recRam,
                'rec_storage_gb'     => $recStorage,
            ]
        );
    }

    private function bestCpuScoreFromText(string $text): ?int
    {
        if ($text === '') {
            return null;
        }

        $parts = preg_split('/\s+or\s+|\/|,/i', $text);

        $scores = [];
        foreach ($parts as $part) {
            $clean = strtolower($part);
            $clean = str_replace(['amd', 'intel', 'processor', 'cpu', '®', '™'], '', $clean);
            $clean = preg_replace('/\s+/', ' ', $clean);
            $clean = trim($clean);

            if ($clean === '') {
                continue;
            }

            $cpu = CPUbench::whereRaw('LOWER(name) LIKE ?', ['%' . $clean . '%'])->first();

            if (! $cpu && preg_match('/(i[3579]-\d{3,4}|ryzen\s*\d\s*\d{3,4})/i', $clean, $m)) {
                $short = strtolower($m[1]);
                $cpu   = CPUbench::whereRaw('LOWER(name) LIKE ?', ['%' . $short . '%'])->first();
            }

            if ($cpu && $cpu->score) {
                $scores[] = $cpu->score;
            }
        }

        return empty($scores) ? null : min($scores);
    }

    private function bestGpuScoreFromText(string $text): ?int
    {
        if ($text === '') {
            return null;
        }

        $parts = preg_split('/,|\s+or\s+/i', $text);

        $scores = [];
        foreach ($parts as $part) {
            $clean = strtolower($part);
            $clean = str_replace(
                ['amd', 'nvidia', 'geforce', 'radeon', 'graphics', 'video card', 'video', '®', '™'],
                '',
                $clean
            );
            $clean = preg_replace('/\s+/', ' ', $clean);
            $clean = trim($clean);

            $clean = preg_replace('/\s*\d+\s*gb\b/i', '', $clean);
            $clean = trim($clean);

            if ($clean === '') {
                continue;
            }

            $gpu = GPUbench::whereRaw('LOWER(name) LIKE ?', ['%' . $clean . '%'])->first();

            if (! $gpu && preg_match('/(gtx\s*\d{3,4}|rtx\s*\d{3,4}|rx\s*\d{3,4})/i', $clean, $m)) {
                $short = strtolower($m[1]);
                $gpu   = GPUbench::whereRaw('LOWER(name) LIKE ?', ['%' . $short . '%'])->first();
            }

            if (! $gpu && preg_match('/(\d{3,4})/', $clean, $m)) {
                $num = $m[1];
                $gpu = GPUbench::whereRaw('LOWER(name) LIKE ?', ['%' . $num . '%'])->first();
            }

            if ($gpu && $gpu->score) {
                $scores[] = $gpu->score;
            }
        }

        return empty($scores) ? null : min($scores);
    }

    private function ramFromText(string $text): ?int
    {
        if ($text === '') {
            return null;
        }

        if (preg_match('/(\d+)\s*gb/i', $text, $m)) {
            return (int) $m[1];
        }

        return null;
    }

    private function parse(?string $html): ?array
    {
        if (! $html) {
            return null;
        }

        $lines  = $this->normalizeHtmlToLines($html);
        $parsed = $this->parseFromLines($lines);

        if ($this->hasAtLeastOneValue($parsed)) {
            $parsed['other'] = $this->extractOther($lines, $parsed);
            return $parsed;
        }

        return null;
    }

    private function normalizeHtmlToLines(string $html): array
    {
        $html = str_ireplace(
            ['<br>', '<br/>', '<br />', '</li>', '</p>', '</div>'],
            '__NEWLINE__',
            $html
        );

        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/^\s*(?:Minimum|Recommended)\s*:\s*/i', '', $text);
        $text = preg_replace('/\s+/u', ' ', $text);

        $lines = array_map('trim', explode('__NEWLINE__', $text));

        return array_values(array_filter($lines, fn ($line) => $line !== ''));
    }

    private function parseFromLines(array $lines): array
    {
        $map = [
            'os'        => ['os', 'operating system'],
            'processor' => ['processor', 'cpu'],
            'memory'    => ['memory', 'ram'],
            'graphics'  => ['graphics', 'gpu', 'video card', 'graphics card'],
            'directx'   => ['directx'],
            'storage'   => ['storage', 'hdd', 'hard drive', 'available space', 'disk space'],
            'network'   => ['network', 'internet'],
        ];

        $out = [
            'os'        => null,
            'processor' => null,
            'memory'    => null,
            'graphics'  => null,
            'directx'   => null,
            'storage'   => null,
            'network'   => null,
        ];

        $unmatchedLines = [];

        foreach ($lines as $line) {
            $parts = preg_split('/:/', $line, 2);
            if (count($parts) === 2) {
                $keyPart   = strtolower(trim($parts[0]));
                $valuePart = trim($parts[1]);
                $found     = false;

                foreach ($map as $field => $aliases) {
                    if (in_array($keyPart, $aliases, true)) {
                        if (! $out[$field]) {
                            $out[$field] = $valuePart;
                        }
                        $found = true;
                        break;
                    }
                }

                if (! $found) {
                    $unmatchedLines[] = $line;
                }
            } else {
                $unmatchedLines[] = $line;
            }
        }

        foreach ($unmatchedLines as $line) {
            if (stripos($line, 'requires a 64-bit processor and operating system') !== false) {
                continue;
            }
            foreach ($map as $field => $aliases) {
                foreach ($aliases as $alias) {
                    if (preg_match('/\b' . preg_quote($alias, '/') . '\b/i', $line)) {
                        if (! $out[$field]) {
                            $out[$field] = $line;
                            continue 3;
                        }
                    }
                }
            }
        }

        foreach ($out as $key => &$value) {
            if ($value) {
                $value = preg_replace('/^(?:' . implode('|', $map[$key]) . ')\s*:\s*/i', '', $value);
                $value = trim($value);
            }
        }

        return $out;
    }

    private function extractOther(array $lines, array $picked): ?string
    {
        $otherLines = [];

        foreach ($lines as $line) {
            $isPicked = false;

            foreach ($picked as $value) {
                if ($value && str_contains($value, $line)) {
                    $isPicked = true;
                    break;
                }
            }

            if ($isPicked) {
                continue;
            }

            $parts = preg_split('/:/', $line, 2);
            if (count($parts) === 2) {
                $keyPart = strtolower(trim($parts[0]));
                if (in_array($keyPart, ['os', 'processor', 'memory', 'graphics', 'directx', 'storage', 'network'], true)) {
                    continue;
                }
            }

            $otherLines[] = $line;
        }

        $otherText = trim(implode(' ', $otherLines));

        if ($otherText === '' || stripos($otherText, 'requires a 64-bit processor and operating system') !== false) {
            return null;
        }

        return $otherText ?: null;
    }

    private function hasAtLeastOneValue(array $arr): bool
    {
        foreach (['os', 'processor', 'memory', 'graphics', 'directx', 'storage'] as $k) {
            if (! empty($arr[$k])) {
                return true;
            }
        }
        return false;
    }
}