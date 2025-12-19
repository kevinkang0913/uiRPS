<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserHasAppRole
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // kalau belum login, biarkan middleware auth yang handle
        if (!$user) return $next($request);

        // anggap role valid aplikasi = salah satu ini
        $validRoles = ['Dosen','CTL','Kaprodi','Admin','Super Admin'];

        // jika user tidak punya role valid, arahkan ke halaman "pending"
        if (!$user->hasAnyRole($validRoles)) {
            return redirect()->route('pending-role');
        }

        return $next($request);
    }
}
