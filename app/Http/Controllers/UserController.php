<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $current = $request->user();

        $q       = trim($request->string('q')->toString());
        $perPage = (int) $request->integer('per_page') ?: 15;
        $perPage = max(5, min($perPage, 100));

        $users = User::with('roles')
            ->when($q !== '', function ($qr) use ($q) {
                $qr->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%");
                });
            })
            // 🔒 Kalau BUKAN Super Admin:
            //     - jangan tampilkan user Super Admin
            //     - jangan tampilkan user CTL
            ->when(
                ! $current->hasRole('Super Admin'),
                function ($qr) {
                    $qr->whereDoesntHave('roles', function ($r) {
                        $r->whereIn('name', ['Super Admin', 'CTL']);
                    });
                }
            )
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return view('users.index', compact('users','q','perPage'));
    }
}
