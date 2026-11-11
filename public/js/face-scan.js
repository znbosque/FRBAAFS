/*
 * Live face recognition scanner.
 * Grabs a frame from the webcam every SCAN_INTERVAL ms, computes a 128-d
 * descriptor with face-api.js and POSTs it to /attendance/recognize.
 * The server matches the descriptor against enrolled students, records
 * the attendance and queues the parent SMS.
 */
(function () {
    'use strict';

    var root = document.getElementById('face-scan');
    if (!root) return;

    var modelsUrl = root.dataset.modelsUrl;
    var recognizeUrl = root.dataset.recognizeUrl;
    var csrfToken = root.dataset.csrf;
    var scanInterval = parseInt(root.dataset.scanInterval || '2000', 10);

    var video = root.querySelector('video');
    var overlay = root.querySelector('canvas.scan-overlay');
    var startBtn = root.querySelector('.js-start-scan');
    var statusEl = root.querySelector('.js-scan-status');
    var resultEl = root.querySelector('.js-scan-result');
    var logEl = root.querySelector('.js-scan-log');

    var scanning = false;
    var busy = false;
    var timer = null;
    var coolDownUntil = 0;
    var seenToday = {};

    function setStatus(text, tone) {
        statusEl.textContent = text;
        statusEl.className = 'js-scan-status mt-2 text-sm ' + ({
            info: 'text-slate-500 dark:text-slate-400',
            ok: 'text-green-600 dark:text-green-400',
            error: 'text-red-600 dark:text-red-400',
        }[tone || 'info']);
    }

    function addLog(entry) {
        var row = document.createElement('tr');
        row.className = 'js-scan-log-entry';
        row.innerHTML =
            '<td class="px-6 py-3 font-medium text-slate-900 dark:text-white">' + entry.name + '</td>' +
            '<td class="px-6 py-3 text-slate-500 dark:text-slate-400">' + entry.time + '</td>' +
            '<td class="px-6 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-semibold ' + (entry.late
                ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/10 dark:text-yellow-400'
                : 'bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-400') + '">' + entry.status + '</span></td>' +
            '<td class="px-6 py-3 text-slate-500 dark:text-slate-400">' + entry.event + '</td>';
        logEl.prepend(row);

        while (logEl.querySelectorAll('tr').length > 15) {
            logEl.querySelector('tr:last-child').remove();
        }
    }

    function showResult(data) {
        var tone = {
            checked_in: 'border-green-300 bg-green-50 dark:border-green-500/40 dark:bg-green-500/10',
            checked_out: 'border-blue-300 bg-blue-50 dark:border-blue-500/40 dark:bg-blue-500/10',
            already: 'border-slate-300 bg-slate-50 dark:border-slate-600 dark:bg-slate-700/50',
            not_recognized: 'border-red-300 bg-red-50 dark:border-red-500/40 dark:bg-red-500/10',
        }[data.status] || 'border-slate-300 bg-slate-50';

        var inner = data.status === 'not_recognized'
            ? '<p class="font-semibold text-red-700 dark:text-red-400">Face not recognized</p>'
            : '<p class="font-semibold text-slate-900 dark:text-white">' + data.student.full_name + '</p>' +
              '<p class="text-sm text-slate-600 dark:text-slate-400">ID: ' + data.student.student_id +
              (data.attendance ? ' &middot; In: ' + (data.attendance.time_in || '-') + (data.attendance.time_out ? ' &middot; Out: ' + data.attendance.time_out : '') : '') + '</p>';

        resultEl.className = 'js-scan-result mt-4 rounded-xl border p-4 ' + tone;
        resultEl.innerHTML = inner +
            '<p class="mt-2 text-sm font-medium ' + (data.status === 'not_recognized' ? 'text-red-700 dark:text-red-400' : 'text-slate-700 dark:text-slate-300') + '">' + data.message + '</p>' +
            (data.status === 'checked_in'
                ? '<p class="mt-1 text-xs text-slate-500 dark:text-slate-400">An SMS notification is being sent to the parents\' number on file.</p>'
                : '');
    }

    function drawBox(box) {
        var ratio = overlay.width / overlay.getBoundingClientRect().width;
        var ctx = overlay.getContext('2d');
        ctx.clearRect(0, 0, overlay.width, overlay.height);

        if (!box) return;

        ctx.strokeStyle = '#34d399';
        ctx.lineWidth = 3 * ratio;
        ctx.strokeRect(box.x * ratio, box.y * ratio, box.width * ratio, box.height * ratio);
    }

    async function scan() {
        if (busy || !scanning || Date.now() < coolDownUntil) return;

        busy = true;

        try {
            var result = await faceapi
                .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.5 }))
                .withFaceLandmarks()
                .withFaceDescriptor();

            overlay.width = video.videoWidth;
            overlay.height = video.videoHeight;
            drawBox(result ? result.detection.box : null);

            if (!result) {
                busy = false;
                return;
            }

            var response = await fetch(recognizeUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ descriptor: Array.from(result.descriptor) }),
            });

            var data = await response.json();

            if (response.ok && data.student) {
                coolDownUntil = Date.now() + 4000;

                if (!seenToday[data.student.id] || data.status === 'checked_in') {
                    seenToday[data.student.id] = true;

                    if (data.status === 'checked_in') {
                        addLog({
                            name: data.student.full_name,
                            time: data.attendance.time_in,
                            status: data.attendance.status === 'late' ? 'Late' : 'Present',
                            event: 'Arrived (SMS queued)',
                        });
                    }
                }
            }

            showResult(data);
        } catch (error) {
            setStatus('Scanner error: ' + error.message, 'error');
        }

        busy = false;
    }

    startBtn.addEventListener('click', async function () {
        startBtn.disabled = true;
        setStatus('Starting camera and loading face models...', 'info');

        try {
            var stream = await navigator.mediaDevices.getUserMedia({ video: { width: 640, height: 480, facingMode: 'user' } });
            video.srcObject = stream;
            await video.play();

            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri(modelsUrl),
                faceapi.nets.faceLandmark68Net.loadFromUri(modelsUrl),
                faceapi.nets.faceRecognitionNet.loadFromUri(modelsUrl),
            ]);

            scanning = true;
            setStatus('Scanning... position a student in front of the camera.', 'ok');
            timer = setInterval(scan, scanInterval);
        } catch (error) {
            startBtn.disabled = false;
            setStatus('Could not start the camera or load face models: ' + error.message, 'error');
        }
    });

    window.addEventListener('beforeunload', function () {
        if (timer) clearInterval(timer);
    });
})();
