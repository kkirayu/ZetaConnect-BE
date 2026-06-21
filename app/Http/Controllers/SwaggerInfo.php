<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 * title="ZetaConnect API Documentation",
 * version="1.0.0",
 * description="Dokumentasi API Terintegrasi"
 * )
 * @OA\Server(
 * url="http://localhost:8000",
 * description="Local Server"
 * )
 */
class SwaggerInfo
{
    /**
     * @OA\Get(
     * path="/api/ping",
     * summary="Ping server",
     * @OA\Response(
     * response=200,
     * description="Server is alive"
     * )
     * )
     */
    public function ping()
    {
        return response()->json(['message' => 'pong']);
    }
}