<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class VerifyXsollaAuthorization
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        ini_set('serialize_precision', -1);
        $body = json_encode($request->json()->all()).config('xsolla.projectKey'); // . env('XSOLLA_PROJECT_KEY', 0)
        $hash = sha1($body);

        if (! isset($_SERVER['HTTP_AUTHORIZATION'])) {
            return response([
                'error' => [
                    'code' => 'INVALID_AUTHORIZATION',
                    'message' => 'Please set Authorization Header',
                ],
            ], Response::HTTP_NOT_FOUND);
        }

        if ($_SERVER['HTTP_AUTHORIZATION'] != 'Signature '.$hash) {
            return response([
                'error' => [
                    'code' => 'INVALID_SIGNATURE',
                    'message' => 'The Authorization Signature is invalid',
                ],
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
