<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BearerTransfer
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // If we have a token add it to the headers
        if ($request->cookie('_token'))
            $request->headers->set('Authorization', 'Bearer ' . $request->cookie('_token'));
        // Create our response 
        $response = $next($request);
        // Return it
        return $response;
    }
}
