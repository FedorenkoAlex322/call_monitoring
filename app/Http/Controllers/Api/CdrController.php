<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CdrResource;
use App\Models\Cdr;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CdrController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $account = $request->user()->account;

        $cdrs = $account->cdrs()
            ->completed()
            ->orderBy('ended_at', 'desc')
            ->paginate(20);

        return CdrResource::collection($cdrs);
    }

    public function show(Request $request, Cdr $cdr): CdrResource
    {
        abort_unless($cdr->account_id === $request->user()->account->id, 403);

        return new CdrResource($cdr);
    }
}
