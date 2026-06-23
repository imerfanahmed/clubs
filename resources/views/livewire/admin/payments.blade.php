<div class="space-y-6">
    <div>
        <flux:heading size="xl">{{ __('Payments') }}</flux:heading>
        <flux:subheading>{{ __('Manage recorded member payments and log manual cash/bank transfers.') }}</flux:subheading>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Payments List -->
        <div class="lg:col-span-2 space-y-4">
            <div class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-850 shadow-sm">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-zinc-50 dark:bg-zinc-800 text-neutral-600 dark:text-neutral-300 font-medium border-b border-neutral-200 dark:border-neutral-700">
                            <th class="text-left p-3">{{ __('Member') }}</th>
                            <th class="text-left p-3">{{ __('Amount') }}</th>
                            <th class="text-left p-3">{{ __('Payment Date') }}</th>
                            <th class="text-left p-3">{{ __('Type / Status') }}</th>
                            <th class="text-left p-3">{{ __('Paid To') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->payments as $payment)
                            <tr class="border-t border-neutral-200 dark:border-neutral-700 text-neutral-800 dark:text-neutral-200">
                                <td class="p-3">
                                    <div class="font-medium">{{ $payment->user->name }}</div>
                                    <div class="text-xs text-neutral-500">{{ $payment->user->email }}</div>
                                </td>
                                <td class="p-3 font-semibold text-emerald-600 dark:text-emerald-400">
                                    £{{ number_format($payment->amount / 100, 2) }}
                                </td>
                                <td class="p-3">
                                    {{ $payment->paid_at ? $payment->paid_at->format('d M Y') : $payment->created_at->format('d M Y') }}
                                </td>
                                <td class="p-3">
                                    @if ($payment->stripe_invoice_id && str_starts_with($payment->stripe_invoice_id, 'manual_'))
                                        <flux:badge color="amber">{{ __('Manual') }}</flux:badge>
                                    @else
                                        <flux:badge color="blue">{{ __('Stripe') }}</flux:badge>
                                    @endif
                                </td>
                                <td class="p-3">
                                    @if ($payment->paidTo)
                                        <span class="text-xs font-medium bg-neutral-100 dark:bg-neutral-700 px-2.5 py-1 rounded text-neutral-700 dark:text-neutral-300">
                                            {{ $payment->paidTo->name }}
                                        </span>
                                    @else
                                        <span class="text-xs text-neutral-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="p-6 text-center text-zinc-500 dark:text-zinc-400" colspan="5">
                                    {{ __('No payments recorded yet.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Record Manual Payment Form -->
        <div class="space-y-4">
            <div class="p-6 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-850 shadow-sm">
                <flux:heading size="lg">{{ __('Record Manual Payment') }}</flux:heading>
                <flux:subheading class="mb-4">{{ __('Record a cash or bank transfer payment from a member.') }}</flux:subheading>

                <form wire:submit="recordPayment" class="space-y-4">
                    <!-- Select Member -->
                    <flux:field>
                        <flux:label>{{ __('Select Member') }}</flux:label>
                        <flux:select wire:model.live="userId" required>
                            <option value="">{{ __('Choose a member...') }}</option>
                            @foreach ($this->members as $member)
                                <option value="{{ $member->id }}">{{ $member->name }} ({{ $member->email }})</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="userId" />
                    </flux:field>

                    <!-- Amount -->
                    <flux:field>
                        <flux:label>{{ __('Amount (£)') }}</flux:label>
                        <flux:input wire:model="amount" type="number" step="0.01" min="0.01" required placeholder="0.00" />
                        <flux:error name="amount" />
                    </flux:field>

                    <!-- Package (Optional) -->
                    <flux:field>
                        <flux:label>{{ __('Package (Optional)') }}</flux:label>
                        <flux:select wire:model="packageId">
                            <option value="">{{ __('None / One-off') }}</option>
                            @foreach ($this->packages as $package)
                                <option value="{{ $package->id }}">{{ $package->name }} (£{{ number_format($package->price / 100, 2) }})</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="packageId" />
                    </flux:field>

                    <!-- Payment Date -->
                    <flux:field>
                        <flux:label>{{ __('Payment Date') }}</flux:label>
                        <flux:input wire:model="paidAt" type="date" required />
                        <flux:error name="paidAt" />
                    </flux:field>

                    <!-- Paid To -->
                    <flux:field>
                        <flux:label>{{ __('Paid To') }}</flux:label>
                        <flux:select wire:model="paidToId" required>
                            @foreach ($this->users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="paidToId" />
                    </flux:field>

                    <flux:button type="submit" variant="primary" class="w-full">
                        {{ __('Record Payment') }}
                    </flux:button>
                </form>
            </div>
        </div>
    </div>
</div>
