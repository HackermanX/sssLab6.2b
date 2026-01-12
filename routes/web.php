<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MyReqController;
use Illuminate\Http\Request;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

use App\Services\SteamService;

Route::get('/steam/{appId}', function ($appId, SteamService $steamService) {
    return response()->json($steamService->getRequirements($appId));
});

Route::get('/pc-reqs', [MyReqController::class, 'showForm'])->name('main.form');
Route::post('/pc-reqs', [MyReqController::class, 'storeAndShow'])->name('main.store');


Route::get('/debug-ram/{appId}', function (SteamService $steam, int $appId) {
    $response = Http::get('https://store.steampowered.com/api/appdetails', [
        'appids' => $appId,
        'cc'     => 'US',
        'l'      => 'english',
    ]);

    if ($response->failed() || !$response->json("{$appId}.success")) {
        dd('failed or no data');
    }

    $pc = $response->json("{$appId}.data.pc_requirements");

    $minHtml = $pc['minimum'] ?? ($pc[0]['minimum'] ?? null);

    $minParsed = (function ($service, $html) {
        $ref = new \ReflectionClass($service);
        $m = $ref->getMethod('parse');
        $m->setAccessible(true);
        return $m->invoke($service, $html);
    })($steam, $minHtml);

    $ram = $minParsed['memory'] ?? null;

    $ramGb = (function ($service, $ramText) {
        $ref = new \ReflectionClass($service);
        $m = $ref->getMethod('ramFromText');
        $m->setAccessible(true);
        return $m->invoke($service, $ramText);
    })($steam, $ram);

    dd([
        'minHtml'  => $minHtml,
        'minParsed_memory' => $ram,
        'ramFromText_result' => $ramGb,
    ]);
});
