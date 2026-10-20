<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Akun dengan kata sandi sementara (dibuat/disetel ulang admin) wajib menggantinya dulu. */
class EnsurePasswordIsChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->must_change_password && ! $request->routeIs('admin.profile.*', 'admin.logout')) {
            return redirect()->route('admin.profile.edit')
                ->with('warning', 'Anda masih memakai kata sandi sementara dari admin. Ganti dulu sebelum melanjutkan.');
        }

        return $next($request);
    }
}
