<?php

namespace App\Http\Controllers;
use App\Models\FetchReq;
use App\Models\GameRequirement;
use App\Services\SteamService;
use Illuminate\Http\Request;
use App\Models\CPUbench;
use App\Models\GPUbench;

class ComparePerformance
{
    public function compare(FetchReq $pc, GameRequirement $game): array
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

class MyReqController extends Controller
{
    public function showForm()
    {
        $mySpecs = FetchReq::latest()->first();

        $cpus = CPUbench::orderBy('score')->get();
        $gpus = GPUbench::orderBy('score')->get();

        return view('main', [
            'mySpecs'           => $mySpecs,
            'steamRequirements' => null,
            'appId'             => null,
            'cpus'              => $cpus,
            'gpus'              => $gpus,
            'comparison'        => null,
            'pcDebug'           => null,
            'gameDebug'         => null,
        ]);
    }

    public function storeAndShow(Request $request, SteamService $steamService)
    {
        $data = $request->validate([
            'cpu_id'  => ['required', 'exists:c_p_ubenches,id'],
            'gpu_id'  => ['required', 'exists:g_p_ubenches,id'],
            'STORAGE' => 'required|string|max:255',
            'RAM'     => ['required', 'string', 'max:255'],
            'appId'   => 'required|numeric',
        ]);

        $cpu = CPUbench::findOrFail($data['cpu_id']);
        $gpu = GPUbench::findOrFail($data['gpu_id']);

        $mySpecs = FetchReq::create([
            'cpu_id'  => $cpu->id,
            'gpu_id'  => $gpu->id,
            'GPU'     => $gpu->name,
            'CPU'     => $cpu->name,

            'RAM'     => $data['RAM'],
            'STORAGE' => $data['STORAGE'],
        ]);

        $steamRequirements = $steamService->getRequirements((int)$data['appId']);

        $cpus = CPUbench::orderBy('score')->get();
        $gpus = GPUbench::orderBy('score')->get();

        $appId = (int) $data['appId'];

        $gameReq = GameRequirement::where('appid', $appId)->first();

        $comparator = new ComparePerformance();

        $comparison = $gameReq
            ? $comparator->compare($mySpecs, $gameReq)
            : null;

        $cpus = CPUBench::orderBy('score')->get();
        $gpus = GPUBench::orderBy('score')->get();


        $pcDebug = [
        'cpu_id'      => $mySpecs->cpu_id,
        'gpu_id'      => $mySpecs->gpu_id,
        'CPU'         => $mySpecs->CPU,
        'GPU'         => $mySpecs->GPU,
        'RAM'         => $mySpecs->RAM,
        'STORAGE'     => $mySpecs->STORAGE,
        'cpu_score'   => $mySpecs->cpu?->score,
        'gpu_score'   => $mySpecs->gpu?->score,
        ];

        $gameDebug = $gameReq ? [
            'appid'           => $gameReq->appid,
            'minimum_parsed'  => $gameReq->minimum_parsed,
            'min_cpu_score'   => $gameReq->min_cpu_score,
            'min_gpu_score'   => $gameReq->min_gpu_score,
            'min_ram_gb'      => $gameReq->min_ram_gb,
        ] : null;

        return view('main', [
            'mySpecs'           => $mySpecs,
            'steamRequirements' => $steamRequirements,
            'comparison'        => $comparison,
            'appId'             => $data['appId'],
            'cpus'              => $cpus,
            'gpus'              => $gpus,
            'comparison'        => $comparison,
            'pcDebug'           => $pcDebug,
            'gameDebug'         => $gameDebug,
        ]);
    }
}
