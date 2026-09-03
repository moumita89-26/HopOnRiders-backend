<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use \App\Helpers\AdminHelper;

class AdminAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (AdminHelper::myId() == '') {
            $url = AdminHelper::adminpath('login');

            return redirect($url);
        }

        if (AdminHelper::isLocked()) {
            $url = AdminHelper::adminpath('lock-screen');

            return redirect($url);
        }

        return $next($request);
    }
}
