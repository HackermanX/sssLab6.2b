<?php

namespace App\Http\Controllers;

use App\Models\GameRequirement;
use App\Models\FetchReq;
use Illuminate\Http\Request;
use App\Services\ComparePerformance;

class GameBrowserController extends Controller
{
    public function index(Request $request, ComparePerformance $comparator)
    {
        $featured       = config('featured_games');
        $featuredAppids = array_keys($featured);

        $specId  = session('current_specs_id');
        $mySpecs = $specId ? FetchReq::with(['cpu', 'gpu'])->find($specId) : null;

        $query = GameRequirement::whereIn('appid', $featuredAppids);

        if ($search = $request->input('q')) {
            $query->where('name', 'like', '%'.$search.'%');
        }
        if ($minYear = $request->input('year_from')) {
            $query->where('year', '>=', $minYear);
        }
        if ($maxYear = $request->input('year_to')) {
            $query->where('year', '<=', $maxYear);
        }

        $sort    = $request->input('sort', 'name');
        $dir     = $request->input('dir', 'asc');
        $allowed = ['name', 'year', 'min_cpu_score', 'min_gpu_score', 'min_ram_gb'];

        if (! in_array($sort, $allowed, true)) {
            $sort = 'name';
        }
        $dir = $dir === 'desc' ? 'desc' : 'asc';

        $query->orderBy($sort, $dir);

        $games = $query->get();

        $comparisons = [];
        if ($mySpecs) {
            foreach ($games as $game) {
                $comparisons[$game->id] = $comparator->compare($mySpecs, $game);
            }
        }

        return view('games.index', [
            'games'        => $games,
            'mySpecs'      => $mySpecs,
            'comparisons'  => $comparisons,
            'sort'         => $sort,
            'dir'          => $dir,
            'featured'     => $featured,
        ]);
    }

    public function show(int $id, ComparePerformance $comparator)
    {
        $game = GameRequirement::findOrFail($id);

        $specId  = session('current_specs_id');
        $mySpecs = $specId ? FetchReq::with(['cpu', 'gpu'])->find($specId) : null;

        $comparison = $mySpecs
            ? $comparator->compare($mySpecs, $game)
            : null;

        return view('games.show', [
            'game'       => $game,
            'mySpecs'    => $mySpecs,
            'comparison' => $comparison,
        ]);
    }
}