@extends('layouts.guest')

@section('title', 'Verify email')

@section('content')
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-slate-900">Verify your email</h2>
        <p class="mt-1 text-sm text-slate-500">One quick step before you can manage attendance.</p>
    </div>

    <div class="rounded-xl border border-maroon-200 bg-maroon-50 p-5 text-sm text-maroon-900">
        We sent a verification link to
        <strong class="block truncate text-maroon-700">{{ auth()->user()->email }}</strong>
        <span class="mt-2 block text-maroon-700/80">Click the link in that email to activate your account. Didn't get it? Check your spam folder or resend it below.</span>
    </div>

    <div class="mt-6 space-y-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="w-full rounded-xl bg-maroon-600 px-4 py-3 text-sm font-semibold text-white shadow-sm shadow-maroon-600/30 hover:bg-maroon-700 focus:outline-none focus:ring-2 focus:ring-maroon-500 focus:ring-offset-2">
                Resend verification email
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-medium text-slate-600 hover:bg-slate-50">
                Log out
            </button>
        </form>
    </div>
@endsection
