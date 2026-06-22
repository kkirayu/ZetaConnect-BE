<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: "ZetaConnect API Documentation",
    version: "1.0.0",
    description: "Integrated ZetaConnect API for veterinary clinic management system"
)]
#[OA\Server(
    url: "http://localhost:8000/api",
    description: "Local Development Server"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT",
    in: "header",
    name: "Authorization"
)]
class SwaggerInfo
{
    #[OA\Get(
        path: "/ping",
        summary: "Ping server",
        tags: ["System"]
    )]
    #[OA\Response(
        response: 200,
        description: "Server is alive",
        content: new OA\JsonContent(
            example: ["message" => "pong"]
        )
    )]
    public function ping()
    {
        return response()->json(['message' => 'pong']);
    }
}