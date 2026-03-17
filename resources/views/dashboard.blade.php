@extends('layouts.app')

@section('content')
<div x-data="dashboard()" x-init="init()">

    <!-- Balance Card -->
    <div class="mb-8">
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400">Account</p>
                    <p class="text-lg font-semibold text-white">{{ $account->number }} &mdash; {{ $account->name }}</p>
                    <p class="text-sm text-gray-400 mt-1">Tariff: {{ $account->tariff->name }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-400">Balance</p>
                    <p class="text-3xl font-bold" :class="{
                        'text-green-400': balance > 50,
                        'text-yellow-400': balance > 0 && balance <= 50,
                        'text-red-400': balance <= 0
                    }" x-text="balance.toFixed(2) + ' RUB'"></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Calls -->
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-white mb-4">
            Active Calls
            <span class="ml-2 text-sm font-normal text-gray-400" x-text="'(' + activeCalls.length + ')'"></span>
        </h2>
        <div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
            <template x-if="activeCalls.length === 0">
                <div class="p-8 text-center text-gray-500">No active calls</div>
            </template>
            <template x-if="activeCalls.length > 0">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-700">
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Source</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Destination</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Duration</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="call in activeCalls" :key="call.id">
                            <tr class="border-b border-gray-700/50">
                                <td class="px-4 py-3 text-sm text-white" x-text="call.src"></td>
                                <td class="px-4 py-3 text-sm text-gray-300" x-text="call.dst"></td>
                                <td class="px-4 py-3 text-sm text-white font-mono" x-text="formatDuration(call.duration)"></td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-500/10 text-green-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-400 mr-1.5 animate-pulse"></span>
                                        Active
                                    </span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </template>
        </div>
    </div>

    <!-- Recent CDRs -->
    <div>
        <h2 class="text-lg font-semibold text-white mb-4">
            Recent Calls
            <span class="ml-2 text-sm font-normal text-gray-400" x-text="'(' + recentCdrs.length + ')'"></span>
        </h2>
        <div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
            <template x-if="recentCdrs.length === 0">
                <div class="p-8 text-center text-gray-500">No call records</div>
            </template>
            <template x-if="recentCdrs.length > 0">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-700">
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Source</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Destination</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Duration</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Cost</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="cdr in recentCdrs" :key="cdr.id">
                            <tr class="border-b border-gray-700/50">
                                <td class="px-4 py-3 text-sm text-white" x-text="cdr.src"></td>
                                <td class="px-4 py-3 text-sm text-gray-300" x-text="cdr.dst"></td>
                                <td class="px-4 py-3 text-sm text-white font-mono" x-text="formatDuration(cdr.duration)"></td>
                                <td class="px-4 py-3 text-sm text-white font-mono" x-text="parseFloat(cdr.cost).toFixed(2) + ' RUB'"></td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                        :class="{
                                            'bg-green-500/10 text-green-400': cdr.disposition === 'ANSWERED',
                                            'bg-gray-500/10 text-gray-400': cdr.disposition === 'NO ANSWER',
                                            'bg-yellow-500/10 text-yellow-400': cdr.disposition === 'BUSY',
                                            'bg-red-500/10 text-red-400': cdr.disposition === 'FAILED'
                                        }"
                                        x-text="cdr.disposition">
                                    </span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </template>
        </div>
    </div>
</div>

<script>
function dashboard() {
    return {
        balance: {{ $account->balance }},
        accountId: {{ $account->id }},
        activeCalls: @json($activeCalls),
        recentCdrs: @json($recentCdrs),

        init() {
            window.Echo.channel('calls')
                .listen('.call.started', (e) => {
                    if (e.account_id === this.accountId) {
                        this.activeCalls.unshift(e);
                    }
                })
                .listen('.call.updated', (e) => {
                    let call = this.activeCalls.find(c => c.id === e.id);
                    if (call) {
                        call.duration = e.duration;
                    }
                })
                .listen('.call.ended', (e) => {
                    if (e.account_id === this.accountId) {
                        this.activeCalls = this.activeCalls.filter(c => c.id !== e.id);
                        this.recentCdrs.unshift(e);
                        if (this.recentCdrs.length > 20) {
                            this.recentCdrs.pop();
                        }
                    }
                });

            window.Echo.private('account.' + this.accountId)
                .listen('.balance.updated', (e) => {
                    this.balance = e.balance;
                });
        },

        formatDuration(seconds) {
            const mins = Math.floor(seconds / 60);
            const secs = seconds % 60;
            return String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
        }
    }
}
</script>
@endsection
