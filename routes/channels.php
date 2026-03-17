<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Public channel "calls" is used without authorization for monitoring
| all active calls. Private channel "account.{id}" requires the
| authenticated user to own the account.
|
*/

Broadcast::channel('account.{accountId}', function ($user, int $accountId) {
    return $user->account?->id === $accountId;
});
