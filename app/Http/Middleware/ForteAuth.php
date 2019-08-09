<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\XsollaUrl;
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
        if (Auth::check() and Auth::user()->id == XsollaUrl::where('token', $request->token)->first()->user_id) {
            return $next($request);
        } else {
            return redirect()->route('login');
        }
    }
}
