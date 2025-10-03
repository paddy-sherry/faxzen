<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureLandingPage
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only track the first page visit if no landing page cookie exists
        if (!$request->hasCookie('landing_page') && $request->isMethod('GET')) {
            $landingPage = $request->fullUrl();
            
            // Debug logging
            \Log::info('CaptureLandingPage middleware: Setting cookie', [
                'landing_page' => $landingPage,
                'url' => $request->url(),
                'full_url' => $request->fullUrl(),
                'has_cookie_before' => $request->hasCookie('landing_page'),
                'user_agent' => $request->userAgent(),
            ]);
            
            // Create response and add the landing page cookie (expires in 30 days)
            $response = $next($request);
            
            return $response->withCookie(cookie(
                'landing_page', 
                $landingPage, 
                60 * 24 * 30, // 30 days
                '/', // path
                null, // domain
                request()->secure(), // secure only if HTTPS
                false // httpOnly (false so JS can read it if needed)
            ));
        }
        
        return $next($request);
    }
}
