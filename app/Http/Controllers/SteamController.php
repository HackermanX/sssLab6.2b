<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SteamController extends Controller
{
    public function __construct(protected SteamService $steam) {}

    public function specsByName(Request $request)
    {
        $request->validate(['game' => 'required|string']);

        $specs = $this->steam->getSpecsByName($request->game);

        return $specs
            ? response()->json($specs)
            : response()->json(['error' => 'Game not found on Steam'], 404);
    }

    public function specsById($appId)
    {
        $specs = $this->steam->getGameSpecs((int) $appId);

        return $specs
            ? response()->json($specs)
            : response()->json(['error' => 'Game not found on Steam'], 404);
    }
}