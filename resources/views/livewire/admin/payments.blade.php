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
                            <th class="text-left p-3">{{ __('Reason') }}</th>
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
                                    @if ($payment->reason)
                                        <flux:badge size="sm" color="blue">{{ $payment->reason }}</flux:badge>
                                        @if ($payment->reason_description)
                                            <div class="text-xs text-neutral-500 mt-1">{{ $payment->reason_description }}</div>
                                        @endif
                                    @else
                                        <span class="text-xs text-neutral-400">—</span>
                                    @endif
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
                                <td class="p-6 text-center text-zinc-500 dark:text-zinc-400" colspan="6">
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

                        @if ($userId)
                            <div class="flex items-center gap-2 p-2 rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800">
                                <span class="text-sm flex-1">{{ $selectedMemberName }}</span>
                                <button type="button" wire:click="clearMember" class="text-zinc-400 hover:text-zinc-600">
                                    <flux:icon name="x-mark" class="w-4 h-4" />
                                </button>
                            </div>
                        @else
                            <div class="relative" wire:ignore x-data="{ open: false }">
                                <flux:input
                                    wire:model.live="memberSearch"
                                    x-on:focus="open = true"
                                    x-on:click.away="open = false"
                                    x-on:keydown.escape="open = false"
                                    placeholder="{{ __('Search by name or email...') }}"
                                />
                                <div x-show="open && $wire.memberSearch.length > 0"
                                     class="absolute z-50 mt-1 w-full rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800 shadow-lg max-h-60 overflow-y-auto">
                                    @foreach ($this->searchMembers as $member)
                                        <button type="button"
                                                wire:click="selectMember({{ $member->id }})"
                                                x-on:click="open = false"
                                                class="w-full text-left px-3 py-2 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700 border-b border-neutral-100 dark:border-neutral-700 last:border-0">
                                            <div class="font-medium">{{ $member->name }}</div>
                                            <div class="text-xs text-zinc-500">{{ $member->email }}</div>
                                        </button>
                                    @endforeach
                                    <div x-show="$wire.searchMembers.length === 0" class="px-3 py-4 text-sm text-zinc-500 text-center">
                                        {{ __('No members found.') }}
                                    </div>
                                </div>
                            </div>
                        @endif

                        <flux:error name="userId" />
                    </flux:field>

                    <!-- Amount -->
                    <flux:field>
                        <flux:label>{{ __('Amount (£)') }}</flux:label>
                        <flux:input wire:model="amount" type="number" step="0.01" min="0.01" required placeholder="0.00" />
                        <flux:error name="amount" />
                    </flux:field>

                    <!-- Reason -->
                    <flux:field>
                        <flux:label>{{ __('Reason') }}</flux:label>
                        <flux:select wire:model.live="reason" required>
                            <option value="">{{ __('Select a reason...') }}</option>
                            <option value="Membership Fee">{{ __('Membership Fee') }}</option>
                            <option value="Event Fee">{{ __('Event Fee') }}</option>
                            <option value="Donation">{{ __('Donation') }}</option>
                            <option value="Late Fee">{{ __('Late Fee') }}</option>
                            <option value="Other">{{ __('Other') }}</option>
                        </flux:select>
                        <flux:error name="reason" />
                    </flux:field>

                    @if ($reason === 'Other')
                        <flux:field>
                            <flux:label>{{ __('Description') }}</flux:label>
                            <flux:textarea wire:model="reasonDescription" rows="3" placeholder="{{ __('Describe the reason for this payment...') }}" />
                            <flux:error name="reasonDescription" />
                        </flux:field>
                    @endif

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
