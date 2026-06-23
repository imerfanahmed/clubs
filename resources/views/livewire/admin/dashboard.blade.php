<div>
    <flux:heading size="xl">{{ __('Admin Dashboard') }}</flux:heading>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
        <div class="p-4 rounded-xl border border-neutral-200 dark:border-neutral-700">
            <div class="text-sm text-zinc-500">{{ __('Collected This Month') }}</div>
            <div class="text-2xl font-bold">{{ number_format($this->collectedThisMonth / 100, 2) }} GBP</div>
        </div>
        <div class="p-4 rounded-xl border border-neutral-200 dark:border-neutral-700">
            <div class="text-sm text-zinc-500">{{ __('Cumulative Total') }}</div>
            <div class="text-2xl font-bold">{{ number_format($this->cumulativeTotal / 100, 2) }} GBP</div>
        </div>
        <div class="p-4 rounded-xl border border-neutral-200 dark:border-neutral-700">
            <div class="text-sm text-zinc-500">{{ __('Monthly Recurring Revenue (MRR)') }}</div>
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
</div>
