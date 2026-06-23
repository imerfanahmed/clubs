<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-zinc-50 antialiased dark:bg-zinc-900">
        <header class="border-b border-emerald-900/10 bg-gradient-to-r from-emerald-950 via-emerald-900 to-emerald-800">
            <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-4 sm:px-6">
                <a href="{{ route('home') }}" class="flex items-center gap-3" wire:navigate>
                    <span class="font-['Amiri'] text-2xl leading-none text-amber-300" dir="rtl">﷽</span>
                    <span class="text-sm font-semibold tracking-wide text-white">
                        {{ config('app.name', 'Markaz Club') }}
                    </span>
                </a>

                <nav class="flex items-center gap-2 sm:gap-3">
                    <a href="{{ route('campaigns.index') }}"
                       class="rounded-lg px-3 py-2 text-sm font-medium text-emerald-100 transition hover:bg-white/5">
                        Campaigns
                    </a>
                    @auth
                        <a href="{{ route('dashboard') }}"
                           class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-emerald-950 transition hover:bg-amber-400">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="rounded-lg border border-white/20 px-4 py-2 text-sm font-medium text-white transition hover:bg-white/5">
                            Login
                        </a>
                    @endauth
                </nav>
            </div>
        </header>

        <main class="mx-auto w-full max-w-5xl px-4 py-8 sm:px-6 sm:py-12">
            {{ $slot }}
        </main>

        <footer class="border-t border-zinc-200 py-6 text-center text-xs text-zinc-400 dark:border-zinc-800">
            &copy; {{ now()->year }} {{ config('app.name', 'Markaz Club') }}
        </footer>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
