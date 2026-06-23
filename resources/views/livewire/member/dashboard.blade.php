<div>
    <flux:heading size="xl">{{ __('My Membership') }}</flux:heading>

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
