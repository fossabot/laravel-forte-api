<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\TrustIP;
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
            return response()->json([
                'error' => [
                    'code' => 'INVALID_IP_ADDRESS',
                    'message' => 'The IP was not trusted.',
                ],
            ], 401);
        }

        return $next($request);
    }
}
