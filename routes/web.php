<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\GeojsonController;

Route::get('/', function () {
    return Inertia::render('Dashboard');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->name('dashboard');

Route::middleware(['auth','admin'])->group(function () {
    Route::post   ('/geojson/upload', [GeojsonController::class,'upload'])->name('geojson.upload');
    Route::delete('/ruas/{code}',     [GeojsonController::class,'destroy'])->name('ruas.destroy');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
