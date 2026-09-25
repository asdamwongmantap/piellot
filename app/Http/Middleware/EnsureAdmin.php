<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Hanya izinkan Admin Fleet yang aktif. Dipakai untuk semua rute
 * pengelolaan armada, approval perusahaan, tagihan, export, dan settings.
 */
class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isAdmin() || ! $user->isActive()) {
            abort(403, 'Aksi ini hanya dapat dilakukan Admin Fleet.');
        }

        return $next($request);
    }
}
