<?php

namespace App\Http\Middleware;

use App\Models\Booking;
use App\Models\Company;
use App\Models\CompanyAdminRequest;
use App\Models\Message;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'status' => $user->status,
                    'isAdmin' => $user->isAdmin(),
                    'companyName' => $user->company?->name,
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'badges' => fn () => $this->badges($user),
        ];
    }

    /** Angka notifikasi pada menu (Approval, Booking, Chat). */
    private function badges($user): array
    {
        if (! $user || ! $user->isActive()) {
            return [];
        }

        if ($user->isAdmin()) {
            return [
                'companies.pending' => Company::where('status', 'PENDING')->count()
                    + CompanyAdminRequest::where('status', 'PENDING')->count(),
                'bookings.index' => Booking::where('status', 'PENDING')->count(),
                'chat.index' => Message::where('read_by_admin', false)->count(),
            ];
        }

        return [
            'chat.index' => Message::where('company_id', $user->company_id)->where('read_by_pic', false)->count(),
        ];
    }
}
