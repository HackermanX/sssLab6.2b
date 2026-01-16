<?php

namespace App\Services;

use App\Models\FetchReq;

class ComparePerformance
{
    public function compare(FetchReq $pc, object $game): array
    {
        $pcCpuScore = $pc->cpu?->score ?? 0;
        $pcGpuScore = $pc->gpu?->score ?? 0;
        $pcRamGb    = $this->parseRamToGb($pc->RAM);

        $cpuOk = $pcCpuScore >= (int) $game->min_cpu_score;
        $gpuOk = $pcGpuScore >= (int) $game->min_gpu_score;
        $ramOk = $pcRamGb    >= (int) $game->min_ram_gb;

        return [
            'ok'  => $cpuOk && $gpuOk && $ramOk,
            'cpu' => ['ok' => $cpuOk, 'pc' => $pcCpuScore, 'req' => $game->min_cpu_score],
            'gpu' => ['ok' => $gpuOk, 'pc' => $pcGpuScore, 'req' => $game->min_gpu_score],
            'ram' => ['ok' => $ramOk, 'pc' => $pcRamGb,    'req' => $game->min_ram_gb],
        ];
    }

    protected function parseRamToGb(string $ram): int
    {
        if (preg_match('/(\d+)/', $ram, $m)) {
            return (int) $m[1];
        }
        return 0;
    }
}