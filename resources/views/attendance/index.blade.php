@extends('layouts.app')

@section('title', 'Attendance')
@section('page-title', "Today's Attendance")

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Attendance - {{ now()->format('l, F j, Y') }}</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Students recognized by the face scanner today.</p>
        </div>
        <a href="{{ route('attendance.scan') }}" class="rounded-lg bg-maroon-600 px-4 py-2 text-sm font-medium text-white hover:bg-maroon-700">
            Open Face Scanner
        </a>
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Present</p>
            <p class="mt-2 text-3xl font-bold text-green-600 dark:text-green-400">{{ $presentCount }} <span class="text-base font-normal text-slate-400">/ {{ $activeStudents }}</span></p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Late</p>
            <p class="mt-2 text-3xl font-bold text-yellow-600 dark:text-yellow-400">{{ $lateCount }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Absent</p>
            <p class="mt-2 text-3xl font-bold text-red-600 dark:text-red-400">{{ $absentCount }}</p>
        </div>
    </div>

    {{-- Manual entry fallback --}}
    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
        <details>
            <summary class="cursor-pointer text-sm font-semibold text-slate-700 dark:text-slate-300">Manual entry (fallback when the camera cannot be used)</summary>
            <form method="POST" action="{{ route('attendance.manual') }}" class="mt-4 flex flex-wrap items-end gap-3">
                @csrf
                <div class="w-full max-w-sm">
                    <label for="manual-student" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Student</label>
                    <select id="manual-student" name="student_id" required class="mt-1 block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-maroon-500 focus:ring-maroon-500">
                        @foreach (\App\Models\Student::active()->orderBy('surname')->orderBy('first_name')->get() as $student)
                            <option value="{{ $student->id }}">{{ $student->full_name() }} ({{ $student->student_id }})</option>
                        @endforeach
                    </select>
                    @error('student_id') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>
                <div class="w-40">
                    <label for="manual-date" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Date</label>
                    <input id="manual-date" type="date" name="attendance_date" value="{{ now()->toDateString() }}" required
                           class="mt-1 block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-maroon-500 focus:ring-maroon-500">
                </div>
                <button type="submit" class="rounded-lg bg-maroon-600 px-4 py-2 text-sm font-semibold text-white hover:bg-maroon-700">
                    Record Attendance
                </button>
                <p class="text-xs text-slate-400 dark:text-slate-500">An SMS is still sent to the parents' number on file.</p>
            </form>
        </details>
    </div>

    {{-- Today's sheet --}}
    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
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
                    @forelse ($attendance as $record)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40">
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
                            <td class="px-6 py-3">
                                @if ($record->sms_sent)
                                    <span class="text-green-600 dark:text-green-400">Yes</span>
                                @else
                                    <span class="text-slate-400">Pending / No</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                                No attendance recorded yet today.
                                <a href="{{ route('attendance.scan') }}" class="font-medium text-maroon-600 dark:text-maroon-400">Open the scanner &rarr;</a>
                            </td>
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
