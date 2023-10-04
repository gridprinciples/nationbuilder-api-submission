<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NationBuilderAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->nationbuilder->loggedIn()) {
            // Remember where the user intended to go
            session()->put('nationbuilder-redirect', $request->fullUrl());

            // Redirect to NationBuilder OAuth
            return redirect()->to(
                app()->nationbuilder->getOauthLoginUrl()
            );
        }
        
        return $next($request);
    }
}
