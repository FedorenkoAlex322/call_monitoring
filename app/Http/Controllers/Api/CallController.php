<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActiveCallResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CallController extends Controller
{
    public function active(Request $request): AnonymousResourceCollection
    {
        $account = $request->user()->account;

        $activeCalls = $account->cdrs()
            ->active()
            ->orderBy('started_at', 'desc')
            ->get();

        return ActiveCallResource::collection($activeCalls);
    }
}
