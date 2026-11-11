@extends('layouts.app')

@section('title', 'Archive')
@section('page-title', 'Archived Students')

@section('content')
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Archive</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400">
            Students who have left or graduated. Their attendance history is preserved; restore them to return them to the active roster.
        </p>
    </div>

    <form method="GET" action="{{ route('archive.index') }}" class="mb-4 flex gap-2">
        <input type="text" name="search" value="{{ $search }}" placeholder="Search archived students..."
               class="w-full max-w-md rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-maroon-500 focus:ring-maroon-500">
        <button type="submit" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-600">
            Search
        </button>
        @if ($search)
            <a href="{{ route('archive.index') }}" class="rounded-lg px-3 py-2 text-sm text-slate-500 hover:text-slate-700 dark:text-slate-400">Clear</a>
        @endif
    </form>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500 dark:bg-slate-900/50 dark:text-slate-400">
                    <tr>
                        <th class="px-6 py-3">Student</th>
                        <th class="px-6 py-3">Student ID</th>
                        <th class="px-6 py-3">Parent's Number</th>
                        <th class="px-6 py-3">Total Attendance</th>
                        <th class="px-6 py-3">Archived On</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse ($students as $student)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40">
                            <td class="px-6 py-3 font-medium text-slate-900 dark:text-white">
                                <a href="{{ route('students.show', $student) }}" class="hover:text-maroon-600 dark:hover:text-maroon-400">{{ $student->full_name() }}</a>
                            </td>
                            <td class="px-6 py-3 text-slate-500 dark:text-slate-400">{{ $student->student_id }}</td>
                            <td class="px-6 py-3 text-slate-500 dark:text-slate-400">{{ $student->parent_phone }}</td>
                            <td class="px-6 py-3 text-slate-500 dark:text-slate-400">{{ $student->attendances_count }} day(s)</td>
                            <td class="px-6 py-3 text-slate-500 dark:text-slate-400">{{ $student->archived_at->format('M j, Y') }}</td>
                            <td class="px-6 py-3">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('students.show', $student) }}" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-600">View</a>
                                    <form method="POST" action="{{ route('archive.restore', $student) }}" onsubmit="return confirm('Restore {{ $student->full_name() }} to the active roster?');">
                                        @csrf
                                        <button type="submit" class="rounded-lg bg-green-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-green-500">Restore</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                                No archived students. Use the "Archive" button on a student's profile to move them here.
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
