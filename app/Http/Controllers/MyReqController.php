<?php

namespace App\Http\Controllers;
use App\Models\FetchReq;
use App\Models\GameRequirement;
use App\Services\SteamService;
use Illuminate\Http\Request;

class MyReqController extends Controller
{
    public function showForm()
    {
        $mySpecs = FetchReq::latest()->first();

        return view('main', [
            'mySpecs' => $mySpecs,
            'steamRequirements' => null,
            'appId' => null,
        ]);
    }

    public function storeAndShow(Request $request, SteamService $steamService)
    {
        $data = $request->validate([
            'CPU'     => 'required|string|max:255',
            'RAM'     => 'required|string|max:255',
            'STORAGE' => 'required|string|max:255',
            'GPU'     => 'required|string|max:255',
            'appId'   => 'required|numeric',
        ]);

        $mySpecs = FetchReq::create([
            'CPU'     => $data['CPU'],
            'RAM'     => $data['RAM'],
            'STORAGE' => $data['STORAGE'],
            'GPU'     => $data['GPU'],
        ]);

        $steamRequirements = $steamService->getRequirements((int)$data['appId']);

        return view('main', [
            'mySpecs'           => $mySpecs,
            'steamRequirements' => $steamRequirements,
            'appId'             => $data['appId'],
        ]);
    }
}
