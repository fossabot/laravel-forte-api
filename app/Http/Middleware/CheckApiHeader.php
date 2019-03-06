<?php

namespace App\Http\Middleware;

use Closure;
use App\Client;

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
            return response([
                'error' => [
                    'code' => 'INVALID_AUTHORIZATION',
                    'message' => 'Please set Authorization Header',
                ],
            ], 404);
        }

        if (! Client::where('token', $_SERVER['HTTP_AUTHORIZATION'])->first()) {
            return response([
                'error' => [
                    'code' => 'INVALID_AUTHORIZATION',
                    'message' => 'The Authorization Header is invalid',
                ],
            ], 401);
        }
        return $next($request);
    }
}
