<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
        <div class="relative grid h-dvh flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0">
            <div class="bg-muted relative hidden h-full flex-col p-10 text-white lg:flex dark:border-e dark:border-neutral-800">
                <div class="absolute inset-0 bg-gradient-to-br from-emerald-900 via-emerald-800 to-teal-900"></div>
                <div class="absolute inset-0 opacity-[0.06] pointer-events-none"
                     style="background-image: url('data:image/svg+xml,%3Csvg width=\'80\' height=\'80\' viewBox=\'0 0 100 100\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cpath d=\'M50 5 L95 50 L50 95 L5 50 Z\' fill=\'none\' stroke=\'%23ffffff\' stroke-width=\'1\'/%3E%3Cpath d=\'M50 20 L80 50 L50 80 L20 50 Z\' fill=\'none\' stroke=\'%23ffffff\' stroke-width=\'1\'/%3E%3C/svg%3E'); background-size: 100px 100px;">
                </div>
                <div class="absolute -top-20 -right-20 w-72 h-72 bg-amber-500/15 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-20 -left-20 w-72 h-72 bg-emerald-400/10 rounded-full blur-3xl"></div>

                <a href="{{ route('home') }}" class="relative z-20 flex items-center gap-3" wire:navigate>
                    <span class="font-['Amiri'] text-3xl text-amber-300" dir="rtl">﷽</span>
                    <div class="flex flex-col">
                        <span class="text-base font-semibold tracking-wide">Markaz Club</span>
                        <span class="text-xs text-emerald-200/70">Bangladesh Community</span>
                    </div>
                </a>

                @php
                    [$message, $author] = str(Illuminate\Foundation\Inspiring::quotes()->random())->explode('-');
                @endphp

                <div class="relative z-20 mt-auto">
                    <blockquote class="space-y-2">
                        <flux:heading size="lg">&ldquo;{{ trim($message) }}&rdquo;</flux:heading>
                        <footer><flux:heading>{{ trim($author) }}</flux:heading></footer>
                    </blockquote>
                </div>
            </div>
            <div class="w-full lg:p-8">
                <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px]">
                    <a href="{{ route('home') }}" class="z-20 flex flex-col items-center gap-1 font-medium lg:hidden" wire:navigate>
                        <span class="font-['Amiri'] text-2xl leading-none text-emerald-600 dark:text-emerald-400" dir="rtl">﷽</span>
                        <span class="text-sm font-semibold tracking-wide text-emerald-700 dark:text-emerald-300">Markaz Club</span>
                    </a>
                    {{ $slot }}
                </div>
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
