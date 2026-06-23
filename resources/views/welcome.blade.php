<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ __('Welcome') }} - {{ config('app.name', 'Markaz Club Bangladesh Community') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&display=swap" rel="stylesheet">

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-gradient-to-br from-emerald-950 via-emerald-900 to-emerald-800 flex flex-col relative overflow-hidden">

        <div class="absolute inset-0 opacity-[0.04] pointer-events-none"
             style="background-image: url('data:image/svg+xml,%3Csvg width=\'100\' height=\'100\' viewBox=\'0 0 100 100\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cpath d=\'M50 5 L95 50 L50 95 L5 50 Z\' fill=\'none\' stroke=\'%23ffffff\' stroke-width=\'1\'/%3E%3Cpath d=\'M50 20 L80 50 L50 80 L20 50 Z\' fill=\'none\' stroke=\'%23ffffff\' stroke-width=\'1\'/%3E%3C/svg%3E'); background-size: 120px 120px;">
        </div>
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-emerald-500/20 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-amber-500/10 rounded-full blur-3xl"></div>

        <div class="relative z-10 w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 sm:pt-8">
            <div class="flex items-center justify-end gap-3 sm:gap-4">
                @auth
                    <a href="{{ route('dashboard') }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-500 hover:bg-amber-400 text-emerald-950 font-semibold rounded-xl transition-all duration-200 shadow-lg hover:shadow-amber-500/25 text-sm">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 border border-white/20 hover:border-white/40 text-white font-medium rounded-xl transition-all duration-200 hover:bg-white/5 text-sm">
                        Member Login
                    </a>
                    <a href="{{ route('register.member') }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-500 hover:bg-amber-400 text-emerald-950 font-semibold rounded-xl transition-all duration-200 shadow-lg hover:shadow-amber-500/25 text-sm">
                        Member Registration
                    </a>
                @endauth
            </div>
        </div>

        <div class="relative z-10 flex-1 flex items-center justify-center px-4 sm:px-6 lg:px-8 py-12">
            <div class="w-full max-w-2xl text-center">

                <div class="mx-auto w-16 h-0.5 bg-gradient-to-r from-transparent via-amber-400/70 to-transparent rounded-full mb-10"></div>

                <p class="font-['Amiri'] text-5xl sm:text-6xl text-amber-300/90 mb-8 leading-relaxed" dir="rtl">
                    ﷽
                </p>

                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-3 tracking-tight">
                    Welcome to <span class="text-amber-300">Markaz Club</span>
                </h1>

                <h2 class="text-lg sm:text-xl lg:text-2xl font-medium text-emerald-200/90 mb-6">
                    Bangladesh Community
                </h2>

                <div class="flex items-center gap-3 justify-center mb-6">
                    <span class="w-8 h-px bg-emerald-400/30"></span>
                    <span class="text-emerald-400/40 text-xs">✦</span>
                    <span class="w-8 h-px bg-emerald-400/30"></span>
                </div>

                <p class="text-emerald-100/60 text-base sm:text-lg max-w-md mx-auto leading-relaxed">
                    A community of Bangladeshi brothers united in faith, gathering at the masjid for prayer, learning, and brotherhood.
                </p>

                <div class="mx-auto w-16 h-0.5 bg-gradient-to-r from-transparent via-amber-400/70 to-transparent rounded-full mt-10"></div>

            </div>
        </div>

        <div class="relative z-10 w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pb-6 text-center">
            <div class="flex items-center justify-center gap-4 text-emerald-200/30 text-xs">
                <span>Masjid</span>
                <span class="w-1 h-1 rounded-full bg-emerald-200/30"></span>
                <span>Community</span>
                <span class="w-1 h-1 rounded-full bg-emerald-200/30"></span>
                <span>Brotherhood</span>
            </div>
        </div>

    </body>
</html>
