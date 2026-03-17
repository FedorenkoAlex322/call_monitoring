<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AccountResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function show(Request $request): AccountResource
    {
        return new AccountResource($request->user()->account->load('tariff'));
    }

    public function balance(Request $request): JsonResponse
    {
        $account = $request->user()->account;

        return response()->json([
            'account_id' => $account->id,
            'balance' => (float) $account->balance,
            'number' => $account->number,
        ]);
    }
}
