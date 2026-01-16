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
            'STORAGE' => ['required', 'string', 'max:255'],
            'RAM'     => ['required', 'string', 'max:255'],
            'appId'   => [
                'nullable',
                'numeric',
                function ($attribute, $value, $fail) use ($steamService) {
                    if ($value === null || $value === '') {
                        return;
                    }
                
                    $req = $steamService->getRequirements((int) $value);

                    if (! $req || empty($req['minimum'])) {
                        $fail('This Steam App ID has no readable PC requirements or does not exist.');
                    }
                },
            ],
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

        session(['current_specs_id' => $mySpecs->id]);
        return redirect()->route('games.index');
    }

    public function destroy(FetchReq $spec)
    {
        FetchReq::query()->delete();

        return redirect()
            ->route('main.form')
            ->with('status', 'Your saved specs have been deleted.');
    }
}