@extends('layouts.app')

@section('title', 'Enroll Student')
@section('page-title', 'Enroll Student')

@section('content')
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Enroll a New Student</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400">Fill in the student's details and capture their face data from the webcam.</p>
    </div>

    <form method="POST" action="{{ route('students.store') }}" class="space-y-6">
        @csrf

        <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-800">
            <h3 class="mb-4 font-semibold text-slate-900 dark:text-white">Student Information</h3>
            @include('students.partials.fields')
        </div>

        @include('students.partials.face-capture')

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('students.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-600">Cancel</a>
            <button type="submit" class="rounded-lg bg-maroon-600 px-5 py-2 text-sm font-semibold text-white hover:bg-maroon-700">
                Enroll Student
            </button>
        </div>
    </form>
@endsection
