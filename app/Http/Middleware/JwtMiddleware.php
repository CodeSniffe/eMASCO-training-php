<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class JwtMiddleware
{
    public function handle($request, Closure $next)
    {
        try {
            $token = JWTAuth::getToken();

            if (!$token) {
                throw new UnauthorizedHttpException('jwt-auth', 'Token not provided');
            }

            $payload = JWTAuth::getPayload($token);
            $userId = $payload->get('sub');

            if (!$userId) {
                throw new UnauthorizedHttpException('jwt-auth', 'User ID not found in token');
            }

            // Create mocked user instance with hardcoded data
            $user = new User();
            $user->id = $userId;
            $user->name = 'John Doe';
            $user->email = 'john@example.com';

            // Set the authenticated user for the current request
            auth()->setUser($user);

        } catch (\Exception $e) {
            throw new UnauthorizedHttpException('jwt-auth', $e->getMessage());
        }

        return $next($request);
    }
}