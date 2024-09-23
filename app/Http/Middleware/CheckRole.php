<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();
        // dd($user);

        if (!$user) {
            return response()->json(['error' => 'Non autorisée'], 403);
        }

       
        if (!$user->role) {
            return response()->json(['error' => 'Non autorisée'], 403);
        }

        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return $next($request);
            }
        }

        return response()->json(['error' => 'Non autorisé'], 403);
    }
    
}
