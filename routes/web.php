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

Route::get('/pc-reqs', [MyReqController::class, 'showForm'])->name('main.form');
Route::post('/pc-reqs', [MyReqController::class, 'storeAndShow'])->name('main.store');
Route::delete('/pc-reqs', [MyReqController::class, 'destroy'])->name('main.destroy');

use App\Http\Controllers\GameBrowserController;

Route::get('/games', [GameBrowserController::class, 'index'])->name('games.index');
Route::get('/games/{id}', [GameBrowserController::class, 'show'])->name('games.show');

Route::get('/debug/game/{id}', function ($id) {
    return \App\Models\GameRequirement::find($id) ?? 'null';
});