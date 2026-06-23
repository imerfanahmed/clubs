<div>
    <flux:heading size="xl">{{ __('Expenses') }}</flux:heading>

    <div class="mt-8">
        <flux:heading size="lg">{{ __('Record Expense') }}</flux:heading>
        <form wire:submit="recordExpense" class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
            <flux:input wire:model="description" :label="__('Description')" type="text" required />
            <flux:input wire:model="amount" :label="__('Amount (£)')" type="number" step="0.01" min="0.01" required />
            <flux:input wire:model="category" :label="__('Category')" type="text" placeholder="e.g. Utilities, Rent, Supplies" />
            <flux:input wire:model="expenseDate" :label="__('Date')" type="date" required />

            <div class="md:col-span-2">
                <flux:field>
                    <flux:label>{{ __('Receipt (optional)') }}</flux:label>
                    <flux:input wire:model="receipt" type="file" accept="image/*" />
                    <flux:error name="receipt" />
                    <div wire:loading wire:target="receipt" class="text-sm text-zinc-500 mt-1">{{ __('Uploading...') }}</div>
                </flux:field>
            </div>

            <div class="md:col-span-2">
                <flux:button type="submit" variant="primary">
                    {{ __('Record Expense') }}
                </flux:button>
            </div>
        </form>
    </div>

    <div class="mt-8">
        <flux:heading size="lg">{{ __('All Expenses') }}</flux:heading>
        <div class="mt-4 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-zinc-50 dark:bg-zinc-800">
                        <th class="text-left p-3">{{ __('Date') }}</th>
                        <th class="text-left p-3">{{ __('Description') }}</th>
                        <th class="text-left p-3">{{ __('Category') }}</th>
                        <th class="text-right p-3">{{ __('Amount') }}</th>
                        <th class="text-left p-3">{{ __('Receipt') }}</th>
                        <th class="text-left p-3">{{ __('Recorded By') }}</th>
                        <th class="text-right p-3">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->expenses as $expense)
                        <tr class="border-t border-neutral-200 dark:border-neutral-700">
                            <td class="p-3">{{ $expense->expense_date->format('d M Y') }}</td>
                            <td class="p-3">{{ $expense->description }}</td>
                            <td class="p-3">
                                @if ($expense->category)
                                    <flux:badge size="sm">{{ $expense->category }}</flux:badge>
                                @else
                                    <span class="text-zinc-400">—</span>
                                @endif
                            </td>
                            <td class="p-3 text-right font-medium text-red-600 dark:text-red-400">{{ number_format($expense->amount / 100, 2) }} GBP</td>
                            <td class="p-3">
                                @if ($expense->receipt_path)
                                    <a href="{{ asset('storage/'.$expense->receipt_path) }}" target="_blank" class="inline-flex items-center gap-1 text-sm text-blue-600 dark:text-blue-400 hover:underline">
                                        <flux:icon name="document-text" class="w-4 h-4" />
                                        {{ __('View') }}
                                    </a>
                                @else
                                    <span class="text-zinc-400">—</span>
                                @endif
                            </td>
                            <td class="p-3">{{ $expense->creator?->name }}</td>
                            <td class="p-3 text-right">
                                <flux:button wire:click="deleteExpense({{ $expense->id }})" variant="danger" size="xs" onclick="return confirm('Are you sure?')">
                                    {{ __('Delete') }}
                                </flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr class="border-t border-neutral-200 dark:border-neutral-700">
                            <td class="p-3 text-center text-zinc-500" colspan="7">{{ __('No expenses recorded yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="border-t border-neutral-200 dark:border-neutral-700 bg-zinc-50 dark:bg-zinc-800 font-semibold">
                        <td class="p-3" colspan="3">{{ __('Total') }}</td>
                        <td class="p-3 text-right text-red-600 dark:text-red-400">{{ number_format($this->expenses->sum('amount') / 100, 2) }} GBP</td>
                        <td class="p-3" colspan="3"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
