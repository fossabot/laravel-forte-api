<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\RequestLog;

class APIRequestLog
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
        $request->start = microtime(true);

        return $next($request);
    }

    public function terminate($request, $response)
    {
        $request->end = microtime(true);
        $this->log($request, $response);
    }

    protected function log($request, $response)
    {
        RequestLog::create([
           'duration' => $request->end - $request->start,
           'url' => $request->fullUrl(),
           'method' => $request->getMethod(),
           'ip' => $request->getClientIp(),
           'request' => json_encode($request->all(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
           'response' => json_encode($response->getContent(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        ]);
    }
}
