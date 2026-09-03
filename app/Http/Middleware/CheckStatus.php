<?php

namespace App\Http\Middleware;
use Closure;

class CheckStatus
{
 
    public function handle($request, Closure $next)
    {
        if(auth()->check() && auth()->User()->status == 0)
        {
           //auth()->logout();
           $request->user()->currentAccessToken()->delete();           
            return response()->json([
                'responseCode' => 0,
                'responseMessage'=> 'Ihre Sitzung ist abgelaufen, da Ihr Konto nicht aktiv ist.',
                'responseData' => []
            ]);
        }
           
        return $next($request);

    }

}