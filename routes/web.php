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

Route::delete('/pc-reqs/', [MyReqController::class, 'destroy'])
    ->name('main.destroy');