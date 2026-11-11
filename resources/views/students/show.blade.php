@extends('layouts.app')

@section('title', $student->full_name())
@section('page-title', $student->full_name())

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $student->full_name() }}</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Student ID: {{ $student->student_id }}</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('students.edit', $student) }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">Edit</a>
            <form method="POST" action="{{ route('students.archive', $student) }}" onsubmit="return confirm('Archive {{ $student->full_name() }}?');">
                @csrf
                <button type="submit" class="rounded-lg border border-yellow-300 bg-yellow-50 px-4 py-2 text-sm font-medium text-yellow-700 hover:bg-yellow-100 dark:border-yellow-500/40 dark:bg-yellow-500/10 dark:text-yellow-400">Archive</button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Profile card --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <div class="flex flex-col items-center">
                @if ($student->photo_path)
                    <img src="{{ Storage::url($student->photo_path) }}" alt="{{ $student->full_name() }}" class="h-28 w-28 rounded-2xl object-cover">
                @else
                    <span class="flex h-28 w-28 items-center justify-center rounded-2xl bg-slate-200 text-3xl font-bold text-slate-600 dark:bg-slate-600 dark:text-slate-200">
                        {{ strtoupper(substr($student->first_name, 0, 1).substr($student->surname, 0, 1)) }}
                    </span>
                @endif
                <h3 class="mt-4 text-lg font-semibold text-slate-900 dark:text-white">{{ $student->full_name() }}</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ $student->student_id }}</p>
            </div>

            <dl class="mt-6 space-y-3 text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500 dark:text-slate-400">First name</dt>
                    <dd class="font-medium text-slate-900 dark:text-white">{{ $student->first_name }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500 dark:text-slate-400">Middle name</dt>
                    <dd class="font-medium text-slate-900 dark:text-white">{{ $student->middle_name ?? '-' }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500 dark:text-slate-400">Surname</dt>
                    <dd class="font-medium text-slate-900 dark:text-white">{{ $student->surname }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500 dark:text-slate-400">Parent's number</dt>
                    <dd class="font-medium text-slate-900 dark:text-white">{{ $student->parent_phone }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500 dark:text-slate-400">Face samples</dt>
                    <dd class="font-medium {{ $student->hasFaceData() ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ count($student->descriptors()) }}
                    </dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500 dark:text-slate-400">Days attended</dt>
                    <dd class="font-medium text-slate-900 dark:text-white">{{ $presentCount }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500 dark:text-slate-400">Days late</dt>
                    <dd class="font-medium text-yellow-600 dark:text-yellow-400">{{ $lateCount }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500 dark:text-slate-400">Status</dt>
                    <dd>
                        @if ($student->isArchived())
                            <span class="rounded-full bg-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-700 dark:bg-slate-600 dark:text-slate-300">Archived {{ $student->archived_at->format('M j, Y') }}</span>
                        @else
                            <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-800 dark:bg-green-500/10 dark:text-green-400">Active</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>

        {{-- Attendance history --}}
        <div class="lg:col-span-2">
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4 dark:border-slate-700">
                    <h3 class="font-semibold text-slate-900 dark:text-white">Attendance History</h3>
                    <a href="{{ route('history.index', ['student' => $student->id]) }}" class="text-sm font-medium text-maroon-600 hover:text-maroon-700 dark:text-maroon-400">Full history &rarr;</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500 dark:bg-slate-900/50 dark:text-slate-400">
                            <tr>
                                <th class="px-6 py-3">Date</th>
                                <th class="px-6 py-3">Time In</th>
                                <th class="px-6 py-3">Time Out</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">SMS Sent</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            @forelse ($attendance as $record)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40">
                                    <td class="px-6 py-3 font-medium text-slate-900 dark:text-white">{{ $record->attendance_date->format('D, M j, Y') }}</td>
                                    <td class="px-6 py-3 text-slate-500 dark:text-slate-400">{{ $record->time_in?->format('g:i A') ?? '-' }}</td>
                                    <td class="px-6 py-3 text-slate-500 dark:text-slate-400">{{ $record->time_out?->format('g:i A') ?? '-' }}</td>
                                    <td class="px-6 py-3">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $record->isLate()
                                            ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/10 dark:text-yellow-400'
                                            : 'bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-400' }}">
                                            {{ ucfirst($record->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3">
                                        @if ($record->sms_sent)
                                            <span class="text-green-600 dark:text-green-400">Yes</span>
                                        @else
                                            <span class="text-slate-400">No</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">No attendance records yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-slate-200 px-6 py-4 dark:border-slate-700">
                    {{ $attendance->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
