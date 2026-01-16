<?php

namespace App\Http\Controllers;
use App\Models\FetchReq;
use App\Models\GameRequirement;
use App\Services\SteamService;
use Illuminate\Http\Request;
use App\Models\CPUbench;
use App\Models\GPUbench;

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