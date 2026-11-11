<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ config('app.name') }}</title>
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
<body class="min-h-screen bg-slate-100">
<div class="flex min-h-screen">

    {{-- Sidebar --}}
    <aside class="hidden w-64 flex-shrink-0 flex-col bg-maroon-900 text-maroon-100 md:flex">
        <div class="flex items-center gap-3 px-6 py-6">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white font-bold text-maroon-900">FR</div>
            <div>
                <p class="font-semibold leading-tight text-white">FRBAAFS</p>
                <p class="text-[11px] text-maroon-300">Face Attendance + SMS</p>
            </div>
        </div>

        <nav class="flex-1 space-y-1 px-3 py-4 text-sm">
            @include('layouts.partials.nav', ['navItems' => [
                ['route' => 'dashboard', 'active' => 'dashboard', 'icon' => 'M3 12l9-9 9 9M5 10v10a1 1 0 001 1h4v-6h4v6h4a1 1 0 001-1V10', 'label' => 'Home'],
                ['route' => 'students.index', 'active' => 'students.*', 'icon' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-6.93 4 4 0 004 6.93zm0 0a6 6 0 016 6', 'label' => 'Students'],
                ['route' => 'attendance.index', 'active' => 'attendance.*', 'icon' => 'M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V11H3v8a2 2 0 002 2z', 'label' => 'Attendance'],
                ['route' => 'attendance.scan', 'active' => 'attendance.scan', 'icon' => 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z', 'label' => 'Scan Faces'],
            ]])
        </nav>

        <div class="space-y-1 px-3 pb-6 text-sm">
            <p class="px-3 pb-1 pt-4 text-[11px] font-semibold uppercase tracking-wider text-maroon-400">Reports</p>
            @include('layouts.partials.nav', ['navItems' => [
                ['route' => 'reports.daily', 'active' => 'reports.daily', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', 'label' => 'Daily'],
                ['route' => 'reports.monthly', 'active' => 'reports.monthly', 'icon' => 'M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V11H3v8a2 2 0 002 2z', 'label' => 'Monthly'],
                ['route' => 'reports.yearly', 'active' => 'reports.yearly', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'label' => 'Yearly'],
            ]])

            <p class="px-3 pb-1 pt-4 text-[11px] font-semibold uppercase tracking-wider text-maroon-400">Records</p>
            @include('layouts.partials.nav', ['navItems' => [
                ['route' => 'history.index', 'active' => 'history.*', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'label' => 'History'],
                ['route' => 'archive.index', 'active' => 'archive.*', 'icon' => 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4', 'label' => 'Archive'],
            ]])
        </div>
    </aside>

    {{-- Content --}}
    <div class="flex min-w-0 flex-1 flex-col">
        <header class="sticky top-0 z-10 flex items-center justify-between border-b border-slate-200 bg-white px-6 py-4">
            <div class="flex items-center gap-3">
                <button type="button" class="md:hidden rounded-lg p-2 text-maroon-900 hover:bg-maroon-50" onclick="document.querySelector('aside').classList.toggle('hidden')">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                </button>
                <h1 class="text-lg font-semibold text-slate-900">@yield('page-title', 'Dashboard')</h1>
            </div>

            <div class="flex items-center gap-4">
                <span class="hidden text-sm text-slate-500 sm:block">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-lg bg-maroon-800 px-4 py-2 text-sm font-medium text-white hover:bg-maroon-700">
                        Log out
                    </button>
                </form>
            </div>
        </header>

        <main class="flex-1 overflow-x-auto p-6">
            @if (session('success'))
                <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('verified'))
                <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800">
                    Your email address has been verified. Welcome!
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <ul class="list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
