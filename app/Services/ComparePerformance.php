<?php

namespace App\Services;

use App\Models\FetchReq;

class ComparePerformance
{
    public function compare(FetchReq $pc, object $game): array
    {
        $pcCpuScore   = $pc->cpu->score ?? 0;
        $pcGpuScore   = $pc->gpu->score ?? 0;
        $pcRamGb      = $this->parseRamToGb($pc->RAM);
        $pcStorageGb  = $this->parseStorageToGb($pc->STORAGE ?? '');

        // Minimum requirements
        $cpuMinReq     = (int) ($game->min_cpu_score      ?? 0);
        $gpuMinReq     = (int) ($game->min_gpu_score      ?? 0);
        $ramMinReq     = (int) ($game->min_ram_gb         ?? 0);
        $storageMinReq = (int) ($game->min_storage_gb     ?? 0);

        $cpuMinOk      = $pcCpuScore   >= $cpuMinReq;
        $gpuMinOk      = $pcGpuScore   >= $gpuMinReq;
        $ramMinOk      = $pcRamGb      >= $ramMinReq;
        $storageMinOk  = $pcStorageGb  >= $storageMinReq;
        
        // Recommended  requirements
        $cpuRecReq     = (int) ($game->rec_cpu_score      ?? 0);
        $gpuRecReq     = (int) ($game->rec_gpu_score      ?? 0);
        $ramRecReq     = (int) ($game->rec_ram_gb         ?? 0);
        $storageRecReq = (int) ($game->rec_storage_gb     ?? 0);

        $cpuRecOk      = $cpuRecReq     === 0 ? true : ($pcCpuScore   >= $cpuRecReq);
        $gpuRecOk      = $gpuRecReq     === 0 ? true : ($pcGpuScore   >= $gpuRecReq);
        $ramRecOk      = $ramRecReq     === 0 ? true : ($pcRamGb      >= $ramRecReq);
        $storageRecOk  = $storageRecReq === 0 ? true : ($pcStorageGb  >= $storageRecReq);

        $meetsMinOverall = $cpuMinOk && $gpuMinOk && $ramMinOk && $storageMinOk;

        return [
            'ok'             => $meetsMinOverall,

            'pc_cpu_score'   => $pcCpuScore,
            'pc_gpu_score'   => $pcGpuScore,
            'pc_ram_gb'      => $pcRamGb,
            'pc_storage_gb'  => $pcStorageGb,

            'cpu_min_ok'     => $cpuMinOk,
            'gpu_min_ok'     => $gpuMinOk,
            'ram_min_ok'     => $ramMinOk,
            'storage_min_ok' => $storageMinOk,

            'cpu_rec_ok'     => $cpuRecOk,
            'gpu_rec_ok'     => $gpuRecOk,
            'ram_rec_ok'     => $ramRecOk,
            'storage_rec_ok' => $storageRecOk,
        ];
    }

    protected function parseRamToGb(string $ram): int
    {
        if (preg_match('/(\d+)/', $ram, $m)) {
            return (int) $m[1];
        }
        return 0;
    }

    protected function parseStorageToGb(string $storage): int
    {
        if (preg_match('/(\d+)\s*tb/i', $storage, $m)) {
            return (int) $m[1] * 1024;
        }
        if (preg_match('/(\d+)\s*gb/i', $storage, $m)) {
            return (int) $m[1];
        }
        if (preg_match('/(\d+)/', $storage, $m)) {
            return (int) $m[1];
        }
        return 0;
    }
}