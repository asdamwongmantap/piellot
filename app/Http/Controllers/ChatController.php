<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

/**
 * Menggantikan action send_message dan mark_read pada
 * app/api/action/route.ts, ditambah tampilan daftar percakapan
 * yang sebelumnya dirender langsung di dalam app/piellot-app.tsx.
 */
class ChatController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            $companies = Company::where('status', 'ACTIVE')
                ->withCount(['messages as unread_count' => fn ($q) => $q->where('read_by_admin', false)])
                ->orderBy('name')
                ->get();

            $companyId = $request->integer('company_id') ?: null;
        } else {
            $companies = collect();
            $companyId = $user->company_id;
        }

        $thread = $companyId
            ? Message::where('company_id', $companyId)->orderBy('created_at')->get()
            : collect();

        if ($companyId) {
            $this->markRead($companyId);
        }

        $activeCompany = $companyId
            ? ($user->isAdmin() ? $companies->firstWhere('id', $companyId) : $user->company)
            : null;

        return Inertia::render('Chat/Index', [
            'companies' => $companies,
            'thread' => $thread,
            'companyId' => $companyId,
            'activeCompany' => $activeCompany,
        ]);
    }

    public function send(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:1500'],
            'company_id' => [$user->isAdmin() ? 'required' : 'nullable', 'exists:companies,id'],
        ]);

        $companyId = $user->isAdmin() ? $validated['company_id'] : $user->company_id;

        Message::create([
            'company_id' => $companyId,
            'sender_user_id' => $user->id,
            'sender_role' => $user->role,
            'sender_name' => $user->name,
            'body' => trim($validated['body']),
            'read_by_admin' => $user->isAdmin(),
            'read_by_pic' => $user->isPic(),
        ]);

        return redirect()->route('chat.index', ['company_id' => $companyId]);
    }

    private function markRead(int $companyId): void
    {
        $user = Auth::user();
        $column = $user->isAdmin() ? 'read_by_admin' : 'read_by_pic';

        Message::where('company_id', $companyId)->where($column, false)->update([$column => true]);
    }
}
