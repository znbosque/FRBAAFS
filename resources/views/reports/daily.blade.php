@extends('layouts.app')

@section('title', 'Daily Report')
@section('page-title', 'Daily Report')

@section('content')
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Daily Report</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400">{{ $date->format('l, F j, Y') }}</p>
    </div>

    <div class="mb-4 flex flex-wrap items-center gap-3">
        <form method="GET" action="{{ route('reports.daily') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label for="date" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Date</label>
                <input id="date" type="date" name="date" value="{{ $date->toDateString() }}"
                       class="mt-1 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-maroon-500 focus:ring-maroon-500">
            </div>
            <button type="submit" class="rounded-lg bg-maroon-600 px-4 py-2 text-sm font-medium text-white hover:bg-maroon-700">Show</button>
        </form>
        <a href="{{ route('reports.daily', ['date' => now()->toDateString()]) }}" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-600 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-slate-600">Today</a>
        <a href="{{ route('reports.daily', ['date' => $date->copy()->subDay()->toDateString()]) }}" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-600 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-slate-600">&larr; Previous day</a>
        <a href="{{ route('reports.daily', ['date' => $date->copy()->addDay()->toDateString()]) }}" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-600 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-slate-600">Next day &rarr;</a>
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Active Students</p>
            <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ $activeStudents }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Present</p>
            <p class="mt-2 text-3xl font-bold text-green-600 dark:text-green-400">{{ $presentCount }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Late</p>
            <p class="mt-2 text-3xl font-bold text-yellow-600 dark:text-yellow-400">{{ $lateCount }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Absent</p>
            <p class="mt-2 text-3xl font-bold text-red-600 dark:text-red-400">{{ $absentStudents->count() }}</p>
        </div>
    </div>

    {{-- Present list --}}
    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <h3 class="border-b border-slate-200 px-6 py-4 font-semibold text-slate-900 dark:text-white dark:border-slate-700">Students Present</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500 dark:bg-slate-900/50 dark:text-slate-400">
                    <tr>
                        <th class="px-6 py-3">Student</th>
                        <th class="px-6 py-3">Student ID</th>
                        <th class="px-6 py-3">Time In</th>
                        <th class="px-6 py-3">Time Out</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">SMS Sent</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse ($attendance as $row)
                        <tr>
                            <td class="px-6 py-3 font-medium text-slate-900 dark:text-white">{{ $row['student']->full_name() }}</td>
                            <td class="px-6 py-3 text-slate-500 dark:text-slate-400">{{ $row['student']->student_id }}</td>
                            <td class="px-6 py-3 text-slate-500 dark:text-slate-400">{{ $row['time_in'] ?? '-' }}</td>
                            <td class="px-6 py-3 text-slate-500 dark:text-slate-400">{{ $row['time_out'] ?? '-' }}</td>
                            <td class="px-6 py-3">
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $row['status'] === 'late'
                                    ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/10 dark:text-yellow-400'
                                    : 'bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-400' }}">
                                    {{ ucfirst($row['status']) }}
                                </span>
                            </td>
                            <td class="px-6 py-3 {{ $row['sms_sent'] ? 'text-green-600 dark:text-green-400' : 'text-slate-400' }}">{{ $row['sms_sent'] ? 'Yes' : 'No' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">No students were recorded present on this date.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Absent list --}}
    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <h3 class="border-b border-slate-200 px-6 py-4 font-semibold text-slate-900 dark:text-white dark:border-slate-700">Students Absent</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500 dark:bg-slate-900/50 dark:text-slate-400">
                    <tr>
                        <th class="px-6 py-3">Student</th>
                        <th class="px-6 py-3">Student ID</th>
                        <th class="px-6 py-3">Parent's Number</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse ($absentStudents as $student)
                        <tr>
                            <td class="px-6 py-3 font-medium text-slate-900 dark:text-white">{{ $student->full_name() }}</td>
                            <td class="px-6 py-3 text-slate-500 dark:text-slate-400">{{ $student->student_id }}</td>
                            <td class="px-6 py-3 text-slate-500 dark:text-slate-400">{{ $student->parent_phone }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">Everyone was present. Great attendance!</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
