@extends('layouts.app')

@section('title', 'Students')
@section('page-title', 'Students')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Students</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Active roster: {{ $students->total() }} student(s)</p>
        </div>
        <a href="{{ route('students.create') }}" class="rounded-lg bg-maroon-600 px-4 py-2 text-sm font-medium text-white hover:bg-maroon-700">
            + Enroll Student
        </a>
    </div>

    <form method="GET" action="{{ route('students.index') }}" class="mb-4 flex gap-2">
        <input type="text" name="search" value="{{ $search }}" placeholder="Search by name, student ID or parent number..."
               class="w-full max-w-md rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-maroon-500 focus:ring-maroon-500">
        <button type="submit" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-600">
            Search
        </button>
        @if ($search)
            <a href="{{ route('students.index') }}" class="rounded-lg px-3 py-2 text-sm text-slate-500 hover:text-slate-700 dark:text-slate-400">Clear</a>
        @endif
    </form>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500 dark:bg-slate-900/50 dark:text-slate-400">
                    <tr>
                        <th class="px-6 py-3">Photo</th>
                        <th class="px-6 py-3">Name</th>
                        <th class="px-6 py-3">Student ID</th>
                        <th class="px-6 py-3">Parent's Number</th>
                        <th class="px-6 py-3">Face Samples</th>
                        <th class="px-6 py-3">Total Attendance</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse ($students as $student)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40">
                            <td class="px-6 py-3">
                                @if ($student->photo_path)
                                    <img src="{{ Storage::url($student->photo_path) }}" alt="{{ $student->full_name() }}" class="h-10 w-10 rounded-full object-cover">
                                @else
                                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-200 text-sm font-semibold text-slate-600 dark:bg-slate-600 dark:text-slate-200">
                                        {{ strtoupper(substr($student->first_name, 0, 1).substr($student->surname, 0, 1)) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-3 font-medium text-slate-900 dark:text-white">
                                <a href="{{ route('students.show', $student) }}" class="hover:text-maroon-600 dark:hover:text-maroon-400">{{ $student->full_name() }}</a>
                            </td>
                            <td class="px-6 py-3 text-slate-500 dark:text-slate-400">{{ $student->student_id }}</td>
                            <td class="px-6 py-3 text-slate-500 dark:text-slate-400">{{ $student->parent_phone }}</td>
                            <td class="px-6 py-3">
                                @if ($student->hasFaceData())
                                    <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-800 dark:bg-green-500/10 dark:text-green-400">
                                        {{ count($student->face_data) }} sample(s)
                                    </span>
                                @else
                                    <span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-800 dark:bg-red-500/10 dark:text-red-400">No face data</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-slate-500 dark:text-slate-400">{{ $student->attendances_count }}</td>
                            <td class="px-6 py-3">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('students.show', $student) }}" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-600">View</a>
                                    <a href="{{ route('students.edit', $student) }}" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-600">Edit</a>
                                    <form method="POST" action="{{ route('students.archive', $student) }}" onsubmit="return confirm('Archive {{ $student->full_name() }}? Their records will be kept and can be restored later.');">
                                        @csrf
                                        <button type="submit" class="rounded-lg border border-yellow-300 bg-yellow-50 px-3 py-1.5 text-xs font-medium text-yellow-700 hover:bg-yellow-100 dark:border-yellow-500/40 dark:bg-yellow-500/10 dark:text-yellow-400">Archive</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                                No students found. @if (! $search) <a href="{{ route('students.create') }}" class="font-medium text-maroon-600 dark:text-maroon-400">Enroll the first student &rarr;</a> @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-200 px-6 py-4 dark:border-slate-700">
            {{ $students->links() }}
        </div>
    </div>
@endsection
