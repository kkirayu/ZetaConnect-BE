<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogApiActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Hanya log operasi yang merubah data
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            
            // Jangan log kalau request ke audit log sendiri
            if ($request->is('api/audit-logs*') || $request->is('audit-logs*')) {
                return $response;
            }

            $user = auth('sanctum')->user();
            
            $actionName = 'Melakukan aksi ' . $request->method() . ' pada ' . $request->path();

            $content = $response->getContent();
            $decodedResponse = json_decode($content, true);

            activity()
                ->causedBy($user)
                ->withProperties([
                    'endpoint' => '/' . $request->path(),
                    'method' => $request->method(),
                    'payload' => $request->except(['password', 'password_confirmation', 'pin']),
                    'response' => $decodedResponse ?? $content
                ])
                ->log($actionName);
        }

        return $response;
    }
}
