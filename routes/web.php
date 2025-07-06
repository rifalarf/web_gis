<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\GeojsonController;
use App\Http\Controllers\RuasController;
use App\Http\Controllers\Api\MapApiController;

Route::get('/', function () {
    return Inertia::render('Dashboard');
})->name('dashboard');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->name('home');

Route::get('/api/segments.geojson', [MapApiController::class, 'index']);

Route::get('/ruas-jalan',          [RuasController::class,'index']);
Route::get('/ruas-jalan/{code}',   [RuasController::class,'show']);


Route::middleware('auth')->group(function () {
    Route::post   ('/geojson/upload', [GeojsonController::class,'upload'])
         ->name('geojson.upload');
    Route::delete('/ruas-jalan/{code}',     [GeojsonController::class,'destroy'])
         ->name('ruas.destroy');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

/*
Route::get('/names', [NameController::class, 'index'])->name('names.index');
Route::get('/names/{name}', [NameController::class, 'show'])->name('names.show');

Route::middleware('auth')->group(function () {
    Route::post('/names', [NameController::class, 'store'])->name('names.store');
    Route::get('/names/create', [NameController::class, 'create'])->name('names.create');
    Route::get('/names/{name}/edit', [NameController::class, 'edit'])->name('names.edit');
    Route::put('/names/{name}', [NameController::class, 'update'])->name('names.update');
    Route::delete('/names/{name}', [NameController::class, 'destroy'])->name('names.destroy');
});
*/
