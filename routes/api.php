<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // User management routes (HR only)
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/pending', [UserController::class, 'pendingUsers']);
    Route::get('/users/pending/count', [UserController::class, 'pendingUsersCount']);
    Route::post('/users/{id}/activate', [UserController::class, 'activateUser']);
    Route::post('/users/{id}/deactivate', [UserController::class, 'deactivateUser']);
    
    // Employee management routes
    Route::apiResource('employees', EmployeeController::class);
    Route::get('/employees/pending', [EmployeeController::class, 'pendingEmployees']);
    Route::post('/employees/{id}/activate', [EmployeeController::class, 'activateEmployee']);
    Route::post('/employees/{id}/deactivate', [EmployeeController::class, 'deactivateEmployee']);
});