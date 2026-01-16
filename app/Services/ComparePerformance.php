<?php

namespace App\Services;

use App\Models\FetchReq;

class ComparePerformance
{
    public function compare(FetchReq $pc, object $game): array
    {
        $pcCpuScore = $pc->cpu->score ?? 0;
        $pcGpuScore = $pc->gpu->score ?? 0;
        $pcRamGb    = $this->parseRamToGb($pc->RAM);

        $cpuMinOk = $pcCpuScore >= (int) $game->min_cpu_score;
        $gpuMinOk = $pcGpuScore >= (int) $game->min_gpu_score;
        $ramMinOk = $pcRamGb    >= (int) $game->min_ram_gb;

        $cpuRecOk = $pcCpuScore >= (int) ($game->rec_cpu_score ?? 0);
        $gpuRecOk = $pcGpuScore >= (int) ($game->rec_gpu_score ?? 0);
        $ramRecOk = $pcRamGb    >= (int) ($game->rec_ram_gb  ?? 0);

        $meetsMinOverall = $cpuMinOk && $gpuMinOk && $ramMinOk;

        return [
            'ok'          => $meetsMinOverall,
            'cpu_min_ok'  => $cpuMinOk,
            'gpu_min_ok'  => $gpuMinOk,
            'ram_min_ok'  => $ramMinOk,
            'cpu_rec_ok'  => $cpuRecOk,
            'gpu_rec_ok'  => $gpuRecOk,
            'ram_rec_ok'  => $ramRecOk,
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