{{--
    Webcam face enrollment widget.
    Exposes two hidden inputs to the enclosing form:
      - input.js-face-data  : JSON array of 128-d face descriptors
      - input.js-photo-data : data-URL snapshot of the last captured sample
    Set $allowEmpty = true when editing a student who already has face data.
--}}
<div id="face-enroll"
     data-models-url="{{ asset('face-models') }}"
     data-allow-empty="{{ isset($allowEmpty) && $allowEmpty ? 'true' : 'false' }}"
     class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <h3 class="font-semibold text-slate-900 dark:text-white">Face Data</h3>
        <p class="text-xs text-slate-500 dark:text-slate-400">
            Captured samples: <span class="js-sample-count font-semibold text-slate-700 dark:text-slate-200">0</span>
            @if (($existingSamples ?? 0) > 0)
                &mdash; {{ $existingSamples }} sample(s) already stored; capturing new ones will replace them
            @endif
        </p>
    </div>

    <div class="mt-4 flex flex-col gap-5 md:flex-row">
        <div class="relative md:flex-shrink-0">
            <video autoplay muted playsinline class="h-56 w-72 rounded-xl bg-slate-900 object-cover"></video>
            <canvas class="capture-canvas hidden"></canvas>
            <div class="mt-3 flex gap-2">
                <button type="button" class="js-start-camera rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-600">
                    Start Camera
                </button>
                <button type="button" disabled class="js-capture-sample rounded-lg bg-maroon-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-maroon-700 disabled:cursor-not-allowed disabled:opacity-50">
                    Capture Face Sample
                </button>
            </div>
            <p class="js-capture-status mt-2 text-sm text-slate-500 dark:text-slate-400">Camera is off. Click "Start Camera" to begin.</p>
        </div>

        <div class="flex-1">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Captured samples</p>
            <div class="js-samples mt-3 flex flex-wrap gap-3">
                @if (($existingSamples ?? 0) > 0)
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        {{ $existingSamples }} sample(s) already stored for this student. You only need to capture new
                        samples if you want to replace the existing ones.
                    </p>
                @else
                    <p class="text-sm text-slate-500 dark:text-slate-400">No samples yet. At least one is required.</p>
                @endif
            </div>
            <p class="mt-4 text-xs text-slate-400 dark:text-slate-500">
                Tip: capture 3-5 samples per student, looking slightly left / center / right, in good lighting.
                The descriptors (not the photos) are what the scanner uses to recognize the student.
            </p>
        </div>
    </div>

    <input type="hidden" name="face_data" class="js-face-data" value="">
    <input type="hidden" name="photo_data" class="js-photo-data" value="">
</div>

<script src="{{ asset('js/face-api.min.js') }}"></script>
<script src="{{ asset('js/face-enroll.js') }}"></script>
