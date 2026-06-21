<?php

namespace App\Http\Controllers;

/**
 * @OA\Server(
 * url="http://localhost:8000",
 * description="Server Pengembangan Lokal"
 * )
 *
 * @OA\SecurityScheme(
 * securityScheme="bearerAuth",
 * type="http",
 * scheme="bearer",
 * bearerFormat="JWT",
 * in="header",
 * name="Authorization"
 * )
 */
abstract class Controller
{
    
}