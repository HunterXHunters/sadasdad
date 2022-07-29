<?php

namespace App\Http\Middleware;

use Closure;
use DB;

class AfterMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $publicKey = DB::table('oauth_clients')->where('user_id',13)->value('public_key');
        $request->headers->set('public_key', $publicKey);
        $response = $next($request);
        
        return $response;
    }
}
