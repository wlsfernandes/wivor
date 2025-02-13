<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class EnsureInstitutionScope
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        // Restrict queries to the user's institution
        if ($user) {
            // Example: Use a global scope for Institution
            $request->merge(['institution_id' => $user->institution_id]);
        }

        return $next($request);
    }
}
