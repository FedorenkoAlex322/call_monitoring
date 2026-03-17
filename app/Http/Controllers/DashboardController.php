<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $account = $request->user()->account->load('tariff');
        $activeCalls = $account->cdrs()->active()->orderBy('started_at', 'desc')->get();
        $recentCdrs = $account->cdrs()->completed()->orderBy('ended_at', 'desc')->limit(20)->get();

        return view('dashboard', compact('account', 'activeCalls', 'recentCdrs'));
    }
}
