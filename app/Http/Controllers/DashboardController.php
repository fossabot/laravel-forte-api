<?php

namespace App\Http\Controllers;

use App\User;
use App\ErrorLog;
use App\UserItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function signin(Request $request)
    {
        if ($request->isMethod('get')) {
            return view('dashboard.signin');
        }

        if ($request->isMethod('post')) {
            if (! strpos($request->email, '@team-crescendo.me')) {
                return view('dashboard.access');
            }

            $credentials = $request->only('email', 'password');

            if (Auth::attempt($credentials)) {
                return redirect()->intended('dashboard.index');
            }
        }
    }

    public function logout()
    {
        Auth::logout();

        return redirect()->route('login');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = [
            'users' => User::count(),
            'items' => UserItem::count(),
            'points' => User::sum('points'),
        ];

        return view('dashboard.index', compact('result'));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function users()
    {
        $result = [
            'counts' => User::count(),
            'users' => User::scopeAllUsers(),
        ];

        return view('dashboard.users', compact('result'));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function errors()
    {
        $result = [
            'counts' => ErrorLog::count(),
            'today_counts' => ErrorLog::whereDate('created_at', date('Y-m-d'))->count(),
            'errors' => ErrorLog::get(),
        ];

        return view('dashboard.errors', compact('result'));
    }
}
