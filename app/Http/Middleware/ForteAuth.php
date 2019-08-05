<?php

namespace App\Http\Middleware;


use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class ForteAuth extends Middleware
{

    /**
     * @param \Illuminate\Http\Request $request
     * @param Closure $next
     * @return \Illuminate\Http\RedirectResponse|mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->auth->check() and $this->auth->user)
        {
            return $next($request);
        } else
        {
            return redirect()->guest('auth/login');
        }
    }
}