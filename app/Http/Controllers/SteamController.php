<?php

namespace App\Http\Controllers;

use App\Services\SteamService;
use Illuminate\Http\Request;

class SteamController extends Controller
{
    public function __construct(protected SteamService $steam) {}

    public function specsById($appId)
    {
        $specs = $this->steam->getGameSpecs((int) $appId);

        if (! $specs) {
            return response()->json(['error' => 'Game not found on Steam'], 404);
        }

        $data = is_array($specs) ? $specs : (is_object($specs) ? (array) $specs : []);

        $minRaw = $data['minimum_requirements'] ?? $data['minimumRequirements'] ?? null;
        $recRaw = $data['recommended_requirements'] ?? $data['recommendedRequirements'] ?? null;

        $parseRequirements = function (?string $text) {
            if ($text === null) {
                return null;
            }

            $clean = trim(preg_replace('/^(Minimum:|Recommended:)/i', '', $text));
            if ($clean === '' || preg_match('/not specified/i', $clean)) {
                return null;
            }

            $get = function (string $pattern, string $subject) {
                if (preg_match($pattern, $subject, $m)) {
                    return trim($m[1]);
                }
                return null;
            };

            $fields = [
                'os' => $get('/OS:\\s*([^\\n\\r]*?)(?=(?:Processor:|CPU:|Memory:|Graphics:|Video card:|Video:|DirectX:|Storage:|$))/is', $clean),
                'processor' => $get('/(?:Processor|CPU):\\s*([^\\n\\r]*?)(?=(?:Memory:|Graphics:|Video card:|Video:|DirectX:|Storage:|$))/is', $clean),
                'memory' => $get('/Memory:\\s*([^\\n\\r]*?)(?=(?:Graphics:|Video card:|Video:|DirectX:|Storage:|$))/is', $clean),
                'graphics' => $get('/(?:Graphics|Video card|Video):\\s*([^\\n\\r]*?)(?=(?:DirectX:|Storage:|$))/is', $clean),
                'directx' => $get('/DirectX:\\s*([^\\n\\r]*?)(?=(?:Storage:|$))/is', $clean),
                'storage' => $get('/Storage:\\s*([^\\n\\r]*?)(?=$)/is', $clean), // shoutout to chatgpt for knowing how to format
            ];

            $others = preg_replace([
                '/OS:\\s*[^\\n\\r]*/i',
                '/(?:Processor|CPU):\\s*[^\\n\\r]*/i',
                '/Memory:\\s*[^\\n\\r]*/i',
                '/(?:Graphics|Video card|Video):\\s*[^\\n\\r]*/i',
                '/DirectX:\\s*[^\\n\\r]*/i',
                '/Storage:\\s*[^\\n\\r]*/i',
            ], '', $clean);

            $others = trim(preg_replace('/[\\s\\t\\n\\r]{2,}/', ' ', $others));

            return array_merge($fields, [
                'other' => $others === '' ? null : $others,
                'raw' => $text,
            ]);
        };

        $systemSpecs = [
            'minimum_requirements_raw'     => $minRaw,
            'recommended_requirements_raw' => $recRaw,
            'minimum_requirements'         => $parseRequirements($minRaw),
            'recommended_requirements'     => $parseRequirements($recRaw),
        ];

        return response()->json($systemSpecs);
    }
}