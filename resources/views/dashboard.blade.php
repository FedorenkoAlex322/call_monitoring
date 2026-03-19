@extends('layouts.app')

@section('content')
<div x-data="dashboard()" x-init="init()">

    <!-- Balance Card -->
    <div class="mb-8">
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400">Account</p>
                    <p class="text-lg font-semibold text-white">
                        {{ $account->number }} &mdash; {{ $account->name }}
                        @if($isAdmin)
                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-500/10 text-purple-400">Admin</span>
                        @endif
                    </p>
                    <p class="text-sm text-gray-400 mt-1">Tariff: {{ $account->tariff->name }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-400">Balance</p>
                    <p class="text-3xl font-bold" :class="{
                        'text-green-400': balance > 50,
                        'text-yellow-400': balance > 0 && balance <= 50,
                        'text-red-400': balance <= 0
                    }" x-text="balance.toFixed(2) + ' UAH'"></p>
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
            <div x-show="activeCalls.length === 0" class="p-8 text-center text-gray-500">No active calls</div>
            <table x-show="activeCalls.length > 0" class="w-full">
                <thead>
                    <tr class="border-b border-gray-700">
                        @if($isAdmin)
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Account</th>
                        @endif
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Destination</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Duration</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Started At</th>
                    </tr>
                </thead>
                <tbody x-html="activeCallsHtml"></tbody>
            </table>
        </div>
    </div>

    <!-- Recent CDRs -->
    <div>
        <h2 class="text-lg font-semibold text-white mb-4">
            Recent Calls
            <span class="ml-2 text-sm font-normal text-gray-400" x-text="'(' + recentCdrs.length + ')'"></span>
        </h2>
        <div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
            <div x-show="recentCdrs.length === 0" class="p-8 text-center text-gray-500">No call records</div>
            <table x-show="recentCdrs.length > 0" class="w-full">
                <thead>
                    <tr class="border-b border-gray-700">
                        @if($isAdmin)
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Account</th>
                        @endif
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Destination</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Duration</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Cost</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Disposition</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Started At</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Ended At</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Billsec</th>
                    </tr>
                </thead>
                <tbody x-html="recentCdrsHtml"></tbody>
            </table>
        </div>
    </div>
</div>

<script>
function dashboard() {
    return {
        isAdmin: @json($isAdmin),
        balance: {{ $account->balance }},
        accountId: {{ $account->id }},
        activeCalls: @json($activeCalls),
        recentCdrs: @json($recentCdrs),

        get activeCallsHtml() {
            return this.activeCalls.map(call => `
                <tr class="border-b border-gray-700/50">
                    ${this.isAdmin ? `<td class="px-4 py-3 text-sm text-gray-300">${call.account_number} — ${call.user_name}</td>` : ''}
                    <td class="px-4 py-3 text-sm text-white">${call.dst}</td>
                    <td class="px-4 py-3 text-sm text-white font-mono">${this.formatDuration(call.duration)}</td>
                    <td class="px-4 py-3 text-sm text-gray-300">${this.formatTime(call.started_at)}</td>
                </tr>
            `).join('');
        },

        get recentCdrsHtml() {
            return this.recentCdrs.map(cdr => `
                <tr class="border-b border-gray-700/50">
                    ${this.isAdmin ? `<td class="px-4 py-3 text-sm text-gray-300">${cdr.account_number} — ${cdr.user_name}</td>` : ''}
                    <td class="px-4 py-3 text-sm text-white">${cdr.dst}</td>
                    <td class="px-4 py-3 text-sm text-white font-mono">${this.formatDuration(cdr.duration)}</td>
                    <td class="px-4 py-3 text-sm text-white font-mono">${parseFloat(cdr.cost).toFixed(2)} UAH</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${this.dispositionClass(cdr.disposition)}">
                            ${cdr.disposition}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-300">${this.formatTime(cdr.started_at)}</td>
                    <td class="px-4 py-3 text-sm text-gray-300">${this.formatTime(cdr.ended_at)}</td>
                    <td class="px-4 py-3 text-sm text-white font-mono">${this.formatDuration(cdr.billsec)}</td>
                </tr>
            `).join('');
        },

        init() {
            window.Echo.leaveChannel('calls');
            window.Echo.leaveChannel('private-account.' + this.accountId);

            window.Echo.channel('calls')
                .listen('.call.started', (e) => {
                    if (this.isAdmin || e.account_id === this.accountId) {
                        this.activeCalls = [e, ...this.activeCalls];
                    }
                })
                .listen('.call.updated', (e) => {
                    this.activeCalls = this.activeCalls.map(c =>
                        c.uniqueid === e.uniqueid ? {...c, duration: e.duration} : c
                    );
                })
                .listen('.call.ended', (e) => {
                    if (this.isAdmin || e.account_id === this.accountId) {
                        this.activeCalls = this.activeCalls.filter(c => c.uniqueid !== e.uniqueid);
                        this.recentCdrs = [e, ...this.recentCdrs.slice(0, 19)];
                    }
                });

            window.Echo.private('account.' + this.accountId)
                .listen('.balance.updated', (e) => {
                    this.balance = e.balance;
                });
        },

        formatTime(isoString) {
            if (!isoString) return '—';
            const d = new Date(isoString);
            return d.toLocaleTimeString('uk-UA', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        },

        formatDuration(seconds) {
            const mins = Math.floor(seconds / 60);
            const secs = seconds % 60;
            return String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
        },

        dispositionClass(disposition) {
            const map = {
                'ANSWERED': 'bg-green-500/10 text-green-400',
                'NO ANSWER': 'bg-gray-500/10 text-gray-400',
                'BUSY': 'bg-yellow-500/10 text-yellow-400',
                'FAILED': 'bg-red-500/10 text-red-400'
            };
            return map[disposition] || 'bg-gray-500/10 text-gray-400';
        }
    }
}
</script>
@endsection
