<?php

namespace App\Http\Middleware;


use App\Models\XsollaUrl;
use Closure;
use Illuminate\Support\Facades\Auth;

class ForteAuth
{

    /**
     * @param \Illuminate\Http\Request $request
     * @param Closure $next
     * @return \Illuminate\Http\RedirectResponse|mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check() and Auth::user()->id == XsollaUrl::where('token', $request->token->user_id))
        {
            return $next($request);
        } else
        {
            return redirect()->guest('login');
        }
    }
}