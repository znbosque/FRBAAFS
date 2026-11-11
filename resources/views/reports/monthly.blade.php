@extends('layouts.app')

@section('title', 'Monthly Report')
@section('page-title', 'Monthly Report')

@section('content')
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Monthly Report</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400">{{ $month->format('F Y') }}</p>
    </div>

    <div class="mb-4 flex flex-wrap items-center gap-3">
        <form method="GET" action="{{ route('reports.monthly') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label for="month" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Month</label>
                <input id="month" type="month" name="month" value="{{ $month->format('Y-m') }}"
                       class="mt-1 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-maroon-500 focus:ring-maroon-500">
            </div>
            <button type="submit" class="rounded-lg bg-maroon-600 px-4 py-2 text-sm font-medium text-white hover:bg-maroon-700">Show</button>
        </form>
        <a href="{{ route('reports.monthly', ['month' => $month->copy()->subMonth()->format('Y-m')]) }}" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-600 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-slate-600">&larr; Previous month</a>
        <a href="{{ route('reports.monthly', ['month' => $month->copy()->addMonth()->format('Y-m')]) }}" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-600 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-slate-600">Next month &rarr;</a>
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">School Days (days with records)</p>
            <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ $schoolDayCount }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Attendances</p>
            <p class="mt-2 text-3xl font-bold text-green-600 dark:text-green-400">{{ $totalPresent }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Late</p>
            <p class="mt-2 text-3xl font-bold text-yellow-600 dark:text-yellow-400">{{ $totalLate }}</p>
        </div>
    </div>

    {{-- Per-day breakdown --}}
    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <h3 class="border-b border-slate-200 px-6 py-4 font-semibold text-slate-900 dark:text-white dark:border-slate-700">Per-Day Breakdown</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500 dark:bg-slate-900/50 dark:text-slate-400">
                    <tr>
                        <th class="px-6 py-3">Date</th>
                        <th class="px-6 py-3">Present</th>
                        <th class="px-6 py-3">Late</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse ($dailyTotals as $day)
                        <tr>
                            <td class="px-6 py-3 font-medium text-slate-900 dark:text-white">
                                <a href="{{ route('reports.daily', ['date' => $day->attendance_date]) }}" class="hover:text-maroon-600 dark:hover:text-maroon-400">
                                    {{ \Illuminate\Support\Carbon::parse($day->attendance_date)->format('D, M j, Y') }}
                                </a>
                            </td>
                            <td class="px-6 py-3 text-green-600 dark:text-green-400">{{ $day->present }}</td>
                            <td class="px-6 py-3 text-yellow-600 dark:text-yellow-400">{{ $day->late }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">No attendance recorded this month.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Per-student totals --}}
    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <h3 class="border-b border-slate-200 px-6 py-4 font-semibold text-slate-900 dark:text-white dark:border-slate-700">Per-Student Totals</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500 dark:bg-slate-900/50 dark:text-slate-400">
                    <tr>
                        <th class="px-6 py-3">Student</th>
                        <th class="px-6 py-3">Student ID</th>
                        <th class="px-6 py-3">Days Present</th>
                        <th class="px-6 py-3">Days Late</th>
                        <th class="px-6 py-3">Days Absent</th>
                        <th class="px-6 py-3">Attendance Rate</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse ($perStudent as $row)
                        <tr>
                            <td class="px-6 py-3 font-medium text-slate-900 dark:text-white">
                                <a href="{{ route('students.show', $row['student']) }}" class="hover:text-maroon-600 dark:hover:text-maroon-400">{{ $row['student']->full_name() }}</a>
                            </td>
                            <td class="px-6 py-3 text-slate-500 dark:text-slate-400">{{ $row['student']->student_id }}</td>
                            <td class="px-6 py-3 text-green-600 dark:text-green-400">{{ $row['present_days'] }}</td>
                            <td class="px-6 py-3 text-yellow-600 dark:text-yellow-400">{{ $row['late_days'] }}</td>
                            <td class="px-6 py-3 text-red-600 dark:text-red-400">{{ $row['absent_days'] }}</td>
                            <td class="px-6 py-3 text-slate-500 dark:text-slate-400">
                                {{ $schoolDayCount > 0 ? round(($row['present_days'] / $schoolDayCount) * 100) . '%' : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">No active students.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
