<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Traffic;

class LogTraffic
{
    public function handle(Request $request, Closure $next)
    {
        // Log the traffic data
        Traffic::insert([
            'visited_at' => now(),
            'source' => $request->input('source', 'direct'), // Default to 'direct' if not provided
            'user_id' => $request->user() ? $request->user()->id : null, // Log user ID if logged in
            'ip_address' => $request->ip(), // Get IP address of the visitor
            'user_agent' => $request->header('User-Agent'), // Get user agent string
        ]);

        return $next($request);
    }
}
