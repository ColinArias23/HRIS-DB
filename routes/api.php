<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController; 
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    Route::get('/pending-users', [UserController::class, 'pendingUsers']);
    Route::middleware('auth:sanctum')->patch('/users/{id}/activate', [UserController::class, 'activateUser']);

});
