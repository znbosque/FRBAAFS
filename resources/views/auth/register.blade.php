@extends('layouts.guest')

@section('title', 'Sign up')

@section('content')
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-slate-900">Create your account</h2>
        <p class="mt-1 text-sm text-slate-500">
            Sign up to start enrolling students and recording attendance. We'll email you a verification link to activate your account.
        </p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <div>
            <label for="name" class="block text-sm font-medium text-slate-700">Full name</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                   class="mt-1.5 block w-full rounded-xl border-slate-300 px-4 py-2.5 shadow-sm focus:border-maroon-500 focus:ring-maroon-500">
            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                   class="mt-1.5 block w-full rounded-xl border-slate-300 px-4 py-2.5 shadow-sm focus:border-maroon-500 focus:ring-maroon-500">
            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                <input id="password" type="password" name="password" required autocomplete="new-password"
                       class="mt-1.5 block w-full rounded-xl border-slate-300 px-4 py-2.5 shadow-sm focus:border-maroon-500 focus:ring-maroon-500">
                @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Confirm password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                       class="mt-1.5 block w-full rounded-xl border-slate-300 px-4 py-2.5 shadow-sm focus:border-maroon-500 focus:ring-maroon-500">
            </div>
        </div>

        <button type="submit" class="w-full rounded-xl bg-maroon-600 px-4 py-3 text-sm font-semibold text-white shadow-sm shadow-maroon-600/30 hover:bg-maroon-700 focus:outline-none focus:ring-2 focus:ring-maroon-500 focus:ring-offset-2">
            Sign up
        </button>
    </form>
@endsection

@section('footer-links')
    <p class="text-sm text-slate-500">
        Already have an account?
        <a href="{{ route('login') }}" class="font-semibold text-maroon-600 hover:text-maroon-700">Log in</a>
    </p>
@endsection
