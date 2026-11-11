@extends('layouts.app')

@section('title', 'Scan Faces')
@section('page-title', 'Face Scanner')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Face Recognition Scanner</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Recognized students are marked present/late and their parents receive an SMS.
                A second scan records the student's time out.
            </p>
        </div>
        <a href="{{ route('attendance.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
            Today's attendance sheet
        </a>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Scanner --}}
        <div id="face-scan"
             data-models-url="{{ asset('face-models') }}"
             data-recognize-url="{{ route('attendance.recognize') }}"
             data-csrf="{{ csrf_token() }}"
             data-scan-interval="2000"
             class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
            <div class="relative overflow-hidden rounded-xl">
                <video autoplay muted playsinline class="h-72 w-full bg-slate-900 object-cover"></video>
                <canvas class="scan-overlay pointer-events-none absolute left-0 top-0 h-full w-full"></canvas>
            </div>

            <button type="button" class="js-start-scan mt-4 w-full rounded-lg bg-maroon-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-maroon-700">
                Start Scanner
            </button>
            <p class="js-scan-status mt-2 text-sm text-slate-500 dark:text-slate-400">Camera is off. Click "Start Scanner" to begin.</p>

            <div class="js-scan-result mt-4 hidden rounded-xl border border-slate-300 bg-slate-50 p-4 dark:border-slate-600 dark:bg-slate-700/50">
                <p class="text-sm text-slate-500">Recognition results will appear here.</p>
            </div>

            <p class="mt-4 text-xs text-slate-400 dark:text-slate-500">
                Requires HTTPS or localhost for webcam access. Recognition runs locally in the browser;
                only the face descriptor is sent to the server.
            </p>
        </div>

        {{-- Live log --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4 dark:border-slate-700">
                <h3 class="font-semibold text-slate-900 dark:text-white">Recognized This Session</h3>
                <a href="{{ route('attendance.index') }}" class="text-sm font-medium text-maroon-600 hover:text-maroon-700 dark:text-maroon-400">Today's sheet &rarr;</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500 dark:bg-slate-900/50 dark:text-slate-400">
                        <tr>
                            <th class="px-6 py-3">Student</th>
                            <th class="px-6 py-3">Time</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Event</th>
                        </tr>
                    </thead>
                    <tbody class="js-scan-log divide-y divide-slate-100 dark:divide-slate-700">
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                                Start the scanner to begin recording attendance.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/face-api.min.js') }}"></script>
    <script src="{{ asset('js/face-scan.js') }}"></script>
@endsection
