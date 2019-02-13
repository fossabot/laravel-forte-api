<?php

namespace App\Http\Middleware;

use Closure;
use App\Client;
use Illuminate\Support\Facades\Response;

class CheckApiHeader
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        if (! isset($_SERVER['HTTP_AUTHORIZATION'])) {
            return Response::json([
                'error' => 'Please set Authorization header'
            ]);
        }

        if (! Client::where('token', $_SERVER['HTTP_AUTHORIZATION'])->first()) {
            return Response::json([
                'error' => 'Wrong Authorization header'
            ]);
        }
        return $next($request);
    }
}
