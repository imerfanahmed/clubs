<div class="space-y-6">
    <div class="flex items-center gap-4">
        <flux:button icon="arrow-left" :href="route('admin.members')" wire:navigate variant="ghost" />
        <div>
            <flux:heading size="xl">{{ $user->name }}</flux:heading>
            <flux:subheading>{{ $user->email }}</flux:subheading>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="p-4 rounded-xl border border-neutral-200 dark:border-neutral-700">
            <div class="text-sm text-zinc-500">{{ __('Status') }}</div>
            <div class="mt-1">
                <flux:badge :color="match($user->status) {
                    'active' => 'emerald',
                    'pending' => 'amber',
                    'suspended' => 'red',
                    'cancelled' => 'gray',
                    default => 'gray',
                }" size="lg">{{ ucfirst($user->status) }}</flux:badge>
            </div>
        </div>
        <div class="p-4 rounded-xl border border-neutral-200 dark:border-neutral-700">
            <div class="text-sm text-zinc-500">{{ __('Package') }}</div>
            <div class="text-xl font-bold">{{ $user->package?->name ?? 'N/A' }}</div>
            <div class="text-sm text-zinc-500">{{ $user->package?->priceFormatted() ?? '' }}</div>
        </div>
        <div class="p-4 rounded-xl border border-neutral-200 dark:border-neutral-700">
            <div class="text-sm text-zinc-500">{{ __('Total Paid') }}</div>
            <div class="text-xl font-bold text-emerald-600 dark:text-emerald-400">£{{ number_format($this->totalPaid / 100, 2) }}</div>
        </div>
        <div class="p-4 rounded-xl border border-neutral-200 dark:border-neutral-700">
            <div class="text-sm text-zinc-500">{{ __('This Month') }}</div>
            <div class="text-xl font-bold">
                @if ($this->isPaidThisMonth)
                    <span class="text-emerald-600 dark:text-emerald-400">{{ __('Paid') }}</span>
                @else
                    <span class="text-red-600 dark:text-red-400">{{ __('Unpaid') }}</span>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="p-6 rounded-xl border border-neutral-200 dark:border-neutral-700">
            <flux:heading size="lg" class="mb-4">{{ __('Personal Details') }}</flux:heading>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-zinc-500">{{ __('Name') }}</dt>
                    <dd class="font-medium">{{ $user->name }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-zinc-500">{{ __('Email') }}</dt>
                    <dd class="font-medium">{{ $user->email }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-zinc-500">{{ __('Phone') }}</dt>
                    <dd class="font-medium">{{ $user->phone ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-zinc-500">{{ __('Member Since') }}</dt>
                    <dd class="font-medium">{{ $user->created_at->format('d M Y') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-zinc-500">{{ __('Approved At') }}</dt>
                    <dd class="font-medium">{{ $user->approved_at?->format('d M Y') ?? '—' }}</dd>
                </div>
            </dl>
        </div>

        <div class="p-6 rounded-xl border border-neutral-200 dark:border-neutral-700">
            <flux:heading size="lg" class="mb-4">{{ __('Address') }}</flux:heading>
            @if ($user->address)
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-zinc-500">{{ __('Line 1') }}</dt>
                        <dd class="font-medium">{{ $user->address->line_1 }}</dd>
                    </div>
                    @if ($user->address->line_2)
                        <div class="flex justify-between">
                            <dt class="text-zinc-500">{{ __('Line 2') }}</dt>
                            <dd class="font-medium">{{ $user->address->line_2 }}</dd>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <dt class="text-zinc-500">{{ __('City') }}</dt>
                        <dd class="font-medium">{{ $user->address->city }}</dd>
                    </div>
                    @if ($user->address->county)
                        <div class="flex justify-between">
                            <dt class="text-zinc-500">{{ __('County') }}</dt>
                            <dd class="font-medium">{{ $user->address->county }}</dd>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <dt class="text-zinc-500">{{ __('Postcode') }}</dt>
                        <dd class="font-medium">{{ $user->address->postcode }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-zinc-500">{{ __('Country') }}</dt>
                        <dd class="font-medium">{{ $user->address->country }}</dd>
                    </div>
                </dl>
            @else
                <p class="text-sm text-zinc-500">{{ __('No address recorded.') }}</p>
            @endif
        </div>
    </div>

    <div class="p-6 rounded-xl border border-neutral-200 dark:border-neutral-700">
        <flux:heading size="lg" class="mb-4">{{ __('Payment History') }}</flux:heading>
        <div class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-zinc-50 dark:bg-zinc-800">
                        <th class="text-left p-3">{{ __('Date') }}</th>
                        <th class="text-left p-3">{{ __('Amount') }}</th>
                        <th class="text-left p-3">{{ __('Reason') }}</th>
                        <th class="text-left p-3">{{ __('Status') }}</th>
                        <th class="text-left p-3">{{ __('Type') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($user->payments()->latest('paid_at')->get() as $payment)
                        <tr class="border-t border-neutral-200 dark:border-neutral-700">
                            <td class="p-3">{{ $payment->paid_at?->format('d M Y') ?? $payment->created_at->format('d M Y') }}</td>
                            <td class="p-3 font-medium">£{{ number_format($payment->amount / 100, 2) }}</td>
                            <td class="p-3">
                                @if ($payment->reason)
                                    <flux:badge size="sm" color="blue">{{ $payment->reason }}</flux:badge>
                                @else
                                    <span class="text-zinc-400">—</span>
                                @endif
                            </td>
                            <td class="p-3">
                                <flux:badge :color="match($payment->status) {
                                    'paid' => 'emerald',
                                    'failed' => 'red',
                                    'refunded' => 'blue',
                                    default => 'gray',
                                }">{{ ucfirst($payment->status) }}</flux:badge>
                            </td>
                            <td class="p-3">
                                @if ($payment->stripe_invoice_id && str_starts_with($payment->stripe_invoice_id, 'manual_'))
                                    <flux:badge color="amber">{{ __('Manual') }}</flux:badge>
                                @else
                                    <flux:badge color="blue">{{ __('Stripe') }}</flux:badge>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr class="border-t border-neutral-200 dark:border-neutral-700">
                            <td class="p-3 text-center text-zinc-500" colspan="5">{{ __('No payments yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
