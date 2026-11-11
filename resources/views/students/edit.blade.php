@extends('layouts.app')

@section('title', 'Edit Student')
@section('page-title', 'Edit Student')

@section('content')
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Edit {{ $student->full_name() }}</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400">Student ID: {{ $student->student_id }}</p>
    </div>

    <form method="POST" action="{{ route('students.update', $student) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-800">
            <h3 class="mb-4 font-semibold text-slate-900 dark:text-white">Student Information</h3>
            @include('students.partials.fields', ['student' => $student])
        </div>

        @include('students.partials.face-capture', [
            'existingSamples' => count($student->descriptors()),
            'allowEmpty' => $student->hasFaceData(),
        ])

        <div class="flex items-center justify-between gap-3">
            <a href="{{ route('students.show', $student) }}" class="text-sm font-medium text-red-600 hover:text-red-500 dark:text-red-400"
               onclick="event.preventDefault(); if (confirm('Permanently delete {{ $student->full_name() }} and all their attendance records? This cannot be undone.')) { document.getElementById('delete-student-form').submit(); }">
                Delete student
            </a>
            <div class="flex items-center gap-3">
                <a href="{{ route('students.show', $student) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-600">Cancel</a>
                <button type="submit" class="rounded-lg bg-maroon-600 px-5 py-2 text-sm font-semibold text-white hover:bg-maroon-700">
                    Save Changes
                </button>
            </div>
        </div>
    </form>

    <form id="delete-student-form" method="POST" action="{{ route('students.destroy', $student) }}" class="hidden">
        @csrf
        @method('DELETE')
    </form>
@endsection
