<?php

use App\Http\Controllers\Api\KerusakanApiController;
use Illuminate\Support\Facades\Route;

Route::get('/kerusakan.geojson', [KerusakanApiController::class,'index']);
