@props(['label' => null, 'valueText' => null, 'percent' => 0])

<div>
    @if ($label || $valueText)
        <div class="mb-1 flex items-end justify-between gap-2">
            <span class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ $label }}</span>
            <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ $valueText }}</span>
        </div>
    @endif
    <div class="h-3 w-full overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700">
        <div class="h-full rounded-full bg-gradient-to-r from-emerald-600 to-emerald-400 transition-all"
             style="width: {{ $percent }}%"></div>
    </div>
    <div class="mt-1 text-right text-xs font-semibold text-emerald-700 dark:text-emerald-400">{{ $percent }}%</div>
</div>
