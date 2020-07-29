<?php

namespace App\Http\Middleware;

use App\Models\TrustIP;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CheckTrustIP
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        if (config('app.env') === 'testing') {
            return $next($request);
        }

        if (! TrustIP::where('ip', request()->ip())->first()) {
            return new JsonResponse([
                'error' => [
                    'code' => 'INVALID_IP_ADDRESS',
                    'message' => 'The IP was not trusted.',
                ],
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
