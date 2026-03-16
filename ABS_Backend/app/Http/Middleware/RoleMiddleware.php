<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role): Response
    {

        if (!Auth::guard('sanctum')->check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }


        $user = Auth::guard('sanctum')->user();


        $userClass = class_basename($user);

        
        if (strcasecmp($userClass, $role) !== 0) {
            return response()->json(['message' => 'Forbidden - Anda bukan ' . $role], 403);
        }

        return $next($request);
    }
}
