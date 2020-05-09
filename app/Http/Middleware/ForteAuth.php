<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ForteAuth
{
    /**
     * @param Request $request
     * @param Closure $next
     * @return RedirectResponse|mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check() && Auth::User()->{User::ID}) {
            return $next($request);
        } else {
            return redirect()->route('login');
        }
    }
}
