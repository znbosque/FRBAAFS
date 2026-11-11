<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'FRBAAFS') - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        maroon: {
                            50: '#fdf2f3',
                            100: '#fce4e6',
                            200: '#facdd1',
                            300: '#f2a5ac',
                            400: '#e5747e',
                            500: '#cf4351',
                            600: '#ab2b3b',
                            700: '#8f2131',
                            800: '#7a1e2c',
                            900: '#681d2a',
                            950: '#3c0c14',
                        },
                    },
                },
            },
        };
    </script>
</head>
<body class="min-h-screen bg-white font-sans text-slate-900 antialiased">
<div class="flex min-h-screen">

    {{-- Brand panel --}}
    <div class="relative hidden w-2/5 flex-col justify-between overflow-hidden bg-maroon-900 p-12 lg:flex xl:w-1/2">
        <div class="pointer-events-none absolute -left-24 -top-24 h-96 w-96 rounded-full bg-maroon-800/60 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-32 -right-24 h-[28rem] w-[28rem] rounded-full bg-maroon-800/50 blur-3xl"></div>
        <div class="pointer-events-none absolute right-16 top-24 h-24 w-24 rounded-full border-8 border-maroon-800/40"></div>

        <div class="relative">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-xl font-bold text-maroon-900 shadow-lg">FR</div>
                <span class="text-xl font-semibold tracking-wide text-white">FRBAAFS</span>
            </div>
            <p class="mt-2 text-sm text-maroon-200">Face Recognition Attendance with SMS Notifications</p>
        </div>

        <div class="relative">
            <h1 class="text-4xl font-bold leading-tight text-white xl:text-5xl">
                Attendance made<br>
                <span class="text-maroon-200">simple and automatic.</span>
            </h1>
            <p class="mt-4 max-w-md text-maroon-100/80">
                Students walk in, the camera recognizes their face, and their parents instantly get an SMS. No roll calls, no paperwork.
            </p>

            <ul class="mt-10 space-y-4">
                @foreach ([
                    ['Face recognition check-in', 'Webcam-based, contactless and instant'],
                    ['SMS to parents', 'Notified the moment their child arrives'],
                    ['Daily, monthly & yearly reports', 'Full history and archive included'],
                ] as [$title, $subtitle])
                    <li class="flex items-start gap-4">
                        <span class="mt-1 flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-maroon-800 text-sm font-bold text-maroon-200">&#10003;</span>
                        <div>
                            <p class="font-medium text-white">{{ $title }}</p>
                            <p class="text-sm text-maroon-200/70">{{ $subtitle }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        <p class="relative text-xs text-maroon-300/60">&copy; {{ now()->year }} {{ config('app.name') }}. All rights reserved.</p>
    </div>

    {{-- Form panel --}}
    <div class="flex w-full flex-col justify-center bg-white px-6 py-12 sm:px-12 lg:w-3/5 xl:w-1/2">
        <div class="mx-auto w-full max-w-md">
            {{-- Mobile brand header --}}
            <div class="mb-10 flex flex-col items-center lg:hidden">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-maroon-900 text-2xl font-bold text-white shadow-lg shadow-maroon-900/30">FR</div>
                <h1 class="mt-4 text-xl font-semibold text-slate-900">FRBAAFS</h1>
                <p class="text-sm text-slate-500">Face Recognition Attendance with SMS Notifications</p>
            </div>

            @if (session('status'))
                <div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 text-sm font-medium text-green-700" role="alert">
                    {{ session('status') === 'verification-link-sent'
                        ? __('A new verification link has been sent to the email address you provided during registration.')
                        : __(session('status')) }}
                </div>
            @endif

            @yield('content')

            <div class="mt-8 border-t border-slate-200 pt-6">
                @yield('footer-links')
            </div>
        </div>
    </div>
</div>
</body>
</html>
