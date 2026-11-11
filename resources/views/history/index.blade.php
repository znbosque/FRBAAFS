@extends('layouts.app')

@section('title', 'History')
@section('page-title', 'Attendance History')

@section('content')
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Attendance History</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400">All recorded attendance, newest first. Filter by student, date range or status.</p>
    </div>

    <form method="GET" action="{{ route('history.index') }}" class="mb-4 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-800">
        <div class="w-full max-w-xs">
            <label for="filter-student" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Student</label>
            <select id="filter-student" name="student" class="mt-1 w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-maroon-500 focus:ring-maroon-500">
                <option value="">All students</option>
                @foreach ($students as $student)
                    <option value="{{ $student->id }}" {{ (string) ($filters['student'] ?? '') === (string) $student->id ? 'selected' : '' }}>
                        {{ $student->full_name() }} ({{ $student->student_id }})
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="filter-from" class="block text-sm font-medium text-slate-700 dark:text-slate-300">From</label>
            <input id="filter-from" type="date" name="from" value="{{ $filters['from'] ?? '' }}"
                   class="mt-1 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-maroon-500 focus:ring-maroon-500">
        </div>
        <div>
            <label for="filter-to" class="block text-sm font-medium text-slate-700 dark:text-slate-300">To</label>
            <input id="filter-to" type="date" name="to" value="{{ $filters['to'] ?? '' }}"
                   class="mt-1 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-maroon-500 focus:ring-maroon-500">
        </div>
        <div>
            <label for="filter-status" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Status</label>
            <select id="filter-status" name="status" class="mt-1 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-maroon-500 focus:ring-maroon-500">
                <option value="">All</option>
                <option value="present" {{ ($filters['status'] ?? '') === 'present' ? 'selected' : '' }}>Present</option>
                <option value="late" {{ ($filters['status'] ?? '') === 'late' ? 'selected' : '' }}>Late</option>
            </select>
        </div>
        <button type="submit" class="rounded-lg bg-maroon-600 px-4 py-2 text-sm font-medium text-white hover:bg-maroon-700">Filter</button>
        <a href="{{ route('history.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-600">Reset</a>
    </form>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500 dark:bg-slate-900/50 dark:text-slate-400">
                    <tr>
                        <th class="px-6 py-3">Date</th>
                        <th class="px-6 py-3">Student</th>
                        <th class="px-6 py-3">Student ID</th>
                        <th class="px-6 py-3">Time In</th>
                        <th class="px-6 py-3">Time Out</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">SMS Sent</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse ($attendance as $record)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40">
                            <td class="px-6 py-3 font-medium text-slate-900 dark:text-white">{{ $record->attendance_date->format('M j, Y') }}</td>
                            <td class="px-6 py-3 font-medium text-slate-900 dark:text-white">
                                <a href="{{ route('students.show', $record->student) }}" class="hover:text-maroon-600 dark:hover:text-maroon-400">{{ $record->student->full_name() }}</a>
                            </td>
                            <td class="px-6 py-3 text-slate-500 dark:text-slate-400">{{ $record->student->student_id }}</td>
                            <td class="px-6 py-3 text-slate-500 dark:text-slate-400">{{ $record->time_in?->format('g:i A') ?? '-' }}</td>
                            <td class="px-6 py-3 text-slate-500 dark:text-slate-400">{{ $record->time_out?->format('g:i A') ?? '-' }}</td>
                            <td class="px-6 py-3">
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $record->isLate()
                                    ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/10 dark:text-yellow-400'
                                    : 'bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-400' }}">
                                    {{ ucfirst($record->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-3 {{ $record->sms_sent ? 'text-green-600 dark:text-green-400' : 'text-slate-400' }}">{{ $record->sms_sent ? 'Yes' : 'No' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">No attendance records match the current filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-200 px-6 py-4 dark:border-slate-700">
            {{ $attendance->links() }}
        </div>
    </div>
@endsection
