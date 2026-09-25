<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Hanya izinkan PIC yang aktif dan sudah terhubung ke perusahaan.
 * Dipakai untuk membuat booking dan mengajukan admin tambahan.
 */
class EnsurePic
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isPic() || ! $user->isActive() || ! $user->company_id) {
            abort(403, 'Akun PIC Anda belum aktif.');
        }

        return $next($request);
    }
}
