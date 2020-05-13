<?php

namespace App\Http\Middleware;

use Closure;
use Webstronauts\Unpoly\Unpoly as UnpolyMiddleware;

class Unpoly
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
        $response = $next($request);

        (new UnpolyMiddleware)->decorateResponse($request, $response);
        

        return $response;
    }
}
