@extends('layouts.app')

@section('title', 'Home')
@section('page-title', 'Home')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Welcome back, {{ auth()->user()->name }}</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Today is {{ now()->format('l, F j, Y') }}.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('students.create') }}" class="rounded-lg bg-maroon-600 px-4 py-2 text-sm font-medium text-white hover:bg-maroon-700">
                + Enroll Student
            </a>
            <a href="{{ route('attendance.scan') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
                Scan Faces
            </a>
        </div>
    </div>

    {{-- Stat cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
        @include('dashboard.partials.stat-card', [
            'label' => 'Active Students',
            'value' => $activeStudents,
            'color' => 'bg-maroon-50 text-maroon-700 dark:bg-maroon-500/10 dark:text-maroon-400',
        ])
        @include('dashboard.partials.stat-card', [
            'label' => 'Present Today',
            'value' => $presentToday,
            'color' => 'bg-green-50 text-green-700 dark:bg-green-500/10 dark:text-green-400',
        ])
        @include('dashboard.partials.stat-card', [
            'label' => 'Late Today',
            'value' => $lateToday,
            'color' => 'bg-yellow-50 text-yellow-700 dark:bg-yellow-500/10 dark:text-yellow-400',
        ])
        @include('dashboard.partials.stat-card', [
            'label' => 'Absent Today',
            'value' => $absentToday,
            'color' => 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400',
        ])
        @include('dashboard.partials.stat-card', [
            'label' => 'Archived',
            'value' => $archivedStudents,
            'color' => 'bg-slate-100 text-slate-700 dark:bg-slate-500/10 dark:text-slate-400',
        ])
    </div>

    {{-- Quick links --}}
    <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-3">
        @foreach ([
            ['route' => 'students.index', 'title' => 'Students', 'description' => 'Enroll students with their face data, parent contact numbers and manage profiles.'],
            ['route' => 'reports.daily', 'title' => 'Reports', 'description' => 'Daily, monthly and yearly attendance reports with summaries.'],
            ['route' => 'archive.index', 'title' => 'Archive', 'description' => 'View archived students and restore them when they return.'],
        ] as $link)
            <a href="{{ route($link['route']) }}" class="rounded-2xl border border-slate-200 bg-white p-5 transition hover:border-maroon-300 hover:shadow-md dark:border-slate-700 dark:bg-slate-800 dark:hover:border-maroon-500/50">
                <h3 class="font-semibold text-slate-900 dark:text-white">{{ $link['title'] }}</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $link['description'] }}</p>
            </a>
        @endforeach
    </div>

    {{-- Recent attendance --}}
    <div class="mt-6 rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4 dark:border-slate-700">
            <h3 class="font-semibold text-slate-900 dark:text-white">Recent Attendance</h3>
            <a href="{{ route('history.index') }}" class="text-sm font-medium text-maroon-600 hover:text-maroon-700 dark:text-maroon-400">View history &rarr;</a>
        </div>

        @if ($recentAttendance->isEmpty())
            <p class="px-6 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                No attendance has been recorded yet. Enroll students and scan their faces to get started.
            </p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500 dark:bg-slate-900/50 dark:text-slate-400">
                        <tr>
                            <th class="px-6 py-3">Student</th>
                            <th class="px-6 py-3">Student ID</th>
                            <th class="px-6 py-3">Date</th>
                            <th class="px-6 py-3">Time In</th>
                            <th class="px-6 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @foreach ($recentAttendance as $record)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40">
                                <td class="px-6 py-3 font-medium text-slate-900 dark:text-white">{{ $record->student->full_name() }}</td>
                                <td class="px-6 py-3 text-slate-500 dark:text-slate-400">{{ $record->student->student_id }}</td>
                                <td class="px-6 py-3 text-slate-500 dark:text-slate-400">{{ $record->attendance_date->format('M j, Y') }}</td>
                                <td class="px-6 py-3 text-slate-500 dark:text-slate-400">{{ $record->time_in?->format('g:i A') }}</td>
                                <td class="px-6 py-3">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $record->isLate()
                                        ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/10 dark:text-yellow-400'
                                        : 'bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-400' }}">
                                        {{ ucfirst($record->status) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
