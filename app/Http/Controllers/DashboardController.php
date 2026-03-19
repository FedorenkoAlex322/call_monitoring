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
            $activeCalls = Cdr::active()->orderBy('started_at', 'desc')->get();
            $recentCdrs = Cdr::completed()->orderBy('ended_at', 'desc')->limit(20)->get();
        } else {
            $activeCalls = $account->cdrs()->active()->orderBy('started_at', 'desc')->get();
            $recentCdrs = $account->cdrs()->completed()->orderBy('ended_at', 'desc')->limit(20)->get();
        }

        $activeCalls = $activeCalls->map(fn ($cdr) => [
            'id' => $cdr->id,
            'uniqueid' => $cdr->uniqueid,
            'account_id' => $cdr->account_id,
            'src' => $cdr->src,
            'dst' => $cdr->dst,
            'duration' => $cdr->duration,
            'started_at' => $cdr->started_at->toISOString(),
            'status' => $cdr->status,
        ])->values();

        $recentCdrs = $recentCdrs->map(fn ($cdr) => [
            'id' => $cdr->id,
            'uniqueid' => $cdr->uniqueid,
            'account_id' => $cdr->account_id,
            'src' => $cdr->src,
            'dst' => $cdr->dst,
            'duration' => $cdr->duration,
            'billsec' => $cdr->billsec,
            'cost' => (float) $cdr->cost,
            'disposition' => $cdr->disposition,
            'ended_at' => $cdr->ended_at?->toISOString(),
        ])->values();

        return view('dashboard', compact('account', 'activeCalls', 'recentCdrs', 'isAdmin'));
    }
}
