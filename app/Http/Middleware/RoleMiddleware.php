<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Usage:
     *   ->middleware('role:Dosen')
     *   ->middleware('role:Dosen,Super Admin')
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'Unauthenticated.');
        }

        if (empty($roles)) {
            return $next($request);
        }

        if (! method_exists($user, 'hasAnyRole')) {
            abort(500, 'Role helper not defined on User model.');
        }

        if (! $user->hasAnyRole($roles)) {
            abort(403, 'Anda tidak memiliki hak akses untuk fitur ini.');
        }

        return $next($request);
    }
}
