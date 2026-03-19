<?php

namespace App\Http\Controllers;

use App\Models\Cdr;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $isAdmin = $user->isAdmin();
        $account = $user->account->load('tariff');

        if ($isAdmin) {
            $activeCalls = Cdr::with('account.user')->active()->orderBy('started_at', 'desc')->get();
            $recentCdrs = Cdr::with('account.user')->completed()->orderBy('ended_at', 'desc')->limit(20)->get();
        } else {
            $activeCalls = $account->cdrs()->active()->orderBy('started_at', 'desc')->get();
            $recentCdrs = $account->cdrs()->completed()->orderBy('ended_at', 'desc')->limit(20)->get();
        }

        $activeCalls = $activeCalls->map(fn ($cdr) => [
            'id' => $cdr->id,
            'uniqueid' => $cdr->uniqueid,
            'account_id' => $cdr->account_id,
            'dst' => $cdr->dst,
            'duration' => $cdr->duration,
            'started_at' => $cdr->started_at->toISOString(),
            ...($isAdmin ? [
                'account_number' => $cdr->account->number,
                'user_name' => $cdr->account->user->name,
            ] : []),
        ])->values();

        $recentCdrs = $recentCdrs->map(fn ($cdr) => [
            'id' => $cdr->id,
            'uniqueid' => $cdr->uniqueid,
            'account_id' => $cdr->account_id,
            'dst' => $cdr->dst,
            'duration' => $cdr->duration,
            'billsec' => $cdr->billsec,
            'cost' => (float) $cdr->cost,
            'disposition' => $cdr->disposition,
            'started_at' => $cdr->started_at->toISOString(),
            'ended_at' => $cdr->ended_at?->toISOString(),
            ...($isAdmin ? [
                'account_number' => $cdr->account->number,
                'user_name' => $cdr->account->user->name,
            ] : []),
        ])->values();

        return view('dashboard', compact('account', 'activeCalls', 'recentCdrs', 'isAdmin'));
    }
}
