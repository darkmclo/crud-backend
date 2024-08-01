<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/status', [App\Http\Controllers\StatusController::class, 'getStatus']);
Route::get('/products', [App\Http\Controllers\ProductController::class, 'index']);
Route::get('/clients', [App\Http\Controllers\ClientController::class, 'index']);