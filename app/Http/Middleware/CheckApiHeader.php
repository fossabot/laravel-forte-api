<?php

namespace App\Http\Middleware;

use App\Http\Controllers\ClientController;
use App\Models\Client;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CheckApiHeader
{
    /**
     * @var ClientController
     */
    protected $cc;

    /**
     * CheckApiHeader constructor.
     * @param ClientController $cc
     */
    public function __construct(ClientController $cc)
    {
        $this->cc = $cc;
    }

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! isset($_SERVER['HTTP_AUTHORIZATION'])) {
            return new JsonResponse([
                'error' => [
                    'code' => 'INVALID_AUTHORIZATION',
                    'message' => 'Please set Authorization Header',
                ],
            ], Response::HTTP_NOT_FOUND);
        }

        if (Client::where(Client::PREV_TOKEN, $_SERVER['HTTP_AUTHORIZATION'])->first()) {
            return $this->cc->issue();
        }

        if (! Client::where(Client::TOKEN, $_SERVER['HTTP_AUTHORIZATION'])->first()) {
            return new JsonResponse([
                'error' => [
                    'code' => 'INVALID_AUTHORIZATION',
                    'message' => 'The Authorization Header is invalid',
                ],
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
