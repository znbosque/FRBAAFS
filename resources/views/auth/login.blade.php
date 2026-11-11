@extends('layouts.guest')

@section('title', 'Log in')

@section('content')
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-slate-900">Welcome back</h2>
        <p class="mt-1 text-sm text-slate-500">Log in to your {{ config('app.name') }} account.</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                   class="mt-1.5 block w-full rounded-xl border-slate-300 px-4 py-2.5 shadow-sm focus:border-maroon-500 focus:ring-maroon-500">
            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <div class="flex items-center justify-between">
                <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                <a href="{{ route('password.request') }}" class="text-sm font-medium text-maroon-600 hover:text-maroon-700">
                    Forgot password?
                </a>
            </div>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                   class="mt-1.5 block w-full rounded-xl border-slate-300 px-4 py-2.5 shadow-sm focus:border-maroon-500 focus:ring-maroon-500">
            @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <label class="flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" name="remember" class="rounded border-slate-300 text-maroon-600 focus:ring-maroon-500">
            Remember me
        </label>

        <button type="submit" class="w-full rounded-xl bg-maroon-600 px-4 py-3 text-sm font-semibold text-white shadow-sm shadow-maroon-600/30 hover:bg-maroon-700 focus:outline-none focus:ring-2 focus:ring-maroon-500 focus:ring-offset-2">
            Log in
        </button>
    </form>
@endsection

@section('footer-links')
    <p class="text-sm text-slate-500">
        Don't have an account?
        <a href="{{ route('register') }}" class="font-semibold text-maroon-600 hover:text-maroon-700">Sign up</a>
    </p>
@endsection
