<div>
    <flux:heading size="xl">{{ __('Dashboard') }}</flux:heading>

    @if (!auth()->user()->isAdmin())
        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="p-4 rounded-xl border border-neutral-200 dark:border-neutral-700">
                <div class="text-sm text-zinc-500">{{ __('Status') }}</div>
                <div class="text-2xl font-bold">
                    <flux:badge :color="match(auth()->user()->status) {
                        'active' => 'emerald',
                        'pending' => 'amber',
                        'suspended' => 'red',
                        'cancelled' => 'gray',
                        default => 'gray',
                    }" size="lg">{{ ucfirst(auth()->user()->status) }}</flux:badge>
                </div>
            </div>

            <div class="p-4 rounded-xl border border-neutral-200 dark:border-neutral-700">
                <div class="text-sm text-zinc-500">{{ __('Package') }}</div>
                <div class="text-2xl font-bold">{{ auth()->user()->package?->name ?? 'N/A' }}</div>
                <div class="text-sm text-zinc-500">{{ auth()->user()->package?->priceFormatted() ?? '' }}</div>
            </div>

            <div class="p-4 rounded-xl border border-neutral-200 dark:border-neutral-700">
                <div class="text-sm text-zinc-500">{{ __('Member Since') }}</div>
                <div class="text-2xl font-bold">{{ auth()->user()->created_at->format('d M Y') }}</div>
            </div>
        </div>
    @endif

    @if (auth()->user()->isAdmin())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
            <div class="p-4 rounded-xl border border-neutral-200 dark:border-neutral-700">
                <div class="text-sm text-zinc-500">{{ __('Collected This Month') }}</div>
                <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($this->collectedThisMonth / 100, 2) }} GBP</div>
            </div>
            <div class="p-4 rounded-xl border border-neutral-200 dark:border-neutral-700">
                <div class="text-sm text-zinc-500">{{ __('Expenses This Month') }}</div>
                <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ number_format($this->totalExpensesThisMonth / 100, 2) }} GBP</div>
            </div>
            <div class="p-4 rounded-xl border border-neutral-200 dark:border-neutral-700">
                <div class="text-sm text-zinc-500">{{ __('Net This Month') }}</div>
                <div class="text-2xl font-bold {{ $this->netThisMonth >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">{{ number_format($this->netThisMonth / 100, 2) }} GBP</div>
            </div>
            <div class="p-4 rounded-xl border border-neutral-200 dark:border-neutral-700">
                <div class="text-sm text-zinc-500">{{ __('MRR') }}</div>
                <div class="text-2xl font-bold">{{ number_format($this->mrr / 100, 2) }} GBP</div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
            <div class="p-4 rounded-xl border border-neutral-200 dark:border-neutral-700">
                <div class="text-sm text-zinc-500">{{ __('Pending') }}</div>
                <div class="text-2xl font-bold">{{ $this->pendingCount }}</div>
            </div>
            <div class="p-4 rounded-xl border border-neutral-200 dark:border-neutral-700">
                <div class="text-sm text-zinc-500">{{ __('Active') }}</div>
                <div class="text-2xl font-bold">{{ $this->activeCount }}</div>
            </div>
            <div class="p-4 rounded-xl border border-neutral-200 dark:border-neutral-700">
                <div class="text-sm text-zinc-500">{{ __('Suspended') }}</div>
                <div class="text-2xl font-bold">{{ $this->suspendedCount }}</div>
            </div>
            <div class="p-4 rounded-xl border border-neutral-200 dark:border-neutral-700">
                <div class="text-sm text-zinc-500">{{ __('Unpaid This Month') }}</div>
                <div class="text-2xl font-bold">{{ $this->unpaidCount }}</div>
            </div>
        </div>

        <div class="mt-8">
            <flux:heading size="lg">{{ __('Monthly Income vs Expenses') }}</flux:heading>
            <div class="mt-4 p-4 rounded-xl border border-neutral-200 dark:border-neutral-700">
                <canvas id="incomeChart" x-data="{}"
                    x-init="new Chart($el, {
                        type: 'bar',
                        data: {
                            labels: {{ json_encode($this->monthlyIncome['labels']) }},
                            datasets: [
                                {
                                    label: 'Income',
                                    data: {{ json_encode($this->monthlyIncome['totals']) }},
                                    backgroundColor: '#22c55e',
                                    borderRadius: 4,
                                },
                                {
                                    label: 'Expenses',
                                    data: {{ json_encode($this->monthlyExpenses['totals']) }},
                                    backgroundColor: '#ef4444',
                                    borderRadius: 4,
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'top',
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(ctx) {
                                            return ctx.dataset.label + ': £' + ctx.parsed.y.toFixed(2);
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return '£' + value.toFixed(2);
                                        }
                                    }
                                }
                            }
                        }
                    })">
                </canvas>
            </div>
        </div>

        <div class="mt-8">
            <flux:heading size="lg">{{ __('Cumulative Total') }}</flux:heading>
            <div class="mt-2 text-3xl font-bold">{{ number_format($this->cumulativeTotal / 100, 2) }} GBP</div>
        </div>

        <div class="mt-8">
            <flux:heading size="lg">{{ __('Per-User Contributions') }}</flux:heading>
            <div class="mt-4 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-zinc-50 dark:bg-zinc-800">
                            <th class="text-left p-3">{{ __('Name') }}</th>
                            <th class="text-left p-3">{{ __('Email') }}</th>
                            <th class="text-right p-3">{{ __('Total Paid') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->perUserContributions as $user)
                            <tr class="border-t border-neutral-200 dark:border-neutral-700">
                                <td class="p-3">{{ $user->name }}</td>
                                <td class="p-3">{{ $user->email }}</td>
                                <td class="p-3 text-right">{{ number_format($user->total_paid / 100, 2) }} GBP</td>
                            </tr>
                        @empty
                            <tr class="border-t border-neutral-200 dark:border-neutral-700">
                                <td class="p-3 text-center text-zinc-500" colspan="3">{{ __('No payments yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="mt-8">
        <flux:heading size="lg">{{ __('Payment History') }}</flux:heading>
        <div class="mt-4 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-zinc-50 dark:bg-zinc-800">
                        <th class="text-left p-3">{{ __('Date') }}</th>
                        <th class="text-left p-3">{{ __('Amount') }}</th>
                        <th class="text-left p-3">{{ __('Status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse (auth()->user()->payments()->latest()->get() as $payment)
                        <tr class="border-t border-neutral-200 dark:border-neutral-700">
                            <td class="p-3">{{ $payment->paid_at?->format('d M Y') ?? $payment->created_at->format('d M Y') }}</td>
                            <td class="p-3">{{ number_format($payment->amount / 100, 2) }} {{ $payment->currency }}</td>
                            <td class="p-3">
                                <flux:badge :color="match($payment->status) {
                                    'paid' => 'emerald',
                                    'failed' => 'red',
                                    'refunded' => 'blue',
                                    default => 'gray',
                                }">{{ ucfirst($payment->status) }}</flux:badge>
                            </td>
                        </tr>
                    @empty
                        <tr class="border-t border-neutral-200 dark:border-neutral-700">
                            <td class="p-3 text-center text-zinc-500" colspan="3">{{ __('No payments yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if (auth()->user()->status === 'active')
        <div class="mt-8">
            <flux:heading size="lg">{{ __('Actions') }}</flux:heading>
            <div class="mt-4 flex gap-2">
                <flux:button wire:click="cancelSubscription" variant="danger" onclick="return confirm('Are you sure you want to cancel your subscription?')">
                    {{ __('Cancel Subscription') }}
                </flux:button>
            </div>
        </div>
    @endif
</div>
