<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//Estado del servidor
Route::get('/status', [App\Http\Controllers\StatusController::class, 'getStatus']);

//Usuarios
Route::post('/register-user', [App\Http\Controllers\AuthController::class, 'register']);

//Productos
Route::get('/products', [App\Http\Controllers\ProductController::class, 'read']);
Route::get('/get-product/{id}', [App\Http\Controllers\ProductController::class, 'get']);
Route::post('/save-product', [App\Http\Controllers\ProductController::class, 'store']);
Route::put('/update-product/{id}', [App\Http\Controllers\ProductController::class, 'update']);
Route::delete('/delete-product/{id}', [App\Http\Controllers\ProductController::class, 'delete']);

//Clientes
Route::get('/clients', [App\Http\Controllers\ClientController::class, 'read']);
Route::get('/get-client/{id}', [App\Http\Controllers\ClientController::class, 'get']);
Route::post('/save-client', [App\Http\Controllers\ClientController::class, 'store']);
Route::put('/update-client/{id}', [App\Http\Controllers\ClientController::class, 'update']);
Route::delete('/delete-client/{id}', [App\Http\Controllers\ClientController::class, 'delete']);

//Facturas

