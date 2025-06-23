<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HttpBasicAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $username = env('ADMIN_USERNAME', 'admin');
        $password = env('ADMIN_PASSWORD', 'password');
        
        if (!isset($_SERVER['PHP_AUTH_USER']) || 
            $_SERVER['PHP_AUTH_USER'] !== $username || 
            $_SERVER['PHP_AUTH_PW'] !== $password) {
            
            return response('Unauthorized', 401, [
                'WWW-Authenticate' => 'Basic realm="Admin Panel"'
            ]);
        }
        
        return $next($request);
    }
} 