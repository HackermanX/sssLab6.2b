<?php

namespace App\Services;

use App\Models\GameRequirement;
use Illuminate\Support\Facades\Http;

use App\Models\CPUbench;
use App\Models\GPUbench;

class SteamService
{
    // helpers
    private function scoresFromParsed(?array $minParsed): array
    {
        if (!$minParsed) {
            return [null, null, null];
        }

        $cpuText = $minParsed['processor'] ?? '';
        $gpuText = $minParsed['graphics']  ?? '';
        $ramText = $minParsed['memory']    ?? '';

        $cpuScore = $this->cpuScoreFromText($cpuText);
        $gpuScore = $this->gpuScoreFromText($gpuText);
        $ramGb    = $this->ramFromText($ramText);

        return [$cpuScore, $gpuScore, $ramGb];
    }

    private function cpuScoreFromText(string $text): ?int
    {
        if ($text === '') return null;

        $clean = strtolower($text);
        $clean = str_replace(['intel', 'amd', 'processor', 'cpu'], '', $clean);
        $clean = preg_replace('/\s+/', ' ', $clean);
        $clean = trim($clean);

        $cpu = Cpubench::whereRaw('LOWER(name) LIKE ?', ['%' . $clean . '%'])->first();
        return $cpu?->score;
    }

    private function gpuScoreFromText(string $text): ?int
    {
        if ($text === '') return null;

        $clean = strtolower($text);
        $clean = str_replace(['nvidia', 'geforce', 'amd', 'radeon', 'graphics', 'video card', 'video'], '', $clean);
        $clean = preg_replace('/\s+/', ' ', $clean);
        $clean = trim($clean);

        $gpu = GPUbench::whereRaw('LOWER(name) LIKE ?', ['%' . $clean . '%'])->first();
        return $gpu?->score;
    }

    private function ramFromText(string $text): ?int
    {
        if ($text === '') return null;

        if (preg_match('/(\d+)\s*gb/i', $text, $m)) {
            return (int) $m[1];
        }

        return null;
    }

    // main method
    public function getRequirements(int $appId): ?array
    {
        $saved = GameRequirement::where('appid', $appId)->first();
        if ($saved) {
            return [
                'minimum'     => $saved->minimum_parsed,
                'recommended' => $saved->recommended_parsed,
            ];
        }

        return $this->fetchParseAndSave($appId);
    }

    private function fetchParseAndSave(int $appId): ?array
    {
        $response = Http::get('https://store.steampowered.com/api/appdetails', [
            'appids' => $appId,
            'cc'     => 'US',
            'l'      => 'english',
        ]);

        if ($response->failed() || !$response->json("{$appId}.success")) {
            return null;
        }

        $pc = $response->json("{$appId}.data.pc_requirements");

        $minHtml = $pc['minimum'] ?? ($pc[0]['minimum'] ?? null);
        $recHtml = $pc['recommended'] ?? ($pc[0]['recommended'] ?? null);

        $minParsed = $this->parse($minHtml);
        $recParsed = $this->parse($recHtml);

        // basically look at what we've gotten and give it a cpu score
        [$cpuScore, $gpuScore, $ramGb] = $this->scoresFromParsed($minParsed);

        GameRequirement::updateOrCreate(
            ['appid' => $appId],
            [
                'minimum_parsed'     => $minParsed,
                'recommended_parsed' => $recParsed,
                'min_cpu_score'      => $cpuScore,
                'min_gpu_score'      => $gpuScore,
                'min_ram_gb'         => $ramGb,
            ]
        );

        return [
            'minimum'     => $minParsed,
            'recommended' => $recParsed,
        ];
    }

    private function parse(?string $html): ?array
    {
        if (!$html) return null;

        $lines = $this->normalizeHtmlToLines($html);

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
            "__NEWLINE__",
            $html
        );

        $text = strip_tags($html);

        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $text = preg_replace('/^\s*(?:Minimum|Recommended)\s*:\s*/i', '', $text);
        $text = preg_replace('/\s+/u', ' ', $text);

        $lines = array_map('trim', explode('__NEWLINE__', $text));
        return array_values(array_filter($lines, fn($line) => $line !== ''));
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
            'os' => null, 'processor' => null, 'memory' => null,
            'graphics' => null, 'directx' => null, 'storage' => null, 'network' => null,
        ];

        $unmatchedLines = [];

        foreach ($lines as $line) {
            $parts = preg_split('/:/', $line, 2);
            if (count($parts) === 2) {
                $keyPart = strtolower(trim($parts[0]));
                $valuePart = trim($parts[1]);
                $found = false;
                foreach ($map as $field => $aliases) {
                    if (in_array($keyPart, $aliases)) {
                        if (!$out[$field]) $out[$field] = $valuePart;
                        $found = true;
                        break;
                    }
                }
                if (!$found) $unmatchedLines[] = $line;
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
                        if (!$out[$field]) {
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

    private function normalizeHtmlToText(string $html): string
    {
        $html = str_ireplace(['<br>', '<br/>', '<br />', '</li>', '</p>'], "\n", $html);
        $html = preg_replace('/<li[^>]*>/i', '- ', $html);
        $html = preg_replace('/<p[^>]*>/i', '', $html);
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\r\n|\r/u', "\n", $text);
        $text = preg_replace('/[ \t]+/u', ' ', $text);
        $text = preg_replace('/\s*:\s*/u', ': ', $text);
        $text = preg_replace('/^\s*(Minimum|Recommended)\s*:\s*/i', '', $text);
        return trim($text);
    }

    private function parseLabeledBlock(string $text): array
    {
        $lines = array_values(array_filter(array_map('trim', preg_split('/\n+/u', $text))));
        $map = [
            'os'        => ['os', 'operating system'],
            'processor' => ['processor', 'cpu'],
            'memory'    => ['memory', 'ram'],
            'graphics'  => ['graphics', 'gpu', 'video card', 'graphics card'],
            'directx'   => ['directx'],
            'storage'   => ['storage', 'hdd', 'hard drive', 'disk space'],
            'network'   => ['network', 'internet'],
        ];

        $out = [
            'os' => null, 'processor' => null, 'memory' => null,
            'graphics' => null, 'directx' => null, 'storage' => null,
        ];

        foreach ($lines as $line) {
            if (preg_match('/^\s*([A-Za-z ]+)\s*:\s*(.+)$/u', $line, $m)) {
                $key = strtolower(trim($m[1]));
                $val = trim($m[2]);
                foreach ($map as $field => $aliases) {
                    foreach ($aliases as $alias) {
                        if (str_contains($key, $alias)) {
                            $out[$field] = $val;
                            continue 3;
                        }
                    }
                }
            } else {
                $this->assignByHints($out, $line);
            }
        }

        foreach ($out as $k => $v) {
            if ($v) {
                $v = $this->truncateAtKnownLabel($v);
                $out[$k] = trim($v);
            }
        }

        return $out;
    }

    private function parsePubgStyle(string $text): array
    {
        $out = [
            'os'        => $this->grab('/Windows\s*(?:7|8\.1|10|11)[^,\n]*/i', $text),
            'processor' => $this->grab('/(?:Intel|AMD)[^,\n]*?(?:i[3579]-\d{4}|Core\s*i[3579]-\d{4}|FX-\d{3,4}|Ryzen\s*\d[^,\n]*)/i', $text),
            'memory'    => $this->grab('/\b\d+\s*GB\s*(?:RAM|Memory)\b/i', $text),
            'graphics'  => $this->grab('/(?:NVIDIA|AMD)[^,\n]*(?:GTX|RTX|RX)\s*\d{3,4}[^,\n]*/i', $text),
            'directx'   => $this->grab('/DirectX\s*(?:Version\s*)?\d{1,2}/i', $text),
            'storage'   => $this->grab('/\b\d+\s*GB\s*(?:available\s*)?space\b/i', $text),
        ];

        if (!$out['os']) {
            $out['os'] = $this->grab('/\bWindows\s*(?:7|8\.1|10|11)[^,\n]*/i', $text);
        }

        return $out;
    }

    private function parseByKeywords(string $text): array
    {
        $out = [
            'os'        => $this->grab('/\b(Windows\s*(?:7|8\.1|10|11)[^,\n]*)/i', $text),
            'processor' => $this->grab('/\b(?:CPU|Processor)[^:]*:\s*([^\n]+)/i', $text) ?: $this->grab('/\b(?:Intel|AMD)[^\n]+/i', $text),
            'memory'    => $this->grab('/\b\d+\s*GB\s*(?:RAM|Memory)\b/i', $text),
            'graphics'  => $this->grab('/\b(?:Graphics|GPU|Video Card)[^:]*:\s*([^\n]+)/i', $text) ?: $this->grab('/\b(?:GTX|RTX|RX)\s*\d{3,4}[^\n]*/i', $text),
            'directx'   => $this->grab('/\bDirectX[^:]*:\s*([^\n]+)/i', $text) ?: $this->grab('/\bDirectX\s*(?:Version\s*)?\d{1,2}\b/i', $text),
            'storage'   => $this->grab('/\b(?:Storage|Disk|Space)[^:]*:\s*([^\n]+)/i', $text) ?: $this->grab('/\b\d+\s*GB\s*(?:available\s*)?space\b/i', $text),
        ];

        foreach ($out as $k => $v) {
            if ($v) $out[$k] = trim($this->truncateAtKnownLabel($v));
        }

        return $out;
    }

    private function assignByHints(array &$out, string $line): void
    {
        $l = strtolower($line);
        if (!$out['os'] && str_contains($l, 'windows')) $out['os'] = $line;
        if (!$out['memory'] && preg_match('/\b\d+\s*gb\s*(ram|memory)\b/i', $line)) $out['memory'] = $line;
        if (!$out['directx'] && str_contains($l, 'directx')) $out['directx'] = $line;
        if (!$out['storage'] && preg_match('/\b\d+\s*gb\b.*\b(space|storage|disk)\b/i', $line)) $out['storage'] = $line;
        if (!$out['graphics'] && (str_contains($l, 'gtx') || str_contains($l, 'rtx') || str_contains($l, 'rx') || str_contains($l, 'graphics') || str_contains($l, 'gpu'))) $out['graphics'] = $line;
        if (!$out['processor'] && (str_contains($l, 'intel') || str_contains($l, 'amd') || str_contains($l, 'processor') || str_contains($l, 'cpu'))) $out['processor'] = $line;
    }

    private function truncateAtKnownLabel(string $value): string
    {
        $labels = ['OS:', 'Processor:', 'CPU:', 'Memory:', 'Graphics:', 'GPU:', 'Video Card:', 'DirectX:', 'Storage:', 'Network:'];
        foreach ($labels as $label) {
            $pos = stripos($value, $label);
            if ($pos !== false && $pos > 0) {
                $value = substr($value, 0, $pos);
            }
        }
        return $value;
    }

    private function extractOther(array $lines, array $picked): ?string
    {
        $allPickedValues = array_filter(array_values($picked));
        $otherLines = [];

        $rawText = implode(' ', $lines);

        foreach ($lines as $line) {
            $isPicked = false;
            foreach ($picked as $key => $pickedValue) {
                if ($pickedValue && str_contains($pickedValue, $line)) {
                    $isPicked = true;
                    break;
                }
            }

            if (!$isPicked) {
                $parts = preg_split('/:/', $line, 2);
                if (count($parts) === 2) {
                    $keyPart = strtolower(trim($parts[0]));
                    foreach ($picked as $field => $value) {
                        if ($value && in_array($keyPart, ['os', 'processor', 'memory', 'graphics', 'directx', 'storage', 'network'])) {
                             $isPicked = true;
                             break 2;
                        }
                    }
                }
            }

            if (!$isPicked) {
                $otherLines[] = $line;
            }
        }

        $otherText = trim(implode(' ', $otherLines));

        if ($otherText === '' || stripos($otherText, 'requires a 64-bit processor and operating system') !== false) {
            return null;
        }

        return $otherText ?: null;
    }

    private function hasAtLeastOneValue(array $arr): bool
    {
        foreach (['os','processor','memory','graphics','directx','storage'] as $k) {
            if (!empty($arr[$k])) return true;
        }
        return false;
    }

    private function grab(string $pattern, string $text): ?string
    {
        return preg_match($pattern, $text, $m) ? trim($m[1] ?? $m[0]) : null;
    }
}
