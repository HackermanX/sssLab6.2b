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

        return view('main', [
            'mySpecs'           => $mySpecs,
            'steamRequirements' => $steamRequirements,
            'appId'             => $data['appId'],
            'cpus'              => $cpus,
            'gpus'              => $gpus,
        ]);
    }
}
