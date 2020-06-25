<?php

namespace App\Http\Middleware;

use App\Models\RequestLog;
use Closure;

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
            RequestLog::DURATION => $request->end - $request->start,
            RequestLog::URL => $request->fullUrl(),
            RequestLog::METHOD => $request->getMethod(),
            RequestLog::IP => $request->getClientIp(),
            RequestLog::REQUEST => json_encode($request->all(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            RequestLog::RESPONSE => json_encode($response->getContent(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        ]);
    }
}
