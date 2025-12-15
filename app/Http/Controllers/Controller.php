<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="Employee Management API",
 *     version="1.0.0",
 *     description="API for managing employees with authentication",
 *     @OA\Contact(
 *         email="admin@example.com"
 *     )
 * )
 * 
 * @OA\Server(
 *     url="http://localhost:8000",
 *     description="Local Development Server"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="Token",
 *     description="Enter your bearer token in the format: Bearer {token}"
 * )
 */
abstract class Controller
{
    //
}