/*
 * Face enrollment capture widget.
 * Uses face-api.js (TinyFaceDetector + Landmark68 + Recognition) to compute
 * 128-dimension face descriptors from the webcam, then stores them in a
 * hidden input as JSON for the server to save with the student record.
 */
(function () {
    'use strict';

    var root = document.getElementById('face-enroll');
    if (!root) return;

    var modelsUrl = root.dataset.modelsUrl;
    var allowEmpty = root.dataset.allowEmpty === 'true';
    var video = root.querySelector('video');
    var canvas = root.querySelector('canvas.capture-canvas');
    var startBtn = root.querySelector('.js-start-camera');
    var captureBtn = root.querySelector('.js-capture-sample');
    var statusEl = root.querySelector('.js-capture-status');
    var samplesEl = root.querySelector('.js-samples');
    var countEl = root.querySelector('.js-sample-count');
    var faceInput = root.querySelector('.js-face-data');
    var photoInput = root.querySelector('.js-photo-data');
    var form = root.closest('form');

    var samples = [];

    function setStatus(text, tone) {
        statusEl.textContent = text;
        statusEl.className = 'js-capture-status mt-2 text-sm ' + ({
            info: 'text-slate-500 dark:text-slate-400',
            ok: 'text-green-600 dark:text-green-400',
            error: 'text-red-600 dark:text-red-400',
        }[tone || 'info']);
    }

    function renderSamples() {
        countEl.textContent = samples.length;
        samplesEl.innerHTML = '';

        samples.forEach(function (sample, index) {
            var thumb = document.createElement('div');
            thumb.className = 'relative';
            thumb.innerHTML =
                '<img src="' + sample.photo + '" alt="Face sample ' + (index + 1) + '" class="h-20 w-20 rounded-lg border border-slate-300 object-cover dark:border-slate-600">' +
                '<button type="button" data-index="' + index + '" class="absolute -right-1.5 -top-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-red-600 text-xs font-bold text-white shadow js-remove-sample">&times;</button>';
            samplesEl.appendChild(thumb);
        });

        faceInput.value = JSON.stringify(samples.map(function (s) {
            return s.descriptor;
        }));

        if (samples.length > 0) {
            photoInput.value = samples[samples.length - 1].photo;
        }
    }

    samplesEl.addEventListener('click', function (event) {
        var button = event.target.closest('.js-remove-sample');
        if (!button) return;
        samples.splice(parseInt(button.dataset.index, 10), 1);
        renderSamples();
    });

    startBtn.addEventListener('click', async function () {
        startBtn.disabled = true;
        setStatus('Starting camera and loading face models...', 'info');

        try {
            var stream = await navigator.mediaDevices.getUserMedia({ video: { width: 480, height: 360, facingMode: 'user' } });
            video.srcObject = stream;
            await video.play();

            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri(modelsUrl),
                faceapi.nets.faceLandmark68Net.loadFromUri(modelsUrl),
                faceapi.nets.faceRecognitionNet.loadFromUri(modelsUrl),
            ]);

            captureBtn.disabled = false;
            setStatus('Camera ready. Look straight into the camera and capture 3+ samples from slightly different angles.', 'ok');
        } catch (error) {
            startBtn.disabled = false;
            setStatus('Could not start the camera or load face models: ' + error.message, 'error');
        }
    });

    captureBtn.addEventListener('click', async function () {
        captureBtn.disabled = true;
        setStatus('Detecting face...', 'info');

        try {
            var result = await faceapi
                .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.5 }))
                .withFaceLandmarks()
                .withFaceDescriptor();

            if (!result) {
                setStatus('No face detected. Make sure your face is clearly visible and well lit.', 'error');
                captureBtn.disabled = false;
                return;
            }

            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);

            samples.push({
                descriptor: Array.from(result.descriptor),
                photo: canvas.toDataURL('image/jpeg', 0.85),
            });

            renderSamples();
            setStatus('Sample ' + samples.length + ' captured. ' + (samples.length < 3 ? 'Capture a few more for better accuracy.' : 'Great! You have enough samples.'), 'ok');
        } catch (error) {
            setStatus('Capture failed: ' + error.message, 'error');
        }

        captureBtn.disabled = false;
    });

    if (form) {
        form.addEventListener('submit', function (event) {
            if (samples.length === 0 && !allowEmpty) {
                event.preventDefault();
                setStatus('At least one face sample is required before saving.', 'error');
                alert('Please capture at least one face sample from the webcam before saving the student.');
                return;
            }

            faceInput.value = JSON.stringify(samples.map(function (s) {
                return s.descriptor;
            }));
        });
    }
})();
