@extends('layouts.app')

@section('title', 'Yearly Report')
@section('page-title', 'Yearly Report')

@section('content')
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Yearly Report</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400">{{ $year }}</p>
    </div>

    <div class="mb-4 flex flex-wrap items-center gap-3">
        <form method="GET" action="{{ route('reports.yearly') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label for="year" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Year</label>
                <input id="year" type="number" name="year" min="2000" max="2100" value="{{ $year }}"
                       class="mt-1 w-32 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-maroon-500 focus:ring-maroon-500">
            </div>
            <button type="submit" class="rounded-lg bg-maroon-600 px-4 py-2 text-sm font-medium text-white hover:bg-maroon-700">Show</button>
        </form>
        <a href="{{ route('reports.yearly', ['year' => $year - 1]) }}" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-600 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-slate-600">&larr; {{ $year - 1 }}</a>
        <a href="{{ route('reports.yearly', ['year' => $year + 1]) }}" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-600 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-slate-600">{{ $year + 1 }} &rarr;</a>
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Attendances</p>
            <p class="mt-2 text-3xl font-bold text-green-600 dark:text-green-400">{{ $totalPresent }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Late</p>
            <p class="mt-2 text-3xl font-bold text-yellow-600 dark:text-yellow-400">{{ $totalLate }}</p>
        </div>
    </div>

    {{-- Month by month --}}
    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <h3 class="border-b border-slate-200 px-6 py-4 font-semibold text-slate-900 dark:text-white dark:border-slate-700">Month by Month</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500 dark:bg-slate-900/50 dark:text-slate-400">
                    <tr>
                        <th class="px-6 py-3">Month</th>
                        <th class="px-6 py-3">Attendances</th>
                        <th class="px-6 py-3">Late</th>
                        <th class="px-6 py-3">Monthly Report</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @foreach (range(1, 12) as $m)
                        @php($row = $monthlyTotals->get(str_pad($m, 2, '0', STR_PAD_LEFT)))
                        <tr>
                            <td class="px-6 py-3 font-medium text-slate-900 dark:text-white">{{ \Illuminate\Support\Carbon::create($year, $m, 1)->format('F') }}</td>
                            <td class="px-6 py-3 text-green-600 dark:text-green-400">{{ $row->present ?? 0 }}</td>
                            <td class="px-6 py-3 text-yellow-600 dark:text-yellow-400">{{ $row->late ?? 0 }}</td>
                            <td class="px-6 py-3">
                                <a href="{{ route('reports.monthly', ['month' => sprintf('%d-%02d', $year, $m)]) }}" class="font-medium text-maroon-600 hover:text-maroon-700 dark:text-maroon-400">View &rarr;</a>
                            </td>
                        </tr>
                    @endforeach
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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">No active students.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
