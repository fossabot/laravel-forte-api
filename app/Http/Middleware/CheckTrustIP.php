<?php

namespace App\Http\Middleware;

use App\Models\TrustIP;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        if (! TrustIP::where('ip', request()->ip())->first()) {
            return new JsonResponse([
                'error' => [
                    'code' => 'INVALID_IP_ADDRESS',
                    'message' => 'The IP was not trusted.',
                ],
            ], 401);
        }

        return $next($request);
    }
}
