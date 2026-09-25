<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirect pengguna yang belum ACTIVE (belum punya perusahaan / masih PENDING)
 * ke halaman menunggu, sebelum mereka bisa mengakses fitur utama aplikasi.
 */
class EnsureActiveAccount
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->role === 'PIC' && ! $user->company_id) {
            return redirect()->route('company.create');
        }

        if ($user && ! $user->isActive()) {
            return redirect()->route('account.waiting');
        }

        return $next($request);
    }
}
