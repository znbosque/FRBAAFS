@extends('layouts.guest')

@section('title', 'Forgot password')

@section('content')
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-slate-900">Forgot your password?</h2>
        <p class="mt-1 text-sm text-slate-500">
            Enter the email address you registered with and we'll send you a link to reset your password.
        </p>
    </div>

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                   class="mt-1.5 block w-full rounded-xl border-slate-300 px-4 py-2.5 shadow-sm focus:border-maroon-500 focus:ring-maroon-500">
            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="w-full rounded-xl bg-maroon-600 px-4 py-3 text-sm font-semibold text-white shadow-sm shadow-maroon-600/30 hover:bg-maroon-700 focus:outline-none focus:ring-2 focus:ring-maroon-500 focus:ring-offset-2">
            Email password reset link
        </button>
    </form>
@endsection

@section('footer-links')
    <p class="text-sm text-slate-500">
        Remembered your password?
        <a href="{{ route('login') }}" class="font-semibold text-maroon-600 hover:text-maroon-700">Log in</a>
    </p>
@endsection
